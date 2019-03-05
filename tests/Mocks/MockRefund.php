<?php

namespace Tests\Mocks;

class MockRefund
{
    /** @var bool */
    public $processRefund;
    /** @var int */
    public $refundAmount;
    /** @var string */
    private $name = null;
    /** @var string */
    protected $uid;

    public function getProcessRefund()
    {
        return $this->processRefund;
    }

    public function getRefundAmount()
    {
        return $this->refundAmount;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public static function getJsonSchemaObject()
    {
        return (object) [
            'type' => 'object',
            'properties' => (object) [
                'processRefund' => (object) [
                    'type' => 'boolean',
                    'required' => true,
                ],
                'refundAmount' => (object) [
                    'type' => 'number',
                ],
                'name' => (object) [
                    'type' => 'string',
                ],
            ],
        ];
    }
}
