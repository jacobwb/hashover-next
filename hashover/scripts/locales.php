<?php

// Copyright (C) 2010-2015 Jacob Barkdull
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

class Locales
{
	public $mode;
	public $locale;

	public function __construct ($language, $mode)
	{
		$this->mode = $mode;

		// Path to PHP locale file
		$locale_file_path = __DIR__ . '/locales/' . strtolower ($language) . '.php';

		// Default to English if locale doesn't exist
		if (!file_exists ($locale_file_path)) {
			$locale_file_path = __DIR__ . '/locales/en.php';
		}

		// Set language locale
		if (@include ($locale_file_path)) {
			$this->locale = $locale;
		} else {
			throw new Exception (strtoupper ($language) . ' locale file could not be included!');
		}
	}

	// Return a locale string, optionally add C-style escaping
	public function locale ($name, $add_slashes = true, $charlist = "'")
	{
		// Don't escape given string in PHP mode if told not to
		if ($this->mode === 'php' or $add_slashes === false) {
			return $this->locale[$name];
		}

		// Check if locale is an array
		if (is_array ($this->locale[$name])) {
			$escaped_array = array ();

			// If so, escape each item in the array
			foreach ($this->locale[$name] as $key => $value) {
				$escaped_array[$key] = addcslashes ($value, $charlist);
			}

			// And return the new array
			return $escaped_array;
		}

		// Otherwise escape the single string
		return addcslashes ($this->locale[$name], $charlist);
	}
}
