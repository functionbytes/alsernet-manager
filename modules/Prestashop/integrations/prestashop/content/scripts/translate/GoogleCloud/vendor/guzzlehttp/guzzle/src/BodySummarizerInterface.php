<?php

namespace GuzzleHttp;

use Psr\Http\Message\MessageInterface;

interface BodySummarizerInterface
{
    /**
     * Return a summarized message body.
     */
    public function summarize(MessageInterface $message): ?string;
}
