<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown for any Gemini failure (network, API error, malformed/invalid JSON).
 * Callers should catch this and show a human-friendly message — never let the
 * raw exception message (which may include API details) reach the user directly.
 */
class GeminiException extends Exception
{
    public static function apiUnavailable(string $detail = ''): self
    {
        return new self("Gemini API is unavailable right now.{$detail}");
    }

    public static function rateLimited(): self
    {
        return new self('Gemini API rate limit reached.');
    }

    public static function timeout(): self
    {
        return new self('Gemini API request timed out.');
    }

    public static function malformedResponse(string $detail = ''): self
    {
        return new self("Gemini returned a response that could not be parsed.{$detail}");
    }

    public static function invalidData(string $detail = ''): self
    {
        return new self("Gemini returned data that failed validation.{$detail}");
    }
}
