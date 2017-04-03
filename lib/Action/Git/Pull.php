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
        if (!file_exists($this->_params['git_base'])) {
            throw new Exception("Base checkout directory does not exist.");
        }

        Cli::$cli->message('Starting update of libraries.');
        foreach (new \DirectoryIterator($this->_params['git_base']) as $it) {
            if (!$it->isDot() && $it->isDir() && is_dir($it->getPathname() . '/.git')) {
                $results[$it->getFilename()] = $this->_callGit($this->_getCommand(), $it->getPathname());
                Cli::$cli->message('Repository: ' . $it->getFilename(), 'cli.success');
            }
        }

        Cli::$cli->message('Starting update of applications.');
        foreach (new \DirectoryIterator($this->_params['git_base'] . '/applications') as $it) {
            if (!$it->isDot() && $it->isDir() && is_dir($it->getPathname() . '/.git')) {
                $results[$it->getFilename()] = $this->_callGit($this->_getCommand(), $it->getPathname());
                Cli::$cli->message('Repository: ' . $it->getFilename(), 'cli.success');
            }
        }

        if (!empty($failures)) {
            Cli::$cli->message('The following repositories failed to be updated.', 'cli.error');
            foreach ($failures as $repo => $results) {
                Cli::$cli->message('---' . $repo . '---', 'cli.error');
                Cli::$cli->writeln(Cli::$cli->red($results));
            }
        }

        if (!empty($results) && $this->_params['debug']) {
            Cli::$cli->writeln(print_r($results, true));
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
