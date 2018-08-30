<?php

// Copyright (C) 2015-2018 Jacob Barkdull
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


class HashOver
{
	protected $usage = array ();
	protected $setupChecks;
	protected $commentCount;
	protected $popularList = array ();
	protected $popularCount = 0;
	protected $rawComments = array ();
	protected $collapseCount = 0;

	public $statistics;
	public $setup;
	public $thread;
	public $locale;
	public $commentParser;
	public $markdown;
	public $cookies;
	public $login;
	public $comments = array ();
	public $ui;
	public $templater;

	public function __construct ($mode = 'php', $context = 'normal')
	{
		// Store usage context information
		$this->usage['mode'] = $mode;
		$this->usage['context'] = $context;

		// Instantiate and start statistics
		$this->statistics = new HashOver\Statistics ($mode);
		$this->statistics->executionStart ();

		// Instantiate general setup class
		$this->setup = new HashOver\Setup ($this->usage);

		//Instantiate setup checks class
		$this->setupChecks = new HashOver\SetupChecks ($this->setup);

		// Instantiate class for reading comments
		$this->thread = new HashOver\Thread ($this->setup);
	}

	// Returns a localized comment count
	public function getCommentCount ($locale_key = 'showing-comments')
	{
		// Shorter variables
		$primary_count = $this->thread->primaryCount;
		$total_count = $this->thread->totalCount;

		// Subtract deleted comment counts
		if ($this->setup->countIncludesDeleted === false) {
			$primary_count -= $this->thread->primaryDeletedCount;
			$total_count -= $this->thread->totalDeletedCount;
		}

		// Decide which locale to use; Exclude "Showing" in API usages
		$locale_key = ($this->usage['context'] === 'api') ? 'count-link' : $locale_key;

		// Decide if comment count is pluralized
		$prime_plural = ($primary_count !== 2) ? 1 : 0;

		// Get appropriate locale
		$showing_comments_locale = $this->locale->text[$locale_key];
		$showing_comments = $showing_comments_locale[$prime_plural];

		// Whether to show reply count separately
		if ($this->setup->showsReplyCount === true) {
			// If so, inject top level comment count into count locale string
			$comment_count = sprintf ($showing_comments, $primary_count - 1);

			// Check if there are any replies
			if ($total_count !== $primary_count) {
				// If so, decide if reply count is pluralized
				$count_diff = $total_count - $primary_count;
				$reply_plural = ($count_diff !== 1) ? 1 : 0;

				// Get appropriate locale
				$reply_locale = $this->locale->text['count-replies'][$reply_plural];

				// Inject total comment count into reply count locale string
				$reply_count = sprintf ($reply_locale, $total_count - 1);

				// And append reply count
				$comment_count .= ' (' . $reply_count . ')';
			}

			// And return count with separate reply count
			return $comment_count;
		}

		// Otherwise inject total comment count into count locale string
		return sprintf ($showing_comments, $total_count - 1);
	}

	// Begin initialization work
	public function initiate ()
	{
		// Query a list of comments
		$this->thread->queryComments ();

		// Read all comments
		$this->rawComments = $this->thread->read ();

		// Instantiate locales class
		$this->locale = new HashOver\Locale ($this->setup);

		// Instantiate cookies class
		$this->cookies = new HashOver\Cookies ($this->setup);

		// Instantiate login class
		$this->login = new HashOver\Login ($this->setup);

		// Instantiate comment parser class
		$this->commentParser = new HashOver\CommentParser ($this->setup);

		// Generate comment count
		$this->commentCount = $this->getCommentCount ();

		// Instantiate markdown class
		$this->markdown = new HashOver\Markdown ();
	}

	// Save various metadata about the page
	public function defaultMetadata ()
	{
		// "localhost" equivalent addresses
		$addresses = array ('127.0.0.1', '::1', 'localhost');

		// Check if local metadata is disabled
		if ($this->setup->allowLocalMetadata !== true) {
			// If so, do nothing if we're on localhost
			if (in_array ($_SERVER['REMOTE_ADDR'], $addresses, true)) {
				return;
			}
		}

		// Attempt to save default page metadata
		$this->thread->data->saveMeta ('page-info', array (
			'url' => $this->setup->pageURL,
			'title' => $this->setup->pageTitle
		));
	}

	// Get reply array from comments via key
	protected function &getRepliesLevel (&$level, $level_count, &$key_parts)
	{
		for ($i = 1; $i < $level_count; $i++) {
			if (isset ($level)) {
				$level =& $level['replies'][$key_parts[$i] - 1];
			}
		}

		return $level;
	}

	// Adds a comment to the popular list if it has enough likes
	protected function checkPopularity (array $comment, $key, array $key_parts)
	{
		// Initial popularity
		$popularity = 0;

		// Add number of likes to popularity value
		if (!empty ($comment['likes'])) {
			$popularity += $comment['likes'];
		}

		// Subtract number of dislikes to popularity value
		if ($this->setup->allowsDislikes === true) {
			if (!empty ($comment['dislikes'])) {
				$popularity -= $comment['dislikes'];
			}
		}

		// Add comment to popular comments list if popular enough
		if ($popularity >= $this->setup->popularityThreshold) {
			$this->popularList[] = array (
				'popularity' => $popularity,
				'comment' => $comment,
				'key' => $key,
				'parts' => $key_parts
			);
		}
	}

	// Parse primary comments
	public function parsePrimary ()
	{
		// Initial comments array
		$this->comments['primary'] = array ();

		// If no comments were found, setup a default message comment
		if ($this->thread->totalCount <= 1) {
			$this->comments['primary'][] = array (
				'title' => $this->locale->text['be-first-name'],
				'avatar' => $this->setup->getImagePath ('first-comment'),
				'permalink' => 'c1',
				'notice' => $this->locale->text['be-first-note'],
				'notice-class' => 'hashover-first'
			);

			return;
		}

		// Last existing comment date for sorting deleted comments
		$last_date = 0;

		// Run all comments through parser
		foreach ($this->rawComments as $key => $comment) {
			// Split comment key by dash
			$key_parts = explode ('-', $key);

			// Count number of reply indention levels
			$indentions = count ($key_parts);

			// Check comment's popularity
			if ($this->setup->popularityLimit > 0) {
				$this->checkPopularity ($comment, $key, $key_parts);
			}

			// Check if the comment has two or more indention levels
			if ($indentions > 1 and $this->setup->streamDepth > 0) {
				// If so, set level to first array item reference
				$level =& $this->comments['primary'][$key_parts[0] - 1];

				// Check if stream mode is enabled and indention goes out of depth
				if ($this->setup->replyMode === 'stream'
				    and $indentions > $this->setup->streamDepth)
				{
					// If so, set level to reply array item reference within depth
					$level =& $this->getRepliesLevel ($level, $this->setup->streamDepth, $key_parts);

					// And set level to reply array new item reference
					$level =& $level['replies'][];
				} else {
					// If not, set level to reply array item reference
					$level =& $this->getRepliesLevel ($level, $indentions, $key_parts);
				}
			} else {
				// If not, set level to new array item reference
				$level =& $this->comments['primary'][];
			}

			// Set status to what's stored in the comment
			$status = HashOver\Misc::getArrayItem ($comment, 'status') ?: 'approved';

			// Switch between different statuses
			switch ($status) {
				// Comment is pending
				case 'pending': {
					// Parse the comment generally
					$parsed = $this->commentParser->parse ($comment, $key, $key_parts, false);

					// Check if the comment is editable
					if (!isset ($parsed['editable'])) {
						// If so, parse comment as pending notice
						$level = $this->commentParser->notice ('pending', $key, $last_date);
					} else {
						// If not, update last sort date
						$last_date = $parsed['sort-date'];

						// And set current level to parsed comment
						$level = $parsed;
					}

					break;
				}

				// Comment is deleted
				case 'deleted': {
					// Check if user is admin
					if ($this->login->userIsAdmin === true) {
						// If so, parse the comment generally
						$level = $this->commentParser->parse ($comment, $key, $key_parts, false);

						// And update the last sort date
						$last_date = $level['sort-date'];
					} else {
						// If not, parse comment as deleted notice
						$level = $this->commentParser->notice ('deleted', $key, $last_date);
					}

					break;
				}

				// Comment is missing; parse as deletion notice
				case 'missing': {
					$level = $this->commentParser->notice ('deleted', $key, $last_date);
					break;
				}

				// Comment read failure; parse as an error notice
				case 'read-error': {
					$level = $this->commentParser->notice ('error', $key, $last_date);
					break;
				}

				// Comment is approved or otherwise
				default: {
					// Set comment status as approved
					$comment['status'] = 'approved';

					// Parse comment generally
					$level = $this->commentParser->parse ($comment, $key, $key_parts);

					// And update last sort date
					$last_date = $level['sort-date'];

					break;
				}
			}
		}

		// Reset array keys
		$this->comments['primary'] = array_values ($this->comments['primary']);
	}

	// Parse popular comments
	public function parsePopular ()
	{
		// Initial popular comments array
		$this->comments['popular'] = array ();

		// If no comments or popularity limit is 0, return void
		if ($this->thread->totalCount <= 1 or $this->setup->popularityLimit <= 0) {
			return;
		}

		// Sort popular comments
		usort ($this->popularList, function ($a, $b) {
			return ($b['popularity'] > $a['popularity']);
		});

		// Calculate how many popular comments will be shown
		$limit = $this->setup->popularityLimit;
		$count = count ($this->popularList);
		$this->popularCount = min ($limit, $count);

		// Parse every popular comment
		for ($i = 0; $i < $this->popularCount; $i++) {
			$this->comments['popular'][$i] = $this->commentParser->parse (
				$this->popularList[$i]['comment'],
				$this->popularList[$i]['key'],
				$this->popularList[$i]['parts'],
				true
			);
		}
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
		// Return microtime difference if dates are different
		if ($b['sort-date'] !== $a['sort-date']) {
			return $b['sort-date'] - $a['sort-date'];
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
	public function sortComments (array $comments, $method = false)
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

	// Sort primary comments
	public function sortPrimary ($method = false)
	{
		// Sort the primary comments
		$sorted = $this->sortComments ($this->comments['primary'], $method);

		// Update comments
		$this->comments['primary'] = $sorted;
	}

	// Collapse a given comment array
	protected function commentCollapser (array &$comments)
	{
		// Trim comments to collapse limit
		$comments = array_slice ($comments, 0, $this->setup->collapseLimit);

		// Run through remaining comments
		for ($i = 0, $il = count ($comments); $i < $il; $i++) {
			// Check if we have reached collapse limit
			if ($this->collapseCount >= $this->setup->collapseLimit) {
				// If so, remove the comment
				unset ($comments[$i]);
			} else {
				// If not, increase the collapse count
				$this->collapseCount++;
			}

			// Collapse replies if comment has replies
			if (!empty ($comments[$i]['replies'])) {
				$this->commentCollapser ($comments[$i]['replies']);
			}
		}
	}

	// Returns limited number of comments
	public function collapseComments ()
	{
		// Numbers of comments added to output
		$this->collapseCount = 0;

		// Collapse the comments
		$this->commentCollapser ($this->comments['primary']);
	}

	// Do final initialization work
	public function finalize ()
	{
		// Expire various temporary cookies
		$this->cookies->clear ();

		// Various comment count numbers
		$commentCounts = array (
			'show-count' => $this->commentCount,
			'primary' => $this->thread->primaryCount,
			'total' => $this->thread->totalCount,
			'popular' => $this->popularCount
		);

		// Instantiate UI output class
		$this->ui = new HashOver\CommentsUI (
			$this->setup,
			$commentCounts
		);

		// Instantiate comment theme templater class
		$this->templater = new HashOver\Templater (
			$this->setup
		);
	}

	// Display all comments as HTML
	public function displayComments ()
	{
		// Set/update default page metadata
		$this->defaultMetadata ();

		// Instantiate PHP mode class
		$phpmode = new HashOver\PHPMode (
			$this->setup,
			$this->ui,
			$this->comments
		);

		// Check if we have popular comments
		if (!empty ($this->comments['popular'])) {
			// If so, run popular comments through parser
			foreach ($this->comments['popular'] as $comment) {
				// Parse comment
				$html = $phpmode->parseComment ($comment, null, true);

				// And add comment to popular comments properly
				$this->ui->popularComments .= $html . PHP_EOL;
			}
		}

		// Check if we have normal comments
		if (!empty ($this->comments['primary'])) {
			// If so, run primary comments through parser
			foreach ($this->comments['primary'] as $comment) {
				// Parse comment
				$html = $phpmode->parseComment ($comment, null);

				// And add comment to comments properly
				$this->ui->comments .= $html . PHP_EOL;
			}
		}

		// Start UI output with initial HTML
		$html  = $this->ui->initialHTML ();

		// End statistics and add them as code comment
		$html .= $this->statistics->executionEnd ();

		// Return final HTML
		return $html;
	}
}
