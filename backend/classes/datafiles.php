<?php namespace HashOver;

// Copyright (C) 2018-2019 Jacob Barkdull
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


class DataFiles
{
	protected $setup;

	public function __construct (Setup $setup)
	{
		// Store parameters as properties
		$this->setup = $setup;
	}

	// Reads a given JSON file
	public function readJSON ($file)
	{
		// Read JSON comment file
		$data = @file_get_contents ($file);

		// Parse JSON comment file
		$json = @json_decode ($data, true);

		// Check for JSON parse error
		if ($json !== null) {
			return $json;
		}

		return false;
	}

	// Convert a string to OS-specific line endings
	protected function osLineEndings ($string)
	{
		return preg_replace ('/\r\n|\r|\n/', PHP_EOL, $string);
	}

	// Writes an array of data to a JSON file
	public function saveJSON ($file, array $contents = array ())
	{
		// Check if we have pretty print support
		if (defined ('JSON_PRETTY_PRINT')) {
			// If so, encode comment to JSON with pretty print
			$json = json_encode ($contents, JSON_PRETTY_PRINT);

			// And convert spaces indentation to tabs
			$json = str_replace ('    ', "\t", $json);
		} else {
			// If not, encode comment to JSON normally
			$json = json_encode ($contents);
		}

		// Convert line endings to OS specific style
		$json = $this->osLineEndings ($json);

		// Return true if file writes successfully (0 counts as failure)
		if (@file_put_contents ($file, $json)) {
			return true;
		}

		// Otherwise, return false
		return false;
	}
}
