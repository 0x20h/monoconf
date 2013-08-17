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

class Monoconf extends \Pimple
{


    public function offsetGet($id)
    {
        $match = $this->match($id);
        
        if ($match) {
            $isFactory = is_object($this->values['appenders'][$match]) && method_exists($this->values['appenders'][$match], '__invoke');
            return $isFactory ? $this->values['appenders'][$match]($this, $id) : $this->values['appenders'][$match];
        }

        return parent::offsetGet($id);
    }
      
   
    public function offsetExists($key) {
        return parent::offsetExists($this->match($key));
    }

    /**
     * Match a requested key with the registered service patterns.
     *
     * @param string $key
     * @return 
     */ 
    private function match($key)
    {
        foreach (array_keys($this->values['appenders']) as $pattern) {
            if (preg_match('#^'.$pattern.'$#', $key)) {
                return $pattern;
            }
        }

        return null;
    }
}
