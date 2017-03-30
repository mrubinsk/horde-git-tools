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

use Horde\GitTools\Cli;
use Horde\GitTools\Exception;

/**
 * Attempts to recursively pull from remote repositories.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl LGPL
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
        $failures = array();

        // Ensure the base directory exists.
        if (!file_exists($this->_params['git_base'])) {
            throw new Exception("Base checkout directory does not exist.");
        }

        Cli::$cli->message('Starting update of libraries.');
        foreach (new \DirectoryIterator($this->_params['git_base']) as $it) {
            if (!$it->isDot() && $it->isDir() && is_dir($it->getPathname() . '/.git')) {
                chdir($it->getPathname());
                // First determine if branch is clean.
                $results = $this->_getStatus();
                if ($results !== true) {
                    Cli::$cli->message('Repository: ' . $it->getFilename(), 'cli.error');
                    $failures[$it->getFilename()] = $results;
                    continue;
                }
                Cli::$cli->message('Repository: ' . $it->getFilename(). 'cli.success');
                exec('git pull --rebase', $results);
            }
        }

        Cli::$cli->message('Starting update of applications.');
        foreach (new \DirectoryIterator($this->_params['git_base'] . '/applications') as $it) {
            if (!$it->isDot() && $it->isDir() && is_dir($it->getPathname() . '/.git')) {
                chdir($it->getPathname());
                $results = $this->_getStatus();
                if ($results !== true) {
                    Cli::$cli->message('Repository: ' . $it->getFilename(), 'cli.error');
                    $failures[$it->getFilename()] = $results;
                    continue;
                }
                Cli::$cli->message('Repository: ' . $it->getFilename(), 'cli.success');
                exec('git pull --rebase', $results);
            }
        }

        if (!empty($failures)) {
            Cli::$cli->message('The following repositories failed to be updated.', 'cli.error');
            foreach ($failures as $repo => $results) {
                Cli::$cli->message('---' . $repo . '---', 'cli.error');
                Cli::$cli->writeln(Cli::$cli->red(implode("\n", $results)));
            }
        }
    }

    /**
     *
     *
     * @return mixed  True on success, error description on failurde.
     */
    protected function _getStatus()
    {
        exec('git status', $results);
        if (strpos($results[1], 'Your branch is up-to-date') !== 0) {
            return $results;
        }

        return true;
    }

}
