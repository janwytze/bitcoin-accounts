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
     * Instantiate a new CommandFailedException instance.
     *
     * @param $errorCode int The http error code
     * @return void
     */
    public function __construct($errorCode)
    {
        $this->httpCode = $errorCode;
    }

    /**
     * The message that gets returned when getMessage() is called
     *
     * @return void
     */
    public function __toString()
    {
        __toString('Could not execute the command'); 
    }

}
