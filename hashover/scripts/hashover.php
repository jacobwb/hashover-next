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
	public $comments = array ();
	public $html;
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
		// Decide if comment count is pluralized
		$prime_plural = ($this->readComments->primaryCount !== 2) ? 1 : 0;

		// Format comment count; Exclude "Showing" in API usages
		$locale_key = ($this->usage['context'] === 'api') ? 'count-link' : $locale_key;
		$showing_comments = $this->locale->text[$locale_key][$prime_plural];

		// Whether to show reply count separately
		if ($this->setup->showsReplyCount === true) {
			// If so, inject top level comment count into count locale string
			$comment_count = sprintf ($showing_comments, $this->readComments->primaryCount - 1);

			// Check if there are any replies
			if ($this->readComments->totalCount !== $this->readComments->primaryCount) {
				// If so, decide if reply count is pluralized
				$reply_plural = (($this->readComments->totalCount - $this->readComments->primaryCount) !== 1) ? 1 : 0;

				// Get appropriate locale
				$reply_locale = $this->locale->text['count-replies'][$reply_plural];

				// Inject total comment count into reply count locale string
				$reply_count = sprintf ($reply_locale, $this->readComments->totalCount - 1);

				// If so, append reply count
				$comment_count .= ' (' . $reply_count . ')';
			}

			// And return count with separate reply count
			return $comment_count;
		}

		// Otherwise inject total comment count into count locale string
		return sprintf ($showing_comments, $this->readComments->totalCount - 1);
	}

	// Begin initialization work
	public function initiate ()
	{
		// Instantiate class for reading comments
		$this->readComments = new HashOver\ReadComments ($this->setup);

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

	// Parse primary comments
	public function parsePrimary ($collapse = false, $start = 0)
	{
		// Initial comments array
		$this->comments['comments'] = array ();

		// If no comments were found, setup a default message comment
		if ($this->readComments->totalCount <= 1) {
			$this->comments['comments'][] = array (
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

		// Where to stop reading comments
		if ($collapse != false and $this->setup->usesAJAX !== false) {
			// Use collapse limit when collapsing and AJAX is enabled
			$end = $this->setup->collapseLimit;
		} else {
			// Otherwise read all comments
			$end = null;
		}

		// Run all comments through parser
		foreach ($this->readComments->read ($start, $end) as $key => $comment) {
			$key_parts = explode ('-', $key);
			$indentions = count ($key_parts);
			$status = 'approved';

			if ($indentions > 1 and $this->setup->streamDepth > 0) {
				$level =& $this->comments['comments'][$key_parts[0] - 1];

				if ($this->setup->replyMode === 'stream'
				    and $indentions > $this->setup->streamDepth)
				{
					$level =& $this->getRepliesLevel ($level, $this->setup->streamDepth, $key_parts);
					$level =& $level['replies'][];
				} else {
					$level =& $this->getRepliesLevel ($level, $indentions, $key_parts);
				}
			} else {
				$level =& $this->comments['comments'][];
			}

			// Set status to what's stored in the comment
			if (!empty ($comment['status'])) {
				$status = $comment['status'];
			}

			switch ($status) {
				// Parse as pending notice, viewable and editable by owner and admin
				case 'pending': {
					$parsed = $this->commentParser->parse ($comment, $key, $key_parts, false, false);

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
						$level = $this->commentParser->parse ($comment, $key, $key_parts, false, false);
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
		}

		// Reset array keys
		$this->comments['comments'] = array_values ($this->comments['comments']);
	}

	// Parse popular comments
	public function parsePopular ()
	{
		// Initial popular comments array
		$this->comments['popularComments'] = array ();

		// If no comments or popularity limit is 0, return void
		if ($this->readComments->totalCount <= 1
		    or $this->setup->popularityLimit <= 0)
		{
			return;
		}

		// Sort popular comments
		usort ($this->commentParser->popularList, function ($a, $b) {
			return ($b['popularity'] > $a['popularity']);
		});

		// Calculate how many popular comments will be shown
		$limit = $this->setup->popularityLimit;
		$count = min ($limit, count ($this->commentParser->popularList));

		// Read, parse, and add popular comments to output
		for ($i = 0; $i < $count; $i++) {
			$item =& $this->commentParser->popularList[$i];
			$comment = $this->readComments->data->read ($item['key']);
			$parsed = $this->commentParser->parse ($comment, $item['key'], $item['parts'], true);
			$this->comments['popularComments'][$i] = $parsed;
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
		);

		// Instantiate HTML output class
		$this->html = new HashOver\HTMLOutput (
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
			$this->html,
			$this->comments
		);

		// Run popular comments through parser
		if (!empty ($this->comments['popularComments'])) {
			foreach ($this->comments['popularComments'] as $comment) {
				$this->html->popularComments .= $phpmode->parseComment ($comment, null, true) . PHP_EOL;
			}
		}

		// Run primary comments through parser
		if (!empty ($this->comments['comments'])) {
			foreach ($this->comments['comments'] as $comment) {
				$this->html->comments .= $phpmode->parseComment ($comment, null) . PHP_EOL;
			}
		}

		// Start HTML output with initial HTML
		$html  = $this->html->initialHTML ($this->commentParser->popularList);

		// End statistics and add them as code comment
		$html .= $this->statistics->executionEnd ();

		// Return final HTML
		return $html;
	}
}
