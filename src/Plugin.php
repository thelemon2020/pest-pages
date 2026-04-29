<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom;

use Pest\Contracts\Plugins\Bootable;
use Pest\TestSuite;
use Thelemon2020\PestPom\Filters\PageObjectTestFilter;

final class Plugin implements Bootable
{
    public function boot(): void
    {
        TestSuite::getInstance()
            ->tests
            ->addTestCaseMethodFilter(new PageObjectTestFilter);
    }
}