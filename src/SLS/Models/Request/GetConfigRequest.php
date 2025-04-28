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
class GetConfigRequest extends Request {

    private $configName;

    /**
     * GetConfigRequest Constructor
     *
     */
    public function __construct($configName = null) {
        $this->configName = $configName;
    }

    public function getConfigName(){
      return $this->configName;
    }

    public function setConfigName($configName){
      $this->configName = $configName;
    }
    
}
