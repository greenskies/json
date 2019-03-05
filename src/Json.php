<?php

namespace Greenskies;

use Greenskies\Exception\JsonException;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Json
{
    public const JSON_SCHEMA = 'json_schema';
    public const CONSTRAINTS = 'constraints';
    public const ASSOCIATIVE = 'associative';
    public const DEPTH = 'depth';
    public const PRIVATE = 'private';
    public const PROTECTED = 'protected';
    public const TRANSFORMERS = 'transformers';
    public const CLASS_NAME = 'class_name';
    public const DECODE_MULTIPLE = 'decode_multiple';
    public const DECODER = 'decoder';
    public const VALIDATOR = 'validator';

    public static function Validate(string $json, array $options = [])
    {
        $options = static::resolveOptions($options);
        // https://stackoverflow.com/a/3845829/2249502
        $pcre_regex = '
  /
  (?(DEFINE)
     (?<number>   -? (?= [1-9]|0(?!\d) ) \d+ (\.\d+)? ([eE] [+-]? \d+)? )    
     (?<boolean>   true | false | null )
     (?<string>    " ([^"\\\\]* | \\\\ ["\\\\bfnrt\/] | \\\\ u [0-9a-f]{4} )* " )
     (?<array>     \[  (?:  (?&json)  (?: , (?&json)  )*  )?  \s* \] )
     (?<pair>      \s* (?&string) \s* : (?&json)  )
     (?<object>    \{  (?:  (?&pair)  (?: , (?&pair)  )*  )?  \s* \} )
     (?<json>   \s* (?: (?&number) | (?&boolean) | (?&string) | (?&array) | (?&object) ) \s* )
  )
  \A (?&json) \Z
  /six   
';
        preg_match($pcre_regex, $json, $matches);
        if (!$matches) {
            return false;
        }

        json_decode($json);

        return JSON_ERROR_NONE === json_last_error();
    }

    /**
     * @param string $json
     * @param array
     *
     * @return mixed
     *
     * @throws JsonException
     * @throws \JsonException
     * @throws \Karriere\JsonDecoder\Exceptions\InvalidBindingException
     */
    public static function Decode(string $json, array $options = [])
    {
        $options = static::resolveOptions($options);
        if (PHP_VERSION_ID >= 70300) {
            $results = json_decode($json, $options[static::ASSOCIATIVE], $options[static::DEPTH], JSON_THROW_ON_ERROR);
        } else {
            $results = json_decode($json, $options[static::ASSOCIATIVE], $options[static::DEPTH]);
            if (json_last_error()) {
                throw new JsonException('JSON Syntax Error');
            }
        }

        if ($options[static::VALIDATOR][static::JSON_SCHEMA] && !self::validateSchema($results, $options)) {
            throw new JsonException('Schema not validated');
        }

        if (null !== $options[static::DECODER][static::CLASS_NAME]) {
            $results = static::jsonToClass($results, $options);
        }

        return $results;
    }

    /**
     * @param $jsonObject
     * @param $options
     *
     * @return array|mixed|null
     *
     * @throws \Karriere\JsonDecoder\Exceptions\InvalidBindingException
     */
    public static function jsonToClass($jsonObject, $options)
    {
        $decoder = new JsonDecodeToClass($options[static::DECODER]);
        if ($options[static::DECODER][static::DECODE_MULTIPLE]) {
            $results = [];
            foreach ((array) $jsonObject as $object) {
                $results[] = $decoder->decodeArray($object, $options[static::DECODER][static::CLASS_NAME]);
            }

            return $results;
        } else {
            return $decoder->decodeArray($jsonObject, $options[static::DECODER][static::CLASS_NAME]);
        }
    }

    public static function validateSchema(&$data, array $options = [])
    {
        $options = static::resolveOptions($options);
        $validator = new Validator();
        if ($options[static::DECODER][static::DECODE_MULTIPLE]) {
            foreach ((array) $data as &$datum) {
                $validator->validate($datum, $options[static::VALIDATOR][static::JSON_SCHEMA], $options[static::VALIDATOR][static::CONSTRAINTS]);
                if (!$validator->isValid()) {
                    return false;
                }
            }

            return true;
        } else {
            $validator->validate($data, $options[static::VALIDATOR][static::JSON_SCHEMA], $options[static::VALIDATOR][static::CONSTRAINTS]);

            return $validator->isValid();
        }
    }

    private static function resolveOptions($options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            static::ASSOCIATIVE => false,
            static::DEPTH => 512,

            static::VALIDATOR => function (OptionsResolver $validatorResolver) {
                $validatorResolver->setDefaults([
                    static::JSON_SCHEMA => null,
                    static::CONSTRAINTS => Constraint::CHECK_MODE_NONE,
                ]);
                $validatorResolver->setAllowedTypes(static::CONSTRAINTS, 'int');
            },

            static::DECODER => function (OptionsResolver $decoderResolver) {
                $decoderResolver->setDefaults([
                    static::PRIVATE => false,
                    static::PROTECTED => false,
                    static::TRANSFORMERS => [],
                    static::CLASS_NAME => null,
                    static::DECODE_MULTIPLE => false,
                ]);
            },
        ]);

        return $resolver->resolve($options);
    }

    /**
     * @param $object
     * @param int $options
     * @param int $depth
     *
     * @return false|string
     *
     * @throws JsonException
     */
    public static function Encode($object, int $options = 0, int $depth = 512)
    {
        $result = json_encode($object, $options, $depth);
        if (!Json::Validate($result)) {
            throw new JsonException('Not a valid Json String');
        }

        return $result;
    }
}
