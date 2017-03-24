<?php namespace HashOver;

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

class Misc
{
	public $mode;

	// XSS-unsafe characters to search for
	protected $searchXSS = array (
		'&',
		'<',
		'>',
		'"',
		"'",
		'/',
		'\\'
	);

	// XSS-safe replacement character entities
	protected $replaceXSS = array (
		'&amp;',
		'&lt;',
		'&gt;',
		'&quot;',
		'&#x27;',
		'&#x2F;',
		'&#92;'
	);

	public function __construct ($mode)
	{
		$this->mode = $mode;
	}

	// Make a string XSS-safe
	public function makeXSSsafe ($string)
	{
		// Return cookie value without harmful characters
		return str_replace ($this->searchXSS, $this->replaceXSS, $string);
	}

	// Returns error in HTML paragraph
	public function displayError ($error = 'Something went wrong!')
	{
		$xss_safe = $this->makeXSSsafe ($error);
		$data = array ();

		switch ($this->mode) {
			// Minimal JavaScript to display error message on page
			case 'javascript': {
				$data[] = 'var hashover = document.getElementById (\'hashover\') || document.body;';
				$data[] = 'var error = \'<p><b>HashOver</b>: ' . $xss_safe . '</p>\';' . PHP_EOL;
				$data[] = 'hashover.innerHTML += error;';

				break;
			}

			// RSS XML to indicate error
			case 'rss': {
				$data[] = '<?xml version="1.0" encoding="UTF-8"?>';
				$data[] = '<error>HashOver: ' . $xss_safe . '</error>';

				break;
			}

			// JSON to indicate error
			case 'json': {
				$data[] = json_encode (array (
					'message' => $error,
					'type' => 'error'
				));

				break;
			}

			// Default just return the error message
			default: {
				$data[] = 'HashOver: ' . $error;
				break;
			}
		}

		echo implode (PHP_EOL, $data);
	}
}
