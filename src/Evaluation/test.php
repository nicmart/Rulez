<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

class A
{
    /**
     * @var A
     */
    private $a;

    function __construct(A $a = null)
    {
        $this->a = $a;
    }

    /** @return $this */
    function iAmA() { return $this; }

    /**
     * @return B
     */
    function b()
    {
        return new B;
    }

    /**
     * @return A|B
     */
    public function uuu()
    {
        return $this->a;
    }
}

class B extends A {

    /** @return $this */
    function iAmB() { return $this; }
}

trait ArrayHost
{
    function ary() { return new ArrayBuilder; }
}

class ArrayBuilder
{

}
