<?php

declare(strict_types=1);

namespace Swis\Guzzle\Sentry\Tests;

use GuzzleHttp\BodySummarizerInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sentry\Breadcrumb;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
use Swis\Guzzle\Sentry\BreadcrumbMiddleware;

class BreadcrumbMiddlewareTest extends TestCase
{
    public function testItLeavesABreadcrumbForSuccessfulRequests(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb');
        SentrySdk::setCurrentHub($hub);
        $middleware = new BreadcrumbMiddleware();

        // act
        $value = $middleware(fn () => $promise)($request, [])->wait();

        // assert
        $this->assertSame($response, $value);
    }

    public function testItLeavesABreadcrumbForUnsuccessfulRequests(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $exception = $this->createMock(GuzzleException::class);
        $promise = new Promise();
        $promise->reject($exception);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb');
        SentrySdk::setCurrentHub($hub);
        $middleware = new BreadcrumbMiddleware();

        // assert
        $this->expectExceptionObject($exception);

        // act
        $middleware(fn () => $promise)($request, [])->wait();
    }

    public function testItSetsTheCorrectCategory(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb')
            ->with(
                self::callback(fn (Breadcrumb $breadcrumb): bool => $breadcrumb->getCategory() === 'foo-bar')
            );
        SentrySdk::setCurrentHub($hub);
        $middleware = new BreadcrumbMiddleware('foo-bar');

        // act
        $middleware(fn () => $promise)($request, [])->wait();

        // assert
        // see expectations
    }

    public function testItSetsTheCorrectMessage(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb')
            ->with(
                self::callback(fn (Breadcrumb $breadcrumb): bool => $breadcrumb->getMessage() === 'foo-bar')
            );
        SentrySdk::setCurrentHub($hub);
        $middleware = new BreadcrumbMiddleware('http', 'foo-bar');

        // act
        $middleware(fn () => $promise)($request, [])->wait();

        // assert
        // see expectations
    }

    public function testItSetsTheCorrectLevel(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb')
            ->with(
                self::callback(fn (Breadcrumb $breadcrumb): bool => $breadcrumb->getLevel() === Breadcrumb::LEVEL_INFO)
            );
        SentrySdk::setCurrentHub($hub);
        $middleware = new BreadcrumbMiddleware();

        // act
        $middleware(fn () => $promise)($request, [])->wait();

        // assert
        // see expectations
    }

    public function testItSetsTheCorrectType(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb')
            ->with(
                self::callback(fn (Breadcrumb $breadcrumb): bool => $breadcrumb->getType() === Breadcrumb::TYPE_HTTP)
            );
        SentrySdk::setCurrentHub($hub);
        $middleware = new BreadcrumbMiddleware();

        // act
        $middleware(fn () => $promise)($request, [])->wait();

        // assert
        // see expectations
    }

    public function testItSetsTheMethod(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')
            ->willReturn('GET');
        $response = $this->createMock(ResponseInterface::class);
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb')
            ->with(
                self::callback(fn (Breadcrumb $breadcrumb): bool => $breadcrumb->getMetadata()['method'] === 'GET')
            );
        SentrySdk::setCurrentHub($hub);
        $middleware = new BreadcrumbMiddleware();

        // act
        $middleware(fn () => $promise)($request, [])->wait();

        // assert
        // see expectations
    }

    public function testItSetsTheUri(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $request->method('getUri')
            ->willReturn(new Uri('https://example.com'));
        $response = $this->createMock(ResponseInterface::class);
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb')
            ->with(
                self::callback(fn (Breadcrumb $breadcrumb): bool => $breadcrumb->getMetadata()['uri'] === 'https://example.com')
            );
        SentrySdk::setCurrentHub($hub);
        $middleware = new BreadcrumbMiddleware();

        // act
        $middleware(fn () => $promise)($request, [])->wait();

        // assert
        // see expectations
    }

    public function testItSetsTheRequestBody(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $request->method('getBody')
            ->willReturn(Utils::streamFor('foo-bar'));
        $response = $this->createMock(ResponseInterface::class);
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb')
            ->with(
                self::callback(fn (Breadcrumb $breadcrumb): bool => $breadcrumb->getMetadata()['requestBody'] === 'foo-bar')
            );
        SentrySdk::setCurrentHub($hub);
        $bodySummarizer = $this->createMock(BodySummarizerInterface::class);
        $bodySummarizer->method('summarize')
            ->willReturn('foo-bar');
        $middleware = new BreadcrumbMiddleware();
        $middleware->setBodySummarizer($bodySummarizer);

        // act
        $middleware(fn () => $promise)($request, [])->wait();

        // assert
        // see expectations
    }

    public function testItSetsTheStatusCode(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')
            ->willReturn(200);
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb')
            ->with(
                self::callback(fn (Breadcrumb $breadcrumb): bool => $breadcrumb->getMetadata()['statusCode'] === 200)
            );
        SentrySdk::setCurrentHub($hub);
        $middleware = new BreadcrumbMiddleware();

        // act
        $middleware(fn () => $promise)($request, [])->wait();

        // assert
        // see expectations
    }

    public function testItSetsTheResponseBody(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')
            ->willReturn(Utils::streamFor('foo-bar'));
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb')
            ->with(
                self::callback(fn (Breadcrumb $breadcrumb): bool => $breadcrumb->getMetadata()['responseBody'] === 'foo-bar')
            );
        SentrySdk::setCurrentHub($hub);
        $bodySummarizer = $this->createMock(BodySummarizerInterface::class);
        $bodySummarizer->method('summarize')
            ->willReturn('foo-bar');
        $middleware = new BreadcrumbMiddleware();
        $middleware->setBodySummarizer($bodySummarizer);

        // act
        $middleware(fn () => $promise)($request, [])->wait();

        // assert
        // see expectations
    }

    public function testItSetsTheTime(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb')
            ->with(
                self::callback(fn (Breadcrumb $breadcrumb): bool => is_string($breadcrumb->getMetadata()['time']))
            );
        SentrySdk::setCurrentHub($hub);
        $middleware = new BreadcrumbMiddleware();

        // act
        $middleware(fn () => $promise)($request, [])->wait();

        // assert
        // see expectations
    }

    public function testItFiltersRedactedStrings(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $request->method('getUri')
            ->willReturn(new Uri('https://example.com?auth=api-key'));
        $request->method('getBody')
            ->willReturn(Utils::streamFor('foo-bar secret'));
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')
            ->willReturn(Utils::streamFor('foo-bar secret'));
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb')
            ->with(
                self::callback(function (Breadcrumb $breadcrumb): bool {
                    $metadata = $breadcrumb->getMetadata();

                    return $metadata['uri'] === 'https://example.com?auth=[FILTERED]'
                        && $metadata['requestBody'] === 'foo-bar [FILTERED]'
                        && $metadata['responseBody'] === 'foo-bar [FILTERED]';
                })
            );
        SentrySdk::setCurrentHub($hub);
        $bodySummarizer = $this->createMock(BodySummarizerInterface::class);
        $bodySummarizer->method('summarize')
            ->willReturn('foo-bar secret');
        $middleware = new BreadcrumbMiddleware('http', 'Message', ['secret', 'api-key']);
        $middleware->setBodySummarizer($bodySummarizer);

        // act
        $middleware(fn () => $promise)($request, [])->wait();

        // assert
        // see expectations
    }

    public function testBodySummarizerCanBeDisabled(): void
    {
        // arrange
        $request = $this->createMock(RequestInterface::class);
        $request->method('getBody')
            ->willReturn(Utils::streamFor('foo-bar'));
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')
            ->willReturn(Utils::streamFor('foo-bar'));
        $response->method('getStatusCode')
            ->willReturn(500);
        $promise = new Promise();
        $promise->resolve($response);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('addBreadcrumb')
            ->with(
                self::callback(fn (Breadcrumb $breadcrumb): bool => !isset($breadcrumb->getMetadata()['requestBody'], $breadcrumb->getMetadata()['responseBody']))
            );
        SentrySdk::setCurrentHub($hub);
        $middleware = new BreadcrumbMiddleware('http', 'Message', [], null);

        // act
        $middleware(fn () => $promise)($request, [])->wait();

        // assert
        // see expectations
    }
}
