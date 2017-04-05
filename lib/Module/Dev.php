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

/**
 * Class for handling 'dev' actions.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Dev extends Base
{

    /**
     * Handles the module's actions.
     *
     * @param  array $arguments  Argv arguments
     * @param  array $params    Configuration parameters.
     */
    public function handle($arguments, $params)
    {
        $this->_params = $params;
        switch (array_shift($arguments)) {
        case 'install':
            $this->_doInstallDev();
            break;
        }
    }


    /**
     * Perform linking of all repositories into the web_directory.
     *
     * @param  array $params  Configuration parameters.
     */
    protected function _doInstallDev()
    {
        $params = $this->_params;

        // First, empty directory.
        $action = new Action\Dev\EmptyLinkedDirectory($params);
        $action->run();

        $action = new Action\Dev\LinkHorde($params);
        $action->run();

        $action = new Action\Dev\LinkApps($params);
        $action->run();

        $action = new Action\Dev\LinkFramework($params);
        $action->run();
    }

/** Horde_Cli_Modular methods */

    public function getOptionGroupOptions($action = null)
    {
            return array(
                new Horde_Argv_Option(
                    '',
                    '--copy',
                    array(
                        'action' => 'store_true',
                        'help'   => 'Copy files instead of linking.'
                    )
                )
            );
    }

    public function hasOptionGroup()
    {
        return true;
    }

    /**
     * Return the options that should be explained in the context help.
     *
     * @return array A list of option help texts.
     */
    public function getContextOptionHelp($action = null)
    {
        $options = array(
            'install' => array('--copy' => '')
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
        return "\n  dev  ACTION   - Perform a development install related action.";
    }

    /**
     * Return the action arguments supported by this module.
     *
     * @return array A list of supported action arguments.
     */
    public function getActions()
    {
        return array('install'  => 'Link/Install all repositories to the web directory.');
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
        return 'This module performs development related tasks.

Available actions for this module are:' . $this->_actionFormatter();
    }

}
