<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace SLS\Models\Request;
use SLS\Models\Request\Request;

class ListShipperRequest extends Request{
    private $logStore;

    /**
     * CreateShipperRequest Constructor
     *
     */
    public function __construct($project) {
        parent::__construct ( $project );
    }

    /**
     * @return mixed
     */
    public function getLogStore()
    {
        return $this->logStore;
    }

    /**
     * @param mixed $logStore
     */
    public function setLogStore($logStore)
    {
        $this->logStore = $logStore;
    }


}