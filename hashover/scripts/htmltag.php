<?php

// Copyright (C) 2015-2016 Jacob Barkdull
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
	protected $isSingleton;
	protected $usesPrettyPrint;
	protected $attributes = array ();
	protected $children = array ();

	public function __construct ($tag = '', $singleton = false, $pretty = true)
	{
		if (!is_string ($tag) or !$this->isWord ($tag)) {
			$this->throwError ('Tag must have a single word String value.');
			return;
		}

		if (!is_bool ($singleton)) {
			$this->throwError ('Singleton must be of type Boolean.');
			return;
		}

		if (!is_bool ($pretty)) {
			$this->throwError ('Pretty Print must be of type Boolean.');
			return;
		}

		$this->tag = $tag;
		$this->isSingleton = $singleton;
		$this->usesPrettyPrint = $pretty;
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

		if (!ctype_alnum ($string)) {
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

		if (empty ($this->tag)) {
			$this->throwError ('No tag to add attribute to.');
			return false;
		}

		$this->attributes[$name] = $value;
		return true;
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
		if (empty ($this->tag)) {
			$this->throwError ('You have not added any HTML tags.');
			return false;
		}

		$tag = '<' . $this->tag;

		foreach ($this->attributes as $attribute => $value) {
			$tag .= ' ' . $attribute . '="' . $value . '"';
		}

		$tag .= '>';

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
