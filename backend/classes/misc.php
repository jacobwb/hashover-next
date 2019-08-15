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


class Misc
{
	// Allowed JavaScript constructors
	protected static $objects = array (
		'HashOver',
		'HashOverCountLink',
		'HashOverLatest'
	);

	// XSS-unsafe characters to search for
	protected static $searchXSS = array (
		'&',
		'<',
		'>',
		'"',
		"'",
		'/',
		'\\'
	);

	// XSS-safe replacement character entities
	protected static $replaceXSS = array (
		'&amp;',
		'&lt;',
		'&gt;',
		'&quot;',
		'&#x27;',
		'&#x2F;',
		'&#92;'
	);

	// Return JSON or JSONP function call
	public static function jsonData ($data, $self_error = false)
	{
		// Encode JSON data
		$json = json_encode ($data);

		// Return JSON as-is if the request isn't for JSONP
		if (!isset ($_GET['jsonp']) or !isset ($_GET['jsonp_object'])) {
			return $json;
		}

		// Otherwise, make JSONP callback index XSS safe
		$index = self::makeXSSsafe ($_GET['jsonp']);

		// Make JSONP object constructor name XSS safe
		$object = self::makeXSSsafe ($_GET['jsonp_object']);

		// Check if constructor is allowed, if not use default
		$allowed_object = in_array ($object, self::$objects, true);
		$object = $allowed_object ? $object : 'HashOver';

		// Check if the JSONP index contains a numeric value
		if (is_numeric ($index) or $self_error === true) {
			// If so, cast index to positive integer
			$index = ($self_error === true) ? 0 : (int)(abs ($index));

			// Construct JSONP function call
			$jsonp = sprintf ('%s.jsonp[%d] (%s);', $object, $index, $json);

			// And return the JSONP script
			return $jsonp;
		}

		// Otherwise, return an error
		return self::jsonData (array (
			'message' => 'JSONP index must have a numeric value.',
			'type' => 'error'
		), true);
	}

	// Makes a string XSS-safe by removing harmful characters
	public static function makeXSSsafe ($string)
	{
		return str_replace (self::$searchXSS, self::$replaceXSS, $string);
	}

	// Returns error in HTML paragraph
	public static function displayError ($error = 'Something went wrong!', $mode = 'php')
	{
		// Initial error message data
		$data = array ();

		// Make error message XSS safe
		$xss_safe = self::makeXSSsafe ($error);

		// Treat JSONP as JavaScript
		if ($mode === 'json' and isset ($_GET['jsonp'])) {
			$mode = 'javascript';
		}

		// Decide how to display error
		switch ($mode) {
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
				$data[] = self::jsonData (array (
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

		// Convert error message data to string
		$message = implode (PHP_EOL, $data);

		return $message;
	}

	// Returns error in HTML paragraph
	public static function displayException (\Exception $error, $mode = 'php')
	{
		return self::displayError ($error->getMessage (), $mode);
	}

	// Returns an array item or a given default value
	public static function getArrayItem (array $data, $key)
	{
		return !empty ($data[$key]) ? $data[$key] : false;
	}
}
