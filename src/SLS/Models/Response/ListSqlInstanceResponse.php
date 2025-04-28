<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace SLS\Models\Response;
use SLS\Models\Response\Response;
use SLS\Models\Response\SqlInstance;

/**
 * The response of the ListSqlInstance API from log service.
 *
 * @author log service dev
 */
class ListSqlInstanceResponse extends Response {

    private $sqlInstances;
    /**
     * ListSqlInstanceResponse constructor
     *
     * @param array $resp
     *            ListSqlInstance HTTP response body
     * @param array $header
     *            ListSqlInstance HTTP response header
     */
    public function __construct($resp, $header) {
        parent::__construct ( $header );
        $arr = $resp;
        if($arr != null)
        {
            foreach($arr as $data)
            {
                $name = $data["name"];
                $cu = $data["cu"];
                $createTime = $data["createTime"];
                $updateTime = $data["updateTime"];
                $this -> sqlInstances [] = new SqlInstance($name,$cu,$createTime,$updateTime);
            }
        }
    }
    
}
