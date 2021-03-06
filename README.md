[![Codacy Badge](https://api.codacy.com/project/badge/Grade/85272d5b41c3471d95f6ceaa32e0e90b)](https://app.codacy.com/app/eidsonator/json?utm_source=github.com&utm_medium=referral&utm_content=greenskies/json&utm_campaign=Badge_Grade_Dashboard)
[![author](https://img.shields.io/badge/author-%40toddeidson-blue.svg)](https://eidson.info)
[![Coverage Status](https://coveralls.io/repos/github/greenskies/json/badge.svg?branch=master)](https://coveralls.io/github/greenskies/json?branch=master)
[![Build Status](https://travis-ci.org/greenskies/json.svg?branch=master)](https://travis-ci.org/greenskies/json)
# Greenskies Json 

A PHP library to improve decoding - including schema validation and decoding to a class

It is basically a wrapper for:
- [karriereat/json-decoder]
- [justinrainbow/json-schema]
With a little syntatic sugar and exceptions, even on PHP < 7.3


## Installation

```bash
composer require greenskies/json
```

## Decoding

To decode a json string simply pass the string to `Json::Decode()`
This will return a standard object

```php
$jsonString = '{"good":true}';

$decoded = Json::Decode($jsonString);

// $decoded->good = true
```

```php
$jsonString = '{"good":true}';
$options = [
    Json::ASSOCIATIVE => true,
];
$decoded = Json::Decode($jsonString, $options);
// $decoded['good'] = true
```

## Schema Validation

```php

$jsonString = '{"processRefund": "true", "refundAmount": "17"}'
                             
$schema = (object) [
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

$options = [
    Json::VALIDATOR => [
        Json::JSON_SCHEMA => $schema,
        Json::CONSTRAINTS => Constraint::CHECK_MODE_COERCE_TYPES,
    ],
];

$decoded = Json::Decode($jsonString, $options);
```
For further instructions visit [justinrainbow/json-schema]

## Decode to a class

```php
$jsonString = '{"id": 1, "name": "John Doe"}';

$options = [
    Json::DECODER => [
        Json::CLASS_NAME => Person::class,       
    ],
];
$decoded = Json::Decode($jsonString, $options);
```

### Decode Multiple

```php
$jsonString = '[{"id": 1, "name": "John Doe"}, {"id": 2, "name": "Jane Doe"}]';

$options = [
    Json::DECODER => [
        Json::CLASS_NAME => Person::class,
        Json::DECODE_MULTIPLE => true,
    ],
];

$personArray = Json::Decode($jsonString, $options);
```

For further instructions visit [karriereat/json-decoder]

[karriereat/json-decoder]: https://github.com/karriereat/json-decoder
[justinrainbow/json-schema]: https://github.com/justinrainbow/json-schema