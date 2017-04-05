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

namespace Horde\GitTools\Module;

use Horde_Cli_Modular_Module;
use Horde_Argv_Option;

/**
 * Base class for modular command handlers.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
abstract class Base implements Horde_Cli_Modular_Module
{
    /**
     * The configuration parameters.
     *
     * @var array
     */
    protected $_params;

    /**
     * Dependency container
     *
     * @var \Horde\GitTools\Dependencies
     */
    protected $_dependencies;

    /**
     * Const'r
     *
     * @param \Horde\GitTools\Dependencies $dependencies [description]
     */
    public function __construct(\Horde\GitTools\Dependencies $dependencies)
    {
        $this->_dependencies = $dependencies;
    }

    /**
     * Handles the module's actions.
     *
     * @param  array $arguments  Argv arguments
     * @param  array $params    Configuration parameters.
     */
    abstract public function handle($arguments, $params);


    /**
     * Formatter for action help.
     *
     * @return string  Formatted help text explaining each action this module
     *                 handles.
     */
    protected function _actionFormatter()
    {
        $help = "\n";
        $lengths = array_map('strlen', array_keys($this->getActions()));
        foreach ($this->getActions() as $action => $desc) {
            $help .= str_pad($action, max($lengths) + 2, ' ') . '  -  ' . $desc . "\n";
        }

        return $help . "\n";
    }

/**Horde_Cli_Modular_Module**/

    /**
     * Returns the list of available actions for this module.
     *
     * @return  array
     */
    public function getActions()
    {
        return array();
    }

    /**
     * Returns the detailed help description for the module.
     *
     * This description is returned when help for a specific module is requested.
     *
     * @return string
     */
    public function getHelp()
    {
        return '';
    }

    /**
     * Returns usage description for this module.
     *
     * This description will be added after the automatically generated usage
     * line, so make sure to add any necessary line breaks or other separators.
     *
     * @return string  The description.
     */
    public function getUsage()
    {
        return '';
    }

    /**
     * Returns a set of base options that this module adds to the CLI argument
     * parser.
     *
     * @return array  Global options. A list of Horde_Argv_Option objects.
     */
    public function getBaseOptions()
    {
        return array();
    }

    /**
     * Returns whether the module provides an option group.
     *
     * @return boolean  True if an option group should be added.
     */
    public function hasOptionGroup()
    {
        return false;
    }

    /**
     * Returns the options for this module.
     *
     * @param  string $action   The ACTION to list options for.
     *
     * @return array  The group options. A list of Horde_Argv_Option objects
     *                that apply to the specified action.
     */
    public function getOptionGroupOptions($action = null)
    {
        return array();
    }

    /**
     * Returns the title for the option group representing this module.
     *
     * @return string  The group title.
     */
    public function getOptionGroupTitle()
    {
        return '';
    }

    /**
     * Returns the description for the option group representing this module.
     *
     * @return string  The group description.
     */
    public function getOptionGroupDescription()
    {
        return '';
    }

}

