<?php

namespace Fido\PHPXray;

use Webmozart\Assert\Assert;

class TraceSingletonAccessor
{
    private static ?Trace $instance = null;

    public static function getInstance(): Trace
    {
        Assert::notNull(self::$instance, "Trace instance not found.");

        return self::$instance;
    }

    public static function hasInstance(): bool
    {
        return null !== self::$instance;
    }

    public static function setInstance(Trace $instance): void
    {
        self::$instance = $instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
