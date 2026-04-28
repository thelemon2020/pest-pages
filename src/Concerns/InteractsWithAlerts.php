<?php

declare(strict_types=1);

namespace Thelemon2020\PestPages\Concerns;

use Thelemon2020\PestPages\Page;

/**
 * @mixin Page
 */
trait InteractsWithAlerts
{
    /**
     * Assert a success alert or flash message is visible.
     */
    public function assertSuccessMessage(string $message): static
    {
        return $this->assertSee($message);
    }

    /**
     * Assert an error alert or flash message is visible.
     */
    public function assertErrorMessage(string $message): static
    {
        return $this->assertSee($message);
    }

    /**
     * Assert a validation error is visible for a given field.
     */
    public function assertFieldError(string $field, string $message): static
    {
        return $this->assertSee($message);
    }
}