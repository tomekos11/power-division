<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class LogResponseMiddleware
{
    /** @var resource|null */
    private mixed $defaultOutputStream = null;

    /**
     * @param  resource|null  $outputStream
     */
    public function __construct(
        private mixed $outputStream = null,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = hrtime(true);

        /** @var Response $response */
        $response = $next($request);

        if ($this->outputStream === null && defined('PHPUNIT_COMPOSER_INSTALL')) {
            return $response;
        }

        $this->logLine(
            $request->method(),
            $request->getRequestUri(),
            $response->headers->get('Content-Type') ?? '-',
            $response->getStatusCode(),
            (int) round((hrtime(true) - $startedAt) / 1_000_000),
        );

        return $response;
    }

    private function logLine(
        string $method,
        string $uri,
        string $contentType,
        int $statusCode,
        int $responseTimeMs,
    ): void {
        $line = sprintf(
            '%s %s %s %d %d',
            $method,
            $uri,
            $contentType,
            $statusCode,
            $responseTimeMs,
        );

        $stream = $this->outputStream ?? $this->defaultOutputStream();

        fwrite($stream, $line.PHP_EOL);
    }

    /**
     * @return resource
     */
    private function defaultOutputStream(): mixed
    {
        if ($this->defaultOutputStream === null) {
            $this->defaultOutputStream = fopen('php://stdout', 'w');
        }

        return $this->defaultOutputStream;
    }
}
