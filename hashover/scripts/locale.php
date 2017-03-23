<?php

// Copyright (C) 2010-2017 Jacob Barkdull
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

class Locale
{
	public $setup;
	public $mode;
	public $text;

	public function __construct (Setup $setup)
	{
		$this->setup = $setup;
		$this->mode = $setup->usage['mode'];

		// Lower case language code
		$language = strtolower ($setup->language);

		// Path to PHP locale file
		$locale_file_path = __DIR__ . '/locales/' . $language . '.php';

		// Default to English if locale doesn't exist
		if (!file_exists ($locale_file_path)) {
			$locale_file_path = __DIR__ . '/locales/en.php';
		}

		// Check if the locale file can be included
		if (@include ($locale_file_path)) {
			// If so, set locale to array stored in the file
			$this->text = $locale;
		} else {
			// If not, throw exception
			$language = strtoupper ($language);
			$exception = $language . ' locale file could not be included!';

			throw new Exception ($exception);
		}

		// Prepare locale
		$this->prepareLocale ();
	}

	// Prepares locale by modifing them in various ways
	public function prepareLocale ()
	{
		foreach ($this->text as $key => $value) {
			switch ($key) {
				// Add optionality to form field title locales
				case 'name-tip':
				case 'password-tip':
				case 'email-tip':
				case 'website-tip': {
					$field = str_replace ('-tip', '', $key);
					$option = $this->setup->fieldOptions[$field];
					$option = ($option === 'required') ? 'required' : 'optional';
					$option_locale = mb_strtolower ($this->text[$option]);

					$field_tip = sprintf ($this->text[$key], $option_locale);
					$this->set ($key, $field_tip);
					break;
				}

				// Inject date and time formats into date-time locale
				case 'date-time': {
					$date_format = $this->setup->dateFormat;
					$time_format = $this->setup->timeFormat;

					$date_time = sprintf ($value, $date_format, $time_format);
					$this->set ($key, $date_time);
					break;
				}
			}
		}
	}

	// Sets a locale string
	public function set ($name, $value)
	{
		$this->text[$name] = $value;
	}

	// Returns a locale string, optionally adding C-style escaping
	public function get ($name, $add_slashes = true, $charlist = "'")
	{
		// Don't escape given string in PHP mode or if told not to
		if ($this->mode === 'php' or $add_slashes === false) {
			return $this->text[$name];
		}

		// Check if locale is an array
		if (is_array ($this->text[$name])) {
			$escaped_array = array ();

			// If so, escape each item in the array
			foreach ($this->text[$name] as $key => $value) {
				$escaped_array[$key] = addcslashes ($value, $charlist);
			}

			// And return the new array
			return $escaped_array;
		}

		// Otherwise escape the single string
		return addcslashes ($this->text[$name], $charlist);
	}
}
