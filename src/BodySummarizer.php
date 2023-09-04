<?php

declare(strict_types=1);

namespace Swis\Guzzle\Sentry;

use GuzzleHttp\BodySummarizerInterface;
use GuzzleHttp\Psr7\Message;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

final class BodySummarizer implements BodySummarizerInterface
{
    private int $truncateAt;

    public function __construct(int $truncateAt)
    {
        $this->truncateAt = $truncateAt;
    }

    /**
     * {@inheritDoc}
     */
    public function summarize(MessageInterface $message): ?string
    {
        if (!$message instanceof ResponseInterface || $message->getStatusCode() < 400) {
            return null;
        }

        return Message::bodySummary($message, $this->truncateAt);
    }
}
