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

namespace Horde\GitTools;

use Horde_Argv_Parser;
use Horde_Argv_Option;
use Horde_Cache;
use Horde_Cache_Storage_File;
use Horde_Cli;

/**
 * Main class
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Cli
{
    /**
     * The useragent to use when issuing HTTP requests to GitHub.
     */
    const USERAGENT = 'Horde/GitTools';

    /**
     * The Horde_Cli instance.
     *
     * @var Hord_Cli
     */
    public static $cli;

    /**
     * Entry point
     */
    public static function main()
    {

        self::$cli = Horde_Cli::init();

        $parser = new Horde_Argv_Parser(
            array('usage' => "%prog [OPTIONS] COMMAND\n
\tAvailable commands:
\t\tlist        List available repositories on remote.
\t\tclone       Creates a full clone of all repositories on remote.
\t\tcheckout    Recursively checkout branch specified in --branch.
\t\tpull        Recursively pull and rebase all repositories.
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
                ),
                new Horde_Argv_Option(
                    '-b',
                    '--branch',
                    array(
                        'action' => 'store',
                        'help'   => 'Name of branch to checkout when using checkout action.',
                    )
                )
            )
        );

        list($options, $arguments) = $parser->parseArgs();
        if (empty($arguments)) {
            $parser->printHelp();
            exit;
        }

        $params = array();
        if (empty($options['config'])) {
             require dirname(__FILE__) . '/../bin/conf.php';
        } else {
            require $options['config'];
        }
        // $options is not a true array so we can't array_merge.
        foreach ($options as $key => $value) {
            if (!empty($value)) {
                $params[$key] = $value;
            }
        }
        if (empty($params['git_base'])) {
            $parser->printHelp();
            exit;
        }
        switch (array_pop($arguments)) {
        case 'clone':
            self::_doClone($params);
            break;
        case 'pull':
            self::_doPull($params);
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
        case 'checkout':
            if (empty($params['branch'])) {
                self::$cli->message('Missing required branch option.', 'cli.error');
                $parser->printHelp();
                exit;
            }
            self::_doCheckout($params);
        }
    }

    /**
     * Report git status of all repositories.
     *
     * @param  array $params  Configuration parameters.
     */
    protected static function _doStatus(array $params)
    {
        $action = new Action\Git\Status($params);
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
        $action = new Action\Dev\EmptyLinkedDirectory($params);
        $action->run();

        $action = new Action\Dev\LinkHorde($params);
        $action->run();

        $action = new Action\Dev\LinkApps($params);
        $action->run();

        $action = new Action\Dev\LinkFramework($params);
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

        $action = new Action\Git\CloneRepositories($params);
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
        if (!empty($params['cache'])) {
            $storage = new Horde_Cache_Storage_File();
            $cache = new Horde_Cache($storage);
        }
        $curl = new Repositories\Http($params, $cache);
        $curl->load(array('org' => $params['org'], 'user-agent' => self::USERAGENT));

        return $curl;
    }

    /**
     * Output a list of available repositories.
     *
     * @param  array $params  Configuration parameters.
     */
    protected static function _doList(array $params)
    {
        $curl = self::_getRepositories($params);
        foreach ($curl->repositories as $repo_name => $repo) {
            if (!empty($params['debug'])) {
                self::$cli->header($repo_name);
                print_r($repo);
            } else {
                echo $repo_name . "\n";
            }
        }
    }

    /**
     * Recursively checkout out $branch.
     *
     * @param  array $params  Configuration parameters.
     */
    protected static function _doCheckout(array $params)
    {
        $action = new Action\Git\Checkout($params);
        $action->run($params['branch']);
    }

    /**
     * Recursively pull and rebase.
     *
     * @param  array $params  Configuration parameters.
     */
    protected static function _doPull(array $params)
    {
        $action = new Action\Git\Pull($params);
        $action->run();
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
