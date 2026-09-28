<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppSettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::query()->first();

        return view('dashboard.settings.whatsapp', compact('setting'));
    }

    public function logs()
    {
        $messages = WhatsAppMessage::query()
            ->with('order')
            ->latest()
            ->paginate(30);

        return view('dashboard.settings.whatsapp-logs', compact('messages'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'whatsapp_enabled' => 'nullable|boolean',
            'whatsapp_base_url' => 'nullable|url|max:255',
            'whatsapp_username' => 'nullable|string|max:100',
            'whatsapp_password' => 'nullable|string|max:255',
            'whatsapp_device_id' => 'nullable|string|max:100',
            'whatsapp_staff_phone' => 'nullable|string|max:30',
        ]);

        $setting = Setting::query()->first() ?? new Setting();
        $setting->whatsapp_enabled = $request->boolean('whatsapp_enabled');
        $setting->whatsapp_base_url = $data['whatsapp_base_url'] ?? null;
        $setting->whatsapp_username = $data['whatsapp_username'] ?? null;
        $setting->whatsapp_device_id = $data['whatsapp_device_id'] ?? null;
        $setting->whatsapp_staff_phone = $data['whatsapp_staff_phone'] ?? null;

        if (filled($data['whatsapp_password'] ?? null)) {
            $setting->whatsapp_password = $data['whatsapp_password'];
        }

        $setting->save();

        return redirect()
            ->route('dashboard.settings.whatsapp')
            ->with('success', 'تم حفظ إعدادات واتساب');
    }

    public function test(WhatsAppService $whatsApp)
    {
        $setting = Setting::query()->first();
        $result = $whatsApp->sendTest($setting ?? new Setting());

        return redirect()
            ->route('dashboard.settings.whatsapp')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
};
