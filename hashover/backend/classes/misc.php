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

	// Return JSON or JSONP function call
	public function jsonData ($data, $self_error = false)
	{
		// Encode JSON data
		$json = json_encode ($data);

		// Return JSON as-is if the request isn't for JSONP
		if (!isset ($_GET['jsonp']) or !isset ($_GET['jsonp_object'])) {
			return $json;
		}

		// Otherwise, make JSONP callback index XSS safe
		$index = $this->makeXSSsafe ($_GET['jsonp']);

		// Check if the JSONP index contains a numeric value
		if (is_numeric ($index) or $self_error === true) {
			// If so, cast index to positive integer
			$index = ($self_error === true) ? 0 : (int)(abs ($index));

			// Construct JSONP function call
			$jsonp = sprintf ('HashOverConstructor.jsonp[%d] (%s);', $index, $json);

			// And return the JSONP script
			return $jsonp;
		}

		// Otherwise, return an error
		return $this->jsonData (array (
			'message' => 'JSONP index must have a numeric value.',
			'type' => 'error'
		), true);
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
				$data[] = $this->jsonData (array (
					'message' => $error,
					'type' => 'error'
				));

				break;
			}

			// Default just return the error message
			default: {
				$data[] = '<p><b>HashOver</b>: ' . $error . '</p>';
				break;
			}
		}

		echo implode (PHP_EOL, $data);
	}
}
