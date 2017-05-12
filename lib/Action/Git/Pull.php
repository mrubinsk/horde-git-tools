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
 * Attempts to recursively pull from remote repositories.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Pull extends Base
{

    protected $_failures = array();

    /**
     * Pulls and rebases.
     *
     * @todo
     *   - ensure a repo is on a specific branch before pulling.
     *   - option try to stash/pull/rebase/stash pop if repo is not clean?
     */
    public function run()
    {
        $results = $failures = array();

        // Ensure the base directory exists.
        if (!strlen($this->_params['git_base']) ||
            !file_exists($this->_params['git_base'])) {
            throw new Exception("Target directory for git checkouts does not exist.");
        }

        $this->_dependencies->getOutput()->info('Starting update of libraries.');
        foreach (new \DirectoryIterator($this->_params['git_base']) as $it) {
            if ($this->_includeRepository($it)) {
                // Debug output
                if ($this->_params['verbose']) {
                    $this->_dependencies->getOutput()->plain(
                        '    >>>GIT COMMAND: ' . $this->_getCommand()
                    );
                }

                // Perform pull
                $results[$it->getFilename()] = $this->_callGit($this->_getCommand(), $it->getPathname());

                // Debug output
                if ($this->_params['verbose']) {
                    $this->_dependencies->getOutput()->plain(
                        '   >>>RESULTS: ' . implode("\n", $results[$it->getFilename()])
                    );
                }
                $this->_dependencies->getOutput()->ok('Repository: ' . $it->getFilename());
            }
        }

        $this->_dependencies->getOutput()->info('Starting update of applications.');
        foreach (new \DirectoryIterator($this->_params['git_base'] . '/applications') as $it) {
            if ($this->_includeRepository($it)) {
                // Debug
                if ($this->_params['verbose']) {
                    $this->_dependencies->getOutput()->plain(
                        '    >>>GIT COMMAND: ' . $this->_getCommand()
                    );
                }

                // Perform pull
                $results[$it->getFilename()] = $this->_callGit($this->_getCommand(), $it->getPathname());

                // Debug results
                if ($this->_params['verbose']) {
                    $this->_dependencies->getOutput()->plain(
                        '    >>>RESULTS: ' . implode("\n", $results[$it->getFilename()])
                    );
                }

                $this->_dependencies->getOutput()->ok('Repository: ' . $it->getFilename());
            }
        }

        if (!empty($failures)) {
            $this->_dependencies->getOutput()->warn('The following repositories failed to be updated.');
            foreach ($failures as $repo => $results) {
                $this->_dependencies->getOutput()->warn('---' . $repo . '---');
                $this->_dependencies->getOutput()->yellow($results);
            }
        }

        if (!empty($results) && $this->_params['verbose']) {
            $this->_dependencies->getOutput()->plain(print_r($results, true));
        }
    }

    /**
     * Returns the command string to use for pull.
     *
     */
    protected function _getCommand()
    {
        if (!empty($this->_params['use_git_get'])) {
            return 'get';
        } elseif (!empty($this->_params['custom_pull'])) {
            return $this->_params['custom_pull'];
        }

        return 'pull --rebase';
    }

}
