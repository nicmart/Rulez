<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Expression;


interface CompositeExpression extends Expression
{
    /**
     * @param Expression $expression
     * @return $this
     */
    function addExpression(Expression $expression);

    /**
     * @param Expression[] $expressions
     * @return $this
     */
    function addExpressions(array $expressions);

    /**
     * @return Expression[]
     */
    function expressions();
} 