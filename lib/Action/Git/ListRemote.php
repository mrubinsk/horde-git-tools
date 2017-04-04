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

namespace Horde\GitTools\Action\Git;

use Horde\GitTools\Cli;
use Horde\GitTools\Repositories;
use Horde_Cache;
use Horde_Cache_Storage_File;

/**
 * Provides a listing of remote repositories.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class ListRemote extends Base
{
    /**
     * The useragent to use when issuing HTTP requests to GitHub.
     */
    const USERAGENT = 'Horde/GitTools';

    /**
     * Return list of remote repositories;
     *
     * @return  array  An array keyed by repository name and result of GitHub
     *                 API call as value.
     */
    public function run()
    {
        $repositories = array();
        $curl = self::_getRepositories($this->_params);
        foreach ($curl->repositories as $repo_name => $repo) {
            // @TODO: Check for horde.yaml file.
            if (!empty($this->_params['debug'])) {
                Cli::$cli->header($repo_name);
                Cli::$cli->writeln(print_r($repo, true));
            }
            $repositories[$repo_name] = $repo;
        }

        return $repositories;
    }

    /**
     * Get a list of all available repositories from the Github remote.
     *
     * @param  array $params  Configuration parameters.
     *
     * @return  Horde\GitTools\Repositories\Curl
     */
    protected function _getRepositories()
    {
        if (!empty($this->_params['cache'])) {
            $storage = new Horde_Cache_Storage_File();
            $cache = new Horde_Cache($storage);
        }
        $curl = new Repositories\Http($this->_params, $cache);
        $curl->load(array('org' => $this->_params['org'], 'user-agent' => self::USERAGENT));

        return $curl;
    }

}
