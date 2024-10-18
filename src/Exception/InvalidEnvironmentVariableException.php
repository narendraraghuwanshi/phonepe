<?php

namespace PhonePe\Exception;
use Exception;
class InvalidEnvironmentVariableException extends Exception
{
    public function __construct($message = "Invalid Environment Variable", $code =500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
