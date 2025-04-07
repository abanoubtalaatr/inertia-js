<?php

namespace Database\Seeders;

use App\Models\Specialization;
use Illuminate\Database\Seeder;

class SpecializationSeeder extends Seeder
{
    public function run()
    {
        $specializations = [
            [
                'name' => [
                    'en' => 'Behavioral Therapy',
                    'ar' => 'العلاج السلوكي',
                    'ur' => 'سلوک تھراپی',
                    'tl' => 'Behavioral Therapy',
                    'fr' => 'Thérapie comportementale',
                ],
                'description' => [
                    'en' => 'A specialization in treating undesirable behaviors through scientific techniques.',
                    'ar' => 'تخصص في علاج السلوكيات غير المرغوب فيها من خلال تقنيات علمية.',
                    'ur' => 'غیر مطلوب سلوک کو سائنسی طریقوں سے علاج کرنے کا تخصص',
                    'tl' => 'Isang espesyalidad sa paggamot ng mga hindi kanais-nais na pag-uugali sa pamamagitan ng mga siyentipikong pamamaraan.',
                    'fr' => 'Une spécialité dans le traitement des comportements indésirables par des techniques scientifiques.',
                ],
            ],
            [
                'name' => [
                    'en' => 'Cognitive Therapy',
                    'ar' => 'العلاج المعرفي',
                    'ur' => 'معرفی تھراپی',
                    'tl' => 'Cognitive Therapy',
                    'fr' => 'Thérapie cognitive',
                ],
                'description' => [
                    'en' => 'A specialization in changing negative thought patterns that affect an individual.',
                    'ar' => 'تخصص في تغيير الأنماط الفكرية السلبية التي تؤثر على الشخص.',
                    'ur' => 'منفی خیالات کے نمونوں کو تبدیل کرنے کا تخصص جو فرد پر اثر انداز ہوتے ہیں',
                    'tl' => 'Isang espesyalidad sa pagbabago ng mga negatibong pattern ng pag-iisip na nakakaapekto sa isang tao.',
                    'fr' => 'Une spécialité dans le changement des schémas de pensée négatifs qui affectent un individu.',
                ],
            ],
            [
                'name' => [
                    'en' => 'Child Psychotherapy',
                    'ar' => 'العلاج النفسي للأطفال',
                    'ur' => 'بچوں کے نفسیاتی علاج',
                    'tl' => 'Psychotherapy ng Bata',
                    'fr' => 'Psychothérapie pour enfants',
                ],
                'description' => [
                    'en' => 'A specialization in treating psychological disorders in children through various techniques.',
                    'ar' => 'تخصص في علاج الاضطرابات النفسية للأطفال من خلال تقنيات مختلفة.',
                    'ur' => 'مختلف تکنیکوں کے ذریعے بچوں میں نفسیاتی مسائل کا علاج کرنے کا تخصص',
                    'tl' => 'Isang espesyalidad sa paggamot ng mga sikolohikal na karamdaman sa mga bata sa pamamagitan ng iba\'t ibang mga pamamaraan.',
                    'fr' => 'Une spécialité dans le traitement des troubles psychologiques chez les enfants par diverses techniques.',
                ],
            ],
            [
                'name' => [
                    'en' => 'Adult Psychotherapy',
                    'ar' => 'العلاج النفسي للبالغين',
                    'ur' => 'بالغوں کا نفسیاتی علاج',
                    'tl' => 'Psychotherapy ng Matanda',
                    'fr' => 'Psychothérapie pour adultes',
                ],
                'description' => [
                    'en' => 'A specialization in treating psychological disorders in adults using various techniques.',
                    'ar' => 'تخصص في علاج الاضطرابات النفسية لدى البالغين باستخدام تقنيات متعددة.',
                    'ur' => 'مختلف تکنیکوں کے ذریعے بالغوں میں نفسیاتی مسائل کا علاج کرنے کا تخصص',
                    'tl' => 'Isang espesyalidad sa paggamot ng mga sikolohikal na karamdaman sa mga matatanda gamit ang iba\'t ibang mga pamamaraan.',
                    'fr' => 'Une spécialité dans le traitement des troubles psychologiques chez les adultes en utilisant diverses techniques.',
                ],
            ],
        ];

        foreach ($specializations as $specializationData) {
            // Create a specialization record (without translation)
            $specialization = Specialization::create();

            // Loop through each language for the translations
            foreach ($specializationData['name'] as $locale => $nameTranslation) {
                // Add translations for 'name' and 'description' attributes
                $specialization->translateOrNew($locale)->name = $nameTranslation;
            }

            foreach ($specializationData['description'] as $locale => $descriptionTranslation) {
                // Add translations for 'description' attribute
                $specialization->translateOrNew($locale)->description = $descriptionTranslation;
            }

            // Save the specialization with its translations
            $specialization->save();
        }
    }
}
