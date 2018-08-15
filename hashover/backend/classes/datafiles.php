<?php namespace HashOver;

// Copyright (C) 2018 Jacob Barkdull
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
	public $setup;

	public function __construct (Setup $setup)
	{
		$this->setup = $setup;
	}

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
	public function osLineEndings ($string)
	{
		return preg_replace ('/\r\n|\r|\n/', PHP_EOL, $string);
	}

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

		// Save the JSON data to the comment file
		if (@file_put_contents ($file, $json)) {
			return true;
		}

		return false;
	}

	// Gets the appropriate thread directory path
	protected function getMetaRoot ($thread)
	{
		// Using the automatically setup thread if told to
		if ($thread === 'auto') {
			return $this->setup->threadDirectory;
		}

		// Otherwise construct thread directory path
		return $this->setup->pagesDirectory . '/' . $thread;
	}

	// Gets the appropriate metadata directory path
	protected function getMetaDirectory ($thread, $global)
	{
		// Check if we're getting metadata for a specific thread
		if ($global !== true) {
			// If so, use the thread's path
			$directory = $this->getMetaRoot ($thread) . '/metadata';
		} else {
			// If not, use the global metadata path
			$directory = $this->setup->commentsDirectory . '/metadata';
		}

		// Return metadata directory path
		return $directory;
	}

	// Gets the appropriate metadata file path
	protected function getMetaPath ($name, $thread, $global)
	{
		// Metadata directory path
		$directory = $this->getMetaDirectory ($thread, $global);

		// Metadata file path
		$path = $directory . '/' . $name . '.json';

		// Return metadata file path
		return $path;
	}

	// Creates metadata directory if it doesn't exist
	protected function setupMeta ($thread, $global)
	{
		// Metadata directory path
		$metadata = $this->getMetaDirectory ($thread, $global);

		// Check if metadata root directory exists
		if (file_exists ($this->getMetaRoot ($thread))) {
			// If so, attempt to create metadata directory
			if (file_exists ($metadata) or @mkdir ($metadata, 0755, true)) {
				// If successful, set permissions to 0755 again
				@chmod ($metadata, 0755);
				return true;
			}

			// Otherwise throw exception
			throw new \Exception (sprintf (
				'Failed to create metadata directory at: %s',
				$metadata
			));
		}
	}

	// Read and return specific metadata from JSON file
	public function readMeta ($name, $thread = 'auto', $global = false)
	{
		// Metadata JSON file path
		$metadata_path = $this->getMetaPath ($name, $thread, $global);

		// Return metadata if read successfully
		return $this->readJSON ($metadata_path);
	}

	// Save metadata to specific metadata JSON file
	public function saveMeta ($name, array $data, $thread = 'auto', $global = false)
	{
		// Metadata JSON file path
		$metadata_path = $this->getMetaPath ($name, $thread, $global);

		// Create metadata directory if it doesn't exist
		$this->setupMeta ($thread, $global);

		// Check if metadata root directory exists
		if (file_exists ($this->getMetaRoot ($thread))) {
			// If so, attempt to save data to metadata JSON file
			if ($this->saveJSON ($metadata_path, $data) === false) {
				// Throw exception on failure
				throw new \Exception ('Failed to save metadata!');
			}
		}

		return true;
	}
}
