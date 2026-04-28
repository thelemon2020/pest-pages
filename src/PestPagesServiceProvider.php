<?php

declare(strict_types=1);

namespace Thelemon2020\PestPages;

use Illuminate\Support\ServiceProvider;
use Thelemon2020\PestPages\Console\MakePageCommand;

final class PestPagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([MakePageCommand::class]);
        }
    }
}