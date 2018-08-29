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


class Thread
{
	protected $setup;

	public $data;
	public $commentList = array ();
	public $threadCount = array ();
	public $primaryCount = 1;
	public $totalCount = 1;
	public $primaryDeletedCount = 0;
	public $collapsedDeletedCount = 0;
	public $totalDeletedCount = 0;

	public function __construct (Setup $setup)
	{
		// Store parameters as properties
		$this->setup = $setup;

		// Instantiate necessary class data format class
		$data_class = 'HashOver\\Parse' . strtoupper ($setup->dataFormat);
		$this->data = new $data_class ($setup);
	}

	// Queries a list of comments
	public function queryComments ()
	{
		// Query a list of comments
		$comment_list = $this->data->query ();

		// Organize comments if comments could be queried
		if ($comment_list !== false) {
			$this->commentList = $comment_list;
			$this->organizeComments ();
		}
	}

	// Counts a comment
	public function countComment ($comment)
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

		// Count all other comments
		$this->totalCount++;
	}

	// Explode a string, cast substrings to integers
	protected function intExplode ($delimiter, $string)
	{
		$parts = explode ($delimiter, $string);
		$ints = array ();

		for ($i = 0, $il = count ($parts); $i < $il; $i++) {
			$ints[] = (int)($parts[$i]);
		}

		return $ints;
	}

	// Counts a deleted comment
	protected function countDeleted ($comment)
	{
		// Count deleted replies
		if (strpos ($comment, '-') === false) {
			$this->primaryDeletedCount++;
		}

		// Get count from comment key
		$comment_parts = $this->intExplode ('-', $comment);
		$comment_count = array_sum ($comment_parts);

		// Count collapsed deleted comments
		if ($comment_count > $this->setup->collapseLimit) {
			$this->collapsedDeletedCount++;
		}

		// Count all other deleted comments
		$this->totalDeletedCount++;
	}

	// Check for missing comments
	protected function findMissingComments ($key)
	{
		// Get integers from key
		$key_parts = $this->intExplode ('-', $key);

		// Initial comment tree
		$comment_tree = '';

		// Run through each key
		foreach ($key_parts as $key => $reply) {
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
					// If it doesn't exist, mark comment as missing
					$this->commentList[$current] = 'missing';

					// Count the missing comment
					$this->countComment ($current);
					$this->countDeleted ($current);
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
	protected function organizeComments ()
	{
		foreach ($this->commentList as $key) {
			// Check for missing comments
			$this->findMissingComments ($key);

			// Count comment
			$this->countComment ($key);
		}

		// Sort comments by their keys alphabetically in ascending order
		uksort ($this->commentList, 'strnatcasecmp');
	}

	// Read comments
	public function read ($end = null)
	{
		// Initial data to return
		$comments = array ();

		// Number of comments successfully added to return data
		$added_count = 0;

		// Run through each comment
		foreach ($this->commentList as $i => $key) {
			// Stop at end point
			if ($end !== null and $added_count >= $end) {
				break;
			}

			// Skip deleted comments
			if ($key === 'missing') {
				$comments[$i]['status'] = 'missing';
				continue;
			}

			// Read comment
			$comment = $this->data->read ($key);

			// See if it read successfully
			if ($comment !== false) {
				// If so, add the comment to output
				$comments[$i] = $comment;

				// Count deleted status comments
				if (Misc::getArrayItem ($comment, 'status') === 'deleted') {
					$this->countDeleted ($key);
				}

				// And increase added count
				$added_count++;
			} else {
				// If not, set comment status as a read error
				$comments[$i]['status'] = 'read-error';
			}
		}

		return $comments;
	}

	// Queries a list of comment threads
	public function queryThreads ()
	{
		return $this->data->queryThreads ();
	}
}
