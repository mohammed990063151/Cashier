<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function notifyNewOrder(Order $order): array
    {
        $setting = Setting::query()->first();
        $connection = $this->connection($setting);
        if (! $connection['enabled'] || ! $this->hasKeys($connection)) {
            return ['sent' => false, 'skipped' => true, 'message' => 'واتساب غير مفعّل أو المفاتيح ناقصة.'];
        }

        $order->loadMissing('client');
        $message = $this->orderMessage($order);
        $results = [];

        $clientPhone = $this->firstPhone($order->client?->phone);
        if ($clientPhone) {
            $results['client'] = $this->deliver($setting, $clientPhone, $message, 'client', $order->id);
        } else {
            $results['client'] = $this->record($order->id, 'client', null, null, $message, 'failed', 'لا يوجد رقم هاتف للعميل');
        }

        $staffPhone = (string) $connection['staff_phone'];
        if (trim($staffPhone) !== '') {
            $results['staff'] = $this->deliver($setting, $staffPhone, $message, 'staff', $order->id);
        }

        return $results;
    }

    public function sendTest(Setting $setting): array
    {
        $connection = $this->connection($setting);

        if (! $connection['enabled']) {
            return ['ok' => false, 'message' => 'فعّل «إرسال رسائل الطلبات» ثم اضغط حفظ. الاختبار لا يُرسل والخيار مطفأ.'];
        }

        if (! $this->hasKeys($connection)) {
            return ['ok' => false, 'message' => 'احفظ عنوان الخدمة واسم المستخدم وكلمة السر ومعرّف الجهاز أولاً.'];
        }

        if (trim((string) $connection['staff_phone']) === '') {
            return ['ok' => false, 'message' => 'أدخل رقم واتساب المستخدم داخل النظام ثم احفظ الإعدادات.'];
        }

        $result = $this->deliver(
            $setting,
            $connection['staff_phone'],
            'رسالة اختبار من نظام الكاشير. الربط يعمل.',
            'test',
            null
        );

        return [
            'ok' => $result['ok'],
            'message' => $result['ok']
                ? 'وصلت رسالة الاختبار إلى رقم المستخدم.'
                : ('فشل الإرسال: '.($result['error'] ?? 'خطأ غير معروف')),
        ];
    }

    public function isReady(?Setting $setting): bool
    {
        $connection = $this->connection($setting);

        return $connection['enabled'] && $this->hasKeys($connection);
    }

    /** @return array{enabled: bool, base_url: ?string, username: ?string, password: ?string, device_id: ?string, staff_phone: ?string} */
    public function connection(?Setting $setting): array
    {
        return [
            'enabled' => (bool) ($setting?->whatsapp_enabled ?? config('services.whatsapp.enabled')),
            'base_url' => $this->prefer($setting?->whatsapp_base_url, config('services.whatsapp.base_url')),
            'username' => $this->prefer($setting?->whatsapp_username, config('services.whatsapp.username')),
            'password' => $this->prefer($setting?->whatsapp_password, config('services.whatsapp.password')),
            'device_id' => $this->prefer($setting?->whatsapp_device_id, config('services.whatsapp.device_id')),
            'staff_phone' => $this->cleanStaffPhone($this->prefer($setting?->whatsapp_staff_phone, config('services.whatsapp.staff_phone'))),
        ];
    }

    public function deliver(Setting $setting, string $phone, string $message, string $recipient, ?int $orderId): array
    {
        $error = $this->phoneError($phone);
        $normalized = $error ? null : $this->normalizePhone($phone);

        if ($error) {
            $this->record($orderId, $recipient, $phone, $normalized, $message, 'failed', $error);

            return ['ok' => false, 'error' => $error];
        }

        $connection = $this->connection($setting);
        $url = rtrim((string) $connection['base_url'], '/').'/send/message';

        try {
            $response = Http::timeout(20)
                ->withBasicAuth((string) $connection['username'], (string) $connection['password'])
                ->withHeaders(['X-Device-Id' => (string) $connection['device_id']])
                ->acceptJson()
                ->post($url, [
                    'phone' => $normalized,
                    'message' => $message,
                ]);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp send failed', ['phone' => $normalized, 'error' => $e->getMessage()]);
            $this->record($orderId, $recipient, $phone, $normalized, $message, 'failed', $e->getMessage());

            return ['ok' => false, 'error' => $e->getMessage()];
        }

        $body = $response->json();
        $code = is_array($body) ? ($body['code'] ?? null) : null;
        $ok = $response->successful() && ($code === null || $code === 'SUCCESS' || $code === 200 || $code === '200');

        if (! $ok) {
            $reason = $this->apiReason($body, $response->body());
            Log::warning('WhatsApp API rejected message', ['status' => $response->status(), 'body' => $reason]);
            $this->record($orderId, $recipient, $phone, $normalized, $message, 'failed', $reason);

            return ['ok' => false, 'error' => $reason];
        }

        $this->record($orderId, $recipient, $phone, $normalized, $message, 'success', null);

        return ['ok' => true];
    }

    public function phoneError(?string $phone): ?string
    {
        $raw = trim((string) $phone);
        if ($raw === '') {
            return 'رقم الهاتف فارغ';
        }

        if (preg_match('/[^\d\s+\-().]/u', $raw)) {
            return 'رقم الهاتف يحتوي على أحرف غير مسموحة. أدخل أرقاماً فقط، مثل 0912345678 أو 249912345678';
        }

        $digits = $this->normalizePhone($raw);
        if (strlen($digits) < 11) {
            return 'رقم الهاتف قصير. أدخل الرقم مع مفتاح الدولة، مثل 249912345678';
        }

        if (strlen($digits) > 15) {
            return 'رقم الهاتف أطول من المسموح';
        }

        return null;
    }

    private function record(?int $orderId, string $recipient, ?string $phone, ?string $normalized, string $message, string $status, ?string $reason): array
    {
        WhatsAppMessage::query()->create([
            'order_id' => $orderId,
            'recipient' => $recipient,
            'phone' => $phone,
            'phone_normalized' => $normalized,
            'message' => $message,
            'status' => $status,
            'reason' => $reason,
        ]);

        return ['ok' => $status === 'success', 'error' => $reason];
    }

    private function apiReason(mixed $body, string $fallback): string
    {
        if (! is_array($body)) {
            return $fallback !== '' ? $fallback : 'رفض الخادم الرسالة';
        }

        $text = (string) ($body['message'] ?? $fallback);
        $lower = strtolower($text);
        if (str_contains($lower, 'phone') || str_contains($lower, 'jid') || str_contains($lower, 'not registered') || str_contains($text, 'رقم')) {
            return 'الرقم غير صالح أو غير مسجّل في واتساب: '.$text;
        }

        return $text !== '' ? $text : 'رفض الخادم الرسالة';
    }

    private function orderMessage(Order $order): string
    {
        $clientName = $order->client?->name ?: 'عميل';

        return implode("\n", [
            'طلب جديد '.$order->order_number,
            'العميل: '.$clientName,
            'الإجمالي: '.$order->total_after_discount,
            'المدفوع: '.$order->paid_at_sale,
            'المتبقي: '.$order->remaining,
        ]);
    }

    private function firstPhone(mixed $phones): ?string
    {
        if (is_string($phones)) {
            $phones = [$phones];
        }

        if (! is_array($phones)) {
            return null;
        }

        foreach ($phones as $phone) {
            if (is_array($phone)) {
                $phone = $phone['number'] ?? $phone['phone'] ?? null;
            }
            if (is_string($phone) && trim($phone) !== '') {
                return $phone;
            }
        }

        return null;
    }

    private function cleanStaffPhone(?string $phone): ?string
    {
        if (! filled($phone)) {
            return null;
        }

        $digits = $this->normalizePhone($phone);

        return $digits !== '' ? $digits : trim($phone);
    }

    private function prefer(mixed $saved, mixed $fallback): ?string
    {
        if (filled($saved)) {
            return (string) $saved;
        }

        return filled($fallback) ? (string) $fallback : null;
    }

    /** @param  array{base_url: ?string, username: ?string, password: ?string, device_id: ?string}  $connection */
    private function hasKeys(array $connection): bool
    {
        return filled($connection['base_url'])
            && filled($connection['username'])
            && filled($connection['password'])
            && filled($connection['device_id']);
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';
        $digits = ltrim($digits, '0');

        if (strlen($digits) === 9 && str_starts_with($digits, '9')) {
            return '249'.$digits;
        }

        return $digits;
    }
}
