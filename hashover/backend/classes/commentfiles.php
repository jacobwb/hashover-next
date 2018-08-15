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


// Read and count comments
class CommentFiles extends DataFiles
{
	public $setup;
	public $storageMode;

	public function __construct (Setup $setup)
	{
		parent::__construct ($setup);

		$this->setup = $setup;
		$this->storageMode = 'flat-file';
	}

	// Returns a comment file path for a given file and thread
	public function getCommentPath ($file, $extension, $thread = 'auto')
	{
		$default = $this->setup->threadDirectory;
		$path = $this->setup->pagesDirectory;
		$thread = ($thread !== 'auto') ? $path . '/' . $thread : $default;
		$path = $thread . '/' . $file . '.' . $extension;

		return $path;
	}

	// Read directory contents, put filenames in array, count files
	public function loadFiles ($extension, array $files = array (), $auto = true)
	{
		if ($auto === true) {
			$pattern = $this->setup->threadDirectory . '/*.' . $extension;
			$files = glob ($pattern, GLOB_NOSORT);
		}

		if (!empty ($files)) {
			$comments = array ();

			foreach ($files as $file) {
				$key = basename ($file, '.' . $extension);
				$comments[$key] = (string)($key);
			}

			return $comments;
		}

		return false;
	}

	// Check if comment thread directory exists
	public function checkThread ()
	{
		// Attempt to create the directory
		if (!file_exists ($this->setup->threadDirectory)
		    and !@mkdir ($this->setup->threadDirectory, 0755, true)
		    and !@chmod ($this->setup->threadDirectory, 0755))
		{
			throw new \Exception (sprintf (
				'Failed to create comment thread directory at: %s',
				$this->setup->threadDirectory
			));
		}

		// If yes, check if it is or can be made to be writable
		if (!is_writable ($this->setup->threadDirectory)
		    and !@chmod ($this->setup->threadDirectory, 0755))
		{
			throw new \Exception (sprintf (
				'Comment thread directory at %s is not writable.',
				$this->setup->threadDirectory
			));
		}

		return true;
	}

	// Queries a list of comment threads
	public function queryThreads ()
	{
		$pages = $this->setup->pagesDirectory;
		$directories = glob ($pages . '/*', GLOB_ONLYDIR);

		foreach ($directories as &$directory) {
			$directory = basename ($directory);
		}

		return $directories;
	}
}
