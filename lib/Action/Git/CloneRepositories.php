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
 * Clone all repositories existing in specified organization.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class CloneRepositories extends Base
{
    const MODE = 0775;
    const HTTPS_GITURL = 'https://github.com';
    const GET_ALIAS ='[alias]
        get = !BRANCH=$(git branch -vv | grep ^\\\* | sed -E \'s/^[^[]+\\\[([^]:]+).+$/\\\1/\') && git fetch && ( git rebase -v $BRANCH || ( git stash && ( git rebase -v $BRANCH || echo "WARNING: Run \'git stash pop\' manually!" ) && git stash pop ) );';

    /**
     * Clones the specified package/repository.
     *
     * @param  string  $package  The repository name.
     */
    public function run($package = '')
    {
        // @todo validate the package name.
        //       validate the same type of checkout (dev/anon)?

        // Ensure the base directory exists.
        if (!strlen($this->_params['git_base'])) {
            throw new Exception("Target directory for git checkouts is not configured.");
        }
        if (!file_exists($this->_params['git_base'])) {
            mkdir($this->_params['git_base'], self::MODE, true);
        }

        // Get target
        $target = $this->_params['git_base'] . '/' . $package;
        $this->_dependencies->getOutput()->info(
            'Cloning ' . $package . ' into ' . $target
        );

        if (!empty($this->_params['git_ssh'])) {
            // Do a developer checkout.
            $source = $this->_params['git_ssh'] . '/'
                . $this->_params['org'] . '/' . $package . '.git';
        } else {
            $source = self::HTTPS_GITURL . '/'
                . $this->_params['org'] . '/' . $package . '.git';
        }

        // Clone
        $results = $this->_callGit("clone $source $target", $this->_params['git_base']);

        // Git seems to output certain status to stderr, so don't assume failure
        // if this is non-empty.
        if (!empty($results[1])) {
            if (strpos($results[1], 'fatal') === 0) {
                $this->_dependencies->getOutput()->yellow($results[1]);
            } else {
                $this->_dependencies->getOutput()->ok($results[1]);
            }
        }
        if (!empty($this->_params['verbose'])) {
            $this->_dependencies->getOutput()->plain($results[0]);
        }

        // Add the 'get' alias?
        if (!empty($this->_params['add_get_alias'])) {
            $target = $target . '/.git/config';
            file_put_contents($target, self::GET_ALIAS, FILE_APPEND);
        }
    }

}
