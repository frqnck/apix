<?php
/**
 * Copyright (c) 2011 Franck Cassedanne, Ouarz.net
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
 * @package     Ouarz\Console
 * @author      Franck Cassedanne <fcassedanne@ouarz.net>
 * @copyright   2011 Franck Cassedanne, Ouarz.net
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://ouarz.github.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Apix;

class Console
{
    public $args;

    protected $switches = array(
        'no_colors' => array('--no-colors', '--no-colours'),
        'verbose' => array('--verbose', '-vv', '-vvv')
    );

    protected $no_colors = false;
    protected $verbose = false;

    protected $start = "\033[%sm%s";
    protected $end = "\033[0m";

    protected $options = null;

    public function __construct(array $options = null)
    {
        $this->init();

        $this->options = null === $options ? $this->getOptions() : $options;
    }

    public function init()
    {
        $this->args = array_unique($_SERVER['argv']);

        foreach ($this->switches as $key => $values) {
            if (array_intersect($values, $this->args)) {
                $this->args = array_diff($this->args, $values);
                reset($this->args); // TODO: seems we have a php bug here???
                $this->$key = true;
            }
        }

        // check env variables.
        if (false === $this->no_colors) {
            $this->no_colors = exec('tput colors 2> /dev/null') > 2 ? 0 : 1;
        }
    }

    public function getOptions()
    {
        if ($this->no_colors == true) {
            return null;
        }
        $ansi = array_merge(
            // foreground colors @ level 0.
            array_combine(
                array('grey', 'red', 'green', 'brown', 'blue', 'purple', 'cyan',
                      'light_grey'),
                range(30, 37)
            ),
            // foreground colors @ level 1.
            array_combine(
                array('dark_gray', 'light_red', 'light_green', 'yellow',
                    'light_blue', 'pink', 'light_cyan', 'white'),
                array_map(function($k){return '1;' . $k;}, range(30, 37))
            ),
            // background colors.
            array_combine(
                array('on_red', 'on_green', 'on_brown', 'on_blue',
                      'on_purple', 'on_cyan', 'on_grey'),
                range(41, 47)
            ),
            // text style attributes (italics & outline not predictable).
            array_combine(
                array('normal', 'bold', 'dark', 'italics', 'underline', 'blink',
                    'outline', 'inverse', 'invisible', 'striked'),
                range(0, 9)
            )
        );

        return $ansi;
    }

    public function _out($msg, $styles=null)
    {
        if (!is_array($styles)) {
            $styles = is_array($msg) ? $msg : func_get_args();
            $msg = array_shift($styles);
        }

        if (true !== $this->no_colors) {
            foreach ($styles as $style) {
                if (isset($this->options[$style])) {
                    $msg = sprintf($this->start, $this->options[$style], $msg);
                    $msg .= $this->end;
                }
            }
        }

        return $msg;
    }

    public function out($msg, $styles=null)
    {
        $styles = is_array($msg) ? $msg : func_get_args();
        $msg = array_shift($styles);

        echo $this->_out($msg, $styles);
    }

    // public function getArgs($default=null)
    // {
    //     return empty($_SERVER['argv'][1])
    //         ? $default
    //         : trim($_SERVER['argv'][1]);
    // }

}
