<?php

namespace Zenya\Api;

class Listener implements \SplSubject, \IteratorAggregate, \Countable
{
    /**
     * @var array of SplObjectStorage
     */
    protected $observers = array();

     /**
     * Attaches a new observer
     *
     * @param SplObserver $observer Any object implementing SplObserver
     */
    public function attach(\SplObserver $observer)
    {
        foreach ($this->observers as $attached) {
            if ($attached === $observer) {
                return;
            }
        }
        $this->observers[] = $observer;
    }

    /**
     * Detaches an existing observer
     *
     * @param SplObserver $observer any object implementing SplObserver
     */
    public function detach(\SplObserver $observer)
    {
        foreach ($this->observers as $key => $attached) {
            if ($attached === $observer) {
                unset($this->observers[$key]);

                return;
            }
        }
    }

    /**
     * Notifies all the observers
     *
     * @return void
     */
    public function notify()
    {
        foreach ($this->observers as $observer) {
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
        return $this->observers;
    }

    /**
     * Countable
     *
     * @return integer
     */
    public function count()
    {
        return count($this->observers);
    }

#    public function __construct() {}

    public function addAllListeners($level, $type=null)
    {
        static $listeners = null;

        if($listeners === null) {
            $listeners = Config::getInstance()->getListeners($level);
        }

        $stage = is_null($type)
            ? $listeners
            : $listeners[$type];

        if (isset($stage)) {
            foreach ($stage as $listener => $args) {
                if (is_int($listener)) {
                    $call = $args instanceof \Closure ? $args() : new $args();
                } else {
                    $args = $args instanceof \Closure ? $args() : $args[0];
                    $call = new $listener($args);
                }
                $this->attach($call);
            }
        }

        $this->setNotice($type);
    }


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

}