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


trait CompositeExpressionTrait
{
    /**
     * @var Expression[]
     */
    private $expressions = [];

    /**
     * @param Expression $expression
     * @return $this
     */
    function addExpression(Expression $expression)
    {
        $this->expressions[] = $expression;

        return $this;
    }

    /**
     * @param Expression[] $expressions
     * @return $this
     */
    function addExpressions(array $expressions)
    {
        foreach ($expressions as $condition)
            $this->addExpression($condition);

        return $this;
    }

    /**
     * @return Expression[]
     */
    function expressions()
    {
        return $this->expressions;
    }
} 