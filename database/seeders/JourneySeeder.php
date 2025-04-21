<?php

namespace Database\Seeders;

use App\Models\Journey;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JourneySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $lifeStyleScore = Journey::create([
            'name' => 'امتیاز سبک زندگی',
            'slug' => 'slug-style-score',
            'type' => 'once',
            'price' => 0,
            'interval' => 180,
            're_buy_interval' => 180,
            'is_active' => true,
            'dependency_id' => null,
            'description' => '',
            'summary' => null,
            'extra' => [
                [
                    'uid' => 2,
                    'slug' => '',
                    'type' => 'free-form-id',
                    'value' => 'bJsq9Qy0',
                    'name' => 'شناسه پرس لاین رایگان',
                ],
                [
                    'uid' => 3,
                    'slug' => '',
                    'type' => 'form-id',
                    'value' => 'HKVAbbzX',
                    'name' => 'شناسه پرس لاین',
                ]
            ],
        ]);
        
        $analysisScore = Journey::create([
            'name' => 'آنالیز خون لانجویتی',
            'slug' => 'longevity-score',
            'type' => 'once',
            'price' => 44_000_000,
            'interval' => 180,
            're_buy_interval' => 165,
            'is_active' => true,
            'dependency_id' => $lifeStyleScore->id,
            'description' => "<ul>
    <li>پرسشنامه‌ی شرح حال پزشکی و سبک زندگی</li>
    <li>نمونه گیری و آزمایش چکاپ خون تخصصی لانجویتی</li>
    <li>امتیاز لانجویتی و تحلیل سرعت پیری</li>
    <li>مقایسه سرعت پیری با افراد مشابه</li>
    <li>مشخص کردن مسیرهای سلولی مرتبط با پیری فعال در بدن</li>
    <li>بررسی عملکرد متابولیک، سیستم التهابی و کبد</li>
    <li>بررسی عملکرد قلب و عروق</li>
    <li>امتیاز سبک زندگی</li>
    <li>تحلیل تاثیر سبک زندگی بر سرعت پیری</li>
    <li>ارائه تحلیل دقیق بیومارکرهای آزمایش خون</li>
</ul>",
            'summary' => null,
            'extra' => [
                [
                    'uid' => 1,
                    'slug' => '',
                    'type' => 'price',
                    'value' => 44_000_000,
                    'name' => 'انجام آزمایش و نمونه‌گیری در محل',
                ]
            ],
        ]);

        $journeys = [
            [
                'name' => 'برنامه اجرایی لانجویتی',
                'slug' => 'interaction',
                'type' => 'multiple',
                'price' => 33_000_000,
                'interval' => 90,
                're_buy_interval' => 90,
                'is_active' => true,
                'dependency_id' => $analysisScore->id,
                'description' => "<ul>
    <li>برنامه‌ی اجرایی سلامت و سبک زندگی اختصاصی (تغذیه، خواب، فعالیت فیزیکی و ...) برای ۳ ماه</li>
    <li>همراهی و پشتیبانی تیم پزشکی زینوم در مسیر</li>
</ul>",
                'summary' => null,
                'extra' => null,
            ],
            [
                'name' => 'بسته مکمل شخصی‌سازی شده',
                'slug' => 'pill-pack',
                'type' => 'multiple',
                'price' => 44_000_000,
                'interval' => 28,
                're_buy_interval' => 28,
                'is_active' => true,
                'dependency_id' => $analysisScore->id,
                'description' => "<ul>
    <li>دریافت بسته مکمل‌های شخصی‌سازی شده (پیل‌پک) بر اساس آزمایش خون لانجویتی</li>
    <li>قابلیت تمدید: حداکثر برای ۶ ماه در هر آزمایش خون</li>
</ul>",
                'summary' => null,
                'extra' => null,
            ],
            [
                'name' => 'رژیم غذایی لانجویتی',
                'slug' => 'diet',
                'type' => 'multiple',
                'price' => 11_000_000,
                'interval' => 30,
                're_buy_interval' => 30,
                'is_active' => true,
                'dependency_id' => $analysisScore->id,
                'description' => "<ul>
    <li>دریافت بسته مکمل‌های شخصی‌سازی شده (پیل‌پک) بر اساس آزمایش خون لانجویتی</li>
    <li>قابلیت تمدید: حداکثر برای ۶ ماه در هر آزمایش خون</li>
</ul>",
                'summary' => null,
                'extra' => null,
            ],
            [
                'name' => 'برنامه ورزشی لانجویتی',
                'slug' => 'sport-program',
                'type' => 'multiple',
                'price' => 11_000_000,
                'interval' => 30,
                're_buy_interval' => 30,
                'is_active' => true,
                'dependency_id' => $analysisScore->id,
                'description' => '',
                'summary' => null,
                'extra' => null,
            ]
        ];

        foreach ($journeys as $journey) {
            Journey::create($journey);
        }
    }
}
