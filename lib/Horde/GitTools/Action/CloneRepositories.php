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

/**
 * Clone all repositories existing in specified organization.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl LGPL
 * @package   GitTools
 */
class CloneRepositories extends Base
{
    const MODE = 0775;
    const HTTPS_GITURL = 'https://github.com';

    /**
     * Clones the specified package/repository.
     *
     * @param  string  $package  The repository name.
     * @param  boolean $app      If repository is an application, set to true.
     */
    public function run($package = '', $app = false)
    {
        // @todo validate the package name.
        //       validate the same type of checkout (dev/anon)?

        // Ensure the base directory exists.
        if (!file_exists($this->_params['git_base'])) {
            mkdir($this->_params['git_base'], self::MODE, true);
        }

        // Check for any renaming
        if (!empty($this->_params['map'][$package])) {
            $package_webname = $this->_params['map'][$package];
        } else {
            $package_webname = $package;
        }

        // Is this a developer checkout or anon?
        $target = $this->_params['git_base'] . '/' . ($app ? 'applications/' : '') . $package_webname;
        Cli::$cli->message('Cloning ' . $package_webname . ' into ' . $target);
        if (!empty($this->_params['git_ssh'])) {
            // Do a developer checkout.
            $source = $this->_params['git_ssh'] . '/' . $this->_params['org'] . '/' . $package . '.git';
        } else {
            $source = self::HTTPS_GITURL . '/' . $this->_params['org'] . '/' . $package . '.git';
        }
        exec("(git clone $source $target > /dev/null) 3>&1 1>&2 2>&3", $results);
        if (!empty($this->_params['debug'])) {
            print implode("\n", $results);
        }
    }
}
