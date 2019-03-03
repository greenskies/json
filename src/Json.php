<?php

namespace Greenskies;

use Greenskies\Exception\JsonException;
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
    public const TRANSFORMERS ='transformers';
    public const CLASS_NAME = 'class_name';


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


        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * @param string $json
     * @param array
     * @return mixed
     * @throws JsonException
     * @throws \JsonException
     */
    public static function Decode(string $json, array $options = [])
    {
        $options = static::resolveOptions($options);
        if (PHP_VERSION_ID >= 70300) {
            $results = json_decode($json, $options['associative'], $options['depth'], JSON_THROW_ON_ERROR);
        } else {
            $results = json_decode($json, $options['associative'], $options['depth']);
            if (json_last_error()) {
                throw new JsonException('JSON Syntax Error');
            }
        }

        if (!self::validateSchema($results, $options)) {
            throw new JsonException('Schema not validated');
        };

        if ($options[static::CLASS_NAME] !== null) {
            $results = static::jsonToClass($results, $options);
        }

        return $results;
    }

    public static function jsonToClass($json, $options)
    {
        $decoder = new JsonDecodeToClass();
        return $decoder->decode($json, $options[static::CLASS_NAME]);
    }

    public static function validateSchema($data, array $options = [])
    {
        $options = static::resolveOptions($options);
        $validator = new Validator();
        $validator->validate($data, $options[static::JSON_SCHEMA], $options[static::CONSTRAINTS]);

        return $validator->isValid();
    }

    private static function resolveOptions($options)
    {

        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'associative' => false,
            'depth' => 512,
            'private' => false,
            'protected' => false,
            'transformers' => [],
            'json_schema' => null,
            static::CONSTRAINTS => 0,
            static::CLASS_NAME => null
        ]);

        return $resolver->resolve($options);
    }

    public static function Encode($object, int $options = 0, int $depth = 512)
    {
        $result = json_encode($object, $options, $depth);
        if (!Json::Validate($result)) {
            throw new JsonException('Not a valid Json String');
        }
        return $result;
    }

}