<?php
/**
 *
 * Simple front controller for URL handling and routing, PHP 5.2.x compatible
 * Author: Adrien Anceau <adrien.anceau@gmail.com>
 * Licence: MIT
 *
 * Copyright (C) 2012 Adrien Anceau
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *      - The above copyright notice and this permission notice shall be included in all copies or substantial portions
 *      of the Software.
 *      - THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 *      TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 *      THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 *      CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 *      DEALINGS IN THE SOFTWARE.
 *
 */

class Router
{
    private $basePath="/";

    private $routes=array();        // List of routes
    private $conditions=array();    // List of conditions
    private $arguments=array();     // List of arguments
    private $scriptName;            // Scriptname (w/o path)
    private $requestUri;            // Formatted request URI (w/o scriptname, w/o trailing '/', starting with '/')

    /**
     * Default constructor
     * @param $config array containing configuration options:    - basePath: root folder
     */
    public function __construct($config)
    {
        $this->basePath=dirname($config['basePath']);
        $this->scriptName=basename($_SERVER['SCRIPT_NAME']);

        // Removing filename from URI to be more generic
        $this->requestUri='/'.trim(preg_replace(":".$this->scriptName."(\/*):", "", $_SERVER['REQUEST_URI']), '/');
    }

    /**
     * @param $r array containing the route to add
     *          array(
     *              'pattern' => '/the/pattern/{of}/{the}/{route}',
     *              'name' => 'short-name'
     *          )
     *          Variable parts of the pattern are to be put between braces: ex '/show/{id}'
     *          and retrieved in your controller by using function 'getArgument('id')'
     */
    public function addRoute($r) {
        $r['input']['count']=preg_match_all(":{([a-zA-Z0-9]*)}:", $r['pattern'], $r['input']);
        $r['regex']=preg_replace(":{([a-zA-Z0-9]*)}:", "([a-zA-Z0-9]*)", $r['pattern']);
        $this->routes[]=$r;
    }

    /**
     * @param $c array containing the condition to add
     *          array(
     *              'route' => the matching pattern for which this condition should apply
     *              'condition' => the condition (should be something returning true of false)
     *              'fallbackRoute' => the name of the route to summon when condition is not satisfied
     *          )
     */
    public function addCondition($c) {
        $this->conditions[]=$c;
    }

    /**
     * @param $key the name of the argument as defined in the route pattern (surrounded by '{..}', ex: {variable})
     * @return null if the argument does not exist, or the value of the argument.
     */
    public function getArgument($key) {
        if (isset($this->arguments[$key])) {
            return $this->arguments[$key];
        } else {
            return null;
        }
    }

    /**
     * @return a rule's name if a matching route is found, or false if no route is matching the requested URI
     */
    public function run() {
        $found=false;
        $matchedRule=null;

        // Looking for a matching rule
        foreach ($this->routes as $r) {
            if (!$found) {
                if (preg_match(":".$this->basePath.$r['regex']."$:", $this->requestUri, $r['input']['values'])) {
                    $matchedRule=$r;
                    $found=true;
                } else {
                }
            }
        }

        if ($found) {
            // Getting arguments passed for the rule
            for ($i=0; $i<count($matchedRule['input'][0]); $i++) {
                $matchedRule['arguments'][$matchedRule['input'][1][$i]]=$matchedRule['input']['values'][$i+1];
                $this->arguments[$matchedRule['input'][1][$i]]=$matchedRule['input']['values'][$i+1];
            }

            // Checking if there is a condition for that rule
            foreach ($this->conditions as $c) {
                if (preg_match(":".$this->basePath.$c['route'].":", $this->basePath.$matchedRule['pattern'])) {
                    if ($this->evalCondition($c['condition']) == false) {
                        return $c['fallbackRoute'];
                    } else {
                    }
                }
            }

            // Return the rule matched, if any, and if conditions are OK
            return $matchedRule['name'];
        }

        return false;
    }

    /**
     * @param $ruleName The name of the rule to build an URL for
     * @param $arg an array containing the arguments' values, in the same order as defined in the rule
     * @return string the URL
     */
    public function urlTo($ruleName, $arg) {
        // Looking for a matching rule
        foreach ($this->routes as $r) {
            if ($ruleName == $r['name']) { // Found one ...
                $matchedRule=$r;
                $count=1;
                $pattern=$matchedRule['pattern'];
                for ($i=0; $i<count($arg); $i++) { // Replacing variables with arguments
                    $tmpPattern=preg_replace(":{([a-zA-Z0-9]*)}:", $arg[$i], $pattern, 1, $count);
                    $pattern=$tmpPattern;
                }
                return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].$pattern;
            }
        }
    }

    /**
     * @param $condition
     * @return bool the result of the condition
     */
    private function evalCondition($condition) {
        return $condition;
    }
}