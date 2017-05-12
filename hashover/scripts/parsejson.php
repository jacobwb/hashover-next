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

// Functions for reading and writing JSON files
class ParseJSON extends ReadFiles
{
	public function __construct (Setup $setup)
	{
		parent::__construct ($setup);

		// Throw exception if the JSON extension isn't loaded
		$setup->extensionsLoaded (array ('json'));
	}

	public function query (array $files = array (), $auto = true)
	{
		// Return array of files
		return $this->loadFiles ('json', $files, $auto);
	}

	public function read ($file, $fullpath = false)
	{
		if ($fullpath === false) {
			$file = $this->setup->dir . '/' . $file . '.json';
		}

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

	public function save (array $contents, $file, $editing = false, $fullpath = false)
	{
		if ($fullpath === false) {
			$file = $this->setup->dir . '/' . $file . '.json';
		}

		// Return false on attempts to override an existing file
		if (file_exists ($file) and $editing === false) {
			return false;
		}

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

		// Save the JSON data to the comment file
		if (file_put_contents ($file, $json)) {
			@chmod ($file, 0600);
			return true;
		}

		return false;
	}

	public function delete ($file, $hardUnlink = false)
	{
		// Actually delete the comment file
		if ($hardUnlink === true) {
			return unlink ($this->setup->dir . '/' . $file . '.json');
		}

		// Read comment file
		$json = $this->read ($file);

		// Check for JSON parse error
		if ($json !== false) {
			// Change status to deleted
			$json['status'] = 'deleted';

			// Attempt to save file
			if ($this->save ($json, $file, true)) {
				return true;
			}
		}

		return false;
	}
}
