<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContactRequest;
use App\Models\Contact;

class ContactController extends Controller
{
    use ApiResponse;

    public function store(ContactRequest $request)
    {
        Contact::create($request->validated());

        return $this->success([], 'Send successfully.');
    }
}
