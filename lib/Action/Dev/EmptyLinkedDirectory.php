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

use Horde\GitTools\Cli;

/**
 * Empty the linked directories of a Git install.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @author    Michael Slusarz <slusarz@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class EmptyLinkedDirectory extends \Horde\GitTools\Action\Base
{
    /**
     */
    public function run()
    {
        $web_dir = rtrim(ltrim($this->_params['web_dir']), '/ ');
        $this->_emptyWebDir($web_dir);
    }

    /**
     * Empties the web directory.
     *
     * @param  string $web_dir    Path to the web accessible directory.
     */
    protected function _emptyWebDir($web_dir)
    {
        Cli::$cli->message("EMPTYING old web directory $web_dir");
        try {
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($web_dir), \RecursiveIteratorIterator::CHILD_FIRST);
        } catch (\UnexpectedValueException $e) {
            Cli::$cli->message('Old web directory not found. Creating it.');
            mkdir($web_dir);
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($web_dir), \RecursiveIteratorIterator::CHILD_FIRST);
        }
        while ($it->valid()) {
            if (!$it->isDot()) {
                if ($it->isLink()) {
                    if ($this->_params['debug']) {
                        Cli::$cli->message('DELETING LINK: ' . $it->key());
                    }
                    unlink($it->key());
                } elseif ($it->isDir()) {
                    if ($this->_params['debug']) {
                        Cli::$cli->message('DELETING DIR: ' . $it->key());
                    }
                    rmdir($it->key());
                } elseif ($it->isFile()) {
                    if ($this->_params['debug']) {
                        Cli::$cli->message('DELETING FILE: ' . $it->key());
                    }
                    unlink($it->key());
                }
            }
            $it->next();
        }
    }

}
