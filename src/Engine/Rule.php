<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Engine;


use NicMart\Rulez\Expression\AndProposition;

class Rule
{
    /**
     * @var AndProposition
     */
    private $proposition;

    /**
     * @var
     */
    private $production;

    /**
     * @param AndProposition $proposition
     * @param callable $production
     */
    function __construct(AndProposition $proposition, $production)
    {
        $this->proposition = $proposition;
        $this->production = $production;
    }

    /**
     * Get Production
     *
     * @return callable
     */
    public function production()
    {
        return $this->production;
    }

    /**
     * Get AndProposition
     *
     * @return \NicMart\Rulez\Expression\AndProposition
     */
    public function proposition()
    {
        return $this->proposition;
    }
}