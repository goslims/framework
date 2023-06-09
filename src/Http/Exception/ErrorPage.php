<?php
namespace SLiMS\Http\Exception;

use Exception;

class ErrorPage extends Exception
{
    public function __construct($message, $code = 0, \Throwable $previous = null) {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}