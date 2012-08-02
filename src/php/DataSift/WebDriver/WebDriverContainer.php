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

/**
 * A base class for elements etc that the caller might want to interact
 * with
 */
abstract class WebDriverContainer extends WebDriverBase
{
    /**
     * retrieve an element from the currently loaded page
     *
     * @param  string $using the search strategy
     * @param  string $value the term to search for
     *
     * @return WebDriverElement        the element that has been searched for
     */
    public function element($using, $value)
    {
        // try to get the requested element from the open session
        try {
            $results = $this->curl(
                'POST',
                '/element',
                array(
                    'using' => $using,
                    'value' => $value
                )
            );
        }
        catch (NoSuchElementWebDriverError $e) {
            // the element does not exist
            throw new NoSuchElementWebDriverError(
                sprintf(
                    'Element not found with %s, %s',
                    $using,
                    $value
                ) . "\n\n" . $e->getMessage(),
                $e->getResults()
            );
        }

        // if we get here, then we can return the element back to the
        // caller :)
        return $this->webDriverElement($results['value']);
    }

    /**
     * Find all occurances of an element on the current page
     *
     * @param  string $using the search strategy
     * @param  string $value the term to search for
     *
     * @return array(WebDriverElement)
     */
    public function elements($using, $value)
    {
        try {
            $results = $this->curl(
                'POST',
                '/elements',
                array(
                    'using' => $using,
                    'value' => $value
                )
            );
        }
        catch (NoSuchElementWebDriverError $e) {
            // the element does not exist
            throw new NoSuchElementWebDriverError(
                sprintf(
                    'Element not found with %s, %s',
                    $using,
                    $value
                ) . "\n\n" . $e->getMessage(),
                $e->getResults()
            );
        }

        // if we get here, then we can return the set of elements back
        // to the caller :)
        return array_filter(array_map(
            array($this, 'webDriverElement'), $results['value'])
        );
    }

    /**
     * helper method to wrap an element inside the WebDriverElement
     * object
     *
     * @param  array $value the raw element data returned from webdriver
     *
     * @return WebDriverElement the WebDriverElement object for the raw
     *                          element
     */
    protected function webDriverElement($value)
    {
        // is the returned element in the format we expect?
        if (!array_key_exists('ELEMENT', (array) $value)) {
            // no, it is not
            return null;
        }

        return new WebDriverElement(
            $this->getElementPath($value['ELEMENT']), // url
            $value['ELEMENT'] // id
        );
    }

    abstract protected function getElementPath($element_id);
}