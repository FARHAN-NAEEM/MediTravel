<?php

namespace Tests\Feature;

use App\Http\Middleware\ProductionSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProductionSecurityTest extends TestCase
{
    public function test_production_redirects_http_before_sessions_to_configured_https_host(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://asianhealthconnect.com']);
        $request = Request::create('http://untrusted.example/agent/login?next=1');
        $response = app(ProductionSecurity::class)->handle($request, function () {
            $this->fail('Insecure request reached the application.');
        });
        $this->assertSame(308, $response->getStatusCode());
        $this->assertSame('https://asianhealthconnect.com/agent/login?next=1', $response->headers->get('Location'));
        $this->assertSame([], $response->headers->getCookies());
    }

    public function test_https_production_uses_canonical_links_and_preserves_stricter_headers(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://asianhealthconnect.com']);
        $request = Request::create('https://untrusted.example/agent/login');
        $response = app(ProductionSecurity::class)->handle($request, fn () => response('OK')->header('X-Frame-Options', 'DENY'));
        $this->assertSame('https://asianhealthconnect.com/agent/login', URL::to('/agent/login'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('max-age=31536000', $response->headers->get('Strict-Transport-Security'));
    }

    public function test_local_http_preview_remains_available_without_hsts(): void
    {
        $response = app(ProductionSecurity::class)->handle(Request::create('http://localhost/'), fn () => response('OK'));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Strict-Transport-Security'));
    }
}
