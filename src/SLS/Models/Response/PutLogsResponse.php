<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace SLS\Models\Response;
use SLS\Models\Response\Response;
/**
 * The response of the PutLogs API from log service.
 *
 * @author log service dev
 */
class PutLogsResponse extends Response {
    /**
     * PutLogsResponse constructor
     *
     * @param array $header
     *            PutLogs HTTP response header
     */
    public function __construct($headers) {
        parent::__construct ( $headers );
    }
}
