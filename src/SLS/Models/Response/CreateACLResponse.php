<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace SLS\Models\Response;
use SLS\Models\Response\Response;

/**
 * The response of the GetLog API from log service.
 *
 * @author log service dev
 */
class CreateACLResponse extends Response {

    private $aclId; 
    /**
     * Response constructor
     *
     * @param array $resp
     *            GetLogs HTTP response body
     * @param array $header
     *            GetLogs HTTP response header
     */
    public function __construct($resp, $header) {
        parent::__construct ( $header );
        $this->aclId = $resp['aclId'];
    }
    public function getAclId(){
        return $this->aclId;
    }

}
