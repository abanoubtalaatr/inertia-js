<?php

namespace App\Providers;

use App\Interfaces\BaseInterface;
use App\Repositories\BaseRepository;
use App\Repositories\PageRepository;
use App\Repositories\PageRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {

        $this->app->bind(BaseInterface::class, BaseRepository::class);

        $this->app->bind(PageRepositoryInterface::class, PageRepository::class);
    }
}
