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

namespace Horde\GitTools\Components\Helper;

use Components_Helper_Root;

/**
 * Extend Components_Helper_Root to deal with split repository structure.
 *
 * Basically, since each library is it's own repo now, getRoot() can just
 * return $this->_path instead of trying to figure out where the root is.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */

class Root extends Components_Helper_Root
{
    /**
     * Return the root position of the repository.
     *
     * @return string The root path.
     *
     * @throws Components_Exception If the Horde repository root could not be
     *                              determined.
     */
    public function getRoot()
    {
        return $this->_path;
    }

}
