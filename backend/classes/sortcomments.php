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


class SortComments
{
	protected $setup;

	public function __construct (Setup $setup)
	{
		$this->setup = $setup;
	}

	// Recursively descend into comment replies
	protected function replyDescender (array &$output, array $comment)
	{
		// Add the current comment to flattened output
		$output[] =& $comment;

		// Check if comment has replies
		if (!empty ($comment['replies'])) {
			// If so, descend into the replies
			for ($i = 0, $il = count ($comment['replies']); $i < $il; $i++) {
				$this->replyDescender ($output, $comment['replies'][$i]);
			}

			// And remove replies from flattened output
			unset ($comment['replies']);
		}
	}

	// "Flatten" an array of comments
	protected function getAllComments (array $comments)
	{
		// Initial flattened comments
		$output = array ();

		// Initial descent into comments
		for ($i = 0, $il = count ($comments); $i < $il; $i++) {
			$this->replyDescender ($output, $comments[$i]);
		}

		// Return flattened comments
		return $output;
	}

	// Sorts comments by date
	protected function sortByDate (array $a, array $b)
	{
		// Return timestamp difference if dates are different
		if ($b['timestamp'] !== $a['timestamp']) {
			return $b['timestamp'] - $a['timestamp'];
		}

		// Otherwise, return 1
		return 1;
	}

	// Returns a comment's number of likes minus dislikes
	protected function netLikes (array $comment)
	{
		// Number of likes or zero
		$likes = !empty ($comment['likes']) ? $comment['likes'] : 0;

		// Number of dislikes or zero
		$dislikes = !empty ($comment['dislikes']) ? $comment['dislikes'] : 0;

		// Return the difference
		return $likes - $dislikes;
	}

	// Returns a comment's number of replies
	protected function replyCounter (array $comment)
	{
		return !empty ($comment['replies']) ? count ($comment['replies']) : 0;
	}

	// Returns the sum number of replies in a comment thread
	protected function replySum (array $comment, $callback)
	{
		// Initial sum
		$sum = 0;

		// Check if there are replies to the current comment
		if (!empty ($comment['replies'])) {
			// If so, run through them adding up the number of replies
			for ($i = 0, $il = count ($comment['replies']); $i < $il; $i++) {
				$sum += $this->replySum ($comment['replies'][$i], $callback);
			}
		}

		// Calculate the sum based on the give callback
		$sum += $callback ($comment);

		return $sum;
	}

	// Sorts comments alphabetically by commenters names
	protected function sortByCommenter (array $a, array $b)
	{
		// Commenter name or default name
		$name_a = !empty ($a['name']) ? $a['name'] : $this->setup->defaultName;
		$name_b = !empty ($b['name']) ? $b['name'] : $this->setup->defaultName;

		// Remove @ character if present
		$name_a = ($name_a[0] === '@') ? substr ($name_a, 1) : $name_a;
		$name_b = ($name_b[0] === '@') ? substr ($name_b, 1) : $name_b;

		// Convert names to lowercase
		$name_a = mb_strtolower ($name_a);
		$name_b = mb_strtolower ($name_b);

		// Return 1 or -1 based on lexicographical difference
		if ($name_a !== $name_b) {
			return ($name_a > $name_b) ? 1 : -1;
		}

		// Sort by permalink when the names are the same
		return ($a['permalink'] > $b['permalink']);
	}

	// Sort any given comments
	public function sort (array $comments, $method = false)
	{
		// Sort method or default
		$method = $method ?: $this->setup->defaultSorting;

		// Decide how to sort the comments
		switch ($method) {
			// Sort all comments in reverse order
			case 'descending': {
				// Get all comments
				$sort_array = $this->getAllComments ($comments);

				// And return reversed comments
				return array_reverse ($sort_array);
			}

			// Sort all comments by date
			case 'by-date': {
				// Get all comments
				$sort_array = $this->getAllComments ($comments);

				// Sort comments by date
				usort ($sort_array, 'self::sortByDate');

				// And return comments sorted by date
				return $sort_array;
			}

			// Sort all comments by net number of likes
			case 'by-likes': {
				// Get all comments
				$sort_array = $this->getAllComments ($comments);

				// Sort comments by net likes
				usort ($sort_array, function (array $a, array $b) {
					return $this->netLikes ($b) - $this->netLikes ($a);
				});

				// And return sorted comments
				return $sort_array;
			}

			// Sort all comment by number of replies
			case 'by-replies': {
				// Copy the comments
				$sort_array = $comments;

				// Sort comments by number of replies
				usort ($sort_array, function (array $a, array $b) {
					return $this->replyCounter ($b) - $this->replyCounter ($a);
				});

				// And return sorted comments
				return $sort_array;
			}

			// Sort threads by the sum of replies to its comments
			case 'by-discussion': {
				// Copy the comments
				$sort_array = $comments;

				// Sort comments by the sum of each comment's replies
				usort ($sort_array, function (array $a, array $b) {
					$reply_count_a = $this->replySum ($a, array ($this, 'replyCounter'));
					$reply_count_b = $this->replySum ($b, array ($this, 'replyCounter'));

					return $reply_count_b - $reply_count_a;
				});

				// And return sorted comments
				return $sort_array;
			}

			// Sort threads by the sum of likes to it's comments
			case 'by-popularity': {
				// Copy the comments
				$sort_array = $comments;

				// Sort comments by the sum of each comment's net likes
				usort ($sort_array, function (array $a, array $b) {
					$like_count_a = $this->replySum ($a, array ($this, 'netLikes'));
					$like_count_b = $this->replySum ($b, array ($this, 'netLikes'));

					return $like_count_b - $like_count_a;
				});

				// And return sorted comments
				return $sort_array;
			}

			// Sort all comments by the commenter names
			case 'by-name': {
				// Get all comments
				$sort_array = $this->getAllComments ($comments);

				// Sort comments by the commenter names
				usort ($sort_array, 'self::sortByCommenter');

				// And return sorted comments
				return $sort_array;
			}

			// Sort threads in reverse order
			case 'threaded-descending': {
				return array_reverse ($comments);
			}

			// Sort threads by date
			case 'threaded-by-date': {
				// Copy the comments
				$sort_array = $comments;

				// Sort threads by date
				usort ($sort_array, 'self::sortByDate');

				// And return sorted comments
				return $sort_array;
			}

			// Sort threads by net likes
			case 'threaded-by-likes': {
				// Copy the comments
				$sort_array = $comments;

				// Sort threads by not likes
				usort ($sort_array, function ($a, $b) {
					return $this->netLikes ($b) - $this->netLikes ($a);
				});

				// And return sorted comments
				return $sort_array;
			}

			// Sort threads by commenter names
			case 'threaded-by-name': {
				// Copy the comments
				$sort_array = $comments;

				// Sort threads by commenter names
				usort ($sort_array, 'self::sortByCommenter');

				// And return sorted comments
				return $sort_array;
			}
		}

		// By default simply return the comments as-is
		return $comments;
	}
}
