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
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2012-present MediaSift Ltd
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      http://www.datasift.com
 */

namespace DataSift\WebDriver;

/**
 * Helper class defining all the non-text keys that can be sent to the
 * web browser, as listed in the Json Wire Protocol
 *
 * @category Libraries
 * @package  WebDriver
 * @license  http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.datasift.com
 * @link     https://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/value
 */

class WebDriverKeys
{
	const NULL_KEY   = '\uE000';
	const CANCEL_KEY = '\uE001';
	const HELP_KEY   = '\uE002';
	const BACKSPACE_KEY = '\uE003';
	const TAB_KEY    = '\uE004';
	const CLEAR_KEY = '\uE005';
	const RETURN_KEY = '\uE006';
	const ENTER_KEY = '\uE007';
	const SHIFT_KEY = '\uE008';
	const CONTROL_KEY = '\uE009';
	const ALT_KEY = '\uE00A';
	const PAUSE_KEY = '\uE00B';
	const ESC_KEY = '\uE00C';
	const SPACE_KEY = '\uE00D';
	const PGUP_KEY = '\uE00E';
	const PGDN_KEY = '\uE00F';
	const END_KEY = '\uE010';
	const HOME_KEY = '\uE011';
	const LEFT_ARROW_KEY = '\uE012';
	const UP_ARROW_KEY = '\uE013';
	const RIGHT_ARROW_KEY = '\uE014';
	const DOWN_ARROW_KEY = '\uE015';
	const INSERT_KEY = '\u0E16';
	const DELETE_KEY = '\uE017';
	const SEMICOLON_KEY = '\uE018';
	const EQUALS_KEY = '\uE019';
	const NUMPAD_0_KEY = '\uE01A';
	const NUMPAD_1_KEY = '\uE01B';
	const NUMPAD_2_KEY = '\uE01C';
	const NUMPAD_3_KEY = '\uE01D';
	const NUMPAD_4_KEY = '\uE01E';
	const NUMPAD_5_KEY = '\uE01F';
	const NUMPAD_6_KEY = '\uE020';
	const NUMPAD_7_KEY = '\uE021';
	const NUMPAD_8_KEY = '\uE022';
	const NUMPAD_9_KEY = '\uE023';
	const NUMPAD_MULTIPLY_KEY = '\uE024';
	const NUMPAD_ADD_KEY = '\uE025';
	const SEPARATOR_KEY = '\uE026';
	const NUMPAD_SUBTRACT_KEY = '\uE027';
	const NUMPAD_DECIMAL_KEY = '\uE028';
	const NUMPAD_DIVIDE_KEY = '\uE029';

	const F1_KEY = '\uE031';
	const F2_KEY = '\uE032';
	const F3_KEY = '\uE033';
	const F4_KEY = '\uE034';
	const F5_KEY = '\uE035';
	const F6_KEY = '\uE036';
	const F7_KEY = '\uE037';
	const F8_KEY = '\uE038';
	const F9_KEY = '\uE039';
	const F10_KEY = '\uE03A';
	const F11_KEY = '\uE03B';
	const F12_KEY = '\uE03C';
	const META_KEY = '\uE03D';
}