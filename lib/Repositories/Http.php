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

use Horde_Http_Client;
use Horde\GitTools\Cli;
use Horde\GitTools\Exception;


/**
 * Responsible for requesting and parsing a list of available repositories
 * from a GitHub organization using the Horde_Http client.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl LGPL
 * @package   GitTools
 */
class Http extends Base
{
    /**
     * Loads the list of available repositories on the Github remote.
     *
     * @param array  $git  Configuration parameters.
     * @param string $url  If specified, use this URL. Otherwise, generate the
     *                     URL to query. Used for pagination.
     *
     * @throws  \Horde\GitTools\Exception
     */
    public function load(array $git, $url = '')
    {
        if (empty($url)) {
            $url = 'https://api.github.com/orgs/' . $git['org'] . '/repos';
        }
        $key = md5(serialize($git) . $url);
        if (!empty($this->_cache) && $this->_cache->exists($key, $this->_lifetime)) {
            Cli::$cli->message('Using cached data for ' . $url);
            $response = unserialize($this->_cache->get($key, $this->_lifetime));
        }  else {
            Cli::$cli->message('Listing repositories from ' . $url);
            $http_client = new Horde_Http_Client();
            $response = $http_client->get($url);
            if ($this->_cache) {
                $this->_cache->set($key, serialize($response));
            }
        }
        $rate_reset = $response->headers['x-ratelimit-reset'];
        $rate_remaining = $response->headers['x-ratelimit-remaining'];
        if ($response->code != 200) {
            $body = json_decode($response->getBody());
            if (!empty($body->message)) {
                $message = $body->message . "\n You can retry at: " . date('r', $response->headers['x-ratelimit-reset']);
                throw new Exception($message);
            } else {
                throw new Exception();
            }
        }
        Cli::$cli->message('You have ' . $rate_remaining . ' GitHub API requests left until ' . date('r', $response->headers['x-ratelimit-reset']));
        $this->_parseRepositories(json_decode($response->getBody()));

        // Pagination
        if (!empty($response->headers['link'])) {
            $links = $this->_parseLinks($response->headers['link']);
            if (!empty($links['next'])) {
                $this->load($git, $links['next']);
            }
        }
    }

    /**
     * Parse any links returned in the Link: header used for pagination.
     *
     * @param string $link  The text of the Link header returned in the response.
     *
     * @return array  An array possibly containing a 'next' and 'last' URL.
     */
    protected function _parseLinks($link)
    {
        $links = array();
        $regexp = '/<(https:\/\/api\.github\.com\/[^?]+\?([^>]+))>; rel="([^"]+)"/';
        if (preg_match_all($regexp, $link, $matches)) {
           foreach($matches[3] as $index => $page) {
                $links[$page] = $matches[1][$index];
            }
        }

        return $links;
    }

    /**
     * Build the local cache of repository names and properties.
     *
     * @param  array $results  An array of objects representing available
     *                         repositories.
     * @param  array $params   Parameters.
     */
    protected function _parseRepositories(array $results, array $params = array())
    {
        foreach ($results as $repo) {
            if (!empty($this->_params['ignore']) && in_array($repo->name, $this->_params['ignore']) !== false) {
                continue;
            }
            $this->_repositories[$repo->name] = $repo;
        }
    }

}
