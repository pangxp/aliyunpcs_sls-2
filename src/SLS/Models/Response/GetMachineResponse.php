<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace SLS\Models\Response;
use SLS\Models\Machine\Machine;

/**
 * The response of the GetLog API from log service.
 *
 * @author log service dev
 */
class GetMachineResponse extends Response {

    private $machine;

    /**
     * GetMachineResponse constructor
     *
     * @param array $resp
     *            GetLogs HTTP response body
     * @param array $header
     *            GetLogs HTTP response header
     */
    public function __construct($resp, $header) {
        parent::__construct ( $header );
        //echo json_encode($resp);
        $this->machine = new Machine();
        $this->machine->setFromArray($resp);
        
    }

    public function getMachine(){
        return $this->machine;
    }
   
}
