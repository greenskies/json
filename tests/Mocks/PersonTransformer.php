<?php
/**
 * User: todd@eidson.info
 * Date: 3/4/19
 * Time: 2:10 PM.
 */

namespace Tests\Mocks;

use Greenskies\ClassBindings;
use Greenskies\Transformer;
use Karriere\JsonDecoder\Bindings\FieldBinding;

class PersonTransformer implements Transformer
{
    /**
     * register field, array, alias and callback bindings.
     *
     * @param ClassBindings $classBindings
     *
     * @throws \Karriere\JsonDecoder\Exceptions\InvalidBindingException
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new FieldBinding('address', 'address', Address::class));
    }

    /**
     * @return string the full qualified class name that the transformer transforms
     */
    public function transforms()
    {
        return Person::class;
    }
}
