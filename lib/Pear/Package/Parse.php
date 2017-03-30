<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (...). If you
 * did not receive this file, see http://www.horde.org/licenses/....
 *
 * @author   
 * @category Horde
 * @license  http://www.horde.org/licenses/... ...
 * @package  
 */

/**
 * Summary
 *
 * @author    
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/... ...
 * @package   
 */

namespace Horde\GitTools\PEAR\Package;
use PEAR_Config;
use PEAR_PackageFile;

class Parse
{
    public $pear_pkg;

    public function __construct()
    {
        // Create the local PEAR config.
        if (!(@include_once 'PEAR/Config.php') ||
            !(@include_once 'PEAR/PackageFile.php')) {
            throw new Exception('PEAR libraries are not in the PHP include_path.');
        }

        /* We are heavily relying on the PEAR libraries which are not clean
         * with regard to E_STRICT. */
        if (defined('E_DEPRECATED')) {
            error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
        } else {
            error_reporting(E_ALL & ~E_STRICT);
        }

        $pear_config = PEAR_Config::singleton();
        $this->pear_pkg = new PEAR_PackageFile($pear_config);
    }

    public function getPackages(array $srcDirs)
    {
        $pkgs = array();

        foreach ($srcDirs as $dir) {
            $di = new \DirectoryIterator($dir);
            foreach ($di as $val) {
                $pathname = $val->getPathname();
                if ($val->isDir() &&
                    !$di->isDot() &&
                    file_exists($pathname . '/package.xml')) {
                    $pkgs[basename($val)] = $pathname;
                } elseif ($val->isFile() &&
                          ($val == 'package.xml')) {
                    $pkgs[basename($val->getPath())] = $val->getPath();
                }
            }
        }

        asort($pkgs);

        return $pkgs;
    }

}
