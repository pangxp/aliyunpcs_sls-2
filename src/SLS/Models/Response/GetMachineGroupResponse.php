<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace SLS\Models\Response;
use SLS\Models\MachineGroup\MachineGroup;
/**
 * The response of the GetLog API from log service.
 *
 * @author log service dev
 */
class GetMachineGroupResponse extends Response {


    private $machineGroup;
    /**
     * GetMachineGroupResponse constructor
     *
     * @param array $resp
     *            GetLogs HTTP response body
     * @param array $header
     *            GetLogs HTTP response header
     */
    public function __construct($resp, $header) {
        parent::__construct ( $header );
        $this->machineGroup = new MachineGroup();
        $this->machineGroup->setFromArray($resp);
    }

    public function getMachineGroup(){
        return $this->machineGroup;
    } 

}
