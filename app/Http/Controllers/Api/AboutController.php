<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AboutController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $data['first_image'] = 'https://s3-alpha-sig.figma.com/img/0b29/4526/a81009fbe007bbff89cfe72bf3ee597a?Expires=1745193600&Key-Pair-Id=APKAQ4GOSFWCW27IBOMQ&Signature=i~7bMmjhOQJew-83skBAW97I44JBuKlOU9Z7476IRjEEACTWWZRwQK9ByDcs7j0Ror6yYEVFpUuRJQ5xZk39rOoy7HMGTU6eqgAN6MzQZkjmaVZe-zrWI4yNKWfQo7ikLsbIzDWbUwtzyFvOzUoUDKIM9F02feE8eYjXwEqbdt-pTWvt75AFe6AXyVZPu07yakqn891MsDIdpo-5NZuwMID58jTL-Boa6AXflLLkz425R-XTzsTj5fZNNGhPhdEqsRyF2V5G5jG2W3oC-lA7KncX2BoinlDa6yJJOAc9yoNHqLnMSIZvLmBN7VshMPWkcXvjBCZ8rXVL1O29p1~ypg__';
        $data['second_image'] = 'https://s3-alpha-sig.figma.com/img/1a7e/0346/87135ad3b6fdc58de028632722a939eb?Expires=1745193600&Key-Pair-Id=APKAQ4GOSFWCW27IBOMQ&Signature=J0-3a2Ti-m9mKdhfpM4LRHcZWcnr1n-GEe8FBa3~uwr9VJJ6U-7xYsXDEhXBrDUIZNcvJwLOGyWWBEvmZ54d1ACbyNFT9ETtM4Ylf9eXR3o2MCj8Y4UrbjAbAtY75VSd8pPs-U1fQWAx-jqlGDsjWo6TgVyXP2odrHuPd7QYRJUylD1LtKjtuG526zb7pft0fvyw9z9M1cbx1-KeX7Sq5fHgySlFSK9jls3wdE86VVRgrecq3Lc8fiT0LApZPHhMWoeGZzHdk7LEUs22DLaLUNPr8s9xIzCcAIcaQHp2RE77xsT1gjzREGoQJwRnGupZTW~Jp79pbIiZinPyLq2jsg__';
       
        if (app()->getLocale() === 'ar') {
            $data['data'] = [
                [
                    'title' => "1- استشارات نفسية موثوقة",
                    'description' => "يضم بيت الفانوس نخبة من الأطباء النفسيين المعتمدين لمساعدتك في رحلتك نحو التوازن النفسي. نقدم استشارات فردية بسرية تامة وفق أعلى المعايير المهنية.",
                ],
                [
                    'title' => "2- تواصل سهل ومريح",
                    'description' => "نوفر لك طرقًا مرنة لحجز الجلسات والتواصل مع الأطباء عبر الفيديو أو الدردشة النصية، مما يضمن لك تجربة سلسة دون الحاجة إلى التنقل.",
                ],
                [
                    'title' => "3- دعم متكامل للصحة النفسية",
                    'description' => "بجانب الاستشارات، نقدم موارد تثقيفية وأدوات عملية تساعدك على تحسين صحتك النفسية واتخاذ خطوات إيجابية نحو حياة أكثر راحة وسعادة.",
                ],
            ];
        } else {
            $data['data'] = [
                [
                    'title' => "1- Trusted Psychological Consultations",
                    'description' => "Beit Al-Fanoos hosts a selection of certified psychiatrists to help you in your journey toward mental balance. We offer individual consultations with complete confidentiality and the highest professional standards.",
                ],
                [
                    'title' => "2- Easy and Convenient Communication",
                    'description' => "We provide flexible ways to book sessions and communicate with doctors via video or text chat, ensuring a smooth experience without the need for travel.",
                ],
                [
                    'title' => "3- Comprehensive Mental Health Support",
                    'description' => "In addition to consultations, we offer educational resources and practical tools to help you improve your mental health and take positive steps toward a more comfortable and happy life.",
                ],
            ];
        }

        return $this->success($data);
    }
}
