<?php namespace HashOver;

// Copyright (C) 2015-2019 Jacob Barkdull
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


class HTMLTag
{
	protected $tag;
	protected $usesPrettyPrint = true;
	protected $isSingleton;
	protected $attributes = array ();
	protected $children = array ();

	public function __construct ($tag = '', $attributes = '', $pretty = true, $singleton = false, $spaced = true)
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
		$this->usesPrettyPrint = $pretty;
		$this->isSingleton = $singleton;

		switch (gettype ($attributes)) {
			case 'object': {
				$this->appendChild ($attributes);
				break;
			}

			case 'array': {
				$this->createAttributes ($attributes, $spaced);
				break;
			}

			default: {
				$this->innerHTML ($attributes);
				break;
			}
		}
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

		if (!preg_match ('/[a-z0-9:-_.]+/iS', $string)) {
			return false;
		}

		return true;
	}

	protected function throwError ($error)
	{
		$backtrace = debug_backtrace ();
		$line = $backtrace[1]['line'];

		throw new \Exception (
			'Error on line ' . $line . ': ' . $error
		);
	}

	public function getInnerHTML ($indention = '')
	{
		$inner_html = array ();

		foreach ($this->children as $child) {
			if (is_object ($child)) {
				$inner_html[] = $child->asHTML ($indention);
				continue;
			}

			$inner_html[] = $child;
		}

		$glue = PHP_EOL . PHP_EOL . $indention;

		return implode ($glue, $inner_html);
	}

	public function createAttribute ($name = '', $value = '', $spaced = true)
	{
		if (!is_string ($name) or !$this->isWord ($name)) {
			$this->throwError ('Attribute name must have a single word String value.');
			return false;
		}

		if (is_array ($value)) {
			$glue = ($spaced !== false) ? ' ' : '';
			$this->attributes[$name] = implode ($glue, $value);
			return true;
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

	public function createAttributes (array $attributes, $spaced = true)
	{
		if (!is_array ($attributes)) {
			$this->throwError ('Attributes parameter must have an Array value.');
			return;
		}

		foreach ($attributes as $key => $value) {
			switch ($key) {
				case 'children': {
					if (is_array ($value)) {
						for ($i = 0, $il = count ($value); $i < $il; $i++) {
							$this->appendChild ($value[$i]);
						}
					}

					break;
				}

				case 'innerHTML': {
					$this->innerHTML ($value);
					break;
				}

				default: {
					$this->createAttribute ($key, $value, $spaced);
					break;
				}
			}
		}
	}

	public function appendAttribute ($name = '', $value = '', $spaced = true)
	{
		if (!is_string ($name) or !$this->isWord ($name)) {
			$this->throwError ('Attribute name must have a single word String value.');
			return false;
		}

		if (!empty ($this->attributes[$name])) {
			if ($spaced !== false) {
				$this->attributes[$name] .= ' ';
			}
		} else {
			$this->attributes[$name] = '';
		}

		if (is_array ($value)) {
			$glue = ($spaced !== false) ? ' ' : '';
			$this->attributes[$name] .= implode ($glue, $value);
			return true;
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
			if ($key === 'innerHTML') {
				$this->appendInnerHTML ($value);
				continue;
			}

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

	public function asHTML ($indention = '')
	{
		$attributes = '';

		foreach ($this->attributes as $name => $value) {
			$value = str_replace ('"', '&quot;', $value);
			$attributes .= ' ' . $name . '="' . $value . '"';
		}

		$tag = '<' . $this->tag . $attributes . '>';

		if ($this->isSingleton === false) {
			if (!empty ($this->children)) {
				$inner_html = $this->getInnerHTML ();

				if ($this->usesPrettyPrint !== false) {
					$tag .= PHP_EOL . "\t";
					$tag .= str_replace (PHP_EOL, PHP_EOL . "\t", $inner_html);
					$tag .= PHP_EOL;
				} else {
					$tag .= $inner_html;
				}
			}

			$tag .= '</' . $this->tag . '>';
		}

		if (!empty ($indention)) {
			return str_replace (PHP_EOL, PHP_EOL . $indention, $tag);
		}

		return $tag;
	}
}
