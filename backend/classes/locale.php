<?php namespace HashOver;

// Copyright (C) 2010-2019 Jacob Barkdull
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
	protected $setup;

	public $text;

	public function __construct (Setup $setup)
	{
		// Store parameters as properties
		$this->setup = $setup;

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
		// Check if we are automatically selecting the locale
		if ($this->setup->language === 'auto') {
			// If so, get system locale
			$ctype = setlocale (LC_CTYPE, 0);

			// Split locale by encoding
			$ctype_parts = explode ('.', $ctype);

			// Get locale code (en_US, de_DE, etc.)
			$locale = $ctype_parts[0];
		} else {
			// If not, use configured language as locale
			$locale = $this->setup->language;
		}

		// Convert current locale code to lowercase
		$locale = mb_strtolower ($locale);

		// Get path to locales directory
		$locales_path = $this->setup->getAbsolutePath ('backend/locales');

		// Convert locale code to dashed format (en-us, de-de, etc.)
		if (strpos ($locale, '_') !== false) {
			$locale = str_replace ('_', '-', $locale);
		}

		// Convert locale code to dashed format if it isn't hyphenated
		if (strpos ($locale, '-') === false) {
			$locale .= '-' . $locale;
		}

		// Locale file path to try
		$locale_file = $locales_path . '/' . $locale . '.php';

		// Try to use locale file for current locale
		if (file_exists ($locale_file)) {
			// If exists, set locale code as language setting
			$this->setup->language = $locale;

			// And return locale file path
			return $locale_file;
		}

		// Otherwise, set language setting to English
		$this->setup->language = 'en-us';

		// And return path to English locale
		return $locales_path . '/en-us.php';
	}

	// Includes a locale file
	protected function includeLocaleFile ($file)
	{
		// Check if the locale file can be included
		if (@include ($file)) {
			// If so, set locale to array stored in the file
			$this->text = $locale;
		} else {
			// If not, throw exception
			throw new \Exception (sprintf (
				'%s locale file could not be included!',
				mb_strtoupper ($this->setup->language)
			));
		}
	}

	// Injects optionality into a given locale string
	protected function optionality ($locale, $choice = 'optional')
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
	protected function prepareLocale ()
	{
		// Add optionality to form field title locales
		foreach ($this->setup->formFields as $field => $option) {
			// Title locale key
			$tooltip_key = $field . '-tip';

			// Title locale string
			$tooltip_locale = $this->text[$tooltip_key];

			// Inject optionality into title locale
			$optionality = $this->optionality ($tooltip_locale, $option);

			// Update the locale
			$this->text[$tooltip_key] = $optionality;
		}
	}

	// Return file permissions locale with directory and PHP user
	public function permissionsInfo ($file)
	{
		// PHP user, or www-data
		$php_user = Misc::getArrayItem ($_SERVER, 'USER') ?: 'www-data';

		return sprintf (
			$this->text['permissions-info'],
			$this->setup->getHttpPath ($file),
			$php_user
		);
	}
}
