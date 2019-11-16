<?php

// Copyright (C) 2015-2019 Jacob Barkdull
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
	protected $mode;
	protected $setupChecks;
	protected $sortComments;
	protected $popularList = array ();
	protected $popularCount = 0;
	protected $rawComments = array ();
	protected $commentCount;
	protected $collapseCount = 0;

	public $statistics;
	public $setup;
	public $login;
	public $cookies;
	public $thread;
	public $templater;
	public $locale;
	public $commentParser;
	public $markdown;
	public $comments = array ();
	public $ui;

	public function __construct ($mode = 'php')
	{
		// Store output mode (javascript or php)
		$this->mode = $mode;

		// Instantiate statistics class
		$this->statistics = new HashOver\Statistics ();

		// Start execution time
		$this->statistics->executionStart ();

		// Instantiate general setup class
		$this->setup = new HashOver\Setup ();

		//Instantiate setup checks class
		$this->setupChecks = new HashOver\SetupChecks ($this->setup);

		// Instantiate login class
		$this->login = new HashOver\Login ($this->setup);

		// Instantiate cookies class
		$this->cookies = new HashOver\Cookies ($this->setup, $this->login);

		// Instantiate class for reading comments
		$this->thread = new HashOver\Thread ($this->setup);

		// Instantiate comment theme templater class
		$this->templater = new HashOver\Templater ($this->setup);
	}

	// Returns a localized comment count
	public function getCommentCount ($plural = 'showing-comments', $singular = 'showing-comment')
	{
		// Shorter variables
		$primary_count = $this->thread->primaryCount;
		$total_count = $this->thread->totalCount;

		// Subtract deleted comment counts
		if ($this->setup->countsDeletions === false) {
			$primary_count -= $this->thread->primaryDeletedCount;
			$total_count -= $this->thread->totalDeletedCount;
		}

		// Check if there is more than one primary comment
		if ($primary_count !== 2) {
			// If so, use pluralized locale string
			$showing_comments = $this->locale->text[$plural];
		} else {
			// If not, use singular locale string
			$showing_comments = $this->locale->text[$singular];
		}

		// Whether to show reply count separately
		if ($this->setup->showsReplyCount === true) {
			// If so, inject top level comment count into count locale string
			$comment_count = sprintf ($showing_comments, $primary_count - 1);

			// Check if there are any replies
			if ($total_count !== $primary_count) {
				// If so, check if there is more than one primary comment
				if ($total_count - $primary_count !== 1) {
					// If so, use use "X counting replies" locale string
					$reply_locale = $this->locale->text['counting-replies'];
				} else {
					// If not, use use "X counting reply" locale string
					$reply_locale = $this->locale->text['counting-reply'];
				}

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
		// Instantiate locales class
		$this->locale = new HashOver\Locale ($this->setup);

		// Instantiate comment parser class
		$this->commentParser = new HashOver\CommentParser ($this->setup);

		// Instantiate comment sorting class
		$this->sortComments = new HashOver\SortComments ($this->setup);

		// Instantiate markdown class
		$this->markdown = new HashOver\Markdown ();

		// Query a list of comments
		$this->thread->queryComments ();

		// Read all comments
		$this->rawComments = $this->thread->read ();

		// Generate comment count
		$this->commentCount = $this->getCommentCount ();
	}

	// Save various metadata about the page
	public function defaultMetadata ()
	{
		// Check if local metadata is disabled
		if ($this->setup->localMetadata !== true) {
			// If so, get remote address
			$address = HashOver\Misc::getArrayItem ($_SERVER, 'REMOTE_ADDR');

			// Do nothing on if we're on localhost
			if ($this->setup->isLocalhost ($address) === true) {
				return;
			}
		}

		// Otherwise, attempt to save default page metadata
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
						$last_date = $parsed['timestamp'];

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
						$last_date = $level['timestamp'];
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
					$last_date = $level['timestamp'];

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

	// Sort primary comments
	public function sortPrimary ($method = false)
	{
		// Sort the primary comments
		$sorted = $this->sortComments->sort ($this->comments['primary'], $method);

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
			$this->mode,
			$this->setup,
			$commentCounts
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
			$this->comments,
			$this->rawComments
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

		// Increase instance number
		$this->setup->instanceNumber++;

		// End statistics and add them as code comment
		$html .= $this->statistics->executionEnd ('php');

		// Return final HTML
		return $html;
	}
}
