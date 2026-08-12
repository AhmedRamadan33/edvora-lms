<?php

use Illuminate\Support\Str;

if (! function_exists('__status')) {
    /**
     * Translate a machine status value (paid, pending_review, ...).
     */
    function __status(?string $status): string
    {
        return __(Str::headline((string) $status));
    }
}
