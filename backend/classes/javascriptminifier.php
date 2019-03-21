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


class JavaScriptMinifier
{
	// Array for locking minification
	protected $lock = array (
		'status' => false,
		'char' => ''
	);

	// JavaScript minification function
	public function minify ($js, $level = 4)
	{
		if ($level <= 0) {
			return $js;
		}

		if ($level >= 1) {
			// Remove single-line code comments
			$js = preg_replace ('/^[\t ]*?\/\/.*\s?/m', '', $js);

			// Remove end-of-line code comments
			$js = preg_replace ('/([\s;})]+)\/\/.*/m', '\\1', $js);

			// Remove multi-line code comments
			$js = preg_replace ('/\/\*[\s\S]*?\*\//', '', $js);
		}

		if ($level >= 2) {
			// Remove leading whitespace
			$js = preg_replace ('/^\s*/m', '', $js);

			// Replace multiple tabs with a single space
			$js = preg_replace ('/\t+/m', ' ', $js);
		}

		if ($level >= 3) {
			// Remove newlines
			$js = preg_replace ('/[\r\n]+/', '', $js);
		}

		if ($level >= 4) {
			// Split input JavaScript by single and double quotes
			$js_substrings = preg_split ('/([\'"])/', $js, -1, PREG_SPLIT_DELIM_CAPTURE);

			// Empty variable for minified JavaScript
			$js = '';

			foreach ($js_substrings as $substring) {
				// Check if substring is split delimiter
				if ($substring === '\'' or $substring === '"') {
					// If so, check whether minification is unlocked
					if ($this->lock['status'] === false) {
						// If so, lock it and set lock character
						$this->lock['status'] = true;
						$this->lock['char'] = $substring;
					} else {
						// If not, check if substring is lock character
						if ($substring === $this->lock['char']) {
							// If so, unlock minification
							$this->lock['status'] = false;
							$this->lock['char'] = '';
						}
					}

					// Add substring to minified output
					$js .= $substring;

					continue;
				}

				// Minify current substring if minification is unlocked
				if ($this->lock['status'] === false) {
					// Remove unnecessary semicolons
					$substring = str_replace (';}', '}', $substring);

					// Remove spaces round operators
					$substring = preg_replace ('/ *([<>=+\-!\|{(},;&:?]+) */', '\\1', $substring);
				}

				// Add substring to minified output
				$js .= $substring;
			}
		}

		// Create URL to unminified version
		$unminified_url = 'http' . (isset ($_SERVER['HTTPS']) ? 's' : '') . '://';
		$unminified_url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$unminified_url .= '?unminified';

		// Copyright notice
		$copyright = implode (PHP_EOL, array (
			'// Copyright (C) 2010-2019 Jacob Barkdull',
			'// Under the terms of the GNU Affero General Public License.',
			'// This program source code has been minified.',
			'//',
			'// Unminified version:',
			'// ' . $unminified_url . PHP_EOL . PHP_EOL
		));

		// Return final minified JavaScript
		return $copyright . trim ($js);
	}
}
