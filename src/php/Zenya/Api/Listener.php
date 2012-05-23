<?php
/**
 * Copyright (c) 2011 Zenya.com
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
 * @package     Zenya
 * @subpackage  ApiServer
 * @author      Franck Cassedanne <fcassedanne@zenya.com>
 * @copyright   2011 zenya.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://zenya.github.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Zenya\Api;

class Listener implements \SplSubject, \IteratorAggregate, \Countable
{
    /**
     * @var SplObjectStorage
     */
    protected $_observers = array();

     /**
     * Attaches a new observer
     *
     * @param SplObserver $observer Any object implementing SplObserver
     */
    public function attach(\SplObserver $observer)
    {
        foreach ($this->_observers as $attached) {
            if ($attached === $observer) {
                return;
            }
        }
        $this->_observers[] = $observer;
    }

    /**
     * Detaches an existing observer
     *
     * @param SplObserver $observer any object implementing SplObserver
     */
    public function detach(\SplObserver $observer)
    {
        foreach ($this->_observers as $key => $attached) {
            if ($attached === $observer) {
                unset($this->_observers[$key]);

                return;
            }
        }
    }

    /**
     * Notifies all the observers
     *
     * @return void
     * @throws Zenya\Api\Exception
     */
    public function notify()
    {
        foreach ($this->_observers as $observer) {
            $observer->update($this);
        }
    }

    /**
     * IteratorAggregate
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->_observers;
    }

    /**
     * Countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->_observers);
    }

    /* ---- */

    /*
    public function __get($prop)
    {
        return $this->$prop;
    }

    public function __set($prop, $val)
    {
        echo 'set';
        $this->$prop = $val;
        $this->notify();
    }
    */


    /**
     * Last event in request / response handling, intended for observers
     * @var  array
     * @see  getLastEvent()
     */
    protected $_notice = array(
        'name' => null,
        'obj' => null
    );

    /**
     * Sets the last event
     *
     * Adapters should use this method to set the current state of the request
     * and notify the observers.
     *
     * @param string $name event name
     * @param mixed  $data some event data
     */
    public function setNotice($name, $data=null)
    {
        $this->_notice = array(
            'name' => $name,
            'data' => $data
        );
         $this->notify();
    }

    /**
     * Returns the last event
     *
     * Observers should use this method to access the last change in request.
     * The following event names are possible:
     * <ul>
     *   <li>'connect'                 - after connection to remote server,
     *                                   data is the destination (string)</li>
     *   <li>'disconnect'              - after disconnection from server</li>
     *   <li>'sentHeaders'             - after sending the request headers,
     *                                   data is the headers sent (string)</li>
     *   <li>'sentBodyPart'            - after sending a part of the request body,
     *                                   data is the length of that part (int)</li>
     *   <li>'sentBody'                - after sending the whole request body,
     *                                   data is request body length (int)</li>
     *   <li>'receivedHeaders'         - after receiving the response headers,
     *                                   data is HTTP_Request2_Response object</li>
     *   <li>'receivedBodyPart'        - after receiving a part of the response
     *                                   body, data is that part (string)</li>
     *   <li>'receivedEncodedBodyPart' - as 'receivedBodyPart', but data is still
     *                                   encoded by Content-Encoding</li>
     *   <li>'receivedBody'            - after receiving the complete response
     *                                   body, data is HTTP_Request2_Response object</li>
     * </ul>
     * Different adapters may not send all the event types. Mock adapter does
     * not send any events to the observers.
     *
     * @return array The array has two keys: 'name' and 'data'
     */
    public function getNotice()
    {
        return $this->_notice;
    }

    public function addAllListeners($level, $type=null)
    {
        //
        $config = array(
            'listeners' => array(
                'server' => array(
                    // pre-processing stage
                    'early' => array(
                        #'Zenya\Api\Listener\Mock',
                    ),
                    // post-processing stage
                    'late'=>array(),
                    // errors and exceptions
                    'exception' => array(
                        #'Zenya\Api\Listener\Log' => array('php://output'),
                    )
                ),
                'request' => array(
                    #'Zenya\Api\Listener\Log',
                ),
                'resource' => array(
                    'early' => array(
                        #'Zenya\Api\Listener\Auth',
                        #'Zenya\Api\Listener\CheckIp' => null,
                        #'Zenya\Api\Listener\Acl',
                        #'Zenya\Api\Listener\Log',
                        #'Listener\Log',
                    ),
                    // post-processing stage
                    'late'=>array(
                        #'Zenya\Api\Listener\Mock',
                        #'Zenya\Api\Listener\Log' => array('php://output'),
                    ),
                ),
                'response' => array(),
            )
        );

        $stage = is_null($type) ? $config['listeners'][$level] : $config['listeners'][$level][$type];
        if (isset($stage)) {
            foreach ($stage as $k=>$v) {
                if (is_int($k)) {
                    $call = new $v();
                } else {
                    $args = $v[0];
                    $call = new $k($args);
                }
                $this->attach($call);
            }
        }
        switch ($type) {
            case 'late':
            break;
            default:
#				for($i=0;$i<2;$i++)
#				$this->attach(new Listener\Mock);
                //$this->setNotice('early');
        }
        $this->setNotice($type);
    }

}
