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
class UpdateMachineGroupRequest extends Request {

    private $machineGroup; 
    /**
     * UpdateMachineGroupRequest Constructor
     *
     */
    public function __construct($machineGroup) {
        $this->machineGroup = $machineGroup;
    }

    public function getMachineGroup(){
        return $this->machineGroup;
    }

    public function setMachineGroup($machineGroup){
        $this->machineGroup = $machineGroup;
    }

    
}
