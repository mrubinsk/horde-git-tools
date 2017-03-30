<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @author   Michael Slusarz <slusarz@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl LGPL
 * @package  GitTools
 */

namespace Horde\GitTools\Action\Dev;

use Horde\GitTools\Cli;

/**
 * Links the base Horde directory into the web directory.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @author    Michael Slusarz <slusarz@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl LGPL
 * @package   GitTools
 */
class LinkHorde extends Horde\GitTools\Action\Base
{
    /**
     * Empties the linked web directory.
     */
    public function run()
    {
        $horde_git = rtrim(ltrim($this->_params['git_base']), '/ ');
        $web_dir = rtrim(ltrim($this->_params['web_dir']), '/ ');
        $this->_linkHorde($horde_git, $web_dir);
    }

    /**
     * Performs linking of the base Horde application.
     *
     * @param  string $horde_git  Path to the local base directory of all
     *                            repositories.
     * @param  string $web_dir    Path to the web accessible directory.
     */
    protected function _linkHorde($horde_git, $web_dir)
    {
        Cli::$cli->writeln("LINKING horde");;
        $horde_git .= '/applications';

        file_put_contents(
            $horde_git . '/horde/config/horde.local.php',
            "<?php if (!defined('HORDE_BASE')) define('HORDE_BASE', '$web_dir'); ini_set('include_path', '{$web_dir}/libs' . PATH_SEPARATOR . ini_get('include_path'));"
        );

        foreach (new \DirectoryIterator($horde_git . '/horde') as $it) {
            if ($it->isDot()) {
                continue;
            }
            if ($it->isDir()) {
                if (strpos($it->getPathname(), $horde_git . '/horde/js') !== false) {
                    if ($this->_params['debug']) {
                        Cli::$cli->writeln("CREATING DIRECTORY: $web_dir/$it");
                    }
                    mkdir($web_dir . '/' . $it);
                    foreach (new \DirectoryIterator($horde_git . '/horde/' . $it) as $sub) {
                        if ($sub->isDot()) {
                            continue;
                        }
                        if ($this->_params['debug']) {
                            if ($sub->isDir()) {
                                Cli::$cli->writeln("LINKING DIRECTORY: $web_dir/$it/$sub");
                            } else {
                               Cli::$cli->writeln("LINKING FILE: $web_dir/$it/$sub");
                            }
                        }
                        symlink($sub->getPathname(), $web_dir . '/' . $it . '/' . $sub);
                    }
                } else {
                    if ($this->_params['debug']) {
                        Cli::$cli->writeln("LINKING DIRECTORY: $web_dir/$it");
                    }
                    symlink($it->getPathname(), $web_dir . '/' . $it);
                }
            } else {
                if ($this->_params['debug']) {
                    Cli::$cli->writeln("LINKING FILE: $web_dir/$it");
                }
                symlink($it->getPathname(), $web_dir . '/' . $it);
            }
        }

        // Check settings of static cache directory.
        if (file_exists($web_dir . '/static')) {
            Cli::$cli->message('Setting static directory permissions...');
            chmod($web_dir . '/static', $this->_params['static_mode']);
        } else {
            Cli::$cli->message('Creating static directory...');
            mkdir($web_dir . '/static', $this->_params['static_mode']);
        }
        if (!empty($this->_params['static_group'])) {
            chgrp($web_dir . '/static', $this->_params['static_group']);
        }
    }

}
