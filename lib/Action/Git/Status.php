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

namespace Horde\GitTools\Action\Git;

use Horde\GitTools\Cli;

/**
 * Report on the status of all locally checked out repositories.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl LGPL
 * @package   GitTools
 */
class Status extends Base
{
    /**
     * Outputs status of all available locally checkout out repositories.
     *
     * @param  string  $package  The repository name.
     * @todo  Use --porcelain output, parse into array, and output cleaner data.
     */
    public function run()
    {
        Cli::$cli->message('Checking status of libraries');
        foreach (new \DirectoryIterator($this->_params['git_base']) as $it) {
            if (!$it->isDot() && $it->isDir() && $it != 'applications' &&
                is_dir($it->getPathname() . '/.git')) {

                $results = $this->_callGit('status --porcelain -b', $it->getPathname());
                Cli::$cli->message('Status of ' . $it->getFileName());
                Cli::$cli->message($results[0]);
                Cli::$cli->writeLn();
            }
        }

        Cli::$cli->message('Checking status of applications');
        foreach (new \DirectoryIterator($this->_params['git_base'] . '/applications') as $it) {
            if (!$it->isDot() && $it->isDir() && is_dir($it->getPathname() . '/.git')) {

                $results = $this->_callGit('status --porcelain -b', $it->getPathname());
                Cli::$cli->message('Status of ' . $it->getFileName());
                Cli::$cli->message($results[0]);
                Cli::$cli->writeLn();
            }
        }
    }

}
