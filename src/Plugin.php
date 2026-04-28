<?php

declare(strict_types=1);

namespace Thelemon2020\PestPages;

use Pest\Contracts\Plugins\Bootable;
use Pest\TestSuite;
use Thelemon2020\PestPages\Filters\PageObjectTestFilter;

final class Plugin implements Bootable
{
    public function boot(): void
    {
        TestSuite::getInstance()
            ->tests
            ->addTestCaseMethodFilter(new PageObjectTestFilter);
    }
}