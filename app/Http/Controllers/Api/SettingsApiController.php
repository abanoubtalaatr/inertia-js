<?php

namespace App\Http\Controllers\Api;

use App\Repositories\SettingsRepository;

class SettingsApiController extends AppBaseController
{
    protected $settingsRepository;

    public function __construct(SettingsRepository $settingsRepository)
    {
        $this->settingsRepository = $settingsRepository;
    }

    public function index()
    {

        $settings = $this->settingsRepository->getAllSettings();

        return $this->sendResponse($settings, 'items retrieved successfully');
    }
}
