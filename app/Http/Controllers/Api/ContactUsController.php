<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactUsRequest;
use App\Repositories\ContactUsRepository;
use Illuminate\Http\JsonResponse;

class ContactUsController extends Controller
{
    private $contactUsRepository;

    public function __construct(ContactUsRepository $contactUsRepository)
    {
        $this->contactUsRepository = $contactUsRepository;
    }

    public function store(ContactUsRequest $request): JsonResponse
    {
        $contactUsRequest = $this->contactUsRepository->createContactUsRequest($request->validated());

        return response()->json([
            'message' => __('messages.done_successfuly'),
            'data' => $contactUsRequest,
        ]);

    }
}
