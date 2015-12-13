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

class ReadComments
{
	public $setup;
	public $data;
	public $commentList = array ();
	public $threadCount = array ();
	public $totalCount = 1;
	public $primaryCount = 1;

	public function __construct (Setup $setup)
	{
		$this->setup = $setup;

		// Instantiate necessary class data format class
		$data_class = 'Parse' . strtoupper ($setup->dataFormat);
		$this->data = new $data_class ($setup);

		// Query a list of comments
		$this->commentList = $this->data->query ();

		// Organize comments if comments could be queried
		if ($this->commentList !== false) {
			$this->organizeComments ();
		}
	}

	// Check for missing comments
	protected function findMissingComments ($key_parts)
	{
		// Initial comment tree
		$comment_tree = '';

		// Run through each key
		foreach ($key_parts as $key => $value) {
			$reply =(int) $value;

			// Check for comments counting backward from the current
			for ($i = 1; $i <= $reply; $i++) {
				// Current level to check for
				if ($key > 0) {
					$current = $comment_tree . '-' . $i;
				} else {
					$current = $i;
				}

				// Check for the comment in the list
				if (!isset ($this->commentList[$current])) {
					// If it doesn't exist, mark comment as deleted
					$this->commentList[$current] = 'deleted';

					// Count the missing comment
					$this->countComments ($current);
				}
			}

			// Add current reply number to tree
			if ($key > 0) {
				$comment_tree .= '-' . $reply;
			} else {
				$comment_tree = $reply;
			}
		}
	}

	// Organize comments
	public function organizeComments ()
	{
		foreach ($this->commentList as $key) {
			// Check for missing comments
			$this->findMissingComments (explode ('-', $key));

			// Count comment
			$this->countComments ($key);
		}

		// Sort comments by their keys alphabetically in ascending order
		uksort ($this->commentList, 'strnatcasecmp');
	}

	// Read comments
	public function read ($start = 0, $end = null)
	{
		$comments = array ();
		$limit_count = 0;
		$allowed_count = 0;

		foreach ($this->commentList as $key => $comment) {
			// Skip until starting point is reached
			if ($limit_count < $start) {
				$limit_count++;
				continue;
			}

			// Stop at end point
			if ($end !== null and $allowed_count >= $end) {
				break;
			}

			// Skip deleted comments
			if ($comment === 'deleted') {
				$comments[$key]['status'] = 'deleted';
				continue;
			}

			// Read comment
			$read_comment = $this->data->read ($comment);

			// See if it read successfully
			if ($read_comment !== false) {
				// If so, add the comment to output
				$comments[$key] = $read_comment;
			} else {
				// If not, consider it deleted
				$comments[$key]['status'] = 'deleted';
				continue;
			}

			$allowed_count++;
		}

		return $comments;
	}

	// Count the comments
	public function countComments ($comment)
	{
		// Count replies
		if (strpos ($comment, '-') !== false) {
			$file_parts = explode ('-', $comment);
			$thread = basename ($comment, '-' . end ($file_parts));

			if (isset ($this->threadCount[$thread])) {
				$this->threadCount[$thread]++;
			} else {
				$this->threadCount[$thread] = 1;
			}
		} else {
			// Count top level comments
			$this->primaryCount++;
		}

		// Count replies
		if (isset ($this->threadCount[$comment])) {
			$this->threadCount[$comment]++;
		} else {
			$this->threadCount[$comment] = 1;
		}

		// Count all comments
		$this->totalCount++;
	}
}
