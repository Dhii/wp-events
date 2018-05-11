<?php

namespace Dhii\EventManager\WordPress\FuncTest;

use Dhii\EventManager\WordPress\ReplaceWpHookCapableTrait as TestSubject;
use Exception as RootException;
use Mockery;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ReplaceWpHookCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\EventManager\WordPress\ReplaceWpHookCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return TestSubject|MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        $methods = $this->mergeValues(
            $methods,
            [
                '_createWpHookReplacement',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string   $className      Name of the class for the mock to extend.
     * @param string[] $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockObject The object that extends and implements the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf(
            'abstract class %1$s extends %2$s implements %3$s {}',
            [
                $paddingClassName,
                $className,
                implode(', ', $interfaceNames),
            ]
        );
        eval($definition);

        return $this->getMockForAbstractClass($paddingClassName);
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|MockObject The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
                     ->setConstructorArgs([$message])
                     ->getMock();

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests the `_replaceWpHook()` method to assert whether the WP_Hook instance in the WordPress `$wp_filter`
     * global variable is replaced with the replacement instance.
     *
     * @since [*next-version*]
     */
    public function testReplaceWpHook()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $hookName = uniqid('hook-');
        $wpHook = Mockery::mock('WP_Hook');
        $replacer = new stdClass();

        global $wp_filter;
        $wp_filter = [
            $hookName => $wpHook,
        ];

        $subject->expects($this->once())
                ->method('_createWpHookReplacement')
                ->with($wpHook)
                ->willReturn($replacer);

        $reflect->_replaceWpHook($hookName);

        $this->assertSame(
            $replacer,
            $wp_filter[$hookName],
            'The WP_Hook instance was not replaced with the created replacement instance.'
        );
    }

    /**
     * Tests the `_replaceWpHook()` method to assert whether the WP_Hook instances in the WordPress `$wp_filter`
     * global variable are left unchanged when no WP_Hook instance exists for the given hook name.
     *
     * @since [*next-version*]
     */
    public function testReplaceWpHookNoHook()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $hookName1 = uniqid('hook-');
        $hookName2 = uniqid('hook-');
        $wpHook = Mockery::mock('WP_Hook');

        global $wp_filter;
        $wp_filter = [
            $hookName2 => $wpHook,
        ];

        $subject->expects($this->never())
                ->method('_createWpHookReplacement');

        $reflect->_replaceWpHook($hookName1);
    }
}
