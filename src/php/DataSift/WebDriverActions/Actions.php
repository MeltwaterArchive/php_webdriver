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

namespace DataSift\WebDriverActions;

use DataSift\WebDriver\WebDriverSession;

/**
 * Extend this class to add in additional available actions
 *
 * Forget the PageObjects pattern; group your actions by FunctionalArea
 * instead, so that your tests only need to change when the functionality
 * changes, not the navigation of your website!
 */
class Actions
{
	/**
	 * the current webdriver session to use
	 * @var WebDriverSession
	 */
	private $session;

	/**
	 * represents the actions available on the current page
	 * @var CurrentPageActions
	 */
	private $currentPageActions;

	/**
	 * constructor
	 *
	 * @param WebDriverSession $session the webdriver session to use for all
	 *                                  of these actions
	 */
	public function __construct(WebDriverSession $session)
	{
		$this->session = $session;
	}

	/**
	 * returns an object you can use to manipulate the current page
	 *
	 * This object implements generic actions, and does not understand
	 * anything specific about your app at all
	 *
	 * @return CurrentPageActions
	 */
	public function currentPage()
	{
		if (!$this->currentPageActions) {
			$this->currentPageActions = new CurrentPageActions($this->session);
		}

		return $this->currentPageActions;
	}
}