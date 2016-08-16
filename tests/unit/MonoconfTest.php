<?php

namespace Monoconf;

class MonoconfTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Monoconf
     */
    private $monoconf;

    protected function setUp()
    {
        parent::setUp(); 
        $this->monoconf = new Monoconf();
    }

    /**
     * Test that only one instance is available
     */
    public function testGetInstance()
    {
        $this->assertTrue(Monoconf::getInstance() === Monoconf::getInstance());
    }

    /**
     * Test that getLogger returns correctly configured loggers.
     */
    public function testGetLogger()
    {
        $Handler = new \Monolog\Handler\TestHandler();

        $config = array(
            'rules' => array(
                'MyApp\Console\*' => array(
                    'info' => array(
                        'handler' => array('stdout-handler'),
                    ),
                ),
                '*' => array(
                    'error' => array(
                        'handler' => array('error-handler'),
                    ),
                ),
            ),
            'handler' => array(
                'stdout-handler' => $Handler,
                'error-handler' => array(
                    'type' => 'Monolog\Handler\StreamHandler',
                    'args' => array(
                        'php://stderr',
                    ),
                ),
            ),
        );
        
        $this->monoconf->config($config);
        $Logger = $this->monoconf->getLogger('MyApp\Other\Class');
        $this->assertEquals($Logger->getName(), 'MyApp\Other\Class');
        $this->assertTrue($Logger->isHandling(\Monolog\Logger::ERROR));
        $this->assertFalse($Logger->isHandling(\Monolog\Logger::WARNING));
        $this->assertFalse($Logger->isHandling(\Monolog\Logger::INFO));
        $this->assertFalse($Logger->isHandling(\Monolog\Logger::DEBUG));
    }


    public function testProcessorRecords()
    {
        $Handler = new \Monolog\Handler\TestHandler();

        $config = array(
            'rules' => array(
                '*' => array(
                    'error' => array(
                        'handler' => array('error-handler'),
                        'processor' => array('uid'),
                    ),
                ),
            ),
            'handler' => array(
                'error-handler' => $Handler,
            ),
            'processor' => array(
                'uid' => array(
                    'type' => 'Monolog\Processor\UidProcessor',
                    'args' => array(),
                )
            )
        );

        $this->monoconf->config($config);
        $Logger = $this->monoconf->getLogger('MyApp\Other\Class');
        $Logger->error('foo');
        $this->assertTrue($Handler->hasErrorThatContains('foo'));
        $records = $Handler->getRecords();
        $this->assertTrue(isset($records[0]['extra']['uid']));
    }
}
