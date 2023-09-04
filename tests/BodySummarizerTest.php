<?php

declare(strict_types=1);

namespace Swis\Guzzle\Sentry\Tests;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Swis\Guzzle\Sentry\BodySummarizer;

class BodySummarizerTest extends TestCase
{
    public function testItDoesNotSummarizeRequests(): void
    {
        // arrange
        $message = $this->createMock(RequestInterface::class);
        $summarizer = new BodySummarizer(100);

        // act
        $summary = $summarizer->summarize($message);

        // assert
        $this->assertNull($summary);
    }

    public function testItDoesNotSummarizeResponsesWithStatusCodeBelow400(): void
    {
        // arrange
        $message = $this->createMock(ResponseInterface::class);
        $message->method('getStatusCode')
            ->willReturn(200);
        $summarizer = new BodySummarizer(100);

        // act
        $summary = $summarizer->summarize($message);

        // assert
        $this->assertNull($summary);
    }

    public function testItDoesSummarizeResponsesWithStatusCodeOf400OrAbove(): void
    {
        // arrange
        $message = $this->createMock(ResponseInterface::class);
        $message->method('getStatusCode')
            ->willReturn(500);
        $message->method('getBody')
            ->willReturn(Utils::streamFor('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus a vestibulum felis, eu porta dolor. Curabitur ut sapien vulputate est pulvinar euismod non eget magna. Mauris eu gravida odio, at gravida nibh. Donec luctus aliquet mauris, in porta tortor porta eu. Nunc ut risus quis eros iaculis molestie. Donec sit amet arcu faucibus, pretium lectus ac, feugiat nunc. Cras nec fringilla leo. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.'));
        $summarizer = new BodySummarizer(25);

        // act
        $summary = $summarizer->summarize($message);

        // assert
        $this->assertSame('Lorem ipsum dolor sit ame (truncated...)', $summary);
    }
}
