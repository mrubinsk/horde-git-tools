<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @category Horde
 * @license  https://www.horde.org/licenses/bsd BSD
 * @package  GitTools
 */

namespace Horde\GitTools\Local;

/**
 * Base class for working with the checked out repositories on the local
 * filesystem.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
abstract class Base
{
    /**
     * List of available repositories.
     *
     * @var array
     */
    protected static $_repositories;

    /**
     * Const'r
     *
     * @param array   $params     Configuration parameters
     */
    public function __construct(array $params)
    {
        $this->_params = $params;
    }

}
