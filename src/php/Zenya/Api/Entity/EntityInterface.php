<?php

namespace Zenya\Api\Entity;

interface EntityInterface
{
    public function append(array $definitions=null);

    public function getRedirect();

}