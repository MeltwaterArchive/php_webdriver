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

/**
 * Base class for all of the exceptions that can occur when webdriver
 * tells us it ran into a problem
 */
abstract class WebDriverException extends Exception
{
	private $results;

  	public function __construct($code, $message, $results = null)
  	{
    	parent::__construct($message, $code);
    	$this->results = $results;
  	}

  	public function getResults()
  	{
    	return $this->results;
  	}
}
