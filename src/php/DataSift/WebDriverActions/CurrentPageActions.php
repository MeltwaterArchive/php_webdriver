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

class CurrentPageActions extends ActionBase
{
	public function title()
	{
		return $this->session->element('tag', 'title')->value();
	}

	public function click($id)
	{

	}

	public function clickLink($id)
	{

	}

	public function clickName($buttonName)
	{

	}

	public function getText($id)
	{

	}

	public function isElementPresent($id)
	{

	}

	public function select($id, $label)
	{

	}

	public function selectName($name, $label)
	{
	}

	public function check($id)
	{

	}

	public function type($id, $content)
	{

	}

	public function typeCSS($cssSelector, $content)
	{

	}

	public function typeName($name, $content)
	{

	}

	public function waitForElement($id)
	{

	}

	public function completeForm($settings)
	{

	}
}