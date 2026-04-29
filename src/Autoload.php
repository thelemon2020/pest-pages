<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom;

require_once __DIR__.'/Expectations.php';

/**
 * Navigate to a page object by class name and return the typed instance.
 *
 * @template TPage of Page
 *
 * @param  class-string<TPage>  $pageClass
 * @return TPage
 */
function page(string $pageClass): Page
{
    return $pageClass::open();
}