<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace SLS\Models\Response;
use SLS\Models\Response\Response;

/**
 * The response of the CreateSqlInstance API from log service.
 *
 * @author log service dev
 */
class CreateSqlInstanceResponse extends Response {
    
    /**
     * CreateSqlInstanceResponse constructor
     *
     * @param array $resp
     *            CreateSqlInstance HTTP response body
     * @param array $header
     *            CreateSqlInstance HTTP response header
     */
    public function __construct($resp, $header) {
        parent::__construct ( $header );
    }
    
}
