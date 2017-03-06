<?php

namespace Jwz104\BitcoinAccounts\Exceptions;

class CommandFailedException extends \Exception {

    /**
     * The http error code
     *
     * @var int
     */
    protected $httpCode;

    /**
     * The http body
     *
     * @var int
     */
    protected $body;

    /**
     * Instantiate a new CommandFailedException instance.
     *
     * @param $errorCode int The http error code
     * @return void
     */
    public function __construct($errorCode, $errorBody)
    {
        parent::__construct('Could not execute the command, HTTP error code: '.$errorCode."\n".'Error body: '.$errorBody);
        $this->httpCode = $errorCode;
        $this->body = $errorBody;
    }
}
