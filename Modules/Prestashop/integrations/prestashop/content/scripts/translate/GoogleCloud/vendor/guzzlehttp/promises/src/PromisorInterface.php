<?php

namespace GuzzleHttp\Promise;

/**
 * Interface used with classes that return a promise.
 */
interface PromisorInterface
{
    /**
     * Return a promise.
     *
     * @return PromiseInterface
     */
    public function promise();
}
