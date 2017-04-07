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

namespace Horde\GitTools\Action;

use Horde_Cli;

/**
 * Base class for Actions.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
abstract class Base
{
    /**
     * Configuration parameters.
     *
     * @var array
     */
    protected $_params;

    protected $_dependencies;

    /**
     * Const'r
     *
     * @param array $params  Configuration parameters
     */
    public function __construct(
        array $params = array(), \Components_Dependencies $dependencies)
    {
        $this->_dependencies = $dependencies;
        $this->_params = $params;
    }

    abstract public function run();
}
