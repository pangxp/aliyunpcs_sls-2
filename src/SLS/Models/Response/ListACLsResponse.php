<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace SLS\Models\Response;
use SLS\Models\LogLevel\ACL;

/**
 * The response of the GetLog API from log service.
 *
 * @author log service dev
 */
class ListACLsResponse extends Response {


    private $acls; 
    /**
     * ListACLsResponse constructor
     *
     * @param array $resp
     *            GetLogs HTTP response body
     * @param array $header
     *            GetLogs HTTP response header
     */
    public function __construct($resp, $header) {
        parent::__construct ( $header );
        $aclArr = array();
        if(isset($resp['acls'])){
            foreach($resp['acls'] as $value){
                $aclObj = new ACL();
                $aclObj->setFromArray($value);
                $aclArr[]=$aclObj;
            }
        }
        $this->acls = $aclArr;
    }

    public function getAcls(){
        return $this->acls;
    }
   

}
