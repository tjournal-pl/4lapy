<?php

namespace FourPaws\SapBundle\Model;

interface SourceMessageInterface
{
    /**
     * @return string
     */
    public function getId() : string;
    
    /**
     * @return string
     */
    public function getType() : string;
    
    /**
     * @return mixed
     */
    public function getData();
}
