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

use Horde\GitTools\Repositories;
use Horde\GitTools\Exception;
use Horde\GitTools\Action;
use Horde\GitTools\Cli;
use Horde_Argv_Option;

/**
 * Class for handling 'git' actions.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Git extends Base
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
        case 'clone':
            $this->_doClone();
            break;
        case 'pull':
            $this->_doPull();
            break;
        case 'checkout':
            if (!$branch = array_shift($arguments)) {
                throw new Exception('Missing required arguemnts to checkout.');
            }
            $this->_doCheckout($branch);
            break;
        case 'status':
            $this->_doStatus();
            break;
        case 'run':
            $this->_doCmd($arguments);
            break;
        case 'list':
            $this->_doList();
            break;
        }
    }

    /**
     * Perform cloning of remote Github repositories to local copy.
     *
     * @param  array $params  Configuration parameters.
     */
    protected function _doClone()
    {
        $list = new Action\Git\ListRemote($this->_params);
        $repositories = $list->run();

        $action = new Action\Git\CloneRepositories($this->_params);
        foreach ($repositories as $package) {
            $action->run($package->name, $this->_isApplication($package->name));;
        }
    }

    /**
     * Report git status of all repositories.
     *
     * @param  array $params  Configuration parameters.
     */
    protected function _doStatus()
    {
        $action = new Action\Git\Status($this->_params);
        $action->run();
    }

    /**
     * Report git status of all repositories.
     *
     * @param  array $params  Configuration parameters.
     */
    protected function _doPull()
    {
        $action = new Action\Git\Pull($this->_params);
        $action->run();
    }

    /**
     * Report git status of all repositories.
     *
     * @param  array $params  Configuration parameters.
     */
    protected function _doList()
    {
        $action = new Action\Git\ListRemote($this->_params);
        $repos = $action->run();
        Cli::$cli->message('Available remote repositories on ' . $this->_params['org']);
        foreach (array_keys($repos) as $name) {
            Cli::$cli->writeln($name);
        }
    }

    /**
     * Recursively checkout out $branch.
     *
     * @param  array $params  Configuration parameters.
     */
    protected function _doCheckout($branch)
    {
        $action = new Action\Git\Checkout($this->_params);
        $action->run($branch);
    }

    /**
     * Report git status of all repositories.
     *
     * @param  array $params  Configuration parameters.
     */
    protected function _doCmd($cmd)
    {
        $action = new Action\Git\Command($this->_params);
        $action->run($cmd);
    }

    /**
     * Get a list of all available repositories from the Github remote.
     *
     * @param  array $params  Configuration parameters.
     *
     * @return  Horde\GitTools\Repositories\Http
     */
    protected function _getRepositories()
    {
        if (!empty($this->_params['cache'])) {
            $storage = new Horde_Cache_Storage_File();
            $cache = new Horde_Cache($storage);
        }
        $repositories = new Repositories\Http($this->_params, $cache);
        $repositories->load(array(
            'org' => $params['org'],
            'user-agent' => self::USERAGENT)
        );

        return $repositories;
    }

    /**
     * Return whether or not the specified package is an application.
     * For now, this is true if the package name starts with a lower case
     * letter.
     *
     * @param string  $package_name  The package name to check.
     *
     * @return boolean True if $package_name is an applicaton.
     */
    protected function _isApplication($package_name)
    {
        return strtoupper($package_name[0]) != $package_name[0];
    }

/** Horde_Cli_Modular methods */

    public function getOptionGroupOptions($action = null)
    {
            return array(
                new Horde_Argv_Option(
                    '',
                    '--ignore-yml',
                    array(
                        'action' => 'store_true',
                        'help'   => 'Do not look for .horde.yml files.'
                    )
                ),
                new Horde_Argv_Option(
                    '',
                    '--use-git-get',
                    array(
                        'action' => 'store_true',
                        'help'   => 'Use the \'get\' alias for updating.'
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
            'list' => array('--ignore-yml' => ''),
            'pull' => array('--use-git-get' => '')
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
        return "\n  git  ACTION   - Perform Git related actions.";
    }

    public function getActions()
    {
        return array(
            'list'              => 'Lists available remote repositories.',
            'clone'             => 'Clones all remote repositories locally.',
            'pull'              => 'Update local repositories.',
            'checkout [BRANCH]' => 'Checkout BRANCH on all local repositories.',
            'status'            => 'Display status of all local repositories.',
            'run [GIT COMMAND]' => 'Run [GIT COMMAND] on all local repositories.'
        );
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
        return 'This module performs Git related actions on the locally
checked out repositories.

Available actions for this module are:' . $this->_actionFormatter();
    }

}
