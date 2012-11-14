<?php
// Copyright 2012-present MediaSift Ltd. All Rights Reserved.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

namespace DataSift\BrowserMobProxy;

use Exception;
use DataSift\HttpArchive\HttpArchive;
use DataSift\WebDriver\HttpAddress;

class BrowserMobProxySession extends BrowserMobProxyBase
{
    /**
     * the proxy port to use for this session
     * @var integer
     */
    protected $port = null;

    /**
     * has this connection been closed yet?
     * @var boolean
     */
    protected $closed = false;

    /**
     * constructor
     *
     * Objects are normally created from BrowserMobProxy::createProxy(),
     * rather than being created manually
     *
     * @param string  $url  the URL of the REST API of the browsermob-proxy
     * @param integer $port the TCP/IP port that the current proxy session is on
     */
    public function __construct($url, $port) {
        parent::__construct($url);
        $this->port = $port;
    }

    /**
     * Return the port to use for the current proxy session
     *
     * @return integer TCP/IP port of the current proxy
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Returns the config information that needs to be given to
     * @return [type] [description]
     */
    public function getWebDriverProxyConfig()
    {
        $this->requireOpenConnection();

        $address = new HttpAddress($this->getUrl());

        return array (
            'proxyType' => 'manual',
            'httpProxy' => $address->hostname . ':' . $this->getPort(),
            'sslProxy'  => $address->hostname . ':' . $this->getPort()
        );
    }

    /**
     * Start logging into a HTTP Archive (HAR)
     *
     * @param  string $logName the name of the archive to use
     * @return void
     */
    public function startHAR($logName = 'default')
    {
        $this->requireOpenConnection();

        $response = $this->curl(
            'PUT',
            '/proxy/' . $this->port . '/har',
            array('initialPageRef' => $logName)
        );
    }

    /**
     * get the HTTP request/response details about the last page, in
     * HTTP Archive (HAR) format
     *
     * @param  string $logName name of the log used with startHAR()
     * @return HttpArchive
     */
    public function getHAR($logName = 'default')
    {
        $this->requireOpenConnection();

        $response = $this->curl(
            'GET',
            '/proxy/' . $this->port . '/har'
        );

        $har = new HttpArchive();
        $har->initFromProxyData($response);

        return $har;
    }

    /**
     * inject a set of headers into all subsequent requests
     *
     * @param array(string => string) $headers
     *   a list of headers to inject, with the array key being the name
     *   of the header, and the array value being the value of the header
     */
    public function setHeaders($headers)
    {
        $this->requireOpenConnection();

        $response = $this->curl(
            'POST',
            '/proxy/' . $this->port . '/headers',
            (object)$headers
        );
    }

    public function removeHeader($name) {
        $this->requireOpenConnection();
        $this->requireFeature('headerGetDelete');

        $response = $this->curl(
            'DELETE',
            '/proxy/' . $this->port . '/header/' . urlencode($name)
        );
    }

    public function removeAllHeaders() {
        $this->requireOpenConnection();
        $this->requireFeature('headerGetDelete');

        $response = $this->curl(
            'DELETE',
            '/proxy/' . $this->port . '/headers'
        );
    }

    public function getHeader($name) {
        $this->requireOpenConnection();
        $this->requireFeature('headerGetDelete');

        $response = $this->curl(
            'GET',
            '/proxy/' . $this->port . '/header/' . urlencode($name)
        );

        return $response;
    }

    public function setHttpBasicAuth($domain, $username, $password) {
        $this->requireOpenConnection();
        $this->requireFeature('httpBasicAuth');

        $response = $this->curl(
            'PUT',
            '/proxy/' . $this->port . '/basicAuth/' . urlencode($domain),
            array(
                'username' => $username,
                'password' => $password
            )
        );
    }

    /**
     * delete the proxy, because we're done
     *
     * @return void
     */
    public function close()
    {
        $this->requireOpenConnection();

        $response = $this->curl(
            'DELETE',
            '/proxy/' . $this->port
        );

        $this->closed = true;
    }

    /**
     * helper method. throws an exception if this session has already
     * been closed
     *
     * @return void
     */
    protected function requireOpenConnection()
    {
        if ($this->closed) {
            throw new BrowserMobProxyClosedSessionException();
        }
    }
}