<?php

namespace Greenskies;

use Karriere\JsonDecoder\Bindings\RawBinding;
use Karriere\JsonDecoder\Exceptions\InvalidBindingException;
use Karriere\JsonDecoder\PropertyAccessor;
use Karriere\JsonDecoder\Binding;

class ClassBindings
{
    /**
     * @var array
     */
    private $bindings = [];

    /**
     * @var JsonDecoder
     */
    private $jsonDecoder;

    public function __construct(JsonDecodeToClass $jsonDecoder)
    {
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * decodes all available properties of the given class instance.
     *
     * @param $data
     * @param $instance
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public function decode($data, $instance)
    {
        $reflectionClass = new \ReflectionClass($instance);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $needsProxy = false;

            if ($property->isPrivate()) {
                if ($this->jsonDecoder->decodesPrivateProperties()) {
                    $needsProxy = true;
                } else {
                    continue;
                }
            } else {
                if ($property->isProtected()) {
                    if ($this->jsonDecoder->decodesProtectedProperties()) {
                        $needsProxy = true;
                    } else {
                        continue;
                    }
                }
            }

            $propertyAccessor = new PropertyAccessor($property, $instance, $needsProxy);

            $propertyName = $property->getName();
            if ($this->hasBinding($propertyName)) {
                /** @var Binding $binding */
                $binding = $this->bindings[$propertyName];
                $binding->bind($this->jsonDecoder, $data, $propertyAccessor);
            } else {
                $this->handleRaw($propertyName, $data, $propertyAccessor);
            }
        }

        return $instance;
    }

    /**
     * @param Binding $binding
     *
     * @throws InvalidBindingException
     */
    public function register($binding)
    {
        if (!$binding instanceof Binding) {
            throw new InvalidBindingException('the given binding must implement the Binding interface');
        }

        $this->bindings[$binding->property()] = $binding;
    }

    /**
     * checks for a binding for the given property.
     *
     * @param string $property
     *
     * @return bool
     */
    private function hasBinding($property)
    {
        return array_key_exists($property, $this->bindings);
    }

    private function handleRaw($property, $data, $propertyAccessor)
    {
        (new RawBinding($property))->bind($this->jsonDecoder, $data, $propertyAccessor);
    }
}
