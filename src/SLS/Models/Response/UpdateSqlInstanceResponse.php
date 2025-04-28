<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace SLS\Models\Response;
use SLS\Models\Response\Response;

/**
 * The response of the UpdateSqlInstance API from log service.
 *
 * @author log service dev
 */
class UpdateSqlInstanceResponse extends Response {
    
    /**
     * UpdateSqlInstanceResponse constructor
     *
     * @param array $resp
     *            UpdateSqlInstance HTTP response body
     * @param array $header
     *            UpdateSqlInstance HTTP response header
     */
    public function __construct($resp, $header) {
        parent::__construct ( $header );
    }
    
}
