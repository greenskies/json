<?php

namespace Greenskies;

use Karriere\JsonDecoder\Bindings\RawBinding;

class JsonDecodeToClass
{
    public const PRIVATE = 'private';
    public const PROTECTED = 'protected';
    public const TRANSFORMERS = 'transformers';

    private $transformers = [];

    private $decodePrivateProperties;

    private $decodeProtectedProperties;

    public function __construct(array $options = [])
    {
        $this->decodePrivateProperties = $options[self::PRIVATE];
        $this->decodeProtectedProperties = $options[self::PROTECTED];

        /** @var Transformer $transformer */
        foreach ($options[self::TRANSFORMERS] as $transformer) {
            $this->transformers[$transformer->transforms()] = $transformer;
        }
    }

    /**
     * @param $json
     * @param string $classType
     *
     * @return mixed|null
     *
     * @throws \Karriere\JsonDecoder\Exceptions\InvalidBindingException
     */
    public function decodeArray($json, string $classType)
    {
        $instance = new $classType();

        $jsonArrayData = (array) $json;

        if (array_key_exists($classType, $this->transformers)) {
            $instance = $this->transform($this->transformers[$classType], $jsonArrayData, $instance);
        } else {
            $instance = $this->transformRaw($jsonArrayData, $instance);
        }

        return $instance;
    }

    public function decodesPrivateProperties()
    {
        return $this->decodePrivateProperties;
    }

    public function decodesProtectedProperties()
    {
        return $this->decodeProtectedProperties;
    }

    /**
     * @param Transformer $transformer
     * @param $jsonArrayData
     * @param $instance
     *
     * @return mixed|null
     */
    private function transform(Transformer $transformer, $jsonArrayData, $instance)
    {
        if (empty($jsonArrayData)) {
            return null;
        }

        $classBindings = new ClassBindings($this);
        $transformer->register($classBindings);

        return $classBindings->decode($jsonArrayData, $instance);
    }

    /**
     * @param $jsonArrayData
     * @param $instance
     *
     * @return mixed|null
     *
     * @throws \Karriere\JsonDecoder\Exceptions\InvalidBindingException
     */
    protected function transformRaw($jsonArrayData, $instance)
    {
        if (empty($jsonArrayData)) {
            return null;
        }

        $classBindings = new ClassBindings($this);

        foreach (array_keys($jsonArrayData) as $property) {
            $classBindings->register(new RawBinding($property));
        }

        return $classBindings->decode($jsonArrayData, $instance);
    }
}
