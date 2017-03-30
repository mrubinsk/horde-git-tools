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

namespace Horde\GitTools\Action;
use Horde_Cli;

/**
 * Report on the status of all locally checked out repositories.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl LGPL
 * @package   GitTools
 */
class Status extends Base
{
    public function __construct(array $params = array())
    {
        parent::__construct($params);

        // Do CLI checks and environment setup first.
        // @todo: Anyway to be more flexible with this?
        if ((!@include_once 'Horde/Cli.php')  &&
            (!@include_once $horde_git . '/Cli/lib/Horde/Cli.php')) {
            print_usage('Horde_Cli library is not in the include_path or in the src directory.');
        }

        // Make sure no one runs this from the web.
        if (!Horde_Cli::runningFromCLI()) {
            exit;
        }

        // Need to override the error handler Horde_Cli uses, since we can't
        // assume we have a functioning autoloader at this point and
        // Horde_Cli::fatal() relies on one.
        set_exception_handler(array($this, 'fatal'));
    }

    /**
     * Outputs status of all available locally checkout out repositories.
     *
     * @param  string  $package  The repository name.
     */
    public function run()
    {
        Cli::$cli->message('Checking status of libraries');
        foreach (new \DirectoryIterator($this->_params['git_base']) as $it) {
            if (!$it->isDot() && $it->isDir() && $it != 'applications' &&
                is_dir($it->getPathname() . '/.git')) {

                $results = array();
                chdir($it->getPathname());
                exec('git status', $results);

                Cli::$cli->message('Status of ' . $it->getFileName());
                Cli::$cli->message(implode("\n", $results));
                Cli::$cli->writeLn();
            }
        }

        Cli::$cli->message('Checking status of applications');
        foreach (new \DirectoryIterator($this->_params['git_base'] . '/applications') as $it) {
            if (!$it->isDot() && $it->isDir() &&
                is_dir($it->getPathname() . '/.git')) {

                $results = array();
                chdir($it->getPathname());
                exec('git status', $results);

                Cli::$cli->message('Status of ' . $it->getFileName());
                Cli::$cli->message(implode("\n", $results));
                Cli::$cli->writeLn();
            }
        }
    }

    public function fatal($error)
    {
        if ($error instanceof \Exception) {
            $trace = $error;
        } else {
            $trace = debug_backtrace();
        }

        var_dump($trace);
    }

}
