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
class UpdateACLRequest extends Request {

    private $acl;
    /**
     * UpdateACLRequest Constructor
     *
     */
    public function __construct($acl) {
        $this->acl = $acl;
    }

    public function getAcl(){
        return $this->acl;
    }
    public function setAcl($acl){
        $this->acl = $acl;
    }
}
