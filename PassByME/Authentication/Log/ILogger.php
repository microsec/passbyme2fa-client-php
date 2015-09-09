<?php
namespace PassByME\Authentication\Log;

/**
 * Interface for PassByME logging.
 */
interface ILogger
{
    public function info($message);
    public function debug($message);
    public function error($message);
    public function warning($message);
}
