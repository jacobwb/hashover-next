<?php

// Copyright (C) 2010-2015 Jacob Barkdull
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

// Read and count comments
class ReadFiles
{
	public $storageMode;
	public $setup;
	public $metadata = array ();

	public function __construct (Setup $setup)
	{
		$this->setup = $setup;
		$this->storageMode = 'flat-file';
		$metadata_file = $this->setup->dir . '/.metadata';
		$update_metadata = false;

		// Read exist metadata file if one exists
		if (file_exists ($metadata_file)) {
			$json_metadata = json_decode (file_get_contents ($metadata_file), true);
			$this->setup->metadata = array_merge ($this->setup->metadata, $json_metadata);
		}

		// Check if comment thread directory exists
		if ($setup->usage['context'] !== 'api') {
			if (file_exists (dirname ($metadata_file)) and is_writable (dirname ($metadata_file))) {
				// Check whether the page and metadata URLs differ
				if ($this->setup->metadata['title'] !== $setup->pageTitle) {
					$this->setup->metadata['title'] = $setup->pageTitle;
					$update_metadata = true;
				}

				// Check whether the page and metadata titles differ
				if ($this->setup->metadata['url'] !== $setup->pageURL) {
					$this->setup->metadata['url'] = $setup->pageURL;
					$update_metadata = true;
				}

				// Update metadata if the data has changed
				if ($update_metadata === true and
				    $this->saveMetadata ($this->setup->metadata, $metadata_file) === false)
				{
					throw new Exception ('Failed to create metadata file.');
				}
			}
		}
	}

	// Save comment metadata
	public function saveMetadata (array $data, $file)
	{
		if (defined ('JSON_PRETTY_PRINT')) {
			$json = str_replace ('    ', "\t", json_encode ($data, JSON_PRETTY_PRINT));
		} else {
			$json = json_encode ($data);
		}

		return file_put_contents ($file, $json);
	}

	// Read directory contents, put filenames in array, count files
	public function loadFiles ($extension, array $files = array (), $auto = true)
	{
		if ($auto === true) {
			$files = glob ($this->setup->dir . '/*.' . $extension, GLOB_NOSORT);
		}

		if (!empty ($files)) {
			$comments = array ();

			foreach ($files as $file) {
				$key = basename ($file, '.' . $extension);
				$comments[$key] =(string) $key;
			}

			return $comments;
		}

		return false;
	}

	// Check if comment thread directory exists
	public function checkThread ()
	{
		if (!file_exists ($this->setup->dir)) {
			// If no, attempt to create the directory
			if (!@mkdir ($this->setup->dir, 0755, true) and !@chmod ($this->setup->dir, 0755)) {
				throw new Exception ('Failed to create comment thread directory at "' . $this->setup->dir . '"');
				return false;
			}
		}

		// If yes, check if it is or can be made to be writable
		if (!is_writable ($this->setup->dir) and !@chmod ($this->setup->dir, 0755)) {
			throw new Exception ('Comment thread directory at "' . $this->setup->dir . '" is not writable.');
			return false;
		}

		return true;
	}
}
