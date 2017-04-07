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

use Horde_Cli;

/**
 * Base class for Git Actions.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
abstract class Base extends \Horde\GitTools\Action\Base
{
    /**
     * Call the git binary and return the results.
     *
     * @param string $cmd   The command to pass to git.
     * @param string $path  The path to execute $cmd in.
     *
     * @return array  An array containing the following:
     *   0 => stdout output
     *   1 => stderr output
     */
    protected function _callGit($cmd, $path)
    {
        $descriptor = array(
           0 =>  array('pipe', 'r'),
           1 => array('pipe', 'w'),
           2 => array('pipe', 'w')
        );
        $process = proc_open("git $cmd", $descriptor, $pipes, $path);
        $results = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return array($results, $error);
    }

    protected function _includeRepository($it)
    {
        $repositories = !empty($this->_params['repositories'])
            ? explode(',', $this->_params['repositories'])
            : array();

        return !$it->isDot() && $it->isDir() && is_dir($it->getPathname() . '/.git')
            && (empty($repositories) || (in_array($it->getFilename(), $repositories)));
    }

}
