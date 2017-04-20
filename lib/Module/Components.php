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

use Horde\GitTools\Action;
use Horde_Argv_Option;
use Horde_Http_Client;

/**
 * Class for handling 'component' actions.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Components extends Base
{

    /**
     * Handles the module's actions.
     *
     * @param  array $arguments  Argv arguments
     * @param  array $params    Configuration parameters.
     */
    public function handle(\Components_Configs $config)
    {
        $arguments = $config->getArguments();
        if (empty($arguments) ||
           (!empty($arguments[0]) && $arguments[0] != 'components')) {
            return false;
        }
        \Horde\GitTools\Components::main();
        return true;
    }

/** Horde_Cli_Modular methods */

    public function getOptionGroupOptions($action = null)
    {
            return array();
    }

    public function hasOptionGroup()
    {
        return true;
    }

    /**
     * Returns the title for the option group representing this module.
     *
     * @return string  The group title.
     */
    public function getOptionGroupTitle()
    {
        return 'Components actions';
    }

    /**
     * Returns the description for the option group representing this module.
     *
     * @return string  The group description.
     */
    public function getOptionGroupDescription()
    {
        return 'This command performs component related commands';
    }

    /**
     * Return the options that should be explained in the context help.
     *
     * @return array A list of option help texts.
     */
    public function getContextOptionHelp($action = null)
    {
        $options = array(
            'release' => array('--pretend' => '')
        );

        if (!empty($options[$action])) {
            return $options[$action];
        }

        return array();
    }

    /**
     * Returns additional usage title for this module.
     *
     * @return string  The usage title.
     */
    public function getTitle()
    {
        return 'components ACTION';
    }

    /**
     * Returns additional usage description for this module.
     *
     * @return string The description.
     */
    public function getUsage()
    {
        return 'Perform a component related action.';
    }

    /**
     * Return the action arguments supported by this module.
     *
     * @return array A list of supported action arguments.
     */
    public function getActions()
    {
        return array('update COMPONENT'  => 'Updates COMPONENT\'s package.xml file. COMPONENT is name of repository relative to git_base.');
    }

    /**
     * Return the help text for the specified action.
     *
     * @param string $action The action.
     *
     * @return string The help text.
     */
    public function getHelp()
    {
        return 'This module performs component related tasks.

Available actions for this module are:' . $this->_actionFormatter();
    }

}
