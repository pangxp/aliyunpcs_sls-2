<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */
namespace SLS\Models\Config;
class OutputDetail {
    public $projectName;
    public $logstoreName;

    public function __construct($projectName='',$logstoreName=''){
      $this->projectName = $projectName;
      $this->logstoreName = $logstoreName;
    }
    public function toArray(){
      $resArray = array();
      if($this->projectName!==null)
        $resArray['projectName'] = $this->projectName;
      if($this->logstoreName!==null)
        $resArray['logstoreName'] = $this->logstoreName;
      return $resArray;
    }
}

