<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Builder;

use NicMart\Rulez\Expression\OrProposition;

class OrPropositionBuilder extends CompositeExpressionBuilder
{
    /**
     * @param callable $callback
     */
    public function __construct(callable $callback = null)
    {
        return parent::__construct($callback, new OrProposition());
    }
}