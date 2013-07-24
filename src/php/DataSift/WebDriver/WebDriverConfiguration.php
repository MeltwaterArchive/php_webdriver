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
 * @package   BrowserMobProxy
 * @author    Michael Heap <michael.heap@datasift.com>
 * @copyright 2004-present Facebook
 * @copyright 2012-present MediaSift Ltd
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      http://www.datasift.com
 */

namespace DataSift\WebDriver;

/**
 * Class for exposing various configuration options for Webdriver
 *
 * @category Libraries
 * @package  WebDriver
 * @license  http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.datasift.com
 * @link     http://facebook.com
 */
class WebDriverConfiguration
{
    /**
     * Returns the project's version
     * @return string    The version of the project
     */
    public function getVersion()
    {
        $composer = $this->loadJsonFileFromRoot("composer.json");

        if (!isset($composer->version)){
            throw new \Exception("composer.json does not contain a version");
        }

        return $composer->version;
    }

    /**
     * Returns the dependencies required for this project
     * @return object    The dependencies for this project
     */
    public function getDependencies()
    {
        return $this->loadJsonFileFromRoot("dependencies.json");
    }

    private function loadJsonFileFromRoot($file){
        $file = json_decode(file_get_contents(__DIR__.'/../../../../'.$file));
        if (!$file){
            throw new \Exception($file." is not a valid JSON file");
        }

        return $file;
    }

}
