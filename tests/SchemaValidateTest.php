<?php

namespace Tests;

use Greenskies\Exception\JsonException;
use Greenskies\Json;
use JsonSchema\Constraints\Constraint;
use PHPUnit\Framework\TestCase;
use Tests\Mocks\Address;
use Tests\Mocks\MockRefund;
use Tests\Mocks\Person;
use Tests\Mocks\PersonTransformer;

class SchemaValidateTest extends TestCase
{
    public function testSchema()
    {
        $constraints = Constraint::CHECK_MODE_APPLY_DEFAULTS | Constraint::CHECK_MODE_COERCE_TYPES;

        $schema = MockRefund::getJsonSchemaObject();

        $data = json_encode((object) [
            'processRefund' => 'true',
            'refundAmount' => '17',
        ]);

        $options = [
            Json::VALIDATOR => [
                Json::JSON_SCHEMA => $schema,
                Json::CONSTRAINTS => $constraints,
            ],
        ];

        $results = Json::Decode($data, $options);

        $this->assertIsBool($results->processRefund);
        $this->assertIsInt($results->refundAmount);
    }

    public function testNewClass()
    {
        $constraints = Constraint::CHECK_MODE_APPLY_DEFAULTS | Constraint::CHECK_MODE_COERCE_TYPES;

        $schema = MockRefund::getJsonSchemaObject();

        $data = json_encode((object) [
            'processRefund' => 'true',
            'refundAmount' => '17',
        ]);

        $options = [
            Json::VALIDATOR => [
                Json::JSON_SCHEMA => $schema,
                Json::CONSTRAINTS => $constraints,
            ],
            Json::DECODER => [
                Json::CLASS_NAME => MockRefund::class,
            ],
        ];

        /** @var MockRefund $results */
        $results = Json::Decode($data, $options);

        $this->assertInstanceOf(MockRefund::class, $results);
        $this->assertEquals(17, $results->getRefundAmount());
    }

    public function testDecodePrivate()
    {
        $constraints = Constraint::CHECK_MODE_APPLY_DEFAULTS | Constraint::CHECK_MODE_COERCE_TYPES;

        $schema = MockRefund::getJsonSchemaObject();

        $data = json_encode((object) [
            'processRefund' => 'true',
            'refundAmount' => '17',
            'name' => 'Bill',
        ]);

        $options = [
            Json::VALIDATOR => [
                Json::JSON_SCHEMA => $schema,
                Json::CONSTRAINTS => $constraints,
            ],
            Json::DECODER => [
                Json::CLASS_NAME => MockRefund::class,
                Json::PRIVATE => true,
            ],
        ];

        /** @var MockRefund $results */
        $results = Json::Decode($data, $options);

        $this->assertEquals('Bill', $results->getName());
    }

    public function testDecodeProtected()
    {
        $constraints = Constraint::CHECK_MODE_APPLY_DEFAULTS | Constraint::CHECK_MODE_COERCE_TYPES;

        $schema = MockRefund::getJsonSchemaObject();

        $data = json_encode((object) [
            'processRefund' => 'true',
            'refundAmount' => '17',
            'name' => 'Bill',
            'uid' => '867-5309',
        ]);

        $options = [
            Json::VALIDATOR => [
                Json::JSON_SCHEMA => $schema,
                Json::CONSTRAINTS => $constraints,
            ],
            Json::DECODER => [
                Json::CLASS_NAME => MockRefund::class,
                Json::PROTECTED => true,
            ],
        ];

        /** @var MockRefund $results */
        $results = Json::Decode($data, $options);

        $this->assertNull($results->getName());
        $this->assertEquals('867-5309', $results->getUid());
    }

    public function testDecodeMultiple()
    {
        $constraints = Constraint::CHECK_MODE_APPLY_DEFAULTS | Constraint::CHECK_MODE_COERCE_TYPES;

        $schema = MockRefund::getJsonSchemaObject();

        $data = json_encode((object) [
            [
                'processRefund' => 'true',
                'refundAmount' => '17',
            ],
            [
                'processRefund' => 'false',
                'refundAmount' => '19',
            ],
        ]);

        $options = [
            Json::VALIDATOR => [
                Json::JSON_SCHEMA => $schema,
                Json::CONSTRAINTS => $constraints,
            ],
            Json::DECODER => [
                Json::CLASS_NAME => MockRefund::class,
                Json::DECODE_MULTIPLE => true,
            ],
        ];

        /** @var MockRefund $results */
        $results = Json::Decode($data, $options);

        $this->assertInstanceOf(MockRefund::class, $results[0]);
        $this->assertEquals(17, $results[0]->getRefundAmount());

        $this->assertIsInt($results[1]->getRefundAmount());
    }

    public function testDecodeMultipleException()
    {
        if (PHP_VERSION_ID >= 70300) {
            $this->expectException(\JsonException::class);
        } else {
            $this->expectException(JsonException::class);
        }

        $constraints = Constraint::CHECK_MODE_APPLY_DEFAULTS | Constraint::CHECK_MODE_COERCE_TYPES;

        $schema = MockRefund::getJsonSchemaObject();

        $data = json_encode((object) [
            [
                'processRefund' => 'true',
                'refundAmount' => '17',
            ],
            [
                'refundAmount' => '19',
            ],
        ]);

        $options = [
            Json::VALIDATOR => [
                Json::JSON_SCHEMA => $schema,
                Json::CONSTRAINTS => $constraints,
            ],
            Json::DECODER => [
                Json::CLASS_NAME => MockRefund::class,
                Json::DECODE_MULTIPLE => true,
            ],
        ];

        /* @var MockRefund $results */
        Json::Decode($data, $options);
    }

    public function testTransformer()
    {
        $data = json_encode([
            'name' => 'George',
            'address' => [
                'streetAddress' => 'some street',
                'city' => 'somewhere',
            ],
        ]);

        $options = [
            Json::DECODER => [
                Json::CLASS_NAME => Person::class,
                Json::TRANSFORMERS => [new PersonTransformer()],
            ],
        ];

        /** @var Person $person */
        $person = Json::Decode($data, $options);

        $this->assertInstanceOf(Address::class, $person->address);
    }

    public function testValidateSchemaEncode()
    {
        $schema = MockRefund::getJsonSchemaObject();

        $data = (object) [
            'processRefund' => true,
            'refundAmount' => 17,
        ];

        $options = [
            Json::VALIDATOR => [
                Json::JSON_SCHEMA => $schema,
            ],
        ];

        $results = Json::Encode($data, $options);
        $this->assertEquals('{"processRefund":true,"refundAmount":17}', $results);
    }

    public function testValidateSchemaException()
    {
        if (PHP_VERSION_ID >= 70300) {
            $this->expectException(\JsonException::class);
        } else {
            $this->expectException(JsonException::class);
        }

        $schema = MockRefund::getJsonSchemaObject();

        $data = (object) [
            'refundAmount' => 17,
        ];

        $options = [
            Json::VALIDATOR => [
                Json::JSON_SCHEMA => $schema,
            ],
        ];

        Json::Encode($data, $options);
    }
}
