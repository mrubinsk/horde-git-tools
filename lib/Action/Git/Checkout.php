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
use Horde\GitTools\Exception;

/**
 * Attempts to recursively checkout out a branch.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl LGPL
 * @package   GitTools
 */
class Git\Checkout extends \Horde\GitTools\Action\Base
{
    /**
     * Clones the specified package/repository.
     *
     * @param string $branch  The branch to checkout.
     */
    public function run($branch = false)
    {
        $success = array();
        $failure = array();

        // Ensure the base directory exists.
        if (!file_exists($this->_params['git_base'])) {
            throw new Exception("Base checkout directory does not exist.");
        }

        Cli::$cli->message('Switching libraries branch: ' . $branch);
        foreach (new \DirectoryIterator($this->_params['git_base']) as $it) {
            $output = $results = array();
            if (!$it->isDot() && $it->isDir() && is_dir($it->getPathname() . '/.git')) {
                chdir($it->getPathname());
                Cli::$cli->message('Repository: ' . $it->getFilename());
                exec("(git checkout $branch > /dev/null) 3>&1 1>&2 2>&3", $output);
                exec('git branch', $results);
                if (in_array('* ' . $branch, $results) !== false) {
                    $success[] = $it->getFilename();
                } else {
                    $failures[] = $it->getFilename() . ': ' . implode("\n", $output);
                }
            }
        }

        foreach (new \DirectoryIterator($this->_params['git_base'] . '/applications') as $it) {
            $output = $results = array();
            if (!$it->isDot() && $it->isDir() && is_dir($it->getPathname() . '/.git')) {
                Cli::$cli->message('Switching ' . $it->getFilename() . ' to branch: ' . $branch);
                chdir($it->getPathname());
                exec('git branch', $results);
                if (in_array('* ' . $branch, $results) !== false) {
                    $success[] = $it->getFilename();
                } else {
                    $failures[] = $it->getFilename() . ': ' . implode("\n", $output);
                }
            }
        }

        if (!empty($success)) {
            Cli::$cli->message('The following repositories were successfully changed to ' . $branch, 'cli.success');
            Cli::$cli->writeln(implode("\n", $success));
        }

        if (!empty($failures)) {
            Cli::$cli->message('The following repositories failed to be changed to ' . $branch, 'cli.error');
            Cli::$cli->writeln(Cli::$cli->red(implode("\n", $failures)));
        }
    }
}
