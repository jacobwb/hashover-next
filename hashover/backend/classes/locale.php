<?php namespace HashOver;

// Copyright (C) 2010-2018 Jacob Barkdull
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


class Locale
{
	public $setup;
	public $mode;
	public $text;

	public function __construct (Setup $setup)
	{
		$this->setup = $setup;
		$this->mode = $setup->usage['mode'];

		// Get appropriate locale file
		$locale_file_path = $this->getLocaleFile ();

		// Include the locale file
		$this->includeLocaleFile ($locale_file_path);

		// Prepare locale
		$this->prepareLocale ();
	}

	// Check for PHP locale file
	protected function getLocaleFile ()
	{
		// Locales checklist
		$locales = array ();

		// Lowercase language code
		$language = mb_strtolower ($this->setup->language);

		// Get path to locales directory
		$locales_directory = $this->setup->getAbsolutePath ('backend/locales');

		// Check if we are automatically selecting the locale
		if ($language === 'auto') {
			// If so, get system locale
			$system_locale = mb_strtolower (setlocale (LC_CTYPE, 0));

			// Split the locale into specific parts
			$locale_parts = explode ('.', $system_locale);
			$language_parts = explode ('_', $locale_parts[0]);

			// Add locale in 'en-us' format to checklist
			$full_locale = str_replace ('_', '-', $locale_parts[0]);
			$locales[] = $full_locale;

			// Add front part of locale ('en') to checklist
			$locales[] = $language_parts[0];

			// Add end part of locale ('us') to checklist
			if (!empty ($language_parts[1])) {
				$locales[] = $language_parts[1];
			}
		} else {
			// If not, add configured locale to checklist
			$locales[] = $language;
		}

		foreach ($locales as $locale) {
			// Locale file path
			$locale_file = $locales_directory . '/' . $locale . '.php';

			// Check if a locale file exists for current locale
			if (file_exists ($locale_file)) {
				// If so, return PHP locale file path
				return $locale_file;
			}
		}

		// Otherwise, default to English
		return $locales_directory . '/en.php';
	}

	protected function includeLocaleFile ($file)
	{
		// Check if the locale file can be included
		if (@include ($file)) {
			// If so, set locale to array stored in the file
			$this->text = $locale;
		} else {
			// If not, throw exception
			$language = strtoupper ($this->setup->language);
			$exception = $language . ' locale file could not be included!';

			throw new \Exception ($exception);
		}
	}

	// Injects optionality into a given locale string
	public function optionality ($locale, $choice = 'optional')
	{
		// Optionality locale key (default to optional)
		$key = ($choice === 'required') ? 'required' : 'optional';

		// Optionality locale string
		$optionality = mb_strtolower ($this->text[$key]);

		// Inject optionality into locale string
		$new_locale = sprintf ($locale, $optionality);

		return $new_locale;
	}

	// Adds optionality to any given locale string
	public function optionalize ($key, $choice = 'optional')
	{
		return $this->optionality ($this->text[$key] . ' (%s)', $choice);
	}

	// Prepares locale by modifying them in various ways
	public function prepareLocale ()
	{
		// Add optionality to form field title locales
		foreach ($this->setup->fieldOptions as $field => $option) {
			// Title locale key
			$tooltip_key = $field . '-tip';

			// Title locale string
			$tooltip_locale = $this->text[$tooltip_key];

			// Inject optionality into title locale
			$optionality = $this->optionality ($tooltip_locale, $option);

			// Update the locale
			$this->text[$tooltip_key] = $optionality;
		}

		// Run through each locale string
		foreach ($this->text as $key => $value) {
			switch ($key) {
				// Inject date and time formats into date and time locale
				case 'date-time': {
					$this->text[$key] = sprintf (
						$value,
						$this->setup->dateFormat,
						$this->setup->timeFormat
					);

					break;
				}
			}
		}
	}

	// Return file permissions locale with directory and PHP user
	public function permissionsInfo ($file)
	{
		// PHP user, or www-data
		$php_user = isset ($_SERVER['USER']) ? $_SERVER['USER'] : '';
		$php_user = !empty ($php_user) ? $php_user : 'www-data';

		return sprintf (
			$this->text['permissions-info'],
			$this->setup->getHttpPath ($file),
			$php_user
		);
	}
}
