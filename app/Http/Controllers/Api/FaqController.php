<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Repositories\FaqRepository;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public $repository;

    public function __construct(FaqRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *     path="/api/faqs",
     *     summary="Get list of FAQs",
     *     tags={"Pages"},

     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language for the response (e.g., en, ar)",
     *
     *         @OA\Schema(type="string", example="en")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of FAQs",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="question", type="string", example="What is our return policy?"),
     *                 @OA\Property(property="answer", type="string", example="Our return policy lasts 30 days."),
     *                 @OA\Property(property="type", type="string", example="general"),
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return FaqResource::collection($this->repository->getAllApis());
    }
}
