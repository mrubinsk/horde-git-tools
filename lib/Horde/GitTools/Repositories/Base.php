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

namespace Horde\GitTools\Repositories;
use Horde_Cli;
/**
 * Base class for requesting and parsing a list of available repositories from a
 * GitHub organization.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl LGPL
 * @package   GitTools
 */
abstract class Base
{
    /**
     * List of available repositories.
     *
     * @var array
     */
    protected $_repositories;

    /**
     * Local cache of API call to retrieve list of available repositories.
     *
     * @var Horde_Cache
     */
    protected $_cache;

    /**
     * Default lifetime of cache entries.
     *
     * @var integer
     */
    protected $_lifetime = 86400;

    /**
     * Const'r
     *
     * @param array   $params     Configuration parameters
     * @param Horde_Cache $cache  An optional cache to store API call results.
     * @param boolean $lifetime   Optional lifetime of cache entries.
     */
    public function __construct(array $params, $cache = null, $lifetime = null)
    {
        $this->_params = $params;
        if (!empty($cache)) {
            $this->_cache = $cache;
            $this->_lifetime = !empty($lifetime)
                ? $lifetime
                : $this->_lifetime;
        }
    }

    /**
     * Magic
     *
     * @param string  $property  The property name.
     *
     * @return  mixed  The value.
     */
    public function __get($property)
    {
        switch ($property) {
        case 'repositories':
            return $this->_repositories;
        default:
            exit('Unknown Property');
        }
    }

    abstract public function load(array $git, $url = '');
}
