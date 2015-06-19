<?php

	// Copyright (C) 2015 Jacob Barkdull
	//
	//	This file is part of HashOver.
	//
	//	HashOver is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	HashOver is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		}
	}

	class HashOver
	{
		public $mode;
		public $statistics;
		public $settings;
		public $setup;
		public $cookies;
		public $readComments;
		public $locales;
		public $commentParser;
		public $commentCount;
		public $html;
		public $comments = array ();
		public $templater;

		public
		function __construct ($mode = 'javascript', $page_url, $page_title = '')
		{
			$this->mode = $mode;

			// Instantiate and start statistics
			$this->statistics = new Statistics ($mode);
			$this->statistics->executionStart ();

			// Instantiate settings class
			$this->settings = new Settings ();

			// Instantiate general setup class
			$this->setup = new Setup (
				$mode,
				$page_url,
				$page_title
			);

			// Instantiate cookies class
			$this->cookies = new Cookies (
				$this->settings->domain,
				$this->settings->cookieExpiration,
				$this->settings->secureCookies
			);

			// Instantiate locales class
			$this->locales = new Locales ($this->settings->language);

			// Instantiate class for reading comments
			$this->readComments = new ReadComments ($this->setup);

			// Check if a comment was sent via HTTP POST request
			if (isset ($_POST['comment'])) {
				// Instantiate class for writing and editing comments
				$write_comments = new WriteComments (
					$this->readComments,
					$this->locales,
					$this->cookies
				);

				// Execute an action (write/edit/login/etc)
				$write_comments->getAction ();
			}

			// Instantiate comment parser class
			$this->commentParser = new CommentParser (
				$this->setup,
				$this->locales->locale
			);

			// Decide if comment count is pluralized
			$prime_plural = ($this->readComments->primaryCount !== 2) ? 1 : 0;

			// Format comment count; Include "Showing" in non-API usages
			$locale_key = ($mode === 'api') ? 'count_link' : 'showing_cmts';
			$showing_cmts = $this->locales->locale[$locale_key][$prime_plural];

			// Whether to show reply count separately
			if ($this->settings->showsReplyCount) {
				// If so, inject top level comment count into count locale string
				$this->commentCount = str_replace ('_NUM_', $this->readComments->primaryCount - 1, $showing_cmts);

				// Check if there are any replies
				if ($this->readComments->totalCount !== $this->readComments->primaryCount) {
					$reply_plural = (($this->readComments->totalCount - $this->readComments->primaryCount) !== 1) ? 1 : 0;
					$reply_locale = $this->locales->locale['count_replies'][$reply_plural];
					$reply_count = str_replace ('_NUM_', $this->readComments->totalCount - 1, $reply_locale);

					// Append reply count
					$this->commentCount .= ' (' . $reply_count . ')';
				}
			} else {
				// If not, inject total comment count into count locale string
				$this->commentCount = str_replace ('_NUM_', $this->readComments->totalCount - 1, $showing_cmts);
			}
		}

		// Parse primary comments
		protected
		function parsePrimary ()
		{
			// Last existing comment date for sorting deleted comments
			$last_date = 0;

			// Run all comments through parser
			foreach ($this->readComments->read () as $key => $comment) {
				$key_parts = explode ('-', $key);
				$status = !empty ($comment['status']) ? $comment['status'] : 'approved';

				if (count ($key_parts) > 1) {
					if ($this->settings->replyMode !== 'stream') {
						$level =& $this->comments['comments'][$key_parts[0] - 1];

						for ($i = 1, $li = count ($key_parts); $i < $li; $i++) {
							$level =& $level['replies'][$key_parts[$i] - 1];
						}
					} else {
						$level =& $this->comments['comments'][$key_parts[0] - 1]['replies'][];
					}
				} else {
					$level =& $this->comments['comments'][$key - 1];
				}

				switch ($status) {
					case 'deleted': {
						$level = $this->commentParser->notice ('deleted', $key, $last_date);
						break;
					}

					case 'pending': {
						$parsed = $this->commentParser->parse ($comment, $key, $key_parts, false, false);

						if (!isset ($parsed['user_owned'])) {
							$level = $this->commentParser->notice ('pending', $key, $last_date);
							break;
						}

						$parsed['date'] .= ' (' . strtolower ($this->locales->locale['cmt_pending']) . ')';
						$level = $parsed;
						$last_date = $level['sort_date'];

						break;
					}

					default: {
						$level = $this->commentParser->parse ($comment, $key, $key_parts);
						$last_date = $level['sort_date'];

						break;
					}
				}
			}
		}

		// Parse popular comments
		protected
		function parsePopular ()
		{
			// Sort popular comments
			krsort ($this->commentParser->popularList);

			for ($p = 0, $pl = count ($this->commentParser->popularList); $p < $pl; $p++) {
				if ($p > $this->settings->popularityLimit) {
					break;
				}

				$popKey = array_shift ($this->commentParser->popularList);
				$popComment = $this->readComments->data->read ($popKey[0]);
				$this->comments['pop_comments'][$p] = $this->commentParser->parse ($popComment, $popKey[0], $popKey[1], true);
			}
		}

		// Parse all comments
		public
		function parseAll ()
		{
			// Parse all primary comments
			if ($this->readComments->totalCount > 1) {
				$this->parsePrimary ();

				// Parse popular comments
				if ($this->settings->popularityLimit > 0) {
					$this->parsePopular ();
				}
			} else {
				// If no comments were found, setup a default message comment
				$this->comments['comments'][] = array (
					'title' => $this->locales->locale['first_cmt'],
					'avatar' => '/images/' . $this->settings->imageFormat . 's/first-comment.' . $this->settings->imageFormat,
					'permalink' => 'c1',
					'notice' => $this->locales->locale['first_cmt'],
					'notice_class' => 'hashover-first'
				);
			}

			// Expire various temporary cookies
			$this->cookies->clear ();

			// Instantiate HTML output class
			$this->html = new HTMLOutput (
				$this->readComments,
				$this->locales,
				$this->commentCount,
				$this->commentParser->popularList
			);

			// Instantiate comment theme templater class
			$this->templater = new Templater (
				file_get_contents ($this->settings->rootDirectory . '/themes/' . $this->settings->theme . '/layout.html'),
				$this->mode
			);
		}

		// Display all comments as HTML
		public
		function displayComments ()
		{
			// Parse all comments
			$this->parseAll ();

			// Instantiate PHP mode class
			$phpmode = new PHPMode (
				$this->settings,
				$this->html,
				$this->locales,
				$this->templater
			);

			// Run popular comments through parser
			foreach ($this->comments['pop_comments'] as $comment) {
				$this->html->popularComments .= $phpmode->parseComment ($comment, true) . PHP_EOL;
			}

			// Run primary comments through parser
			foreach ($this->comments['comments'] as $comment) {
				$this->html->comments .= $phpmode->parseComment ($comment) . PHP_EOL;
			}

			// Start HTML output with initial HTML
			$html = $this->html->initialHTML ();

			// End statistics and add them as code comment
			$html .= $this->statistics->executionEnd ();

			// Return final HTML
			return $html;
		}
	}

?>
