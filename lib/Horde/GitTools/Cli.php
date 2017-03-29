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

namespace Horde\GitTools;

use Horde_Argv_Parser;
use Horde_Argv_Option;
use Horde_Cli;

/**
 * Summary
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl LGPL
 * @package   GitTools
 */
class Cli
{
    const USERAGENT = 'Horde/GitTools';

    /**
     * Entry point
     */
    public static function main()
    {

        $parser = new Horde_Argv_Parser(
            array('usage' => "%prog [OPTIONS] COMMAND\n
\tAvailable commands:
\t\tlist        List available repositories on remote.
\t\tclone       Creates a full clone of all repositories on remote.
\t\tlink        Links repositories into web directory.
\t\tstatus      List the local git status of all repositories.")
        );

        $parser->addOptions(
            array(
                new Horde_Argv_Option(
                    '-c',
                    '--config',
                    array(
                        'action' => 'store',
                        'help'   => 'Path to configuration file.',
                    )
                ),
                new Horde_Argv_Option(
                    '-a',
                    '--apps',
                    array(
                        'action' => 'store',
                        'help'   => 'Comma delimted list of applications to link.',
                    )
                ),
                new Horde_Argv_Option(
                    '',
                    '--git_base',
                    array(
                        'action' => 'store',
                        'help'   => 'Path the base directory containing git checkouts.',
                    )
                ),
                new Horde_Argv_Option(
                    '-d',
                    '--debug',
                    array(
                        'action' => 'store_true',
                        'help'   => 'Enable debug output.',
                    )
                )
            )
        );
        list($options, $arguments) = $parser->parseArgs();
        if (empty($arguments)) {
            $parser->printHelp();
        }

        $params = array();
        if (empty($options['config'])) {
            include dirname(__FILE__) . '/../../../bin/conf.php';
        } else {
            require $options['config'];
        }
        // $options is not a true array so we can't array_merge.
        foreach ($options as $key => $value) {
            $params[$key] = $value;
        }
        if (empty($params['git_base'])) {
            $parser->printHelp();
        }
        switch (array_pop($arguments)) {
        case 'clone':
            self::_doClone($params);
            break;
        case 'link':
            self::_doLink($params);
            break;
        case 'list':
            self::_doList($params);
            break;
        case 'status':
            self::_doStatus($params);
            break;
        }
    }

    /**
     * Report git status of all repositories.
     *
     * @param  array $params  Configuration parameters.
     */
    protected static function _doStatus(array $params)
    {
        $action = new Action\Status($params);
        $action->run();
    }

    /**
     * Perform linking of all repositories into the web_directory.
     *
     * @param  array $params  Configuration parameters.
     */
    protected static function _doLink(array $params)
    {
        // First, empty directory.
        $action = new Action\EmptyLinkedDirectory($params);
        $action->run();

        $action = new Action\LinkHorde($params);
        $action->run();

        $action = new Action\LinkApps($params);
        $action->run();

        $action = new Action\LinkFramework($params);
        $action->run();
    }

    /**
     * Perform cloning of remote Github repositories to local copy.
     *
     * @param  array $params  Configuration parameters.
     */
    protected static function _doClone(array $params)
    {
        $curl = self::_getRepositories($params);

        $action = new Action\CloneRepositories($params);
        foreach ($curl->repositories as $package) {
            $action->run($package->name, self::_isApplication($package->name));;
        }
    }

    /**
     * Get a list of all available repositories from the Github remote.
     *
     * @param  array $params  Configuration parameters.
     *
     * @return  Horde\GitTools\Repositories\Curl
     */
    protected static function _getRepositories(array $params)
    {
        $curl = new Repositories\Curl($params);
        $curl->load(array('org' => $params['org'], 'user-agent' => self::USERAGENT));

        return $curl;
    }

    /**
     * Output a list of available repositories.
     *
     * @param  array $params  Configuration parameters.
     */
    protected static function _doList($params)
    {
        $curl = self::_getRepositories($params);
        foreach (array_keys($curl->repositories) as $repo_name) {
            echo $repo_name . "\n";
        }
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
    protected static function _isApplication($package_name)
    {
        return strtoupper($package_name[0]) != $package_name[0];
    }

}
