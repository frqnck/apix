<?php

namespace Apix;

class Console
{
    protected $args;

    protected $options = null;

    public $no_colors = false;

    public $verbose = false;

    protected $switches = array(
        'no_colors' => array('--no-colors', '--no-colours'),
        'verbose'   => array('--verbose', '-v', '-vv', '-vvv')
    );

    #private $prompt = "\033[%sm%s\033[0m";
    private $prompt = "\x1b[%sm%s\x1b[0m";

    public function __construct(array $options = null)
    {
        $this->setArgs();
        $this->options = null === $options ? $this->getOptions() : $options;
    }

    public function setArgs(array $args = null)
    {
        $args = null === $args ? $_SERVER['argv'] : $args;

        $this->args = array_unique($args);

        $this->setSwitches($this->switches);

        if (true == $this->verbose) {
            $this->verbose = $this->getVerbosityLevel($args);
        }
    }

    /**
     * Multiple -v options increase the verbosity. The maximum is 3
     */
    public function getVerbosityLevel(array $args)
    {
        foreach ($args as $arg) {
          if (preg_match('/^-(v+)/', $arg, $m)) {
            return strlen($m[1]);
          }
        }

        return 1;
    }

    public function setSwitches(array $switches)
    {
        foreach ($switches as $key => $values) {
            if ($this->hasArgs($values)) {
                $this->args = array_diff($this->args, $values);
                #reset($this->args); // due to a php bug?
                $this->$key = true;
            }
        }
        $this->args = array_values($this->args);

        // check env variables.
        if (false === $this->no_colors) {
            $this->no_colors = exec('tput colors 2> /dev/null') > 2 ? 0 : 1;
        }
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function hasArgs(array $args)
    {
        return array_intersect($args, $this->args);
    }

    public function getOptions()
    {
        if (true === $this->no_colors) {
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
            ),
            array('nl' => "\n") // newline before the output.
        );

        $ansi['dark_gray'] = 90;
        #print_r($ansi);exit;

        return $ansi;
    }

    public function out($msg=null, $styles=null)
    {
        if (null !== $msg) {
            $styles = is_array($msg) ? $msg : func_get_args();
            $msg = array_shift($styles);

            echo $this->_out($msg, $styles);
        } else {
            echo PHP_EOL;
        }
    }

    public function outRegex($msg)
    {
        $pat = '@<(?<name>[^>]+)>(?<value>[^<]+)</\1>@';

        if (true === $this->no_colors) {
            echo preg_replace($pat, '\2', $msg);
        } else {
            preg_match_all($pat, $msg, $tags, PREG_SET_ORDER);
            foreach ($tags as $tag) {
                $msg = str_replace(
                    $tag[0],
                    $this->_out($tag['value'], $tag['name']),
                    $msg
                );
                #$help = preg_replace($pat, $this->_out("$2 $1", "$1", 'bold'), $msg);
            }
            echo $msg;
        }
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
                    $msg = sprintf($this->prompt, $this->options[$style], $msg);
                }
            }
        }

        return $msg;
    }

        // $obj = new \stdClass;
        // $obj->name = 'name';
        // $obj->params = 'params';
        // $obj->summary = 'summary';
        // $obj->since = 'since';
        // $obj->group = 'group';

        // $this->cliOutputCommandHelp($obj);
    #private $prompt = "\x1b[%sm%s\x1b[0m";
    public function cliOutputCommandHelp($help)
    {
        echo "not connected> help keys\r\n";
        printf("\r\n  \x1b[1m%s\x1b[0m \x1b[90m%s\x1b[0m\r\n", ucfirst($help->name), $help->params);
        printf("  \x1b[33msummary:\x1b[0m %s\r\n", $help->summary);
        printf("  \x1b[33msince:\x1b[0m %s\r\n", $help->since);
        printf("  \x1b[33mgroup:\x1b[0m %s\r\n", $help->group);
        echo "\r\nnot connected> help keys\r\n";
    }

}
