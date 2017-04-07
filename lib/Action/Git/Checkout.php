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

namespace Horde\GitTools\Action\Git;

use Horde\GitTools\Cli;
use Horde\GitTools\Exception;

/**
 * Attempts to recursively checkout out a branch.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Checkout extends Base
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

        $this->_dependencies->getOutput()->info('Switching libraries branch: ' . $branch);
        foreach (new \DirectoryIterator($this->_params['git_base']) as $it) {
            if ($this->_includeRepository($it)) {
                $this->_dependencies->getOutput()->info('Repository: ' . $it->getFilename());
                $output = $this->_callGit("checkout $branch", $it->getPathname());
                $results = $this->_callGit('branch', $it->getPathname());
                if (in_array('* ' . $branch, explode("\n", $results[0])) !== false) {
                    $success[] = $it->getFilename();
                } else {
                    $failures[] = $it->getFilename() . ': ' . $output[1];
                }
            }
        }

        // Ensure the base directory exists.
        if (file_exists($this->_params['git_base'] . '/applications')) {
            foreach (new \DirectoryIterator($this->_params['git_base'] . '/applications') as $it) {
                if ($this->_includeRepository($it)) {
                    $this->_dependencies->getOutput()->info('Switching ' . $it->getFilename() . ' to branch: ' . $branch);
                    $output = $this->_callGit("checkout $branch", $it->getPathname());
                    $results = $this->_callGit('branch', $it->getPathname());
                    if (in_array('* ' . $branch, explode("\n", $results[0])) !== false) {
                        $success[] = $it->getFilename();
                    } else {
                        $failures[] = $it->getFilename() . ': ' . $output[1];
                    }
                }
            }
        } else {
            $this->_dependencies->getOutput()->fail('Could not find the applications checkout directory');
        }

        if (!empty($success)) {
            $this->_dependencies->getOutput()->ok('The following repositories were successfully changed to ' . $branch);
            $this->_dependencies->getOutput()->plain(implode("\n", $success));
        }

        if (!empty($failures)) {
            $this->_dependencies->getOutput()->warn('The following repositories failed to be changed to ' . $branch);
            $this->_dependencies->getOutput()->yellow(implode("\n", $failures));
        }
    }
}
