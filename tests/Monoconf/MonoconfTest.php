<?php
/**
 * This file is part of the Phantasktic package.
 *
 * Copyright (c) 2013 Jan Kohlhof <kohj@informatik.uni-marburg.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this 
 * software and associated documentation files (the "Software"), to deal in the Software 
 * without restriction, including without limitation the rights to use, copy, modify, merge, 
 * publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons 
 * to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or 
 * substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
 * FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, 
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
 * SOFTWARE.
 */

namespace Monoconf;

/**
 */
class MonoconfTest extends \PHPUnit_Framework_Testcase
{

    public function setUp()
    {
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

        Monoconf::config($config);
        $Logger = Monoconf::getLogger('MyApp\Other\Class');
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

        Monoconf::config($config);
        $Logger = Monoconf::getLogger('MyApp\Other\Class');
        $Logger->error('foo');
        $this->assertTrue($Handler->hasErrorRecords('foo'));
        $records = $Handler->getRecords();
        $this->assertTrue(isset($records[0]['extra']['uid']));
    }
}
