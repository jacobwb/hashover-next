<?php

// Copyright (C) 2015-2017 Jacob Barkdull
// This file is part of HashOver.
//
// HashOver is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// HashOver is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


// Display source code
if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
	if (isset ($_GET['source'])) {
		header ('Content-type: text/plain; charset=UTF-8');
		exit (file_get_contents (basename (__FILE__)));
	} else {
		exit ('<b>HashOver</b>: This is a class file.');
	}
}

class HTMLTag
{
	protected $tag;
	protected $usesPrettyPrint = true;
	protected $isSingleton;
	protected $attributes = array ();
	protected $children = array ();

	public function __construct ($tag = '', array $attributes = array (), $pretty = true, $singleton = false)
	{
		if (!is_string ($tag) or !$this->isWord ($tag)) {
			$this->throwError ('Tag must have a single word String value.');
			return;
		}

		if (!is_bool ($singleton)) {
			$this->throwError ('Singleton parameter must have a Boolean value.');
			return;
		}

		if (!is_bool ($pretty)) {
			$this->throwError ('Pretty Print parameter must have a Boolean value.');
			return;
		}

		$this->tag = !empty ($tag) ? $tag : 'span';
		$this->createAttributes ($attributes);
		$this->usesPrettyPrint = $pretty;
		$this->isSingleton = $singleton;
	}

	public function __get ($name)
	{
		switch ($name) {
			case 'innerHTML': {
				return $this->getInnerHTML ();
			}
		}
	}

	protected function isWord ($string)
	{
		if (empty ($string)) {
			return false;
		}

		if (!preg_match ('/[a-z0-9:-_.]+/i', $string)) {
			return false;
		}

		return true;
	}

	protected function throwError ($error)
	{
		$backtrace = debug_backtrace ();
		$line = $backtrace[1]['line'];

		throw new Exception ('Error on line ' . $line . ': ' . $error);
	}

	protected function getInnerHTML ()
	{
		$inner_html = array ();

		foreach ($this->children as $child) {
			if (is_object ($child)) {
				$inner_html[] = $child->asHTML ();
				continue;
			}

			$inner_html[] = $child;
		}

		return implode (PHP_EOL, $inner_html);
	}

	public function createAttribute ($name = '', $value = '')
	{
		if (!is_string ($name) or !$this->isWord ($name)) {
			$this->throwError ('Attribute name must have a single word String value.');
			return false;
		}

		$this->attributes[$name] = $value;
		return true;
	}

	public function innerHTML ($html = '')
	{
		if ($this->isSingleton === true) {
			$this->throwError ('Singleton tags do not have innerHTML.');
			return false;
		}

		if (!empty ($html)) {
			$this->children = array ($html);
		}

		return true;
	}

	public function appendInnerHTML ($html = '')
	{
		if ($this->isSingleton === true) {
			$this->throwError ('Singleton tags do not have innerHTML.');
			return false;
		}

		if (!empty ($html)) {
			$this->children[] = $html;
		}

		return true;
	}

	public function createAttributes (array $attributes)
	{
		if (!is_array ($attributes)) {
			$this->throwError ('Attributes parameter must have an Array value.');
			return;
		}

		foreach ($attributes as $key => $value) {
			$this->createAttribute ($key, $value);
		}
	}

	public function appendAttribute ($name = '', $value = '', $spaced = true)
	{
		if (!is_string ($name) or !$this->isWord ($name)) {
			$this->throwError ('Attribute name must have a single word String value.');
			return false;
		}

		if (!empty ($this->attributes[$name])) {
			if ($spaced === true) {
				$this->attributes[$name] .= ' ';
			}
		} else {
			$this->attributes[$name] = '';
		}

		$this->attributes[$name] .= $value;
		return true;
	}

	public function appendAttributes (array $attributes, $spaced = true)
	{
		if (!is_array ($attributes)) {
			$this->throwError ('Attributes parameter must have an Array value.');
			return;
		}

		foreach ($attributes as $key => $value) {
			$this->appendAttribute ($key, $value, $spaced);
		}
	}

	public function appendChild (HTMLTag $object)
	{
		if ($this->isSingleton === true) {
			$this->throwError ('Singleton tags do not have children.');
			return false;
		}

		if (!is_object ($object)) {
			$given_type = ucwords (gettype ($object));
			$this->throwError ($given_type . ' given, when Object is expected.');
			return false;
		}

		$this->children[] = $object;
		return true;
	}

	public function asHTML ()
	{
		$attributes = '';

		foreach ($this->attributes as $attribute => $value) {
			$attributes .= ' ' . $attribute . '="' . $value . '"';
		}

		$tag = '<' . $this->tag . $attributes . '>';

		if ($this->isSingleton === false) {
			if (!empty ($this->children)) {
				$inner_html = $this->getInnerHTML ();

				if ($this->usesPrettyPrint === true) {
					$tag .= PHP_EOL . "\t";
					$tag .= str_replace (PHP_EOL, PHP_EOL . "\t", $inner_html);
					$tag .= PHP_EOL;
				} else {
					$tag .= $inner_html;
				}
			}

			$tag .= '</' . $this->tag . '>';
		}

		return $tag;
	}
}
