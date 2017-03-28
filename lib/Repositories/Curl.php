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
 * Responsible for requesting and parsing a list of available repositories
 * from a GitHub organization using the curl PHP extension.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl LGPL
 * @package   GitTools
 */
class Curl extends Base
{
    const TIMEOUT = 5;

    /**
     * Loads the list of available repositories on the Github remote.
     *
     * @param  array  $git  Parameters for the request:
     *   - org: The name of the GitHub organization, e.g., 'horde'.
     *   - user-agent: The string to use as the user-agent.
     *   - @todo: an ignore list? Proxy settings?
     * @param string $url  If specified, use this URL. Otherwise, generate the
     *                     URL to query. Used for pagination.
     */
    public function load(array $git, $url = '')
    {
        if (empty($url)) {
            $url = 'https://api.github.com/orgs/' . $git['org'] . '/repos';
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $git['user-agent']);
        curl_setopt($curl, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 0);

        $this->_parseRepositories(
            json_decode($this->_getResponseBody(curl_exec($curl)))
        );

        // Pagination
        if (!empty($this->_headers['link'])) {
            $links = $this->_parseLinks($this->_headers['link']);
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
     * Return the body of the HTTP response and places any parsed headers into
     * $this->_headers.
     *
     * @param  string  The full HTTP respone string.
     *
     * @return string  The BODY content.
     */
    protected function _getResponseBody($curlresult)
    {
        /* Curl returns multiple headers, if the last action required multiple
         * requests, e.g. when doing Digest authentication. Only parse the
         * headers of the latest response. */
        preg_match_all('/(^|\r\n\r\n)(HTTP\/)/', $curlresult, $matches, PREG_OFFSET_CAPTURE);
        $startOfHeaders = $matches[2][count($matches[2]) - 1][1];
        $endOfHeaders = strpos($curlresult, "\r\n\r\n", $startOfHeaders);
        $headers = substr($curlresult, $startOfHeaders, $endOfHeaders - $startOfHeaders);
        $this->_parseHeaders($headers);

        return substr($curlresult, $endOfHeaders + 4);
    }

    /**
     * Parse the header text returned in the HTTP response and place results
     * in $this->_headers.
     *
     * @param  string $headers  The header text.
     */
    protected function _parseHeaders($headers)
    {
        $this->_headers = array();
        $headers = preg_split("/\r?\n/", $headers);
        foreach ($headers as $headerLine) {
            $headerLine = trim($headerLine, "\r\n");
            if ($headerLine == '') {
                break;
            }
            if (preg_match('|^([\w-]+):\s+(.+)|', $headerLine, $m)) {
                $headerName = strtolower($m[1]);
                $headerValue = $m[2];

                if (!empty($this->_headers[$headerName])) {
                    $tmp = $this->_headers[$headerName];
                    if (!is_array($tmp)) {
                        $tmp = array($tmp);
                    }
                    $tmp[] = $headerValue;
                    $headerValue = $tmp;
                }

                $this->_headers[$headerName] = $headerValue;
                $lastHeader = $headerName;
            } elseif (preg_match("|^\s+(.+)$|", $headerLine, $m) &&
                      !is_null($lastHeader)) {
                if (is_array($this->_headers[$lastHeader])) {
                    $tmp = $this->_headers[$lastHeader];
                    end($tmp);
                    $tmp[key($tmp)] .= $m[1];
                    $this->_headers[$lastHeader] = $tmp;
                } else {
                    $this->_headers[$lastHeader] .= $m[1];
                }
            }
        }
    }

    /**
     * Build the local cache of repository names and properties.
     *
     * @param  array $results  An array of objects representing available
     *                         repositories.
     * @param  array $params   Parameters. @todo - provide an ignore list?
     * @return [type]          [description]
     */
    protected function _parseRepositories($results, $params = array())
    {
        foreach ($results as $repo) {
            $this->_repositories[$repo->name] = $repo;
        }
    }
}
