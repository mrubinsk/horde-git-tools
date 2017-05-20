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
 * Report on the status of all locally checked out repositories.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
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
        // Ensure the base directory exists.
        if (!strlen($this->_params['git_base']) ||
            !file_exists($this->_params['git_base'])) {
            throw new Exception("Target directory for git checkouts does not exist.");
        }

        $this->_dependencies->getOutput()->info('Checking status of repositories.');
        foreach (new \DirectoryIterator($this->_params['git_base']) as $it) {
            if ($this->_includeRepository($it)) {
                $results = $this->_callGit('status --porcelain -b', $it->getPathname());
                $this->_dependencies->getOutput()->info('Status of ' . $it->getFileName());
                $this->_dependencies->getOutput()->info($results[0]);
            }
        }
    }

}
