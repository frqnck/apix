<?php

/**
 *
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 *
 */

namespace Apix\Output;

class Jsonp extends AbstractOutput
{

    /**
     * {@inheritdoc}
     * @see http://www.rfc-editor.org/rfc/rfc4329.txt
     */
    protected $content_type = 'application/javascript';

    /**
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode=null)
    {
        $cb = isset($_REQUEST['callback']) && !empty($_REQUEST['callback'])
                        ? $_REQUEST['callback']
                        : $rootNode;
        $cb = null === $cb ? 'apix' : $cb;

        if (!$this->isValidIdentifier($cb)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid callback name (%s) used.', $cb)
            );
        }

        $json = new Json();
        $str = $json->encode($data, $rootNode);

        return "{$cb}({$str});";
    }

    /**
     * Check wether a callback string name is a valid javascript identifier.
     * @see http://www.geekality.net/2011/08/03/valid-javascript-identifier/
     * @see http://www.ecma-international.org/publications/files/ECMA-ST/Ecma-262.pdf
     *
     * @param  string  $subject
     * @return boolean
     */
    public function isValidIdentifier($subject)
    {
        $syntax = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

        $reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
          'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue',
          'for', 'switch', 'while', 'debugger', 'function', 'this', 'with',
          'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum',
          'extends', 'super', 'const', 'export', 'import', 'implements', 'let',
          'private', 'public', 'yield', 'interface', 'package', 'protected',
          'static', 'null', 'true', 'false');

        return preg_match($syntax, $subject)
            && ! in_array(strtolower($subject), $reserved_words);
    }
}
