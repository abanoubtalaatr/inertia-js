<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Setting;

class SocialController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $settings = Setting::where('group', 'social')->pluck('value', 'key');

        $data['facebook'] = $settings['facebook'] ?? '';
        $data['twitter'] = $settings['twitter'] ?? '';
        $data['snapchat'] = $settings['snapchat'] ?? '';
        $data['youtube'] = $settings['youtube'] ?? '';
        $data['instagram'] = $settings['instagram'] ?? '';

        return $this->success($data);
    }
}
