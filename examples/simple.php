<?php

require __DIR__.'/../vendor/autoload.php';

$config = array(
    'appenders' => array(
        'Some.*' => function($c, $id) {
            $handlers = array(
                $c['console.info'],
            );

            $processors = array(
                $c['processor.pid'],
            );

            return new \Monolog\Logger($id, array($c['console.info']), $processors);
        },
        'SomeOtherCla.*' => function($c, $id) {
            return new \Monolog\Logger($id, array($c['console.debug']));
        },
    ),
    'console.stdout' => 'php://output',
    'console.info' => function($c) {
        return new \Monolog\Handler\StreamHandler(
            $c['console.stdout'], 
            \Monolog\Logger::INFO 
        );
    },
    'console.debug' => function($c) {
        return new \Monolog\Handler\StreamHandler(
            $c['console.stdout'], 
            \Monolog\Logger::DEBUG
        );
    },

   'processor.pid' => function($c) {
        return new \Monolog\Processor\ProcessIdProcessor();
    },
);

$monoconf = new \Monoconf\Monoconf($config);
$pimple = new Pimple(array('log' => $monoconf));

class SomeClass {
    private $pimple;

    public function __construct(Pimple $pimple)
    {
        $this->pimple = $pimple;
    }
    
   
    public function foo()
    {
        $log = $this->pimple['log'][__CLASS__];
        $log->info('foo');
        $log->debug('bar');
    }
}

class SomeOtherClass {
    private $pimple;

    public function __construct(Pimple $pimple)
    {
        $this->pimple = $pimple;
    }
    
   
    public function foo()
    {
        $log = $this->pimple['log'][__CLASS__];
        $log->info('foo');
        $log->debug('bar');
    }
}

$someClass = new SomeClass($pimple);
$someClass->foo();

$someOtherClass = new SomeOtherClass($pimple);
$someOtherClass->foo();

