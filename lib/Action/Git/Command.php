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
 * Permform arbitrary command(s) in all repositories.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Command extends Base
{

    protected $_failures = array();

    /**
     * Pulls and rebases.
     *
     * @param  array $commands  An array of commands. All ommands are performed
     *                          in each repository before moving on to the next.
     *
     */
    public function run(array $commands = array())
    {
        $results = array();

        // Ensure the base directory exists.
        if (!file_exists($this->_params['git_base'])) {
            throw new Exception("Base checkout directory does not exist.");
        }

        $this->_dependencies->getOutput()->info('Handling git command in libraries.');
        foreach (new \DirectoryIterator($this->_params['git_base']) as $it) {
            if ($this->_includeRepository($it)) {
                foreach ($commands as $cmd) {
                    if (empty($this->_params['quiet'])) {
                        $this->_dependencies->getOutput()->plain(
                            '   >>>GIT COMMAND: ' . $cmd
                        );
                    }
                    $results[$it->getFilename()] = $this->_callGit($cmd, $it->getPathname());

                    if (empty($this->_params['quiet'])) {
                        $this->_dependencies->getOutput()->plain(
                            '   >>>RESULTS: ' . implode("\n", $results[$it->getFilename()])
                        );
                    }

                    $this->_dependencies->getOutput()->ok('Repository: ' . $it->getFilename() . ' completed.');
                }
            }
        }

        $this->_dependencies->getOutput()->info('Handling git command in applications.');
        foreach (new \DirectoryIterator($this->_params['git_base'] . '/applications') as $it) {
            if ($this->_includeRepository($it)) {
                foreach ($commands as $cmd) {
                    if (empty($this->_params['quiet'])) {
                        $this->_dependencies->getOutput()->plain(
                            '   >>>GIT COMMAND: ' . $cmd
                        );
                    }
                    $results[$it->getFilename()] = $this->_callGit($cmd, $it->getPathname());

                    if (empty($this->_params['quiet'])) {
                        $this->_dependencies->getOutput()->plain(
                            '   >>>RESULTS: ' . implode("\n", $results[$it->getFilename()])
                        );
                    }
                    $this->_dependencies->getOutput()->ok('Repository: ' . $it->getFilename() . ' completed.');
                }
            }
        }
        foreach ($results as $name => $result) {
            $this->_dependencies->getOutput()->bold($name);
            $this->_dependencies->getOutput()->plain(implode("\n", $result));
        }
    }

}
