<?php

namespace thgs\Functional\Internal;

// todo: change from object to ReflectionObject|ReflectionFunction
function getAttributeProperty(object $value, string $attribute, string $property): ?string
{
    $reflectionObject = new \ReflectionObject($value);

    $attributes = $reflectionObject->getAttributes($attribute);
    if (empty($attributes)) {
        throw new \TypeError("Expected attribute $attribute is missing.");
    }

    if (count($attributes) > 1) {
        throw new \Exception('Matching from multiple attributes not supported yet. Sorry!!');
    }

    $attribute = array_shift($attributes);
    [$property => $targetValue] = $attribute->getArguments();
    return $targetValue;
}
