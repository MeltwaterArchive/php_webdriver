<?php
// Copyright (c) 2012 Konstantin Kudryashov <ever.zet@gmail.com>
// Copyright 2012-present MediaSift Ltd. All Rights Reserved.
//
// Permission is hereby granted, free of charge, to any person
// obtaining a copy of this software and associated documentation
// files (the "Software"), to deal in the Software without
// restriction, including without limitation the rights to use,
// copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the
// Software is furnished to do so, subject to the following
// conditions:
//
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
// EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
// OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
// WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
// OTHER DEALINGS IN THE SOFTWARE.

namespace DataSift\WebDriver;

abstract class WebDriverHelperContainer extends WebDriverContainer
{
    // ====================================================================
    //
    // HELPER METHODS
    //
    // Taken from, or inspired by, Mink's Selenium2Driver. Also aiming
    // to replicate missing methods from Selenium 1.
    //
    // --------------------------------------------------------------------

    /**
     * Executes JS on a given element - pass in a js script string and {{ELEMENT}} will
     * be replaced with a reference to the result of the $xpath query
     *
     * @example $this->executeJsOnXpath($xpath, 'return {{ELEMENT}}.childNodes.length');
     *
     * @param  string   $xpath  the xpath to search with
     * @param  string   $script the script to execute
     * @param  Boolean  $sync   whether to run the script synchronously (default is TRUE)
     *
     * @return mixed
     */
    protected function executeJsOnXpath($xpath, $script, $sync = true)
    {
        $element   = $this->element('xpath', $xpath);
        $elementID = $element->getID();
        $subscript = "arguments[0]";

        $script  = str_replace('{{ELEMENT}}', $subscript, $script);
        $execute = ($sync) ? 'execute' : 'execute_async';

        return $this->$execute(array(
            'script' => $script,
            'args'   => array(array('ELEMENT' => $elementID))
        ));
    }

    public function find($xpath)
    {
        return $this->elements('xpath', $xpath);
    }

    public function getTagName($xpath)
    {
        return $this->element('xpath', $xpath)->name();
    }

    public function getText($xpath)
    {
        $node = $this->element('xpath', $xpath);
        $text = $node->text();
        $text = (string) str_replace(array("\r", "\r\n", "\n"), ' ', $text);

        return $text;
    }

    public function getHtml($xpath)
    {
        return $this->executeJsOnXpath($xpath, 'return {{ELEMENT}}.innerHTML');
    }

    public function getAttribute($xpath, $name)
    {
        $attribute = $this->element('xpath', $xpath)->attribute($name);
        if ($attribute !== '') {
            return $attribute;
        }

        return null;
    }

    public function getValue($xpath)
    {
        $script = <<<JS
var node = {{ELEMENT}},
    tagName = node.tagName;

if (tagName == "INPUT" || "TEXTAREA" == tagName) {
    var type = node.getAttribute('type');
    if (type == "checkbox") {
        value = "boolean:" + node.checked;
    } else if (type == "radio") {
        var name = node.getAttribute('name');
        if (name) {
            var fields = window.document.getElementsByName(name);
            var i, l = fields.length;
            for (i = 0; i < l; i++) {
                var field = fields.item(i);
                if (field.checked) {
                    value = "string:" + field.value;
                }
            }
        }
    } else {
        value = "string:" + node.value;
    }
} else if (tagName == "SELECT") {
    if (node.getAttribute('multiple')) {
        options = [];
        for (var i = 0; i < node.options.length; i++) {
            if (node.options[ i ].selected) {
                options.push(node.options[ i ].value);
            }
        }
        value = "array:" + options.join(',');
    } else {
        var idx = node.selectedIndex;
        if (idx >= 0) {
            value = "string:" + node.options.item(idx).value;
        } else {
            value = null;
        }
    }
} else {
    attributeValue = node.getAttribute('value');
    if (attributeValue != null) {
        value = "string:" + attributeValue;
    } else if (node.value) {
        value = "string:" + node.value;
    } else {
        return null;
    }
}

return value;
JS;

        $value = $this->executeJsOnXpath($xpath, $script);
        if ($value) {
            if (preg_match('/^string:(.*)$/ms', $value, $vars)) {
                return $vars[1];
            }
            if (preg_match('/^boolean:(.*)$/', $value, $vars)) {
                return 'true' === strtolower($vars[1]);
            }
            if (preg_match('/^array:(.*)$/', $value, $vars)) {
                if ('' === trim($vars[1])) {
                    return array();
                }

                return explode(',', $vars[1]);
            }
        }
    }

    /**
     * Sets element's value by it's XPath query.
     *
     * @param   string  $xpath
     * @param   string  $value
     */
    public function setValue($xpath, $value)
    {
        $element = $this->wdSession->element('xpath', $xpath);
        if (
            strtolower($element->name()) != 'input' ||
            strtolower($element->attribute('type')) != 'file'
        )
        {
            $element->clear();
        }

        $element->value(array('value' => array($value)));
    }

    /**
     * Checks checkbox by it's XPath query.
     *
     * @param   string  $xpath
     */
    public function check($xpath)
    {
        $this->executeJsOnXpath($xpath, '{{ELEMENT}}.checked = true');
    }

    /**
     * Unchecks checkbox by it's XPath query.
     *
     * @param   string  $xpath
     */
    public function uncheck($xpath)
    {
        $this->executeJsOnXpath($xpath, '{{ELEMENT}}.checked = false');
    }

    /**
     * Checks whether checkbox checked located by it's XPath query.
     *
     * @param   string  $xpath
     *
     * @return  Boolean
     */
    public function isChecked($xpath)
    {
        return $this->wdSession->element('xpath', $xpath)->selected();
    }

    /**
     * Selects option from select field located by it's XPath query.
     *
     * @param   string  $xpath
     * @param   string  $value
     * @param   Boolean $multiple
     */
    public function selectOption($xpath, $value, $multiple = false)
    {
        $valueEscaped = str_replace('"', '\"', $value);
        $multipleJS   = $multiple ? 'true' : 'false';

        $script = <<<JS
// Function to triger an event. Cross-browser compliant. See http://stackoverflow.com/a/2490876/135494
var triggerEvent = function (element, eventName) {
    var event;
    if (document.createEvent) {
        event = document.createEvent("HTMLEvents");
        event.initEvent(eventName, true, true);
    } else {
        event = document.createEventObject();
        event.eventType = eventName;
    }

    event.eventName = eventName;

    if (document.createEvent) {
        element.dispatchEvent(event);
    } else {
        element.fireEvent("on" + event.eventType, event);
    }
}

var node = {{ELEMENT}}
if (node.tagName == 'SELECT') {
    var i, l = node.length;
    for (i = 0; i < l; i++) {
        if (node[i].value == "$valueEscaped") {
            node[i].selected = true;
        } else if (!$multipleJS) {
            node[i].selected = false;
        }
    }
    triggerEvent(node, 'change');

} else {
    var nodes = window.document.getElementsByName(node.getAttribute('name'));
    var i, l = nodes.length;
    for (i = 0; i < l; i++) {
        if (nodes[i].getAttribute('value') == "$valueEscaped") {
            node.checked = true;
        }
    }
}
JS;


        $this->executeJsOnXpath($xpath, $script);
    }

    /**
     * Clicks button or link located by it's XPath query.
     *
     * @param   string  $xpath
     */
    public function click($xpath)
    {
        $this->wdSession->element('xpath', $xpath)->click('');
    }

    /**
     * Double-clicks button or link located by it's XPath query.
     *
     * @param   string  $xpath
     */
    public function doubleClick($xpath)
    {
        $script = 'Syn.dblclick({{ELEMENT}})';
        $this->withSyn()->executeJsOnXpath($xpath, $script);
    }

    /**
     * Right-clicks button or link located by it's XPath query.
     *
     * @param   string  $xpath
     */
    public function rightClick($xpath)
    {
        $script = 'Syn.rightClick({{ELEMENT}})';
        $this->withSyn()->executeJsOnXpath($xpath, $script);
    }

    /**
     * Attaches file path to file field located by it's XPath query.
     *
     * @param   string  $xpath
     * @param   string  $path
     */
    public function attachFile($xpath, $path)
    {
        $this->wdSession->element('xpath', $xpath)->value(array('value'=>str_split($path)));
    }

    /**
     * Checks whether element visible located by it's XPath query.
     *
     * @param   string  $xpath
     *
     * @return  Boolean
     */
    public function isVisible($xpath)
    {
        return $this->wdSession->element('xpath', $xpath)->displayed();
    }

    /**
     * Simulates a mouse over on the element.
     *
     * @param   string  $xpath
     */
    public function mouseOver($xpath)
    {
        $script = 'Syn.trigger("mouseover", {}, {{ELEMENT}})';
        $this->withSyn()->executeJsOnXpath($xpath, $script);
    }

    /**
     * Brings focus to element.
     *
     * @param   string  $xpath
     */
    public function focus($xpath)
    {
        $script = 'Syn.trigger("focus", {}, {{ELEMENT}})';
        $this->withSyn()->executeJsOnXpath($xpath, $script);
    }

    /**
     * Removes focus from element.
     *
     * @param   string  $xpath
     */
    public function blur($xpath)
    {
        $script = 'Syn.trigger("blur", {}, {{ELEMENT}})';
        $this->withSyn()->executeJsOnXpath($xpath, $script);
    }

    /**
     * Presses specific keyboard key.
     *
     * @param   string  $xpath
     * @param   mixed   $char       could be either char ('b') or char-code (98)
     * @param   string  $modifier   keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    public function keyPress($xpath, $char, $modifier = null)
    {
        $options = self::charToOptions('keypress', $char, $modifier);
        $script = "Syn.trigger('keypress', $options, {{ELEMENT}})";
        $this->withSyn()->executeJsOnXpath($xpath, $script);
    }

    /**
     * Pressed down specific keyboard key.
     *
     * @param   string  $xpath
     * @param   mixed   $char       could be either char ('b') or char-code (98)
     * @param   string  $modifier   keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    public function keyDown($xpath, $char, $modifier = null)
    {
        $options = self::charToOptions('keydown', $char, $modifier);
        $script = "Syn.trigger('keydown', $options, {{ELEMENT}})";
        $this->withSyn()->executeJsOnXpath($xpath, $script);
    }

    /**
     * Pressed up specific keyboard key.
     *
     * @param   string  $xpath
     * @param   mixed   $char       could be either char ('b') or char-code (98)
     * @param   string  $modifier   keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    public function keyUp($xpath, $char, $modifier = null)
    {
        $options = self::charToOptions('keyup', $char, $modifier);
        $script = "Syn.trigger('keyup', $options, {{ELEMENT}})";
        $this->withSyn()->executeJsOnXpath($xpath, $script);
    }


    /**
     * Drag one element onto another.
     *
     * @param   string  $sourceXpath
     * @param   string  $destinationXpath
     */
    public function dragTo($sourceXpath, $destinationXpath)
    {
        $source      = $this->wdSession->element('xpath', $sourceXpath);
        $destination = $this->wdSession->element('xpath', $destinationXpath);

        $sourceSize = $source->size();
        $sourceX    = $sourceSize['width']/2;
        $sourceY    = $sourceSize['height']/2;

        $destinationSize = $destination->size();
        $destinationX    = $destinationSize['width']/2;
        $destinationY    = $destinationSize['height']/2;

        $this->wdSession->moveto(array(
            'element' => $source->getID(),
            'xoffset' => $sourceX,
            'yoffset' => $sourceY
        ));
        $this->wdSession->buttondown();
        $this->wdSession->moveto(array(
            'element' => $source->getID(),
            'xoffset' => $sourceX+1,
            'yoffset' => $sourceY+1
        ));
        $this->wdSession->moveto(array(
            'element' => $destination->getID(),
            'xoffset' => $destinationX,
            'yoffset' => $destinationY
        ));
        $this->wdSession->moveto(array(
            'element' => $destination->getID(),
            'xoffset' => $destinationX+1,
            'yoffset' => $destinationY+1
        ));
        $this->wdSession->buttonup();
    }

    /**
     * Executes JS script.
     *
     * @param   string  $script
     */
    public function executeScript($script)
    {
        $this->wdSession->execute(array('script' => $script, 'args' => array()));
    }

    /**
     * Evaluates JS script.
     *
     * @param   string  $script
     *
     * @return  mixed           script return value
     */
    public function evaluateScript($script)
    {
        return $this->wdSession->execute(array('script' => $script, 'args' => array()));
    }

    /**
     * Waits some time or until JS condition turns true.
     *
     * @param   integer $time       time in milliseconds
     * @param   string  $condition  JS condition
     */
    public function wait($time, $condition)
    {
        $script = "return $condition;";
        $start = 1000 * microtime(true);
        $end = $start + $time;
        $count = 0;
        while (1000 * microtime(true) < $end && !$this->wdSession->execute(array('script' => $script, 'args' => array()))) {
            sleep(0.1);
        }
    }
}