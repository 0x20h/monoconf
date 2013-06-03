<?php
/**
 * This file is part of the monoconf package.
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

class Monoconf
{
    private static $Instance;

    private $loggers = array();
    private $config = array(
        'rules' => array(
            '*' => array(
                'debug' => array(
                    'handler' => array('null'),
                ),
            ),
        ),
        'handler' => array(
            'null' => array(
                'type' => 'Monolog\\Handler\\NullHandler',
                'args' => array(),
            )
        )
    );


    /**
     * Retrieve an instance.
     *
     */
    public static function getInstance()
    {
        if (!self::$Instance) {
            self::$Instance = new Monoconf();
        }

        return self::$Instance;
    }


    /**
     * Set configuration for logging
     *
     * @param array $config configuration array.
     */
    public static function config(array $config)
    {
        $Instance = self::getInstance();
        
        // reset loggers
        $Instance->loggers = array();
        // reset config
        $Instance->config = $config;
    }


    /**
     * Retrieve a \Monolog\Logger instance for the given name.
     *
     * @param string $name an identifier for the logger.
     * @return \Monolog\Logger configured Logger instance.
     */
    public static function getLogger($name)
    {
        $Instance = self::getInstance();

        if (!isset($Instance->loggers[$name])) {
            $Instance->loggers[$name] = $Instance->initLogger($name);
        }

        return $Instance->loggers[$name];
    }


    private function initLogger($name)
    {
        $config = $this->config;
        $rules = $handlers = $processors = array();

        foreach ($config['rules'] as $key => $ruleSetup) {
            $rulePattern = str_replace(array('*', '\\'), array('.*', '\\\\'), $key);

            if (preg_match('#^'.$rulePattern.'$#', $name)) {
                $rules = $ruleSetup;
                break;
            }
        }
        
        foreach ($rules as $level => $localconf) {
            foreach ($localconf['handler'] as $handler) {
                if (!isset($config['handler'][$handler])) {
                    // error
                    trigger_error('config key not set for handler '.$handler);
                    continue;
                }

                if ($config['handler'][$handler] instanceof \Monolog\Handler\HandlerInterface) {
                    $Handler = $config['handler'][$handler];
                } else {
                    $type = $config['handler'][$handler]['type'];
                    $args = $config['handler'][$handler]['args'];
                    $args[] = constant('\Monolog\Logger::'.strtoupper($level));
                    $args[] = true;

                    $Reflection = new \ReflectionClass($type);
                    $Handler = $Reflection->newInstanceArgs($args);

                    if (isset($config['handler'][$handler]['formatter'])) {
                        $formatter = $config['formatter'][$config['handler'][$handler]['formatter']];
                        $Formatter = new \ReflectionClass($formatter['type']);
                        $Handler->setFormatter($Formatter->newInstanceArgs($formatter['args']));
                    }
                }
               
                $handlers[] = $Handler;
            }

            if (!isset($localconf['processor'])) {
                continue;
            }

            foreach ($localconf['processor'] as $processor) {
                if (!isset($config['processor'][$processor])) {
                    continue;
                }

                $type = $config['processor'][$processor]['type'];
                $args = $config['processor'][$processor]['args'];

                $Reflection = new \ReflectionClass($type);
                $processors[] = $Reflection->newInstanceArgs($args);
            }
        }

        // @TODO: register processors
        return new \Monolog\Logger(
            $name,
            $handlers,
            $processors
        );
    }
}
