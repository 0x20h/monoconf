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
    private static $loggers = array();
    private static $config = array();

    public static function config(array $config)
    {
        self::$config = $config;
    }

    public static function getLogger($name)
    {
        if (!isset(self::$loggers[$name])) {
            self::$loggers[$name] = self::initLogger($name);
        }

        return self::$loggers[$name];
    }

    protected static function initLogger($name)
    {
        $config = self::$config;
        $rules = array();

        foreach ($config['rules'] as $key => $ruleSetup) {
            $rulePattern = str_replace(array('*', '\\'), array('.*', '\\\\'), $key);

            if (preg_match('#^'.$rulePattern.'$#', $name)) {
                $rules = array_merge($rules, $ruleSetup);
            }
        }
        
        foreach ($rules as $level => $localconf) {
            foreach($localconf['handler'] as $handler) {
                if (!isset($config['handler'][$handler])) {
                    // error
                    trigger_error('config key not set for handler '.$handler);
                    continue;
                }

                $type = $config['handler'][$handler]['type'];
                $args = $config['handler'][$handler]['args'];
                $args[] = constant('\Monolog\Logger::'.strtoupper($level));
                $args[] = true;

                $Reflection = new \ReflectionClass($type);
                $handlers[] = $Handler = $Reflection->newInstanceArgs($args);

                if (isset($config['handler'][$handler]['formatter'])) {
                    $formatter = $config['formatter'][$config['handler'][$handler]['formatter']];
                    $Formatter = new \ReflectionClass($formatter['type']);
                    $Handler->setFormatter($Formatter->newInstanceArgs($formatter['args']));
                }
            }
        }

        return new \Monolog\Logger(
            $name,
            $handlers,
            array(new \Monoconf\Processor\PidProcessor)
        );
    }
}
