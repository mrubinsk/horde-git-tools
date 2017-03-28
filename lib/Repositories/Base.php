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
    protected $_repositories;

    public function __construct(array $params)
    {
        $this->_params = $params;
    }

    public function __get($property)
    {
        switch ($property) {
        case 'repositories':
            return $this->_repositories;
        default:
            exit("Unknown Property");
        }
    }

    abstract public function load(array $git, $url = '');
}
