<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */
namespace SLS\Models\OssShipper;
class JsonStorage extends Storage{
    private $enableTag = false;

    /**
     * @return bool
     */
    public function isEnableTag(): bool
    {
        return $this->enableTag;
    }

    /**
     * @param bool $enableTag
     */
    public function setEnableTag(bool $enableTag)
    {
        $this->enableTag = $enableTag;
    }

    public function to_json_object(){
        $detail =  array(
            'enableTag' => $this->enableTag
        );
        return array(
            'detail' => $detail,
            'format' => 'json'
        );
    }
}