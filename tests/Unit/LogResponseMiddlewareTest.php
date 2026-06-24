<?php

namespace Tests\Unit;

use App\Http\Middleware\LogResponseMiddleware;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class LogResponseMiddlewareTest extends TestCase
{
    #[Test]
    public function it_logs_method_uri_content_type_status_and_response_time(): void
    {
        $stream = fopen('php://memory', 'w+');

        $middleware = new LogResponseMiddleware($stream);

        $request = Request::create('/api/health', 'GET');

        $middleware->handle(
            $request,
            fn (): Response => response('', 200, ['Content-Type' => 'application/json']),
        );

        rewind($stream);
        $line = stream_get_contents($stream);
        fclose($stream);

        $this->assertMatchesRegularExpression(
            '/^GET \/api\/health application\/json 200 \d+$/',
            trim($line),
        );
    }
}
