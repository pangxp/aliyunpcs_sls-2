<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */
namespace SLS\Models\OssShipper;
class Storage{
    private $format;

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }


}