<?php

// Copyright (C) 2015-2017 Jacob Barkdull
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

class HashOver
{
	public $usage = array ();
	public $statistics;
	public $misc;
	public $setup;
	public $readComments;
	public $locale;
	public $commentParser;
	public $markdown;
	public $cookies;
	public $commentCount;
	public $popularList = array ();
	public $popularCount = 0;
	public $rawComments = array ();
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

		// Instantiate class of miscellaneous functions
		$this->misc = new HashOver\Misc ($mode);
	}

	public function getCommentCount ($locale_key = 'showing-comments')
	{
		// Shorter variables
		$primary_count = $this->readComments->primaryCount;
		$total_count = $this->readComments->totalCount;

		// Subtract deleted comment counts
		if ($this->setup->countIncludesDeleted === false) {
			$primary_count -= $this->readComments->primaryDeletedCount;
			$total_count -= $this->readComments->totalDeletedCount;
		}

		// Decide which locale to use; Exclude "Showing" in API usages
		$locale_key = ($this->usage['context'] === 'api') ? 'count-link' : $locale_key;

		// Decide if comment count is pluralized
		$prime_plural = ($primary_count !== 2) ? 1 : 0;

		// Get appropriate locale
		$showing_comments_locale = $this->locale->get ($locale_key);
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
		// Instantiate class for reading comments
		$this->readComments = new HashOver\ReadComments ($this->setup);

		// Where to stop reading comments
		if ($this->usage['mode'] === 'javascript'
		    and $this->setup->collapsesComments !== false
		    and $this->setup->popularityLimit <= 0
		    and $this->setup->usesAJAX !== false)
		{
			// Use collapse limit when collapsing and AJAX is enabled
			$end = $this->setup->collapseLimit;
		} else {
			// Otherwise read all comments
			$end = null;
		}

		// TODO: Fix structure when using starting point
		$this->rawComments = $this->readComments->read (0, $end);

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

	// Get reply array from comments via key
	protected function &getRepliesLevel (&$level, $level_count, &$key_parts)
	{
		for ($i = 1; $i < $level_count; $i++) {
			if (!isset ($level)) {
				break;
			}

			$level =& $level['replies'][$key_parts[$i] - 1];
		}

		return $level;
	}

	// Adds a comment to the popular list if it has enough likes
	protected function checkPopularity (array $comment, $key, array $key_parts)
	{
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
	public function parsePrimary ($start = 0)
	{
		// Initial comments array
		$this->comments['primary'] = array ();

		// If no comments were found, setup a default message comment
		if ($this->readComments->totalCount <= 1) {
			$this->comments['primary'][] = array (
				'title' => $this->locale->text['be-first-name'],
				'avatar' => $this->setup->httpImages . '/first-comment.' . $this->setup->imageFormat,
				'permalink' => 'c1',
				'notice' => $this->locale->text['be-first-note'],
				'notice-class' => 'hashover-first'
			);

			return;
		}

		// Last existing comment date for sorting deleted comments
		$last_date = 0;

		// Allowed comment count
		$allowed_count = 0;

		// Where to stop reading comments
		if ($this->usage['mode'] === 'javascript'
		    and $this->setup->collapsesComments !== false
		    and $this->setup->usesAJAX !== false)
		{
			// Use collapse limit when collapsing and AJAX is enabled
			$end = $this->setup->collapseLimit;
		} else {
			// Otherwise read all comments
			$end = null;
		}

		// Run all comments through parser
		foreach ($this->rawComments as $key => $comment) {
			$key_parts = explode ('-', $key);
			$indentions = count ($key_parts);
			$status = 'approved';

			// Check comment's popularity
			if ($this->setup->popularityLimit > 0) {
				$this->checkPopularity ($comment, $key, $key_parts);
			}

			// Stop parsing after end point
			if ($end !== null and $allowed_count >= $end) {
				continue;
			}

			if ($indentions > 1 and $this->setup->streamDepth > 0) {
				$level =& $this->comments['primary'][$key_parts[0] - 1];

				if ($this->setup->replyMode === 'stream'
				    and $indentions > $this->setup->streamDepth)
				{
					$level =& $this->getRepliesLevel ($level, $this->setup->streamDepth, $key_parts);
					$level =& $level['replies'][];
				} else {
					$level =& $this->getRepliesLevel ($level, $indentions, $key_parts);
				}
			} else {
				$level =& $this->comments['primary'][];
			}

			// Set status to what's stored in the comment
			if (!empty ($comment['status'])) {
				$status = $comment['status'];
			}

			switch ($status) {
				// Parse as pending notice, viewable and editable by owner and admin
				case 'pending': {
					$parsed = $this->commentParser->parse ($comment, $key, $key_parts, false);

					if (!isset ($parsed['user-owned'])) {
						$level = $this->commentParser->notice ('pending', $key, $last_date);
						break;
					}

					$last_date = $parsed['sort-date'];
					$level = $parsed;

					break;
				}

				// Parse as deletion notice, viewable and editable by admin
				case 'deleted': {
					if ($this->login->userIsAdmin === true) {
						$level = $this->commentParser->parse ($comment, $key, $key_parts, false);
						$last_date = $level['sort-date'];
					} else {
						$level = $this->commentParser->notice ('deleted', $key, $last_date);
					}

					break;
				}

				// Parse as deletion notice, non-existent comment
				case 'missing': {
					$level = $this->commentParser->notice ('deleted', $key, $last_date);
					break;
				}

				// Parse as an unknown/error notice
				case 'read-error': {
					$level = $this->commentParser->notice ('error', $key, $last_date);
					break;
				}

				// Otherwise parse comment normally
				default: {
					$comment['status'] = 'approved';
					$level = $this->commentParser->parse ($comment, $key, $key_parts);
					$last_date = $level['sort-date'];

					break;
				}
			}

			$allowed_count++;
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
		if ($this->readComments->totalCount <= 1
		    or $this->setup->popularityLimit <= 0)
		{
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

		// Run through each popular comment
		for ($i = 0; $i < $this->popularCount; $i++) {
			$item =& $this->popularList[$i];

			// Parse comment
			$parsed = $this->commentParser->parse ($item['comment'], $item['key'], $item['parts'], true);

			// And add it to popular comments
			$this->comments['popular'][$i] = $parsed;
		}
	}

	// Do final initialization work
	public function finalize ()
	{
		// Expire various temporary cookies
		$this->cookies->clear ();

		// Various comment count numbers
		$commentCounts = array (
			'show-count' => $this->commentCount,
			'primary' => $this->readComments->primaryCount,
			'total' => $this->readComments->totalCount,
			'popular' => $this->popularCount
		);

		// Instantiate UI output class
		$this->ui = new HashOver\CommentsUI (
			$this->setup,
			$commentCounts
		);

		// Instantiate comment theme templater class
		$this->templater = new HashOver\Templater (
			$this->usage['mode'],
			$this->setup
		);
	}

	// Display all comments as HTML
	public function displayComments ()
	{
		// Instantiate PHP mode class
		$phpmode = new HashOver\PHPMode (
			$this->setup,
			$this->ui,
			$this->comments
		);

		// Run popular comments through parser
		if (!empty ($this->comments['popular'])) {
			foreach ($this->comments['popular'] as $comment) {
				$this->ui->popularComments .= $phpmode->parseComment ($comment, null, true) . PHP_EOL;
			}
		}

		// Run primary comments through parser
		if (!empty ($this->comments['primary'])) {
			foreach ($this->comments['primary'] as $comment) {
				$this->ui->comments .= $phpmode->parseComment ($comment, null) . PHP_EOL;
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
