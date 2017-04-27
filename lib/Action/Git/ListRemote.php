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
use Horde_Http_Client;

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
        $remotes= self::_getRepositories($this->_params);
        foreach ($remotes->repositories as $repo_name => $repo) {
            if (empty($this->_params['ignore_yml']) && !$this->_isHordeRepo($repo_name)) {
                if ($this->_params['verbose']) {
                    $this->_dependencies->getOutput()->info('Skipping ' . $repo_name . ' as it does not contain a .horde.yml file');
                }
                continue;
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

        $cache = null;
        if (!empty($this->_params['cache'])) {
            $cache = new Horde_Cache(new Horde_Cache_Storage_File());
        }
        $repositories = new Repositories\Http($this->_params, $this->_dependencies, $cache);
        $repositories->load(array('org' => $this->_params['org'], 'user-agent' => self::USERAGENT));

        return $repositories;
    }

    protected function _isHordeRepo($repo)
    {
        $url = 'https://raw.githubusercontent.com/'
            . $this->_params['org']
            . '/' . $repo . '/master/horde.yml';

        $http = new Horde_Http_Client();
        $response = $http->get($url);
        return $response->code == 200;
    }

}
