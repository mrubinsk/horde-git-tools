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

        \Horde\GitTools\Components::main();
        // $options = $config->getOptions();
        // $arguments = $config->getArguments();
        // if (!empty($options['updatexml'])
        //     || (isset($arguments[0]) && $arguments[0] == 'update')) {
        //     $this->_dependencies->getRunnerUpdate()->run();
        //     return true;
        // }
        // $this->_params = $options;
        // switch (array_shift($arguments)) {
        // case 'update':
        //     $this->_doUpdate(array_shift($arguments));
        //     break;
        // }

        // return false;
    }

    public function _doUpdate($component)
    {
        $action = new \Horde\GitTools\Action\Components\Update(array('component' => $component, 'dependencies' => $this->_dependencies));
        $action->run($this->_params);
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
     * Get the usage description for this module.
     *
     * @return string The description.
     */
    public function getUsage()
    {
        return "\n  components  ACTION   - Perform a component related action.";
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
