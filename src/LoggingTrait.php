<?php

namespace Monoconf;

use Psr\Log\LoggerAwareInterface;

trait LoggingTrait
{
    protected $log;


    public function __construct()
    {
        var_dump('foo');
    }


    public function getLogger()
    {
        if (!$this->log) {
            
        }
        
        return $this->log;
    }
}