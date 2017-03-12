<?php

namespace Jwz104\BitcoinAccounts\Exceptions;

class InvalidAddressException extends \Exception {

    /**
     * The invalid address
     *
     * @var string
     */
    protected $address;

    /**
     * Instantiate a new InvalidAddressException instance.
     *
     * @param $errorCode int The http error code
     * @return void
     */
    public function __construct($address)
    {
        parent::__construct('Address is not valid: '.$address);
        $this->address = $address;
    }

    /**
     * Get the invalid address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
}
