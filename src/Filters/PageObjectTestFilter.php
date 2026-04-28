<?php

declare(strict_types=1);

namespace Thelemon2020\PestPages\Filters;

use Closure;
use Pest\Browser\Plugin as BrowserPlugin;
use Pest\Browser\ServerManager;
use Pest\Contracts\TestCaseMethodFilter;
use Pest\Factories\TestCaseMethodFactory;
use Pest\Plugins\Parallel;
use Pest\Support\Backtrace;
use ReflectionException;
use ReflectionFunction;

/**
 * Marks tests as browser tests when they use the page() helper or a Page::open() call,
 * so the Playwright server is started even without a literal visit() in the closure.
 */
final readonly class PageObjectTestFilter implements TestCaseMethodFilter
{
    public function accept(TestCaseMethodFactory $factory): bool
    {
        if (! $this->isPageObjectTest($factory->closure ?? fn (): null => null)) {
            return true;
        }

        $factory->proxies->add(
            $factory->filename,
            Backtrace::line(),
            '__markAsBrowserTest',
            [],
        );

        if (Parallel::isWorker() === false && BrowserPlugin::$booted === false) {
            BrowserPlugin::$booted = true;

            ServerManager::instance()->playwright()->start();
        }

        return true;
    }

    /**
     * Returns true if the closure contains a page() call or a ::open() call on a Page subclass.
     */
    private function isPageObjectTest(Closure $closure): bool
    {
        try {
            $ref = new ReflectionFunction($closure);
        } catch (ReflectionException) {
            return false;
        }

        $file = $ref->getFileName();

        if ($file === false) {
            return false;
        }

        $lines = file($file);

        if (! is_array($lines)) {
            return false;
        }

        $startLine = $ref->getStartLine();
        $endLine = $ref->getEndLine();

        if ($startLine < 1 || $endLine > count($lines)) {
            return false;
        }

        $code = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));
        $tokens = token_get_all('<?php '.$code);
        $count = count($tokens);

        for ($i = 1; $i < $count - 1; $i++) {
            if (! is_array($tokens[$i]) || ! in_array($tokens[$i][0], [T_STRING, T_NAME_FULLY_QUALIFIED], true)) {
                continue;
            }

            $tokenValue = mb_strtolower(mb_ltrim($tokens[$i][1], '\\'));
            $next = $tokens[$i + 1] ?? null;
            $prev = $tokens[$i - 1] ?? null;

            if ($next !== '(') {
                continue;
            }

            // page(ClassName::class) — bare function call preceded by whitespace
            if ($tokenValue === 'page' && is_array($prev) && $prev[0] === T_WHITESPACE) {
                return true;
            }

            // SomePage::open() — static call on a class
            if ($tokenValue === 'open' && is_array($prev) && $prev[0] === T_DOUBLE_COLON) {
                return true;
            }
        }

        return false;
    }
}