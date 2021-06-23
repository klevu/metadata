<?php

namespace Klevu\Metadata\Traits;

/**
 * Polyfill class to provide strict typing checks given PHP backward compatibility requirements
 */
trait ArgumentValidationTrait
{
    /**
     * Polyfill for (string) argument typing validation
     *
     * @param mixed $value Passed argument value
     * @param string $methodName Calling method name
     * @param string $argumentName Argument name in calling method
     * @param bool $acceptsNull Whether null is allowed (ie string vs ?string)
     */
    private function validateStringArgument($value, $methodName, $argumentName, $acceptsNull = false)
    {
        if (!is_string($value) && (!$acceptsNull || null !== $value)) {
            $expectedType = $acceptsNull ? 'string|null' : 'string';
            $this->throwValidationInvalidArgumentException($value, $methodName, $argumentName, $expectedType);
        }
    }

    /**
     * Polyfill for (int) argument typing validation
     *
     * @param mixed $value Passed argument value
     * @param string $methodName Calling method name
     * @param string $argumentName Argument name in calling method
     * @param bool $acceptsNull Whether null is allowed (ie int vs ?int)
     */
    private function validateIntArgument($value, $methodName, $argumentName, $acceptsNull = false)
    {
        if (!is_int($value) && (!$acceptsNull || null !== $value)) {
            $expectedType = $acceptsNull ? 'int|null' : 'string';
            $this->throwValidationInvalidArgumentException($value, $methodName, $argumentName, $expectedType);
        }
    }

    /**
     * Polyfill for (float) argument typing validation
     *
     * @param mixed $value Passed argument value
     * @param string $methodName Calling method name
     * @param string $argumentName Argument name in calling method
     * @param bool $acceptsNull Whether null is allowed (ie float vs ?float)
     */
    private function validateFloatArgument($value, $methodName, $argumentName, $acceptsNull = false)
    {
        if (!is_float($value) && (!$acceptsNull || null !== $value)) {
            $expectedType = $acceptsNull ? 'float|null' : 'string';
            $this->throwValidationInvalidArgumentException($value, $methodName, $argumentName, $expectedType);
        }
    }

    /**
     * Polyfill for (bool) argument typing validation
     *
     * @param mixed $value Passed argument value
     * @param string $methodName Calling method name
     * @param string $argumentName Argument name in calling method
     * @param bool $acceptsNull Whether null is allowed (ie bool vs ?bool)
     */
    private function validateBoolArgument($value, $methodName, $argumentName, $acceptsNull = false)
    {
        if (!is_bool($value) && (!$acceptsNull || null !== $value)) {
            $expectedType = $acceptsNull ? 'bool|null' : 'string';
            $this->throwValidationInvalidArgumentException($value, $methodName, $argumentName, $expectedType);
        }
    }

    /**
     * Throws InvalidArgumentException with passed parameters in message to keep things DRY
     *
     * @param mixed $value
     * @param string $methodName
     * @param string $argumentName
     * @param string $expectedType
     * @throws \InvalidArgumentException
     */
    private function throwValidationInvalidArgumentException($value, $methodName, $argumentName, $expectedType)
    {
        throw new \InvalidArgumentException(sprintf(
            'TypeError: %s: Argument %s must be of type %s, %s given',
            $methodName,
            $argumentName,
            $expectedType,
            gettype($value) // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        ));
    }
}
