<?php

namespace Tests;

use Greenskies\Exception\JsonException;
use Greenskies\Json;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testValidatePass()
    {
        $arr = ['good' => true];
        $json = json_encode($arr);
        $result = Json::Validate($json);

        $this->assertTrue($result);
    }

    public function testValidateFails()
    {
        $json = '{{';
        $result = Json::Validate($json);

        $this->assertFalse($result);

        $json = '{]';
        $result = Json::Validate($json);

        $this->assertFalse($result);
    }

    public function testEncode()
    {
        $result = Json::Encode( ['good' => true]);
        $this->assertEquals('{"good":true}', $result);
    }

    public function testEncodeException()
    {
        $this->expectException(JsonException::class);
        Json::Decode( '*');

    }

}