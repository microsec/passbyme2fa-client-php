<?php
namespace YourLogger;

use PassByME\Authentication\Log\ILogger;

class Logger implements ILogger
{
    public function __construct()
    {
        openlog('PassByME', LOG_PERROR, LOG_SYSLOG);
    }
    
    public function info($message)
    {
        syslog(LOG_INFO, $message);
    }
    
    public function debug($message)
    {
        syslog(LOG_DEBUG, $message);
    }
    
    public function error($message)
    {
        syslog(LOG_ERR, $message);
    }
    
    public function warning($message)
    {
        syslog(LOG_WARNING, $message);
    }
}
