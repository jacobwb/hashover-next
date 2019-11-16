<?php namespace HashOver;

// Copyright (C) 2010-2019 Jacob Barkdull
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


class PHPMode
{
	protected $setup;
	protected $ui;
	protected $comments;
	protected $rawComments;
	protected $crypto;
	protected $locale;
	protected $templater;
	protected $markdown;

	protected $trimTagRegexes = array (
		'blockquote' => '/(<blockquote>)([\s\S]*?)(<\/blockquote>)/iS',
		'ul' => '/(<ul>)([\s\S]*?)(<\/ul>)/iS',
		'ol' => '/(<ol>)([\s\S]*?)(<\/ol>)/iS'
	);

	protected $linkRegex = '/((http|https|ftp):\/\/[a-z0-9-@:;%_\+.~#?&\/=]+) {0,1}/iS';
	protected $codeTagCount = 0;
	protected $codeTags = array ();
	protected $preTagCount = 0;
	protected $preTags = array ();
	protected $paragraphRegex = '/(?:\r\n|\r|\n){2}/S';
	protected $lineRegex = '/(?:\r\n|\r|\n)/S';

	public function __construct (Setup $setup, CommentsUI $ui, array $comments, array $raw)
	{
		// Store parameters as properties
		$this->setup = $setup;
		$this->ui = $ui;
		$this->comments = $comments;
		$this->rawComments = $raw;

		// Instantiate various classes
		$this->crypto = new Crypto ();
		$this->locale = new Locale ($setup);
		$this->templater = new Templater ($setup);
		$this->markdown = new Markdown ();
	}

	protected function fileFromPermalink ($permalink)
	{
		$file = substr ($permalink, 1);
		$file = str_replace ('r', '-', $file);
		$file = str_replace ('-pop', '', $file);

		return $file;
	}

	protected function replyCheck ($permalink)
	{
		if (empty ($_GET['hashover-reply'])) {
			return;
		}

		if ($_GET['hashover-reply'] === $permalink) {
			$file = $this->fileFromPermalink ($permalink);

			$form = new HTMLTag ('form', array (
				'id' => $this->ui->prefix ('reply-' . $permalink),
				'class' => 'hashover-reply-form',
				'method' => 'post',
				'action' => $this->setup->getBackendPath ('form-actions.php')
			));

			$form->innerHTML ($this->ui->replyForm ($permalink, $this->setup->pageURL, $this->setup->threadName, $this->setup->pageTitle, $file));

			return $form->asHTML ();
		}
	}

	protected function editCheck ($comment)
	{
		if (empty ($_GET['hashover-edit'])) {
			return;
		}

		$permalink = Misc::getArrayItem ($comment, 'permalink') ?: '';

		if ($_GET['hashover-edit'] === $permalink) {
			$file = $this->fileFromPermalink ($permalink);

			$body = $comment['body'];
			$body = preg_replace ($this->linkRegex, '\\1', $body);
			$status = Misc::getArrayItem ($comment, 'status') ?: 'approved';
			$name = Misc::getArrayItem ($comment, 'name') ?: '';
			$website = Misc::getArrayItem ($comment, 'website') ?: '';
			$subscribed = isset ($comment['subscribed']);

			if (!empty ($this->rawComments[$file])) {
				$raw_comment = $this->rawComments[$file];
				$email = Misc::getArrayItem ($raw_comment, 'email') ?: '';
				$encryption = Misc::getArrayItem ($raw_comment, 'encryption') ?: '';
				$email = $this->crypto->decrypt ($email, $encryption);
			} else {
				$email = '';
			}

			$form = new HTMLTag ('form', array (
				'id' => $this->ui->prefix ('edit-' . $permalink),
				'class' => 'hashover-edit-form',
				'method' => 'post',
				'action' => $this->setup->getBackendPath ('form-actions.php')
			), false);

			$edit_form = $this->ui->editForm ($permalink, $this->setup->pageURL, $this->setup->threadName, $this->setup->pageTitle, $file, $name, $email, $website, $body, $status, $subscribed);

			$form->innerHTML ($edit_form);

			return $form->asHTML ();
		}
	}

	protected function codeTagReplace ($grp)
	{
		$code_placeholder = $grp[1] . 'CODE_TAG[' . $this->codeTagCount . ']' . $grp[3];
		$this->codeTags[$this->codeTagCount] = trim ($grp[2], "\r\n");
		$this->codeTagCount++;

		return $code_placeholder;
	}

	protected function codeTagReturn ($grp)
	{
		return $this->codeTags[($grp[1])];
	}

	protected function preTagReplace ($grp)
	{
		$pre_placeholder = $grp[1] . 'PRE_TAG[' . $this->preTagCount . ']' . $grp[3];
		$this->preTags[$this->preTagCount] = trim ($grp[2], "\r\n");
		$this->preTagCount++;

		return $pre_placeholder;
	}

	protected function preTagReturn ($grp)
	{
		return $this->preTags[($grp[1])];
	}

	// Returns the permalink of a comment's parent
	protected function getParentPermalink ($permalink)
	{
		$permalink_parts = explode ('r', $permalink);
		array_pop ($permalink_parts);

		return implode ('r', $permalink_parts);
	}

	// Find a comment by its permalink
	protected function findByPermalink ($permalink, $comments)
	{
		// Loop through all comments
		foreach ($comments as $comment) {
			// Return comment if its permalink matches
			if ($comment['permalink'] === $permalink) {
				return $comment;
			}

			// Recursively check replies when present
			if (!empty ($comment['replies'])) {
				$reply = $this->findByPermalink ($permalink, $comment['replies']);

				if ($reply !== null) {
					return $reply;
				}
			}
		}

		// Otherwise return null
		return null;
	}

	public function parseComment (array $comment, $parent = null, $popular = false)
	{
		$permalink = $comment['permalink'];
		$first_instance = 'hashover-' . $permalink;
		$name_class = 'hashover-name-plain';
		$this->codeTagCount = 0;
		$this->codeTags = array ();
		$this->preTagCount = 0;
		$this->preTags = array ();

		// Get instantiated prefix
		$prefix = $this->ui->prefix ();

		// Initial template
		$template = array (
			'hashover' => $prefix,
			'permalink' => $permalink
		);

		// Text for avatar image alt attribute
		$permatext = substr ($permalink, 1);
		$permatext = explode ('r', $permatext);
		$permatext = array_pop ($permatext);

		// Wrapper element for each comment
		$comment_wrapper = $this->ui->commentWrapper ($permalink);

		// Check if this comment is a popular comment
		if ($popular === true) {
			// Attempt to get parent comment permalink
			$parent = $this->getParentPermalink ($permalink);

			// Get parent comment by its permalink if it exists
			if ($parent !== null) {
				$parent = $this->findByPermalink ($parent, $this->comments['primary']);
			}

			// And remove "-pop" from text for avatar
			$permatext = str_replace ('-pop', '', $permatext);
		} else {
			// Append class to indicate comment is a reply when appropriate
			if ($parent !== null) {
				$comment_wrapper->appendAttribute ('class', 'hashover-reply');
			}
		}

		// Add avatar image to template
		$template['avatar'] = $this->ui->userAvatar ($comment['avatar'], $permalink, $permatext);

		if (!isset ($comment['notice'])) {
			$name = Misc::getArrayItem ($comment, 'name') ?: $this->setup->defaultName;
			$is_twitter = false;

			// Check if user's name is a Twitter handle
			if ($name[0] === '@') {
				$name = mb_substr ($name, 1);
				$name_class = 'hashover-name-twitter';
				$is_twitter = true;
				$name_length = mb_strlen ($name);

				// Check if Twitter handle is valid length
				if ($name_length > 1 and $name_length <= 30) {
					// Set website to Twitter profile if a specific website wasn't given
					if (empty ($comment['website'])) {
						$comment['website'] = 'https://twitter.com/' . $name;
					}
				}
			}

			// Check whether user gave a website
			if (!empty ($comment['website'])) {
				if ($is_twitter === false) {
					$name_class = 'hashover-name-website';
				}

				// If so, display name as a hyperlink
				$name_link = $this->ui->nameElement ('a', $permalink, $name, $comment['website']);
			} else {
				// If not, display name as plain text
				$name_link = $this->ui->nameElement ('span', $permalink, $name);
			}

			// Check if comment has a parent
			if ($parent !== null) {
				// If so, create the parent thread permalink
				$parent_thread = 'hashover-' . $parent['permalink'];

				// Get the parent's name
				$parent_name = Misc::getArrayItem ($parent, 'name') ?: $this->setup->defaultName;

				// Add thread parent hyperlink to template
				$template['parent-link'] = $this->ui->parentThreadLink ($this->setup->filePath, $parent_thread, $permalink, $parent_name);
			}

			if (isset ($comment['user-owned'])) {
				// Append class to indicate comment is from logged in user
				$comment_wrapper->appendAttribute ('class', 'hashover-user-owned');

				// Define "Reply" link with original poster title
				$reply_title = $this->locale->text['commenter-tip'];
				$reply_class = 'hashover-no-email';
			} else {
				// Check if commenter is subscribed
				if (isset ($comment['subscribed'])) {
					// If so, set subscribed title
					$reply_title = $name . ' ' . $this->locale->text['subscribed-tip'];
					$reply_class = 'hashover-has-email';
				} else{
					// If not, set unsubscribed title
					$reply_title = $name . ' ' . $this->locale->text['unsubscribed-tip'];
					$reply_class = 'hashover-no-email';
				}
			}

			// Check if the comment is editable for the user
			if (isset ($comment['editable'])) {
				// If so, add "Edit" hyperlink to template
				if (!empty ($_GET['hashover-edit']) and $_GET['hashover-edit'] === $permalink) {
					$template['edit-link'] = $this->ui->cancelLink ($first_instance, 'edit');
				} else {
					$template['edit-link'] = $this->ui->formLink ($this->setup->filePath, 'edit', $permalink);
				}
			}

			// Check if the comment has been liked
			if (isset ($comment['likes'])) {
				// Add likes to HTML template
				$template['likes'] = $comment['likes'];

				// Check if there is more than one like
				if ($comment['likes'] !== 1) {
					// If so, use "X Likes" locale
					$like_count = $comment['likes'] . ' ' . $this->locale->text['likes'];
				} else {
					// If not, use "X Like" locale
					$like_count = $comment['likes'] . ' ' . $this->locale->text['like'];
				}

				// Add like count to HTML template
				$template['like-count'] = $this->ui->likeCount ('likes', $permalink, $like_count);
			}

			// Check if dislikes are enabled and the comment's been disliked
			if ($this->setup->allowsDislikes === true
			    and isset ($comment['dislikes']))
			{
				// Add likes to HTML template
				$template['dislikes'] = $comment['dislikes'];

				// Check if there is more than one dislike
				if ($comment['dislikes'] !== 1) {
					// If so, use "X Dislikes" locale
					$dislike_count = $comment['dislikes'] . ' ' . $this->locale->text['dislikes'];
				} else {
					// If not, use "X Dislike" locale
					$dislike_count = $comment['dislikes'] . ' ' . $this->locale->text['dislike'];
				}

				// Add dislike count to HTML template
				$template['dislike-count'] = $this->ui->likeCount ('dislikes', $permalink, $dislike_count);
			}

			// Add name HTML to template
			$template['name'] = $this->ui->nameWrapper ($name_class, $name_link);

			// Append status text to date
			if (!empty ($comment['status-text'])) {
				$comment['date'] .= ' (' . $comment['status-text'] . ')';
			}

			// Add date permalink hyperlink to template
			$template['date'] = $this->ui->dateLink ($this->setup->filePath, $first_instance, $comment['date-time'], $comment['date']);

			// Add "Reply" hyperlink to template
			if (!empty ($_GET['hashover-reply']) and $_GET['hashover-reply'] === $permalink) {
				$template['reply-link'] = $this->ui->cancelLink ($first_instance, 'reply', $reply_class);
			} else {
				$template['reply-link'] = $this->ui->formLink ($this->setup->filePath, 'reply', $permalink, $reply_class, $reply_title);
			}

			// Add edit form HTML to template
			if (isset ($comment['editable'])) {
				$template['edit-form'] = $this->editCheck ($comment);
			}

			// Add reply form HTML to template
			$template['reply-form'] = $this->replyCheck ($permalink);

			// Add reply count to template
			if (!empty ($comment['replies'])) {
				$template['reply-count'] = count ($comment['replies']);

				if ($template['reply-count'] > 0) {
					if ($template['reply-count'] !== 1) {
						$template['reply-count'] .= ' ' . $this->locale->text['replies'];
					} else {
						$template['reply-count'] .= ' ' . $this->locale->text['reply'];
					}
				}
			}

			// Add comment data to template
			$template['comment'] = $comment['body'];

			// Remove [img] tags
			$template['comment'] = preg_replace ('/\[(img|\/img)\]/iS', '', $template['comment']);

			// Add HTML anchor tag to URLs (hyperlinks)
			$template['comment'] = preg_replace ($this->linkRegex, '<a href="\\1" rel="noopener noreferrer" target="_blank">\\1</a>', $template['comment']);

			// Parse markdown in comment
			if ($this->setup->usesMarkdown !== false) {
				$template['comment'] = $this->markdown->parseMarkdown ($template['comment']);
			}

			// Replace code tags with placeholder text
			if (mb_strpos ($template['comment'], '<code>') !== false) {
				$template['comment'] = preg_replace_callback ('/(<code>)([\s\S]*?)(<\/code>)/iS', 'self::codeTagReplace', $template['comment']);
			}

			// Replace pre tags with placeholder text
			if (mb_strpos ($template['comment'], '<pre>') !== false) {
				$template['comment'] = preg_replace_callback ('/(<pre>)([\s\S]*?)(<\/pre>)/iS', 'self::preTagReplace', $template['comment']);
			}

			// Check for various multi-line tags
			foreach ($this->trimTagRegexes as $tag => $trimTagRegex) {
				if (mb_strpos ($template['comment'], '<' . $tag . '>') !== false) {
					// Trim leading and trailing whitespace
					$template['comment'] = preg_replace_callback ($trimTagRegex, function ($grp) {
						return $grp[1] . trim ($grp[2], "\r\n") . $grp[3];
					}, $template['comment']);
				}
			}

			// Break comment into paragraphs
			$paragraphs = preg_split ($this->paragraphRegex, $template['comment']);
			$pd_comment = '';

			// Wrap each paragraph in <p> tags and place <br> tags after each line
			for ($i = 0, $il = count ($paragraphs); $i < $il; $i++) {
				$pd_comment .= '<p>' . preg_replace ($this->lineRegex, '<br>', $paragraphs[$i]) . '</p>' . PHP_EOL;
			}

			// Replace code tag placeholders with original code tag HTML
			if ($this->codeTagCount > 0) {
				$pd_comment = preg_replace_callback ('/CODE_TAG\[([0-9]+)\]/S', 'self::codeTagReturn', $pd_comment);
			}

			// Replace pre tag placeholders with original pre tag HTML
			if ($this->preTagCount > 0) {
				$pd_comment = preg_replace_callback ('/PRE_TAG\[([0-9]+)\]/S', 'self::preTagReturn', $pd_comment);
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
			$template['name'] = $this->ui->nameWrapper ($name_class, $comment['title']);
		}

		// Parse theme layout HTML template
		$theme_html = $this->templater->parseTheme ('comments.html', $template);

		// Comment HTML template
		$comment_wrapper->innerHTML ($theme_html);

		// Check if comment has replies
		if (!empty ($comment['replies'])) {
			// If so, append class to indicate comment has replies
			$comment_wrapper->appendAttribute ('class', 'hashover-has-replies');

			// Recursively parse replies
			foreach ($comment['replies'] as $reply) {
				$comment_wrapper->appendInnerHTML ($this->parseComment ($reply, $comment));
			}
		}

		return $comment_wrapper->asHTML ();
	}
}
