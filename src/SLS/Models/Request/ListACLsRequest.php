<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace SLS\Models\Request;
use SLS\Models\Request\Request;

/**
 * 
 *
 * @author log service dev
 */
class ListACLsRequest extends Request {

    private $offset;
    private $size;
    private $principleId;

    /**
     * ListACLsRequest Constructor
     *
     */
    public function __construct($principleId=null,$offset=null,$size=null) {
        $this->offset = $offset;
        $this->size = $size;
        $this->principleId = $principleId;
    }

    public function getOffset(){
        return $this->offset;
    }
    public function setOffset($offset){
        $this->offset = $offset;
    }

    public function getSize(){
        return $this->size;
    }
    public function setSize($size){
        $this->size = $size;
    }
    
    public function getPrincipleId(){
        return $this->principleId;
    }
    public function setPrincipleId($principleId){
        $this->principleId = $principleId;
    }

}
