<?php

/**
 * WebDriver - Client for Selenium 2 (a.k.a WebDriver)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category  Libraries
 * @package   WebDriver
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2004-present Facebook
 * @copyright 2012-present MediaSift Ltd
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      http://www.datasift.com
 * @link      http://facebook.com
 */

namespace DataSift\WebDriver;

/**
 * Represents a single DOM element
 *
 * @category Libraries
 * @package  WebDriver
 * @license  http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.datasift.com
 * @link     http://facebook.com
 */
class WebDriverElement extends WebDriverContainer
{
    // the ID of this element
    private $id;

    /**
     * constructor
     *
     * @param string $url the URL of this element
     * @param string $id  the ID of this element
     */
    public function __construct($url, $id)
    {
        $this->id = $id;
        parent::__construct($url);
    }

    /**
     * A list of the webdriver methods that this class supports
     *
     * @return array the methods and their supported HTTP verbs
     */
    protected function methods()
    {
        return array(
            'click' => 'POST',
            'submit' => 'POST',
            'text' => 'GET',
            'value' => 'POST',
            'name' => 'GET',
            'clear' => 'POST',
            'selected' => 'GET',
            'enabled' => 'GET',
            'attribute' => 'GET',
            'equals' => 'GET',
            'displayed' => 'GET',
            'location' => 'GET',
            'location_in_view' => 'GET',
            'size' => 'GET',
            'css' => 'GET',
        );
    }

    /**
     * get the ID of this element
     *
     * @return string the ID according to webdriver
     */
    public function getID() {
        return $this->id;
    }

    public function containsClass($class)
    {
        // get the class from this element
        $elementClass = $this->attribute('class');

        // turn it into something useful
        $elementClasses = explode(' ', $elementClass);

        // do the test
        return in_array($class, $elementClasses);
    }

    public function type($text)
    {
        return $this->value(array('value' => $this->convertTextForTyping($text)));
    }

    public function typeSpecial($text)
    {
        $params = '{"value": ["' . $text . '"]}';
        return $this->value($params);
    }

    protected function getElementPath($element_id) {
        return preg_replace(sprintf('/%s$/', $this->id), $element_id, $this->url);
    }

    protected function convertTextForTyping($text)
    {
        $len = strlen($text);
        $return = array();

        for($i = 0; $i < $len; $i++) {
            $return[] = $text{$i};
        }

        return $return;
    }
}