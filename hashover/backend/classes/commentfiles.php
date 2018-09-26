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
	protected $setup;

	public function __construct (Setup $setup)
	{
		// Construct parent class
		parent::__construct ($setup);

		// Store parameters as properties
		$this->setup = $setup;
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

	// Returns a comment file path for a given file and thread
	protected function getCommentPath ($file, $extension, $thread = 'auto')
	{
		// Get thread root
		$thread = $this->getThreadRoot ($thread);

		// Construct full file path
		$path = $thread . '/' . $file . '.' . $extension;

		return $path;
	}

	// Read directory contents, put filenames in array, count files
	protected function loadFiles ($extension)
	{
		// Initial comments to return
		$comments = array ();

		// File pattern to match against
		$pattern = $this->setup->threadDirectory . '/*.' . $extension;

		// Find files matching the pattern, with no sorting
		$files = glob ($pattern, GLOB_NOSORT);

		// Run through comments
		foreach ($files as $file) {
			// Get file name without extension
			$key = basename ($file, '.' . $extension);

			// Add file name to comments
			$comments[$key] = (string)($key);
		}

		return $comments;
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
		$path = $this->getMetaPath ($name, $thread, $global);

		// Return metadata if read successfully
		return $this->readJSON ($path);
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
				throw new \Exception (
					'Failed to save metadata!'
				);
			}
		}

		return true;
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
	}

	// Queries an array of directory names
	protected function queryDirs ($path)
	{
		// Get comment directories
		$dirs = glob ($path . '/*', GLOB_ONLYDIR);

		// Convert directories paths to just their names
		foreach ($dirs as $key => $name) {
			$dirs[$key] = basename ($name);
		}

		return $dirs;
	}

	// Queries an array of comment threads
	public function queryThreads ()
	{
		return $this->queryDirs ($this->setup->pagesDirectory);
	}

	// Prepends a comment to latest comments metadata file
	protected function prependLatestComments ($file, $global = false)
	{
		// Add thread to file if metadata is global
		if ($global === true) {
			$file = $this->setup->threadName . '/' . $file;
		}

		// Initial latest comments metadata array
		$latest = array ($file);

		// Attempt to read existing latest comments metadata
		$metadata = $this->readMeta ('latest-comments', 'auto', $global);

		// Merge existing comments with initial array
		if ($metadata !== false) {
			$latest = array_merge ($latest, $metadata);
		}

		// Maximum number of latest comments to store
		$latest_max = max (10, $this->setup->latestMax);

		// Limit latest comments metadata array to configurable size
		$latest = array_slice ($latest, 0, $latest_max);

		// Attempt to save latest comments metadata
		$this->saveMeta ('latest-comments', $latest, 'auto', $global);
	}

	// Removes a comment from latest comments metadata file
	protected function spliceLatestComments ($file, $global = false)
	{
		// Add thread to file if metadata is global
		if ($global === true) {
			$file = $this->setup->threadName . '/' . $file;
		}

		// Attempt to read existing latest comments metadata
		$latest = $this->readMeta ('latest-comments', 'auto', $global);

		// Check if latest comments metadata read successfully
		if ($latest !== false) {
			// If so, get index of file in array
			$index = array_search ($file, $latest);

			// Remove comment from latest array
			if ($index !== false) {
				array_splice ($latest, $index, 1);
			}

			// Attempt to save latest comments metadata
			$this->saveMeta ('latest-comments', $latest, 'auto', $global);
		}
	}

	// Adds a comment to latest comments metadata file
	public function addLatestComment ($file)
	{
		// Add comment to thread-specific latest comments metadata
		$this->prependLatestComments ($file);

		// Add comment to global latest comments metadata
		$this->prependLatestComments ($file, true);
	}

	// Removes a comment from latest comments metadata file
	public function removeFromLatest ($file)
	{
		// Add comment to thread-specific latest comments metadata
		$this->spliceLatestComments ($file);

		// Add comment to global latest comments metadata
		$this->spliceLatestComments ($file, true);
	}
}
