<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.3.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Test\TestCase\Http;

use Cake\Http\MiddlewareQueue;
use Cake\TestSuite\TestCase;
use TestApp\Middleware\SampleMiddleware;

/**
 * Test case for the MiddlewareQueue
 */
class MiddlewareQueueTest extends TestCase
{
    /**
     * Test get()
     *
     * @return void
     */
    public function testGet()
    {
        $stack = new MiddlewareQueue();
        $cb = function () {
        };
        $stack->add($cb);
        $this->assertSame($cb, $stack->get(0));
        $this->assertNull($stack->get(1));
    }


    /**
     * Test the return value of add()
     *
     * @return void
     */
    public function testPushReturn()
    {
        $stack = new MiddlewareQueue();
        $cb = function () {
        };
        $this->assertSame($stack, $stack->add($cb));
    }

    /**
     * Test the add orders correctly
     *
     * @return void
     */
    public function testPushOrdering()
    {
        $one = function () {
        };
        $two = function () {
        };

        $stack = new MiddlewareQueue();
        $this->assertCount(0, $stack);

        $stack->add($one);
        $this->assertCount(1, $stack);

        $stack->add($two);
        $this->assertCount(2, $stack);

        $this->assertSame($one, $stack->get(0));
        $this->assertSame($two, $stack->get(1));
    }

    /**
     * Test the prepend can be chained
     *
     * @return void
     */
    public function testPrependReturn()
    {
        $cb = function () {
        };
        $stack = new MiddlewareQueue();
        $this->assertSame($stack, $stack->prepend($cb));
    }

    /**
     * Test the prepend orders correctly.
     *
     * @return void
     */
    public function testPrependOrdering()
    {
        $one = function () {
        };
        $two = function () {
        };

        $stack = new MiddlewareQueue();
        $this->assertCount(0, $stack);

        $stack->add($one);
        $this->assertCount(1, $stack);

        $stack->prepend($two);
        $this->assertCount(2, $stack);

        $this->assertSame($two, $stack->get(0));
        $this->assertSame($one, $stack->get(1));
    }

    /**
     * Test insertAt ordering
     *
     * @return void
     */
    public function testInsertAt()
    {
        $one = function () {
        };
        $two = function () {
        };
        $three = function () {
        };

        $stack = new MiddlewareQueue();
        $stack->add($one)->add($two)->insertAt(0, $three);
        $this->assertSame($three, $stack->get(0));
        $this->assertSame($one, $stack->get(1));
        $this->assertSame($two, $stack->get(2));

        $stack = new MiddlewareQueue();
        $stack->add($one)->add($two)->insertAt(1, $three);
        $this->assertSame($one, $stack->get(0));
        $this->assertSame($three, $stack->get(1));
        $this->assertSame($two, $stack->get(2));
    }

    /**
     * Test insertAt out of the existing range
     *
     * @return void
     */
    public function testInsertAtOutOfBounds()
    {
        $one = function () {
        };
        $two = function () {
        };

        $stack = new MiddlewareQueue();
        $stack->add($one)->insertAt(99, $two);

        $this->assertCount(2, $stack);
        $this->assertSame($one, $stack->get(0));
        $this->assertSame($two, $stack->get(1));
    }

    /**
     * Test insertAt with a negative index
     *
     * @return void
     */
    public function testInsertAtNegative()
    {
        $one = function () {
        };
        $two = function () {
        };

        $stack = new MiddlewareQueue();
        $stack->add($one)->insertAt(-1, $two);

        $this->assertCount(2, $stack);
        $this->assertSame($two, $stack->get(0));
        $this->assertSame($one, $stack->get(1));
    }

    /**
     * Test insertBefore
     *
     * @return void
     */
    public function testInsertBefore()
    {
        $one = function () {
        };
        $two = new SampleMiddleware();
        $three = function () {
        };
        $stack = new MiddlewareQueue();
        $stack->add($one)->add($two)->insertBefore(SampleMiddleware::class, $three);

        $this->assertCount(3, $stack);
        $this->assertSame($one, $stack->get(0));
        $this->assertSame($three, $stack->get(1));
        $this->assertSame($two, $stack->get(2));
    }

    /**
     * Test insertBefore an invalid classname
     *
     * @expectedException LogicException
     * @expectedExceptionMessage No middleware matching 'InvalidClassName' could be found.
     * @return void
     */
    public function testInsertBeforeInvalid()
    {
        $one = function () {
        };
        $two = new SampleMiddleware();
        $three = function () {
        };
        $stack = new MiddlewareQueue();
        $stack->add($one)->add($two)->insertBefore('InvalidClassName', $three);
    }

    /**
     * Test insertAfter
     *
     * @return void
     */
    public function testInsertAfter()
    {
        $one = new SampleMiddleware();
        $two = function () {
        };
        $three = function () {
        };
        $stack = new MiddlewareQueue();
        $stack->add($one)->add($two)->insertAfter(SampleMiddleware::class, $three);

        $this->assertCount(3, $stack);
        $this->assertSame($one, $stack->get(0));
        $this->assertSame($three, $stack->get(1));
        $this->assertSame($two, $stack->get(2));
    }

    /**
     * Test insertAfter an invalid classname
     *
     * @return void
     */
    public function testInsertAfterInvalid()
    {
        $one = new SampleMiddleware();
        $two = function () {
        };
        $three = function () {
        };
        $stack = new MiddlewareQueue();
        $stack->add($one)->add($two)->insertAfter('InvalidClass', $three);

        $this->assertCount(3, $stack);
        $this->assertSame($one, $stack->get(0));
        $this->assertSame($two, $stack->get(1));
        $this->assertSame($three, $stack->get(2));
    }
}