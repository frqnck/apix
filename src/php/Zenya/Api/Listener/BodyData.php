<?php
namespace Zenya\Api\Listener;

class BodyData implements \SplObserver
{

    public function __construct($auth=null)
    {
        #echo '__construct: (happen once)';
    }

    public function update(\SplSubject $resource)
    {
        static $i = 0;

        ++$i;
        echo ' BodyData update request!='. $i .' ';

#echo '<pre>';print_r($resource);exit;

    }

}
