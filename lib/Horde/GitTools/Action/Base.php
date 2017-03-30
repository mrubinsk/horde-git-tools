<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl LGPL
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
 * @license   http://www.horde.org/licenses/lgpl LGPL
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

    /**
     * Const'r
     *
     * @param array $params  Configuration parameters
     */
    public function __construct(array $params = array())
    {
        $this->_params = $params;

        // Make sure no one runs this from the web.
        if (!Horde_Cli::runningFromCLI()) {
            exit;
        }
    }

    abstract public function run();
}
