<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Thrown when a weather lookup fails or the requested location is unknown.
 */
class WeatherException extends RuntimeException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = 'Weather data is unavailable.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Convenience factory for "location not found" errors.
     */
    public static function unknownLocation(string $location): self
    {
        return new self("The location \"{$location}\" could not be found.");
    }
}
