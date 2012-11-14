<?php
// Copyright 2004-present Facebook. All Rights Reserved.
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

namespace DataSift\WebDriver;

use Exception;

abstract class WebDriverBase
{
    /**
     * Returns the name of the exception class to throw
     * @param  int    $status_code the status code returned from webdriver
     * @return string              the name of the exception class to throw, or null if no error occurred
     */
    public function returnExceptionToThrow($status_code)
    {
        static $map = array (
            1  => 'IndexOutOfBoundsWebDriverError',
            2  => 'NoCollectionWebDriverError',
            3  => 'NoStringWebDriverError',
            4  => 'NoStringLengthWebDriverError',
            5  => 'NoStringWrapperWebDriverError',
            6  => 'NoSuchDriverWebDriverError',
            7  => 'NoSuchElementWebDriverError',
            8  => 'NoSuchFrameWebDriverError',
            9  => 'UnknownCommandWebDriverError',
            10 => 'ObsoleteElementWebDriverError',
            11 => 'ElementNotDisplayedWebDriverError',
            12 => 'InvalidElementStateWebDriverError',
            13 => 'UnhandledWebDriverError',
            14 => 'ExpectedWebDriverError',
            15 => 'ElementNotSelectableWebDriverError',
            16 => 'NoSuchDocumentWebDriverError',
            17 => 'UnexpectedJavascriptWebDriverError',
            18 => 'NoScriptResultWebDriverError',
            19 => 'XPathLookupWebDriverError',
            20 => 'NoSuchCollectionWebDriverError',
            21 => 'TimeOutWebDriverError',
            22 => 'NullPointerWebDriverError',
            23 => 'NoSuchWindowWebDriverError',
            24 => 'InvalidCookieDomainWebDriverError',
            25 => 'UnableToSetCookieWebDriverError',
            26 => 'UnexpectedAlertOpenWebDriverError',
            27 => 'NoAlertOpenWebDriverError',
            28 => 'ScriptTimeoutWebDriverError',
            29 => 'InvalidElementCoordinatesWebDriverError',
            30 => 'IMENotAvailableWebDriverError',
            31 => 'IMEEngineActivationFailedWebDriverError',
            32 => 'InvalidSelectorWebDriverError',
            33 => 'SessionNotCreatedWebDriverError',
            34 => 'MoveTargetOutOfBoundsWebDriverError',
        );

        // did an error occur?
        if ($status_code == 0) {
            return null;
        }

        // is this a known problem?
        if (isset($map[$status_code])) {
            return __NAMESPACE__ . '\\' . $map[$status_code];
        }

        // we have an unknown exception
        return __NAMESPACE__ . '\\UnknownWebDriverError';
    }

    /**
     * A list of the methods that the child class exposes to the user
     * @return array
     */
    abstract protected function methods();

    /**
     * the URL of the webdriver server we are using
     * @var string
     */
    protected $url;

    /**
     * constructor
     *
     * @param string $url the URL where the Selenium server can be found
     */
    public function __construct($url = 'http://localhost:4444/wd/hub')
    {
        $this->url = $url;
    }

    /**
     * convert this class for printing to the screen
     * @return string [description]
     */
    public function __toString() {
        return $this->url;
    }

    /**
     * get the URL of the Selenium webdriver server we are talking to
     * @return string URL of the Selenium webdriver server
     */
    public function getURL() {
        return $this->url;
    }

    /**
     * Curl request to webdriver server.
     *
     * $http_method  'GET', 'POST', or 'DELETE'
     * $command      If not defined in methods() this function will throw.
     * $params       If an array(), they will be posted as JSON parameters
     *               If a number or string, "/$params" is appended to url
     * $extra_opts   key=>value pairs of curl options to pass to curl_setopt()
     */
    protected function curl(
        $http_method,
        $command,
        $params = null,
        $extra_opts = array()
    )
    {
        // catch problems with the definition of allowed methods in
        // child classes
        if ($params && is_array($params) && $http_method !== 'POST') {
            throw new Exception(sprintf(
                'The http method called for %s is %s but it has to be POST' .
                ' if you want to pass the JSON params %s',
                $command,
                $http_method,
                json_encode($params)));
        }

        // determine the URL we are posting to
        $url = sprintf('%s%s', $this->url, $command);
        if ($http_method == 'GET' && $params && (is_int($params) || is_string($params))) {
            $url .= '/' . $params;
        }

        // create the curl request
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json;charset=UTF-8',
                'Accept: application/json'
            )
        );
        if ($http_method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);

            if ($params) {
                if (is_array($params)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
                }
                else {
                    // assume they've already been encoded
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                }
            }
        }
        else if ($http_method == 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        foreach ($extra_opts as $option => $value) {
            curl_setopt($curl, $option, $value);
        }

        // make the curl request
        $raw_results = trim(curl_exec($curl));

        // find out from curl what happened
        $info = curl_getinfo($curl);

        // was there an error?
        if ($error = curl_error($curl)) {
            // yes, there was
            // we throw an exception to explain that the call failed
            $msg = sprintf(
                'Curl error thrown for http %s to %s',
                $http_method,
                $url
            );
            if ($params && is_array($params)) {
                $msg .= sprintf(' with params: %s', json_encode($params));
            }
            throw new WebDriverCurlException($msg . "\n\n" . $error);
        }
        // we're done with curl for this request
        curl_close($curl);

        // convert the response from webdriver into something we can work with
        $results = json_decode($raw_results, true);

        // did we get a value back from webdriver?
        $value = null;
        if (is_array($results) && array_key_exists('value', $results)) {
            $value = $results['value'];
        }

        // did we get a message back from webdriver?
        $message = null;
        if (is_array($value) && array_key_exists('message', $value)) {
            $message = $value['message'];
        }

        // did webdriver send us back an error?
        if ($results['status'] != 0) {
            // yes it did ... throw the appropriate exception from here
            $className = $this->returnExceptionToThrow($results['status']);
            throw new $className($results['status'], $message, $results);
        }

        // if we get here, return the results back to the caller
        return array('value' => $value, 'info' => $info);
    }

    /**
     * The magic that converts a PHP method call into the HTTP request
     * to webdriver
     *
     * @param  string $name      the name of the PHP method
     * @param  array  $arguments the arguments passed to the PHP method
     * @return array             the result returned from webdriver
     */
    public function __call($name, $arguments)
    {
        // make sure the argument count is legit
        if (count($arguments) > 1) {
            throw new Exception(
                'Commands should have at most only one parameter,' .
                ' which should be the JSON Parameter object'
            );
        }

        // the start of the PHP method call tells us which HTTP verb
        // we are going to use to talk to webdriver
        if (preg_match('/^(get|post|delete)/', $name, $matches)) {
            $http_verb = strtoupper($matches[0]);

            $methods = $this->methods();
            if (!in_array($http_verb, $methods[$webdriver_command])) {
                throw new Exception(sprintf(
                    '%s is not an available http method for the command %s.',
                    $http_verb,
                    $webdriver_command
                ));
            }
        } else {
            // special case - methods that look odd when prefixed with
            // 'get' or 'post' or 'delete'. we use the methods() map
            // to look these up
            $webdriver_command = $name;
            $http_verb = $this->getHttpVerb($webdriver_command);
        }

        // make the HTTP call using our curl wrapper
        echo "$http_verb /$webdriver_command\n";
        $results = $this->curl(
            $http_verb,
            '/' . $webdriver_command,
            array_shift($arguments)
        );

        return $results['value'];
    }

    /**
     * determine the HTTP verb to use for a given webdriver command
     *
     * @param  string $webdriver_command the webdriver command to use
     * @return string                    the HTTP verb to use
     */
    private function getHttpVerb($webdriver_command)
    {
        $methods = $this->methods();

        if (!isset($methods[$webdriver_command])) {
            throw new Exception(sprintf(
                '%s is not a valid webdriver command.',
                $webdriver_command
            ));
        }

        // the first element in the array is the default HTTP verb to use
        return $methods[$webdriver_command];
    }
}