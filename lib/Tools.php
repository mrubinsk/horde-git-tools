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

/**
 * Summary
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl LGPL
 * @package   GitTools
 */
class Tools
{
    const USERAGENT = 'horde-git-tools';

    /**
     * Entry point
     */
    public static function main()
    {
        $params = self::_parseOptions();

        switch ($params['action']) {
        case 'clone':
            self::_doClone($params);
            break;
        case 'link':
            self::_doLink($params);
            break;
        case 'list':
            self::_doList($params);
            break;
        }
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

    /**
     * Parse the console options.
     *
     * @return array  Returns an array of configuration parameters.
     */
    protected static function _parseOptions()
    {
        $params = array(
            'action' => false
        );

        $c = new \Console_Getopt();
        $argv = $c->readPHPArgv();
        array_shift($argv);
        if (empty($argv)) {
            self::_printUsage();
        }

        $options = $c->getopt2($argv, '', array('apps=', 'config=', 'debug', 'group=', 'hordegit=', 'mode=', 'webdir=', 'org=', 'ignore='));
        if ($options instanceof PEAR_Error) {
            exit("Invalid arguments.\n");
        }

        if (!empty($options[0])) {
            foreach ($options[0] as $val) {
                switch ($val[0]) {
                case '--apps':
                    $params['apps'] = explode(',', $val[1]);
                    break;

                case '--config':
                    require_once $val[1];
                    break;

                case '--debug':
                    $params['debug'] = (bool)$val[1];
                    break;

                case '--group':
                    $params['static_group'] = $val[1];
                    break;

                case '--hordegit':
                    $params['horde_git'] = $val[1];
                    break;

                case '--mode':
                    $params['static_mode'] = $val[1];
                    break;

                case '--webdir':
                    $params['web_dir'] = $val[1];
                    break;

                case '--org':
                    $params['org'] = $val[1];
                    break;

                case '--ignore':
                    $params['ignore'] = explode(',', $val[1]);
                    break;
                }
            }
        } else {
            require dirname(__FILE__) . '/../bin/conf.php';
        }
        if (empty($options[1])) {
            self::_printUsage();
        }
        $params['action'] = array_pop($options[1]);

        return $params;
    }

    /**
     * @todo  - More detail, more options....
     */
    protected static function _printUsage()
    {
     echo <<<USAGE
Usage: horde-git-tools [OPTION] COMMAND

Optional options:
  --config      Location of configuration file to load.
  --debug       Output debug indormation.

Available commands:
    list        List available repositories on remote.
    clone       Creates a full clone of all repositories on remote.
    link        Links repositories into web directory.

USAGE;
    exit;
    }

}
