<?php

declare(strict_types=1);

use Thelemon2020\PestPom\Tests\Fixtures\ExamplePage;
use Thelemon2020\PestPom\Tests\Fixtures\ParameterizedPage;

it('returns the url unchanged when no parameters are given', function () {
    expect(ExamplePage::resolveUrl())->toBe('/example');
});

it('returns the url unchanged when the url has no placeholders', function () {
    expect(ExamplePage::resolveUrl(['id' => 5]))->toBe('/example');
});

it('substitutes a single placeholder', function () {
    expect(ParameterizedPage::resolveUrl(['id' => 42]))->toBe('/products/42');
});

it('substitutes multiple placeholders', function () {
    $instance = new class(pendingBrowser()) extends \Thelemon2020\PestPom\Page {
        public static function url(): string { return '/users/{userId}/posts/{postId}'; }
    };

    expect($instance::resolveUrl(['userId' => 3, 'postId' => 99]))->toBe('/users/3/posts/99');
});

it('casts parameter values to strings', function () {
    expect(ParameterizedPage::resolveUrl(['id' => 7]))->toBe('/products/7');
});
