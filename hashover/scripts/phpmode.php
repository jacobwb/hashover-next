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

class PHPMode
{
	public $setup;
	public $html;
	public $locales;
	public $templater;
	public $comments;

	protected $trimTagRegexes = array (
		'blockquote' => '/(<blockquote>)([\s\S]*?)(<\/blockquote>)/i',
		'ul' => '/(<ul>)([\s\S]*?)(<\/ul>)/i',
		'ol' => '/(<ol>)([\s\S]*?)(<\/ol>)/i'
	);

	protected $linkRegex = '/((ftp|http|https):\/\/[a-z0-9-@:%_\+.~#?&\/=]+) {0,}/i';
	protected $codeTagCount = 0;
	protected $codeTags = array ();
	protected $preTagCount = 0;
	protected $preTags = array ();

	public function __construct (Setup $setup, HTMLOutput $html, Locales $locales, Templater $templater, array $comments)
	{
		$this->setup = $setup;
		$this->html = $html;
		$this->locales = $locales;
		$this->templater = $templater;
		$this->comments = $comments;
	}

	protected function replyCheck ($permalink)
	{
		if (empty ($_GET['hashover-reply'])) {
			return;
		}

		if ($_GET['hashover-reply'] === $permalink) {
			$file = substr ($permalink, 1);
			$file = str_replace ('r', '-', $file);
			$file = str_replace ('-pop', '', $file);

			$form = new HTMLTag ('form');
			$form->createAttribute ('id', 'hashover-reply-' . $permalink);
			$form->createAttribute ('class', 'hashover-reply-form');
			$form->createAttribute ('method', 'post');
			$form->createAttribute ('action', $this->setup->httpScripts . '/postcomments.php');
			$form->innerHTML ($this->html->replyForm ($permalink, $file));

			return $form->asHTML ();
		}
	}

	protected function editCheck ($permalink, $comment, $subscribed = true)
	{
		if (empty ($_GET['hashover-edit'])) {
			return;
		}

		if ($_GET['hashover-edit'] === $permalink) {
			$file = substr ($permalink, 1);
			$file = str_replace ('r', '-', $file);
			$file = str_replace ('-pop', '', $file);

			$body = $comment['body'];
			$body = preg_replace ($this->linkRegex, '\\1', $body);
			$name = !empty ($comment['name']) ? $comment['name'] : '';
			$website = !empty ($comment['website']) ? $comment['website'] : '';

			$form = new HTMLTag ('form', false, false);
			$form->createAttribute ('id', 'hashover-edit-' . $permalink);
			$form->createAttribute ('class', 'hashover-edit-form');
			$form->createAttribute ('method', 'post');
			$form->createAttribute ('action', $this->setup->httpScripts . '/postcomments.php');
			$form->innerHTML ($this->html->editForm ($permalink, $file, $name, $website, $body, $subscribed));

			return $form->asHTML ();
		}
	}

	protected function codeTagReplace ($grp)
	{
		$codePlaceholder = $grp[1] . 'CODE_TAG[' . $this->codeTagCount . ']' . $grp[3];
		$this->codeTags[$this->codeTagCount] = trim ($grp[2], PHP_EOL);
		$this->codeTagCount++;

		return $codePlaceholder;
	}

	protected function codeTagReturn ($grp) {
		return $this->codeTags[($grp[1])];
	}

	protected function preTagReplace ($grp)
	{
		$prePlaceholder = $grp[1] . 'PRE_TAG[' . $this->preTagCount . ']' . $grp[3];
		$this->preTags[$this->preTagCount] = trim ($grp[2], PHP_EOL);
		$this->preTagCount++;

		return $prePlaceholder;
	}

	protected function preTagReturn ($grp) {
		return $this->preTags[($grp[1])];
	}

	// Find parent comment via permalink
	protected function findParent ($permalink)
	{
		$currentLevel =& $this->comments['comments'];

		// Split permalink into keys
		$keys = substr ($permalink, 1);
		$keys = explode ('r', $keys);

		// Remove last key, get key count
		array_pop ($keys);
		$levels = count ($keys) - 1;

		// Return the comment if it's primary
		if ($levels === 0) {
			return $currentLevel[($keys[0] - 1)];
		}

		// Get replies from each comment
		for ($currentKey = 0; $currentKey < $levels; $currentKey++) {
			$currentLevel =& $currentLevel[($keys[$currentKey] - 1)]['replies'];
		}

		return $currentLevel[($keys[$currentKey] - 1)];
	}

	public function parseComment (array $comment, $parent = null, $popular = false)
	{
		$template = array ();
		$nameClass = 'hashover-name-plain';
		$template['permalink'] = $comment['permalink'];
		$is_reply = (strpos ($comment['permalink'], 'r') !== false);
		$this->codeTagCount = 0;
		$this->codeTags = array ();
		$this->preTagCount = 0;
		$this->preTags = array ();

		// Text for avatar image alt attribute
		$permatext = substr ($comment['permalink'], 1);
		$permatext = explode ('r', $permatext);
		$permatext = array_pop ($permatext);

		// Wrapper element for each comment
		$comment_wrapper = $this->html->commentWrapper ($comment['permalink']);

		// Check if this comment is a popular comment
		if ($popular === true) {
			// Remove "-pop" from text for avatar
			$permatext = str_replace ('-pop', '', $permatext);

			// Get parent comment via permalink
			$parent = $this->findParent ($comment['permalink']);
		} else {
			// Check if comment is a reply
			if ($is_reply === true) {
				// Append class to indicate comment is a reply
				$comment_wrapper->appendAttribute ('class', 'hashover-reply');
			}
		}

		// Add avatar image to template
		$template['avatar'] = $this->html->userAvatar ($permatext, $comment['permalink'], $comment['avatar']);

		if (!isset ($comment['notice'])) {
			$name = !empty ($comment['name']) ? $comment['name'] : $this->setup->defaultName;
			$is_twitter = false;

			// Check if user's name is a Twitter handle
			if ($name[0] === '@') {
				$name = substr ($name, 1);
				$nameClass = 'hashover-name-twitter';
				$is_twitter = true;
				$nameLength = mb_strlen ($name);

				// Check if Twitter handle is valid length
				if ($nameLength > 1 and $nameLength <= 30) {
					// Set website to Twitter profile if a specific website wasn't given
					if (empty ($comment['website'])) {
						$comment['website'] = 'http://twitter.com/' . $name;
					}
				}
			}

			// Check whether user gave a website
			if (!empty ($comment['website'])) {
				if ($is_twitter === false) {
					$nameClass = 'hashover-name-website';
				}

				// If so, display name as a hyperlink
				$nameLink = $this->html->nameElement ('a', $name, $comment['permalink'], $comment['website']);
			} else {
				// If not, display name as plain text
				$nameLink = $this->html->nameElement ('span', $name, $comment['permalink']);
			}

			// Add "Top of Thread" hyperlink to template
			if ($is_reply === true) {
				$parent_name = !empty ($parent['name']) ? $parent['name'] : $this->setup->defaultName;
				$template['thread-link'] = $this->html->threadLink ($parent['permalink'], $parent['permalink'], $parent_name);
			}

			if (isset ($comment['user-owned'])) {
				// Define "Reply" link with original poster title
				$replyTitle = $this->locales->locale ('commenter-tip');
				$replyClass = 'hashover-no-email';

				// Add "Reply" hyperlink to template
				if (!empty ($_GET['hashover-edit']) and $_GET['hashover-edit'] === $comment['permalink']) {
					$template['edit-link'] = $this->html->cancelLink ($comment['permalink'], 'edit', 'hashover-edit');
				} else {
					$template['edit-link'] = $this->html->editLink ($comment['permalink']);
				}
			} else {
				// Check if commenter is subscribed
				if (isset ($comment['subscribed'])) {
					// If so, set subscribed title
					$replyTitle = $comment['name'] . ' ' . $this->locales->locale['subscribed-tip'];
					$replyClass = 'hashover-has-email';
				} else{
					// If not, set unsubscribed title
					$replyTitle = $comment['name'] . ' ' . $this->locales->locale['unsubscribed-tip'];
					$replyClass = 'hashover-no-email';
				}
			}

			// Get number of likes, append "Like(s)" locale
			if (isset ($comment['likes'])) {
				$plural = ($comment['likes'] === 1 ? 0 : 1);
				$likeCount = $comment['likes'] . ' ' . $this->locales->locale['like'][$plural];
			} else {
				$likeCount = '';
			}

			// Add like count to HTML template
			$template['like-count'] = $this->html->likeCount ($comment['permalink'], $likeCount);

			// Get number of dislikes, append "Dislike(s)" locale
			if ($this->setup->allowsDislikes === true) {
				if (isset ($comment['dislikes'])) {
					$plural = ($comment['dislikes'] === 1 ? 0 : 1);
					$dislikeCount = $comment['dislikes'] . ' ' . $this->locales->locale['dislike'][$plural];
				} else {
					$dislikeCount = '';
				}

				// Add dislike count to HTML template
				$template['dislike-count'] = $this->html->dislikeCount ($comment['permalink'], $dislikeCount);
			}

			// Add name HTML to template
			$template['name'] = $this->html->nameWrapper ($nameLink, $nameClass);

			// Add date permalink hyperlink to template
			$template['date'] = $this->html->dateLink ($comment['permalink'], $comment['date']);

			// Add "Reply" hyperlink to template
			if (!empty ($_GET['hashover-reply']) and $_GET['hashover-reply'] === $comment['permalink']) {
				$template['reply-link'] = $this->html->cancelLink ($comment['permalink'], 'reply', $replyClass);
			} else {
				$template['reply-link'] = $this->html->replyLink ($comment['permalink'], $replyClass, $replyTitle);
			}

			// Add edit form HTML to template
			if (isset ($comment['user-owned'])) {
				$template['edit-form'] = $this->editCheck ($comment['permalink'], $comment, isset ($comment['subscribed']));
			}

			// Add reply form HTML to template
			$template['reply-form'] = $this->replyCheck ($comment['permalink']);

			// Add reply count to template
			if (!empty ($comment['replies'])) {
				$template['reply-count'] = count ($comment['replies']);

				if ($template['reply-count'] > 0) {
					if ($template['reply-count'] !== 1) {
						$template['reply-count'] .= ' ' . $this->locales->locale ('replies');
					} else {
						$template['reply-count'] .= ' ' . $this->locales->locale ('reply');
					}
				}
			}

			// Add comment data to template
			$template['comment'] = $comment['body'];

			// Remove [img] tags
			$template['comment'] = preg_replace ('/\[(img|\/img)\]/i', '', $template['comment']);

			// Add HTML anchor tag to URLs (hyperlinks)
			$template['comment'] = preg_replace ($this->linkRegex, '<a href="\\1" target="_blank">\\1</a>', $template['comment']);

			// Check for code tags
			if (strpos ($template['comment'], '<code>') !== false) {
				// Replace code tags with placeholder text
				$template['comment'] = preg_replace_callback ('/(<code>)([\s\S]*?)(<\/code>)/i', 'self::codeTagReplace', $template['comment']);
			}

			// Check for pre tags
			if (strpos ($template['comment'], '<pre>') !== false) {
				// Replace pre tags with placeholder text
				$template['comment'] = preg_replace_callback ('/(<pre>)([\s\S]*?)(<\/pre>)/i', 'self::preTagReplace', $template['comment']);
			}

			// Check for various multi-line tags
			foreach ($this->trimTagRegexes as $tag => $trimTagRegex) {
				if (strpos ($template['comment'], '<' . $tag . '>') !== false) {
					// Trim leading and trailing whitespace
					$template['comment'] = preg_replace_callback ($trimTagRegex, function ($grp) {
						return $grp[1] . trim ($grp[2], PHP_EOL) . $grp[3];
					}, $template['comment']);
				}
			}

			// Break comment into paragraphs
			$paragraphs = explode (PHP_EOL . PHP_EOL, $template['comment']);
			$pd_comment = '';

			// Wrap comment in paragraph tag
			// Replace single line breaks with break tags
			for ($i = 0, $il = count ($paragraphs); $i < $il; $i++) {
				$pd_comment .= '<p>' . preg_replace ('/' . PHP_EOL . '/', '<br>', $paragraphs[$i]) . '</p>' . PHP_EOL;
			}

			// Replace code tag placeholders with original code tag HTML
			if ($this->codeTagCount > 0) {
				$pd_comment = preg_replace_callback ('/CODE_TAG\[([0-9]+)\]/', 'self::codeTagReturn', $pd_comment);
			}

			// Replace pre tag placeholders with original pre tag HTML
			if ($this->preTagCount > 0) {
				$pd_comment = preg_replace_callback ('/PRE_TAG\[([0-9]+)\]/', 'self::preTagReturn', $pd_comment);
			}

			// Add paragraph'd comment data to template
			$template['comment'] = $pd_comment;
		} else {
			// Append notice class
			$comment_wrapper->appendAttribute ('class', 'hashover-notice');
			$comment_wrapper->appendAttribute ('class', $comment['notice-class']);

			// Add notice to template
			$template['comment'] = $comment['notice'];

			// Set name to 'Comment Deleted!'
			$template['name'] = $this->html->nameWrapper ($comment['title'], $nameClass);
		}

		// Comment HTML template
		$comment_wrapper->innerHTML ($this->templater->parseTemplate ($template));

		// Recursively parse replies
		if (!empty ($comment['replies'])) {
			foreach ($comment['replies'] as $reply) {
				$comment_wrapper->appendInnerHTML ($this->parseComment ($reply, $comment));
			}
		}

		return $comment_wrapper->asHTML ();
	}
}
