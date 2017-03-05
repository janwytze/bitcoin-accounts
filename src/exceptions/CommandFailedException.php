<?php

namespace jwz104\Bitcoin\Exceptions;

class CommandFailedException extends \Exception {

    public function __toString()
    {
        __toString('Could not execute the command'); 
    }

}
