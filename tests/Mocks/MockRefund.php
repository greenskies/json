<?php

namespace Tests\Mocks;


class MockRefund
{
    /** @var bool */
    public $processRefund;
    /** @var int */
    public $refundAmount;

    public function getProcessRefund()
    {
        return $this->getProcessRefund();
    }

    public function getRefundAmount()
    {
        return $this->refundAmount;
    }



    public static function getJsonSchemaObject()
    {
        return (object) [
            "type"=>"object",
            "properties"=>(object)[
                "processRefund"=>(object)[
                    "type"=>"boolean"
                ],
                "refundAmount"=>(object)[
                    "type"=>"number"
                ]
            ]
        ];

    }
}