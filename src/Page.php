<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom;

use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\PendingAwaitablePage;
use Pest\Browser\Support\ComputeUrl;

/**
 * Base class for all page objects.
 *
 * Wraps the Pest browser page returned by visit() and delegates method calls
 * to it, keeping fluent chaining typed to the concrete page subclass.
 */
abstract class Page
{
    /**
     * The underlying browser page. Starts as PendingAwaitablePage after visit(),
     * then transitions to AwaitableWebpage once the first method is called.
     */
    protected PendingAwaitablePage|AwaitableWebpage $browser;

    final public function __construct(PendingAwaitablePage|AwaitableWebpage $browser)
    {
        $this->browser = $browser;
    }

    /**
     * The URL path this page represents.
     */
    abstract public static function url(): string;

    /**
     * Navigate to this page and return a typed instance.
     *
     * Enforces that the page class lives in the configured pages directory.
     */
    public static function open(): static
    {
        Config::assertPageIsInConfiguredDirectory(static::class);

        return new static(visit(static::url()));
    }

    /**
     * Create a typed Component instance backed by this page's browser session.
     *
     *
     * @param  class-string<Component>  $componentClass
     * @return Component
     */
    public function component(string $componentClass): Component
    {
        return new $componentClass($this->browser);
    }

    /**
     * Re-wrap the current browser session as a different page type without navigating.
     * Use after an action that causes a server-side redirect (e.g. form submit).
     * Throws if the browser's current URL path does not match the page's URL.
     **
     * @param  class-string<Page>  $pageClass
     * @return Page
     */
    public function nowOn(string $pageClass): Page
    {
        $currentUrl = $this->currentBrowserUrl();

        if ($currentUrl !== null) {
            $currentPath = rtrim((string) parse_url($currentUrl, PHP_URL_PATH), '/');
            $expectedPath = rtrim((string) parse_url($pageClass::url(), PHP_URL_PATH), '/');

            if ($currentPath !== $expectedPath) {
                throw new \RuntimeException(
                    "Expected to be on [{$expectedPath}] but the browser is at [{$currentPath}]."
                );
            }
        }

        return new $pageClass($this->browser);
    }

    /**
     * Returns the browser's current URL, or null when the browser has not yet been resolved.
     * Extracted so tests can override it without needing a live Playwright connection.
     */
    protected function currentBrowserUrl(): ?string
    {
        if (! $this->browser instanceof AwaitableWebpage) {
            return null;
        }

        return $this->browser->page()->url();
    }

    /**
     * Explicitly navigate to a different page class.
     * Use when an action (e.g. submitting a form) takes you to a new screen.
     **
     * @param  class-string<Page>  $pageClass
     * @return Page
     */
    public function navigateTo(string $pageClass): Page
    {
        // When the browser is already resolved, reuse the existing Playwright page
        // (and its context) so cookies/session/auth state are preserved.
        // Calling visit() would create a new browser context (fresh incognito), losing the session.
        if ($this->browser instanceof AwaitableWebpage) {
            $url = ComputeUrl::from($pageClass::url());
            $this->browser->page()->goto($url);

            return new $pageClass(new AwaitableWebpage($this->browser->page(), $url));
        }

        return new $pageClass($this->createVisit($pageClass::url()));
    }

    /**
     * Performs a fresh browser visit. Extracted so tests can override it
     * without starting a real Playwright connection.
     */
    protected function createVisit(string $url): PendingAwaitablePage
    {
        return visit($url);
    }

    /**
     * Delegates all calls to the underlying Pest browser page.
     *
     * When the browser returns itself (chainable methods like click, type,
     * assertSee, etc.), we update our internal reference and return $this so
     * the chain stays typed to the concrete Page subclass.
     *
     * @param  array<int, mixed>  $args
     */
    public function __call(string $method, array $args): mixed
    {
        $result = $this->browser->$method(...$args);

        if ($result instanceof AwaitableWebpage) {
            $this->browser = $result;

            return $this;
        }

        return $result;
    }

}