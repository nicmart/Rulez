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

/**
 * Class AndProposition
 * @package NicMart\Rulez\Condition
 */
class AndProposition implements CompositeExpression
{
    use CompositeExpressionTrait;

    /**
     * @param Expression[] $expressions
     */
    function __construct(array $expressions = [])
    {
        $this->addExpressions($expressions);
    }

    /**
     * @return callable
     */
    public function predicate()
    {
        return function($x)
        {
            foreach ($this->expressions() as $expression) {
                $subPredicate = $expression->predicate();
                if (!$subPredicate($x))
                    return false;
            }

            return true;
        };
    }

    function __toString()
    {
        return "(" . implode(" AND ", $this->expressions()) . ")";
    }


} 