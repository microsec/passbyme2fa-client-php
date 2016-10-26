<?php
namespace PassByME\TwoFactor;
use Exception;
use PassByME\Log\Logger;

class PBMErrorException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        $log = new Logger();
        $log->error('PBMErrorException occurred: ' . $message);
        parent::__construct($message, $code, $previous);
    }
}