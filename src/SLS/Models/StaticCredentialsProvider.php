<?php

/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace SLS\Models;
use SLS\Models\Credentials;

class StaticCredentialsProvider implements CredentialsProvider
{
    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @param string $securityToken
     */
    public function __construct(string $accessKeyId, string $accessKeySecret, string $securityToken = '')
    {
        $this->credentials = new Credentials($accessKeyId, $accessKeySecret, $securityToken);
    }
    /**
     * @return Credentials
     */
    public function getCredentials(): Credentials
    {
        return $this->credentials;
    }
}