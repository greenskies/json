<?php

namespace Tests;

use Greenskies\Json;
use JsonSchema\Constraints\Constraint;
use PHPUnit\Framework\TestCase;
use Tests\Mocks\MockRefund;

class SchemaValidateTest extends TestCase
{
    public function testSchema()
    {
        $constraints =  Constraint::CHECK_MODE_APPLY_DEFAULTS | Constraint::CHECK_MODE_COERCE_TYPES;

        $schema = MockRefund::getJsonSchemaObject();

        $data = json_encode((object)[
            'processRefund'=>"true",
            'refundAmount'=>"17"
        ]);

        $options = [
            JSON::JSON_SCHEMA => $schema,
            JSON::CONSTRAINTS => $constraints
        ];

        $results = Json::Decode($data, $options);

        $this->assertIsBool($results->processRefund);
        $this->assertIsInt($results->refundAmount);
    }

    public function testNewClass()
    {
        $constraints =  Constraint::CHECK_MODE_APPLY_DEFAULTS | Constraint::CHECK_MODE_COERCE_TYPES;

        $schema = MockRefund::getJsonSchemaObject();

        $data = json_encode((object)[
            'processRefund'=>"true",
            'refundAmount'=>"17"
        ]);

        $options = [
            JSON::JSON_SCHEMA => $schema,
            JSON::CONSTRAINTS => $constraints,
            JSON::CLASS_NAME => MockRefund::class,
        ];

        /** @var MockRefund $results */
        $results = Json::Decode($data, $options);

        var_dump($results);

        $this->assertInstanceOf(MockRefund::class, $results);
        $this->assertEquals(17, $results->getRefundAmount());
    }

}