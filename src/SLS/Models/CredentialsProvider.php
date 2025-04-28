<?php

/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */
namespace SLS\Models;
interface CredentialsProvider
{
    /**
     * @return Credentials
     * @throws Exception
     */
    public function getCredentials(): Credentials;
}

