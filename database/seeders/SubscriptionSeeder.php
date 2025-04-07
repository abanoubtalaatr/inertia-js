<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run()
    {
        $subscriptions = [
            [
                'duration' => 1,
                'duration_type' => 'year',
                'amount' => 1000,
                'renewal_amount' => 800,
                'admin_commission' => 15,
                'is_active' => true,
                'translations' => [
                    'ar' => [
                        'name' => 'اشتراك سنة واحدة',
                        'plan_details' => 'الخطة الأساسية لمدة سنة واحدة',
                    ],
                    'en' => [
                        'name' => '1 Year Subscription',
                        'plan_details' => 'Basic plan for 1 year',
                    ],
                ],
            ],
            [
                'duration' => 2,
                'duration_type' => 'year',
                'amount' => 1800,
                'renewal_amount' => 1600,
                'admin_commission' => 10,
                'is_active' => true,
                'translations' => [
                    'ar' => [
                        'name' => 'اشتراك سنتين',
                        'plan_details' => 'الخطة المميزة لمدة سنتين',
                    ],
                    'en' => [
                        'name' => '2 Years Subscription',
                        'plan_details' => 'Premium plan for 2 years',
                    ],
                ],
            ],
            [
                'duration' => 3,
                'duration_type' => 'year',
                'amount' => 2800,
                'renewal_amount' => 2400,
                'admin_commission' => 5,
                'is_active' => true,
                'translations' => [
                    'ar' => [
                        'name' => 'اشتراك ثلاث سنين',
                        'plan_details' => 'الخطة المميزة لمدة ثلاث سنوات',
                    ],
                    'en' => [
                        'name' => '3 Years Subscription',
                        'plan_details' => 'Premium plan for 3 years',
                    ],
                ],
            ],
        ];

        foreach ($subscriptions as $data) {
            $subscription = Subscription::updateOrCreate(
                [
                    'duration' => $data['duration'],
                ],
                [
                    'amount' => $data['amount'],
                    'renewal_amount' => $data['renewal_amount'],
                    'admin_commission' => $data['admin_commission'],
                    'is_active' => $data['is_active'],
                ]
            );

            foreach ($data['translations'] as $locale => $translation) {
                $subscription->translateOrNew($locale)->fill($translation);
            }

            $subscription->save();
        }
    }
}
