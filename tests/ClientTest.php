<?php

namespace Tests;

use SLS\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase {
  public function testClientInit() {
    $client = new Client('cn-hangzhou', 'accessKey', 'secretKey');
    $this->assertInstanceOf(Client::class, $client);
  }
}
