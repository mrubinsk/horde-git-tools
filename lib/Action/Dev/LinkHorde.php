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
 * @license  https://www.horde.org/licenses/bsd BSD
 * @package  GitTools
 */

namespace Horde\GitTools\Action\Dev;

/**
 * Links the base Horde directory into the web directory.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @author    Michael Slusarz <slusarz@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class LinkHorde extends \Horde\GitTools\Action\Base
{
    /**
     * Empties the linked web directory.
     */
    public function run()
    {
        $horde_git = rtrim(ltrim($this->_params['git_base']), '/ ');
        $web_dir = rtrim(ltrim($this->_params['web_base']), '/ ');
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
        $this->_dependencies->getOutput()->plain("LINKING horde");;
        $horde_git .= '/applications';

        file_put_contents(
            $horde_git . '/base/config/horde.local.php',
            "<?php if (!defined('HORDE_BASE')) define('HORDE_BASE', '$web_dir'); ini_set('include_path', '{$web_dir}/libs' . PATH_SEPARATOR . ini_get('include_path'));"
        );

        foreach (new \DirectoryIterator($horde_git . '/base') as $it) {
            if ($it->isDot() || $it->getFilename() == '.git' || $it->getFilename() == 'composer.json') {
                continue;
            }
            if ($it->isDir()) {
                if (strpos($it->getPathname(), $horde_git . '/base/js') !== false) {
                    if ($this->_params['verbose']) {
                        $this->_dependencies->getOutput()->plain(
                            "CREATING DIRECTORY: $web_dir/$it"
                        );
                    }
                    mkdir($web_dir . '/' . $it);
                    foreach (new \DirectoryIterator($horde_git . '/base/' . $it) as $sub) {
                        if ($sub->isDot()) {
                            continue;
                        }
                        if ($this->_params['verbose']) {
                            if ($sub->isDir()) {
                                $this->_dependencies->getOutput()->plain(
                                    "LINKING DIRECTORY: $web_dir/$it/$sub"
                                );
                            } else {
                               $this->_dependencies->getOutput()->plain(
                                    "LINKING FILE: $web_dir/$it/$sub"
                                );
                            }
                        }
                        symlink($sub->getPathname(), $web_dir . '/' . $it . '/' . $sub);
                    }
                } else {
                    if ($this->_params['verbose']) {
                        $this->_dependencies->getOutput()->plain
                            ("LINKING DIRECTORY: $web_dir/$it"
                        );
                    }
                    symlink($it->getPathname(), $web_dir . '/' . $it);
                }
            } else {
                if ($this->_params['verbose']) {
                    $this->_dependencies->getOutput()->plain(
                        "LINKING FILE: $web_dir/$it"
                    );
                }
                symlink($it->getPathname(), $web_dir . '/' . $it);
            }
        }

        // Check settings of static cache directory.
        if (file_exists($web_dir . '/static')) {
            $this->_dependencies->getOutput()->info('Setting static directory permissions...');
            chmod($web_dir . '/static', $this->_params['static_mode']);
        } else {
            $this->_dependencies->getOutput()->info('Creating static directory...');
            mkdir($web_dir . '/static', $this->_params['static_mode']);
        }
        if (!empty($this->_params['static_group'])) {
            chgrp($web_dir . '/static', $this->_params['static_group']);
        }
    }

}
