<?php

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
     *                                   data is the destination (string) </li>
     *   <li>'disconnect'              - after disconnection from server</li>
     *   <li>'sentHeaders'             - after sending the request headers,
     *                                   data is the headers sent (string) </li>
     *   <li>'sentBodyPart'            - after sending a part of the request body,
     *                                   data is the length of that part (int) </li>
     *   <li>'sentBody'                - after sending the whole request body,
     *                                   data is request body length (int) </li>
     *   <li>'receivedHeaders'         - after receiving the response headers,
     *                                   data is HTTP_Request2_Response object</li>
     *   <li>'receivedBodyPart'        - after receiving a part of the response
     *                                   body, data is that part (string) </li>
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
                        'Zenya\Api\Listener\Auth' => array(
                            \Zend_Auth::getInstance()
                        ),

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

        $stage = is_null($type)
            ? $config['listeners'][$level]
            : $config['listeners'][$level][$type];

        if (isset($stage)) {
            foreach ($stage as $class => $args) {
                if (is_int($class)) {
                    $call = new $args();
                } else {
                    $args = $args[0];
                    $call = new $class($args);
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
