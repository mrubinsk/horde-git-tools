<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see https://www.horde.org/licenses/bsd.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @category Horde
 * @license  https://www.horde.org/licenses/bsd BSD
 * @package  GitTools
 */

/**
 * Class to aid in treating local repositories as PEAR pacakges.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
namespace Horde\GitTools;

use Horde\GitTools\Exception;
use PEAR_Config;
use PEAR_PackageFile;

class PearPackages extends Base
{

    /**
     *
     * @var PEAR_PackageFile
     */
    protected $_pearPkgOb;

    /**
     * List of available repositories.
     *
     * @var array
     */
    protected static $_repositories;

    /**
     * Const'r
     *
     * @param array   $params     Configuration parameters
     *
     * @throws  Horde\GitTools\Exception
     */
    public function __construct(array $params)
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

        $this->_params = $params;
        parent::__construct($params);
        if (empty(self::$_repositories)) {
            $this->_getRepositories();
        }
        $pear_config = PEAR_Config::singleton();
        $this->_pearPkgOb = new PEAR_PackageFile($pear_config);
    }

    /**
     * Return list of locally available repositories.
     *
     * @return array  An array containing the pacakge name as keys and the
     *                repository path as the value.
     */
    public function getRepositories()
    {
        return self::$_repositories;
    }

    /**
     * Return a PEAR Pacakge given the pacakge.xml file.
     *
     * @param string $package_xml  The package.xml file path.
     *
     * @return PEAR_Package  The package object.
     * @throws  \Horde\GitTools\Exception
     */
    public function getPearPackage($package_xml)
    {
        $results = $this->_pearPkgOb->fromPackageFile($package_xml, 0);
        if ($results instanceof PEAR_Error) {
            throw new Exception($e->getMessage());
        }

        return $results;
    }

    /**
     * Get a list of locally available repositories. Packages must contain a
     * package.xml file to be considered.
     *
     * @return array
     */
    protected function _getRepositories()
    {
        self::$_repositories = array();
        $di = new \DirectoryIterator($this->_params['git_base']);
        foreach ($di as $val) {
            $pathname = $val->getPathname();
            if ($val->isDir() &&
                !$di->isDot() &&
                file_exists($pathname . '/package.xml')) {
                self::$_repositories[basename($val)] = $pathname;
            }
        }

        asort(self::$_repositories);
    }

}
