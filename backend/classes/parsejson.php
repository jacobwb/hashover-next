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


// Functions for reading and writing JSON files
class ParseJSON extends CommentFiles
{
	public function __construct (Setup $setup, Thread $thread)
	{
		// Construct parent class
		parent::__construct ($setup, $thread);

		// Throw exception if the JSON extension isn't loaded
		$setup->extensionsLoaded (array ('json'));
	}

	// Returns an array of comment files
	public function query ()
	{
		return $this->loadFiles ('json');
	}

	// Reads a comment file
	public function read ($file, $thread = 'auto')
	{
		// Get comment file path
		$file = $this->getCommentPath ($file, 'json', $thread);

		// Read and parse JSON comment file
		$json = $this->readJSON ($file);

		return $json;
	}

	// Saves a comment file
	public function save ($file, array $contents, $editing = false, $thread = 'auto')
	{
		// Get comment file path
		$file = $this->getCommentPath ($file, 'json', $thread);

		// Return false on attempts to override an existing file
		if (file_exists ($file) and $editing === false) {
			return false;
		}

		// Attempt to write file
		$saved = $this->saveJSON ($file, $contents);

		// Change file permission if file saved successfully
		if ($saved !== false) {
			@chmod ($file, 0600);
		}

		// Return status of file write
		return $saved;
	}

	// Deletes a comment file
	public function delete ($file, $hard_unlink = false)
	{
		// Actually delete the comment file
		if ($hard_unlink === true) {
			return unlink ($this->getCommentPath ($file, 'json'));
		}

		// Read comment file
		$json = $this->read ($file);

		// Check for JSON parse error
		if ($json !== false) {
			// Change status to deleted
			$json['status'] = 'deleted';

			// Attempt to save file
			if ($this->save ($file, $json, true)) {
				return true;
			}
		}

		return false;
	}
}
