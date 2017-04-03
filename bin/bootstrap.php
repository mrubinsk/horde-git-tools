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

/**
 * Require necessary files.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */

$base_dir = dirname(__FILE__);
require_once $base_dir . '/../lib/Repositories/Base.php';
require_once $base_dir . '/../lib/Repositories/Curl.php';
require_once $base_dir . '/../lib/Tools.php';
require_once $base_dir . '/../lib/Action/Base.php';
require_once $base_dir . '/../lib/Action/CloneRepositories.php';
require_once $base_dir . '/../lib/Action/LinkApps.php';
require_once $base_dir . '/../lib/Action/EmptyLinkedDirectory.php';
require_once $base_dir . '/../lib/Action/LinkHorde.php';
require_once $base_dir . '/../lib/Action/LinkFramework.php';
require_once $base_dir . '/../lib/Action/Status.php';
require_once $base_dir . '/../lib/Pear/Package/Parse.php';

require_once 'Console/Getopt.php';
