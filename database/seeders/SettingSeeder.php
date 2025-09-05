<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create([
            'name'      => 'شركة  عتاب التجارية',
            'logo'      => 'logo.png', // صورة افتراضية لازم تضعها
            'email'     => 'info@future-tech.com',
            'phone'     => '+966500000000',
            'address'   => 'الخرطوم  - جمهورية السودان',
            'facebook'  => 'https://facebook.com/futuretech',
            'twitter'   => 'https://twitter.com/futuretech',
            'instagram' => 'https://instagram.com/futuretech',
            'linkedin'  => 'https://linkedin.com/company/futuretech',
        ]);
    }
}

