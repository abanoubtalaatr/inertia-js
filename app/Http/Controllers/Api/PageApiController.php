<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Repositories\PageRepositoryInterface;

class PageApiController extends Controller
{
    protected $repository;

    public function __construct(PageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function show($slug)
    {
        $locale = substr(request()->header('Accept-Language', 'en'), 0, 2);

        $page = $this->repository->getPageWithSections($slug, $locale);

        return new PageResource($page);
    }
}
