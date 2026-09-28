<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Setting;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_order_sends_whatsapp_to_client_and_staff(): void
    {
        Setting::query()->create([
            'whatsapp_enabled' => true,
            'whatsapp_base_url' => 'http://whatsapp.test',
            'whatsapp_username' => 'admin',
            'whatsapp_password' => 'secret',
            'whatsapp_device_id' => 'device-1',
            'whatsapp_staff_phone' => '966500000001',
        ]);

        $client = Client::query()->create([
            'name' => 'أحمد',
            'phone' => ['966500000002'],
            'address' => 'الخرطوم',
        ]);

        $order = Order::query()->create([
            'client_id' => $client->id,
            'order_number' => 'SU-100',
            'total_price' => 150,
            'total_after_discount' => 140,
            'paid_at_sale' => 40,
            'remaining' => 100,
            'invoice_discount' => 10,
        ]);

        Http::fake([
            'http://whatsapp.test/send/message' => Http::response(['code' => 'SUCCESS'], 200),
        ]);

        $results = app(WhatsAppService::class)->notifyNewOrder($order);

        $this->assertTrue($results['client']['ok']);
        $this->assertTrue($results['staff']['ok']);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://whatsapp.test/send/message'
                && $request->hasHeader('X-Device-Id', 'device-1')
                && $request['phone'] === '966500000002'
                && str_contains($request['message'], 'SU-100');
        });

        Http::assertSent(fn ($request) => $request['phone'] === '966500000001');
        $this->assertSame(2, WhatsAppMessage::query()->where('status', 'success')->count());
    }

    public function test_invalid_phone_is_logged_and_not_sent(): void
    {
        Setting::query()->create([
            'whatsapp_enabled' => true,
            'whatsapp_base_url' => 'http://whatsapp.test',
            'whatsapp_username' => 'admin',
            'whatsapp_password' => 'secret',
            'whatsapp_device_id' => 'device-1',
            'whatsapp_staff_phone' => 'abc',
        ]);

        $client = Client::query()->create([
            'name' => 'زبون',
            'phone' => ['12'],
            'address' => 'الخرطوم',
        ]);
        $order = Order::query()->create([
            'client_id' => $client->id,
            'order_number' => 'SU-3',
            'total_price' => 10,
            'total_after_discount' => 10,
            'paid_at_sale' => 10,
            'remaining' => 0,
        ]);

        Http::fake();

        $results = app(WhatsAppService::class)->notifyNewOrder($order);

        $this->assertFalse($results['client']['ok']);
        $this->assertFalse($results['staff']['ok']);
        Http::assertNothingSent();
        $this->assertSame(2, WhatsAppMessage::query()->where('status', 'failed')->count());
        $this->assertNotNull(WhatsAppMessage::query()->where('recipient', 'client')->value('reason'));
    }

    public function test_disabled_whatsapp_does_not_call_the_api(): void
    {
        Setting::query()->create(['whatsapp_enabled' => false]);

        $client = Client::query()->create([
            'name' => 'زبون',
            'phone' => ['966511111111'],
            'address' => 'الخرطوم',
        ]);
        $order = Order::query()->create([
            'client_id' => $client->id,
            'order_number' => 'SU-2',
            'total_price' => 10,
            'total_after_discount' => 10,
            'paid_at_sale' => 10,
            'remaining' => 0,
        ]);

        Http::fake();

        $result = app(WhatsAppService::class)->notifyNewOrder($order);

        $this->assertTrue($result['skipped']);
        Http::assertNothingSent();
    }

    public function test_saving_whatsapp_settings_clears_the_cached_setting_shown_on_the_page(): void
    {
        $setting = Setting::query()->create(['name' => 'الشركة']);
        cache()->put('app.setting', $setting, 3600);

        $setting->whatsapp_base_url = 'http://76.13.77.29:3001';
        $setting->whatsapp_username = 'admin';
        $setting->whatsapp_device_id = 'device-1';
        $setting->whatsapp_staff_phone = '966563243208';
        $setting->whatsapp_enabled = true;
        $setting->save();

        $this->assertNull(cache()->get('app.setting'));

        $fresh = Setting::query()->first();
        $this->assertSame('http://76.13.77.29:3001', $fresh->whatsapp_base_url);
        $this->assertSame('admin', $fresh->whatsapp_username);
        $this->assertSame('device-1', $fresh->whatsapp_device_id);
        $this->assertSame('966563243208', $fresh->whatsapp_staff_phone);
        $this->assertTrue($fresh->whatsapp_enabled);
    }
}
