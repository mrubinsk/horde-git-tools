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

use Components_Configs;
use Components_Dependencies;
use Horde_Argv_Option;
use Horde_Cli_Modular_Module;
use Horde_Cli_Modular_ModuleUsage;

/**
 * Base class for modular command handlers.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
abstract class Base
implements Horde_Cli_Modular_Module, Horde_Cli_Modular_ModuleUsage
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
     * @var \Components_Dependencies
     */
    protected $_dependencies;

    /**
     * Const'r
     *
     * @param \Components_Dependencies $dependencies Dependency container
     */
    public function __construct(Components_Dependencies $dependencies)
    {
        $this->_dependencies = $dependencies;
    }

    /**
     * Handles the module's actions.
     *
     * @param \Components_Config $config  The configuration object
     */
    abstract public function handle(Components_Configs $config);


    /**
     * Formatter for action help.
     *
     * @param  integer $indent  Indent this many spaces
     *
     * @return string  Formatted help text explaining each action this module
     *                 handles.
     */
    protected function _actionFormatter($indent = 0)
    {
        $help = "\n";
        $lengths = array_map('strlen', array_keys($this->getActions()));
        foreach ($this->getActions() as $action => $desc) {
            $help .= str_repeat(' ', $indent) . str_pad($action, max($lengths) + 2, ' ') . '  -  ' . $desc . "\n";
        }

        return $help;
    }

    /* Horde_Cli_Modular_Module */

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
     * Returns additional usage title for this module.
     *
     * @return string  The usage title.
     */
    public function getTitle()
    {
        return '';
    }

    /**
     * Returns additional usage description for this module.
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

    /**
     * Return the options that should be explained in the context help.
     *
     * @return array A list of option help texts.
     */
    public function getContextOptionHelp()
    {
        return array();
    }

}

