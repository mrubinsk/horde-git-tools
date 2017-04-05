<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (...). If you
 * did not receive this file, see http://www.horde.org/licenses/....
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/... ...
 * @package  GitTools
 */

namespace Horde\GitTools;

use Horde_Cli;
use Horde_Cli_Modular;
use Horde_Argv_Parser;

/**
 * Simple dependency container for injecting needed objects into module classes.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/... ...
 * @package   GitTools
 */
class Dependencies
{
    /**
     *
     * @var array
     */
    protected static $_cache;

    /**
     *
     * @param Horde_Argv_Parser $parser
     */
    public function setParser(Horde_Argv_Parser $parser)
    {
        self::$_cache['parser'] = $parser;
    }

    /**
     *
     * @return Horde_Arv_Options_Parser $parser
     */
    public function getParser()
    {
        return self::$_cache['parser'];
    }

    /**
     *
     * @param Horde_Cli $cli
     */
    public function setCli(Horde_Cli $cli)
    {
        self::$_cache['cli'] = $cli;
    }

    /**
     *
     * @return Horde_Cli
     */
    public function getCli()
    {
        return self::$_cache['cli'];
    }

    /**
     *
     * @param Horde_Cli_Modular $modular
     */
    public function setModular(Horde_Cli_Modular $modular)
    {
        self::$_cache['modular'] = $modular;
    }

    /**
     *
     * @return Horde_Cli_Modular
     */
    public function getModular()
    {
        return self::$_cache['modular'];
    }
}
