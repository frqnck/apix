<?php
/**
 * Copyright (c) 2011 Franck Cassedanne, Zenya.com
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Zenya nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     Zenya\Api
 * @subpackage  Server
 * @author      Franck Cassedanne <fcassedanne@zenya.com>
 * @copyright   2011 Franck Cassedanne, Zenya.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://zenya.github.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Zenya\Api;

class Console
{
    public $args;

    protected $switches = array(
        'colors' => array('-c', '--colors', '--colours'),
        'verbose' => array('-v', '--verbose')
    );
    
    protected $colors = false;
    protected $verbose = false;

    protected $start = "\033[%dm%s";
    protected $end = "\033[0m";

    protected $options;

    public function __construct(array $options = null)
    {
        $this->args = array_unique($_SERVER['argv']);

        $this->options = null === $options ? $this->getOptions() : $options;
        
        $this->setModes();
    }

    public function setModes()
    {
        foreach($this->switches as $key => $values) {
            if(array_intersect($values, $this->args)) {
                $this->args = array_diff($this->args, $values);
                reset($this->args); // TODO: php bug here???
                $this->$key = true;
            }
        }
    }

    public function getOptions()
    {
        $options = array_merge(
            // Foreground colors.
            array_combine(
                array('grey', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan',
                      'white'),
                range(30, 37)
            ),
            // Background colors.
            array_combine(
                array('on_grey', 'on_red', 'on_green', 'on_yellow', 'on_blue',
                      'on_magenta', 'on_cyan', 'on_white'),
                range(40, 47)

            ),
            // Text style attributes. 3 and 6 are not used.
            array_combine(
                array('bold', 'dark', null, 'underline', 'blink', null, 'inverse',
                      'concealed'),
                range(1, 8)
            )
        );
        unset($options['']); // remove the null(s)
        return $options;
    }

    public function out($msg, $styles=null)
    {
        $styles = is_array($msg) ? $msg : func_get_args();
        $msg = array_shift($styles);

        if(false !== $this->colors) {
            foreach ($styles as $style) {
                if(isset($this->options[$style])) {
                    $msg = sprintf($this->start, $this->options[$style], $msg) . $this->end;
                }
            }
        }
        echo $msg;
    }

    // public function getArgs($default=null)
    // {
    //     return empty($_SERVER['argv'][1])
    //         ? $default
    //         : trim($_SERVER['argv'][1]);
    // }

}