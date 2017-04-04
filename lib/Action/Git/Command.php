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
        $results = $failures = array();

        // Ensure the base directory exists.
        if (!file_exists($this->_params['git_base'])) {
            throw new Exception("Base checkout directory does not exist.");
        }

        Cli::$cli->message('Starting update of libraries.');
        foreach (new \DirectoryIterator($this->_params['git_base']) as $it) {
            if (!$it->isDot() && $it->isDir() && is_dir($it->getPathname() . '/.git')) {
                foreach ($commands as $cmd) {
                    $results[$it->getFilename()] = $this->_callGit($cmd, $it->getPathname());
                    // @debug output
                    Cli::$cli->message('Repository: ' . $it->getFilename(), 'cli.success');
                }
            }
        }

        Cli::$cli->message('Starting update of applications.');
        foreach (new \DirectoryIterator($this->_params['git_base'] . '/applications') as $it) {
            if (!$it->isDot() && $it->isDir() && is_dir($it->getPathname() . '/.git')) {
                foreach ($commands as $cmd) {
                    $results[$it->getFilename()] = $this->_callGit($cmd, $it->getPathname());
                    // @debug output
                    Cli::$cli->message('Repository: ' . $it->getFilename(), 'cli.success');
                }
            }
        }
        if ($this->_params['debug']) {
            foreach ($results as $name => $result) {
                Cli::$cli->header($name);
                Cli::$cli->writeln(implode("\n", $result));
            }
        }
        // if (!empty($failures)) {
        //     Cli::$cli->message('The following repositories failed to be updated.', 'cli.error');
        //     foreach ($failures as $repo => $results) {
        //         Cli::$cli->message('---' . $repo . '---', 'cli.error');
        //         Cli::$cli->writeln(Cli::$cli->red($results));
        //     }
        // }
    }

}
