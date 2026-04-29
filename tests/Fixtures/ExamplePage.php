<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom\Tests\Fixtures;

use Thelemon2020\PestPom\Page;

class ExamplePage extends Page
{
    public static function url(): string
    {
        return '/example';
    }
}