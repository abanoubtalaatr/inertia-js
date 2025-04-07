<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Repositories\PageRepositoryInterface;

class PageController extends Controller
{
    use ApiResponse;

    protected $repository;

    public function __construct(PageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function show(Page $slug)
    {
        $page = $this->repository->getPageWithSections($slug);

        return $this->success($page);
    }
}
