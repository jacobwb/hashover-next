<?php

	// Copyright (C) 2014 Jacob Barkdull
	//
	//	This program is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	This program is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with this program.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		if (isset($_GET['source'])) {
			header('Content-type: text/plain; charset=UTF-8');
			exit(file_get_contents(basename(__FILE__)));
		}
	}

	class Locales
	{
		public $locale;

		public function __construct($lang = 'en')
		{
			if (@include('./scripts/locales/' . strtolower($lang) . '.php')) {
				// Escape single quotes in langage strings
				foreach($locale as $key => $value) {
					if (is_array($value)) {
						foreach($value as $subkey => $subvalue) {
							$locale[$key][$subkey] = addcslashes($subvalue, "'");
						}
					} else {
						$locale[$key] = addcslashes($value, "'");
					}
				}

				$this->locale = $locale;
			} else {
				exit('<b>HashOver</b>: ' . strtoupper($lang) . ' locale file could not be included!');
			}
		}
	}

?>
