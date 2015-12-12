<?php

// Copyright (C) 2015 Jacob Barkdull
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
	public $mode;
	public $statistics;
	public $setup;
	public $readComments;
	public $locales;
	public $avatars;
	public $commentParser;
	public $cookies;
	public $commentCount;
	public $comments = array ();
	public $html;
	public $templater;

	public function __construct ($mode = 'php')
	{
		$this->mode = $mode;

		// Instantiate and start statistics
		$this->statistics = new Statistics ($mode);
		$this->statistics->executionStart ();

		try {
			// Instantiate general setup class
			$this->setup = new Setup ($mode);

		} catch (Exception $error) {
			$this->setup->displayError ($error->getMessage ());
		}
	}

	public function getCommentCount ()
	{
		// Decide if comment count is pluralized
		$prime_plural = ($this->readComments->primaryCount !== 2) ? 1 : 0;

		// Format comment count; Include "Showing" in non-API usages
		$locale_key = ($this->mode === 'api') ? 'count-link' : 'showing-comments';
		$showing_comments = $this->locales->locale[$locale_key][$prime_plural];

		// Whether to show reply count separately
		if ($this->setup->showsReplyCount === true) {
			// If so, inject top level comment count into count locale string
			$this->commentCount = sprintf ($showing_comments, $this->readComments->primaryCount - 1);

			// Check if there are any replies
			if ($this->readComments->totalCount !== $this->readComments->primaryCount) {
				$reply_plural = (($this->readComments->totalCount - $this->readComments->primaryCount) !== 1) ? 1 : 0;
				$reply_locale = $this->locales->locale['count-replies'][$reply_plural];
				$reply_count = sprintf ($reply_locale, $this->readComments->totalCount - 1);

				// Append reply count
				$this->commentCount .= ' (' . $reply_count . ')';
			}
		} else {
			// If not, inject total comment count into count locale string
			$this->commentCount = sprintf ($showing_comments, $this->readComments->totalCount - 1);
		}
	}

	// Begin initialization work
	public function initiate ()
	{
		try {
			// Instantiate class for reading comments
			$this->readComments = new ReadComments ($this->setup);

			// Instantiate locales class
			$this->locales = new Locales ($this->setup->language);

		} catch (Exception $error) {
			$this->setup->displayError ($error->getMessage ());
		}

		// Instantiate avatars class
		$this->avatars = new Avatars ($this->setup);

		// Instantiate cookies class
		$this->cookies = new Cookies (
			$this->setup->domain,
			$this->setup->cookieExpiration,
			$this->setup->secureCookies
		);

		// Instantiate login class
		$this->login = new Login ($this->setup, $this->cookies);

		// Instantiate comment parser class
		$this->commentParser = new CommentParser (
			$this->setup,
			$this->login,
			$this->locales,
			$this->avatars
		);

		// Generate comment count
		$this->getCommentCount ();
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
				'title' => $this->locales->locale['first-comment'],
				'avatar' => $this->setup->httpImages . '/first-comment.' . $this->setup->imageFormat,
				'permalink' => 'c1',
				'notice' => $this->locales->locale['first-comment'],
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
		// TODO: Fix structure when using starting point
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
				// Parse as deletion note
				case 'deleted': {
					$level = $this->commentParser->notice ('deleted', $key, $last_date);
					break;
				}

				// Parse as pending note
				case 'pending': {
					$parsed = $this->commentParser->parse ($comment, $key, $key_parts, false, false);

					if (!isset ($parsed['user-owned'])) {
						$level = $this->commentParser->notice ('pending', $key, $last_date);
						break;
					}

					$parsed['date'] .= ' (' . strtolower ($this->locales->locale['comment-pending']) . ')';
					$level = $parsed;
					$last_date = $level['sort-date'];

					break;
				}

				// Parse comment normally
				default: {
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
		ksort ($this->commentParser->popularList);

		for ($p = 0, $pl = count ($this->commentParser->popularList); $p < $pl; $p++) {
			if ($p > $this->setup->popularityLimit) {
				break;
			}

			$popKey = array_shift ($this->commentParser->popularList);
			$popComment = $this->readComments->data->read ($popKey[0]);
			$this->comments['popularComments'][$p] = $this->commentParser->parse ($popComment, $popKey[0], $popKey[1], true);
		}
	}

	// Do final initialization work
	public function finalize ()
	{
		// Expire various temporary cookies
		$this->cookies->clear ();

		// Instantiate HTML output class
		$this->html = new HTMLOutput (
			$this->readComments,
			$this->login,
			$this->locales,
			$this->avatars,
			$this->commentCount,
			$this->commentParser->popularList
		);

		// Instantiate comment theme templater class
		$this->templater = new Templater (
			$this->mode,
			$this->setup
		);
	}

	// Display all comments as HTML
	public function displayComments ()
	{
		// Instantiate PHP mode class
		$phpmode = new PHPMode (
			$this->setup,
			$this->html,
			$this->locales,
			$this->templater,
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
		$html  = $this->html->initialHTML ();

		// End statistics and add them as code comment
		$html .= $this->statistics->executionEnd ();

		// Return final HTML
		return $html;
	}
}
