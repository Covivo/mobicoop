<?php

namespace App\OAuth\Event;

use Symfony\Contracts\EventDispatcher\Event;

class HttpQueryErrorEvent extends Event
{
    public const NAME = 'oauth.http.query.error';

    /**
     * @var \Throwable
     */
    private $_error;

    public function __construct(\Throwable $error)
    {
        $this->_error = $error;
    }

    public function getError(): \Throwable
    {
        return $this->_error;
    }
}
