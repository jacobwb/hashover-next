<?php namespace HashOver;

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


class CommentsUI extends FormUI
{
	// Creates a wrapper element for each comment
	public function commentWrapper ($permalink = '{permalink}')
	{
		$comment_wrapper = new HTMLTag ('div', array (
			'id' => $this->prefix ($permalink),
			'class' => 'hashover-comment'
		), false);

		if ($this->mode !== 'php') {
			$comment_wrapper->appendAttribute ('class', '{class}', false);
			$comment_wrapper->innerHTML ('{html}');

			return $comment_wrapper->asHTML ();
		}

		return $comment_wrapper;
	}

	// Creates wrapper element to name element
	public function nameWrapper ($class = '{class}', $link = '{link}')
	{
		$name_wrapper = new HTMLTag ('span', array (
			'class' => 'hashover-comment-name ' . $class,
			'innerHTML' => $link
		), false);

		return $name_wrapper->asHTML ();
	}

	// Creates name hyperlink/span element
	public function nameElement ($element, $permalink = '{permalink}', $name = '{name}', $href = '{href}')
	{
		// Decide what kind of element to create
		switch ($element) {
			case 'a': {
				// A hyperlink pointing to the user's input URL
				$name_link = new HTMLTag ('a', array (
					'rel' => 'noopener noreferrer nofollow',
					'href' => $href,
					'id' => $this->prefix ('name-' . $permalink),
					'target' => '_blank',
					'title' => $name,
					'innerHTML' => $name
				), false);

				break;
			}

			case 'span': {
				// A plain wrapper element
				$name_link = new HTMLTag ('span', array (
					'id' => $this->prefix ('name-' . $permalink),
					'innerHTML' => $name
				), false);

				break;
			}
		}

		return $name_link->asHTML ();
	}

	// Creates hyperlink with URL queries to link reference
	protected function queryLink ($href = false, array $queries = array ())
	{
		// Given hyperlink URL or default file path
		$href = $href ?: $this->setup->filePath;

		// Merge given URL queries with existing page URL queries
		$queries = array_merge ($this->setup->urlQueryList, $queries);

		// Add URL queries to path if URL has queries
		if (!empty ($queries)) {
			$href .= '?' . implode ('&', $queries);
		}

		// And create hyperlink
		$link = new HTMLTag ('a', array (
			'rel' => 'nofollow',
			'href' => $href
		), false);

		return $link;
	}

	// Creates "Top of Thread" hyperlink element
	public function parentThreadLink ($href = '{href}', $parent = '{parent}', $permalink = '{permalink}', $name = '{name}')
	{
		// Get locale string
		$thread_locale = $this->locale->text['thread'];

		// Inject OP's name into the locale
		$inner_html = sprintf ($thread_locale, $name);

		// Create hyperlink element
		$thread_link = $this->queryLink ($href);

		// Create hyperlink element
		$thread_link->appendAttributes (array (
			'rel' => 'nofollow',
			'href' => '#' . $parent,
			'id' => $this->prefix ('thread-link-' . $permalink),
			'class' => 'hashover-thread-link',
			'title' => $this->locale->text['thread-tip'],
			'innerHTML' => $inner_html
		), false);

		return $thread_link->asHTML ();
	}

	// Creates date/permalink hyperlink element
	public function dateLink ($href = '{href}', $permalink = '{permalink}', $title = '{title}', $date = '{date}')
	{
		// Create hyperlink element
		$date_link = $this->queryLink ($href);

		// Append more attributes
		$date_link->appendAttributes (array (
			'href' => '#' . $permalink,
			'class' => 'hashover-date-permalink',
			'title' => 'Permalink - ' . $title,
			'innerHTML' => $date
		), false);

		return $date_link->asHTML ();
	}

	// Creates element to hold a count of likes/dislikes each comment has
	public function likeCount ($type, $permalink = '{permalink}', $text = '{text}')
	{
		// Create element
		$count = new HTMLTag ('span', array (
			'id' => $this->prefix ($type . '-' . $permalink),
			'class' => 'hashover-' . $type,
			'innerHTML' => $text
		), false);

		return $count->asHTML ();
	}

	// Creates "Like"/"Dislike" hyperlink element
	public function likeLink ($type, $permalink = '{permalink}', $class = '{class}', $title = '{title}', $text = '{text}')
	{
		// Create hyperlink element
		$link = new HTMLTag ('a', array (
			'rel' => 'nofollow',
			'href' => '#',
			'id' => $this->prefix ($type . '-' . $permalink),
			'class' => $class,
			'title' => $title,
			'innerHTML' => $text
		), false);

		return $link->asHTML ();
	}

	// Creates a form control hyperlink element
	public function formLink ($href, $type, $permalink = '{permalink}', $class = '{class}', $title = '{title}')
	{
		// Form ID for hyperlinks
		$form = 'hashover-' . $type;

		// Create hyperlink element
		$link = $this->queryLink ($href, array ($form . '=' . $permalink));

		// "Reply to Comment" or "Edit Comment" locale key
		$title_locale = ($type === 'reply') ? 'reply-to-comment' : 'edit-your-comment';

		// Create more attributes
		$link->createAttributes (array (
			'id' => $this->prefix ($type. '-link-' . $permalink),
			'class' => 'hashover-comment-' . $type,
			'title' => $this->locale->text[$title_locale]
		));

		// Append href attribute
		$link->appendAttribute ('href', '#' . $form . '-' . $permalink, false);

		// Append attributes
		if ($type === 'reply') {
			$link->appendAttributes (array (
				'class' => $class,
				'title' => '- ' . $title
			));
		}

		// Add link text
		$link->innerHTML ($this->locale->text[$type]);

		return $link->asHTML ();
	}

	// Creates "Cancel" hyperlink element
	public function cancelLink ($permalink, $for, $class = '')
	{
		$cancel_link = $this->queryLink ($this->setup->filePath);
		$cancel_locale = $this->locale->text['cancel'];

		// Append href attribute
		$cancel_link->appendAttribute ('href', '#' . $permalink, false);

		// Create more attributes
		$cancel_link->createAttributes (array (
			'class' => 'hashover-comment-' . $for,
			'title' => $cancel_locale
		));

		// Append optional class
		if (!empty ($class)) {
			$cancel_link->appendAttribute ('class', $class);
		}

		// Add "Cancel" hyperlink text
		$cancel_link->innerHTML ($cancel_locale);

		return $cancel_link->asHTML ();
	}

	// Creates a user avatar image or comment number
	public function userAvatar ($src = '{src}', $href = '{href}', $text = '{text}')
	{
		// If avatars set to images
		if ($this->setup->iconMode !== 'none') {
			// Create wrapper element for avatar image
			$avatar_wrapper = new HTMLTag ('span', array (
				'class' => 'hashover-avatar'
			), false);

			if ($this->setup->iconMode !== 'count') {
				// Create avatar image element
				$comments_avatar = new HTMLTag ('div', array (
					'style' => 'background-image: url(\'' . $src . '\');'
				), false);
			} else {
				// Avatars set to count
				// Create element displaying comment number user will be
				$comments_avatar = new HTMLTag ('a', array (
					'rel' => 'nofollow',
					'href' => '#' . $href,
					'title' => 'Permalink',
					'innerHTML' => $text
				), false);
			}

			// Add comments avatar to avatar image wrapper element
			$avatar_wrapper->appendChild ($comments_avatar);

			return $avatar_wrapper->asHTML ();
		}

		return '';
	}

	// Creates a cancel button hyperlink
	public function cancelButton ($type, $permalink)
	{
		// Create hyperlink element
		$cancel_button = $this->queryLink ($this->setup->filePath);

		// "Cancel" locale string
		$cancel_locale = $this->locale->text['cancel'];

		// Add ID attribute with JavaScript variable single quote break out
		if (!empty ($permalink)) {
			$cancel_button->createAttribute ('id', $this->prefix ($type . '-cancel-' . $permalink));
		}

		// Append href attribute
		$cancel_button->appendAttribute ('href', '#hashover-' . $permalink, false);

		// Create more attributes
		$cancel_button->createAttributes (array (
			'class' => 'hashover-submit hashover-' . $type . '-cancel',
			'title' => $cancel_locale,
			'innerHTML' => $cancel_locale
		));

		return $cancel_button;
	}

	// Creates a comment reply form
	public function replyForm ($permalink = '{permalink}', $url = '{url}', $thread = '{thread}', $title = '{title}', $file = '{file}', $subscribed = true)
	{
		// Create HashOver reply form
		$reply_form = new HTMLTag ('div', array (
			'class' => 'hashover-balloon'
		));

		// If avatars are enabled
		if ($this->setup->iconMode !== 'none') {
			// Create avatar element for HashOver reply form
			$reply_avatar = new HTMLTag ('div', array (
				'class' => 'hashover-avatar-image'
			));

			// Add count element to avatar element
			$reply_avatar->appendChild ($this->avatar ('+'));

			// Add avatar element to inputs wrapper element
			$reply_form->appendChild ($reply_avatar);
		}

		// Display default login inputs when logged out
		if ($this->login->userIsLoggedIn === false) {
			$reply_login_inputs = $this->loginInputs ($permalink);
			$reply_form->appendChild ($reply_login_inputs);
		}

		// Check if form labels are enabled
		if ($this->setup->usesLabels === true) {
			// If so, create label element for comment textarea
			$reply_comment_label = new HTMLTag ('label', array (
				'for' => 'hashover-reply-comment-' . $permalink,
				'class' => 'hashover-comment-label',
				'innerHTML' => $this->locale->text['reply-to-comment']
			), false);

			// Add comment label to form element
			$reply_form->appendChild ($reply_comment_label);
		}

		// Reply form locale
		$reply_form_placeholder = $this->locale->text['reply-form'];

		// Create reply textarea element and add it to form element
		$this->commentForm ($reply_form, 'reply', $reply_form_placeholder, '', $permalink);

		// Add page info fields to reply form
		$this->pageInfoFields ($reply_form, $url, $thread, $title);

		// Create hidden reply to input element
		if (!empty ($file)) {
			$reply_to_input = new HTMLTag ('input', array (
				'type' => 'hidden',
				'name' => 'reply-to',
				'value' => $file
			), false, true);

			// Add hidden reply to input element to form element
			$reply_form->appendChild ($reply_to_input);
		}

		// Create reply form footer element
		$reply_form_footer = new HTMLTag ('div', array (
			'class' => 'hashover-form-footer'
		));

		// Create wrapper for form links
		$reply_form_links_wrapper = new HTMLTag ('span', array (
			'class' => 'hashover-form-links'
		));

		// Add checkbox label element to reply form footer element
		if ($this->setup->emailField !== 'off') {
			if ($this->login->userIsLoggedIn === false or !empty ($this->login->email)) {
				$subscribe_label = $this->subscribeLabel ($permalink, 'reply', $subscribed);
				$reply_form_links_wrapper->appendChild ($subscribe_label);
			}
		}

		// Create and add allowed HTML revealer hyperlink
		if ($this->mode !== 'php') {
			$reply_form_links_wrapper->appendChild ($this->formatting ('reply', $permalink));
		}

		// Add reply form links wrapper to reply form footer element
		$reply_form_footer->appendChild ($reply_form_links_wrapper);

		// Create wrapper for form buttons
		$reply_form_buttons_wrapper = new HTMLTag ('span', array (
			'class' => 'hashover-form-buttons'
		));

		// Create "Cancel" link element
		if ($this->setup->usesCancelButtons === true) {
			// Add "Cancel" link element to reply form footer element
			$reply_cancel_button = $this->cancelButton ('reply', $permalink);
			$reply_form_buttons_wrapper->appendChild ($reply_cancel_button);
		}

		// Create "Post Comment" button element
		$reply_post_button = new HTMLTag ('input', array (), false, true);

		// Add ID attribute with JavaScript variable single quote break out
		if (!empty ($permalink)) {
			$reply_post_button->createAttribute ('id', $this->prefix ('reply-post-' . $permalink));
		}

		// Post reply locale
		$post_reply = $this->locale->text['post-reply'];

		// Continue with other attributes
		$reply_post_button->createAttributes (array (
			'class' => 'hashover-submit hashover-reply-post',
			'type' => 'submit',
			'name' => 'post',
			'value' => $post_reply,
			'title' => $post_reply
		));

		// Add "Post Comment" element to reply form footer element
		$reply_form_buttons_wrapper->appendChild ($reply_post_button);

		// Add reply form buttons wrapper to reply form footer element
		$reply_form_footer->appendChild ($reply_form_buttons_wrapper);

		// Add reply form footer to reply form element
		$reply_form->appendChild ($reply_form_footer);

		return $reply_form->asHTML ();
	}

	// Creates a comment edit form
	public function editForm ($permalink = '{permalink}', $url = '{url}', $thread = '{thread}', $title = '{title}', $file = '{file}', $name = '{name}', $email = '{email}', $website = '{website}', $body = '{body}', $status = '', $subscribed = true)
	{
		// "Edit Comment" locale string
		$edit_comment = $this->locale->text['edit-comment'];

		// "Save Edit" locale string
		$save_edit = $this->locale->text['save'];

		// "Cancel" locale string
		$cancel_edit = $this->locale->text['cancel'];

		// "Delete" locale string
		if ($this->login->userIsAdmin === true) {
			$delete_comment = $this->locale->text['permanently-delete'];
		} else {
			$delete_comment = $this->locale->text['delete'];
		}

		// Create wrapper element
		$edit_form = new HTMLTag ('div');

		// Create edit form title element
		$edit_form_title = new HTMLTag ('div', array (
			'class' => 'hashover-title hashover-dashed-title',
			'innerHTML' => $edit_comment
		), false);

		// Check if user is admin
		if ($this->login->userIsAdmin === true) {
			// If so, create status dropdown wrapper element
			$edit_status_wrapper = new HTMLTag ('span', array (
				'class' => 'hashover-edit-status',
				'innerHTML' => $this->locale->text['status']
			), false);

			// Create select wrapper element
			$edit_status_select_wrapper = new HTMLTag ('span', array (
				'class' => 'hashover-select-wrapper'
			), false);

			// Status dropdown menu options
			$status_options = array (
				'approved' => $this->locale->text['status-approved'],
				'pending' => $this->locale->text['status-pending'],
				'deleted' => $this->locale->text['status-deleted']
			);

			// Create status dropdown menu element
			$edit_status_dropdown = new HTMLTag ('select', array (
				'id' => $this->prefix ('edit-status-' . $permalink),
				'name' => 'status',
				'size' => '1'
			));

			// Run through status options
			foreach ($status_options as $value => $inner_html) {
				// Create status dropdown menu option element
				$edit_status_option = new HTMLTag ('option', array (
					'value' => $value,
					'innerHTML' => $inner_html
				));

				// Set option as selected if it matches the comment status given
				if ($value === $status) {
					$edit_status_option->createAttribute ('selected', 'true');
				}

				// Add option element to status dropdown menu
				$edit_status_dropdown->appendChild ($edit_status_option);
			}

			// Add status dropdown menu to select wrapper element
			$edit_status_select_wrapper->appendChild ($edit_status_dropdown);

			// Add select wrapper to status dropdown wrapper element
			$edit_status_wrapper->appendChild ($edit_status_select_wrapper);

			// Add status dropdown wrapper to edit form title element
			$edit_form_title->appendChild ($edit_status_wrapper);
		}

		// Append edit form title to edit form wrapper
		$edit_form->appendChild ($edit_form_title);

		// Append default login inputs
		$edit_login_inputs = $this->loginInputs ($permalink, true, $name, $email, $website);
		$edit_form->appendChild ($edit_login_inputs);

		// Check if form labels are enabled
		if ($this->setup->usesLabels === true) {
			// If so, create label element for comment textarea
			$edit_comment_label = new HTMLTag ('label', array (
				'for' => 'hashover-edit-comment-' . $permalink,
				'class' => 'hashover-comment-label',
				'innerHTML' => $this->locale->text['edit-your-comment']
			), false);

			// And add comment label to form element
			$edit_form->appendChild ($edit_comment_label);
		}

		// Comment form placeholder text
		$edit_placeholder = $this->locale->text['comment-form'];

		// Create edit textarea element and add it to form element
		$this->commentForm ($edit_form, 'edit', $edit_placeholder, $body, $permalink);

		// Add page info fields to edit form
		$this->pageInfoFields ($edit_form, $url, $thread, $title);

		// Create hidden comment file input element
		$edit_file_input = new HTMLTag ('input', array (
			'type' => 'hidden',
			'name' => 'file',
			'value' => $file
		), false, true);

		// Add hidden page title input element to form element
		$edit_form->appendChild ($edit_file_input);

		// Create wrapper element for edit form buttons
		$edit_form_footer = new HTMLTag ('div', array (
			'class' => 'hashover-form-footer'
		));

		// Create wrapper for form links
		$edit_form_links_wrapper = new HTMLTag ('span', array (
			'class' => 'hashover-form-links'
		));

		// Add checkbox label element to edit form buttons wrapper element
		if ($this->setup->emailField !== 'off') {
			$subscribe_label = $this->subscribeLabel ($permalink, 'edit', $subscribed);
			$edit_form_links_wrapper->appendChild ($subscribe_label);
		}

		// Create and add allowed HTML revealer hyperlink
		if ($this->mode !== 'php') {
			$edit_form_links_wrapper->appendChild ($this->formatting ('edit', $permalink));
		}

		// Add edit form links wrapper to edit form footer element
		$edit_form_footer->appendChild ($edit_form_links_wrapper);

		// Create wrapper for form buttons
		$edit_form_buttons_wrapper = new HTMLTag ('span', array (
			'class' => 'hashover-form-buttons'
		));

		// Create "Cancel" link element
		if ($this->setup->usesCancelButtons === true) {
			// Add "Cancel" hyperlink element to edit form footer element
			$edit_cancel_button = $this->cancelButton ('edit', $permalink);
			$edit_form_buttons_wrapper->appendChild ($edit_cancel_button);
		}

		// Create "Post Comment" button element
		$save_edit_button = new HTMLTag ('input', array (), false, true);

		// Add ID attribute with JavaScript variable single quote break out
		if (!empty ($permalink)) {
			$save_edit_button->createAttribute ('id', $this->prefix ('edit-post-' . $permalink));
		}

		// Continue with other attributes
		$save_edit_button->createAttributes (array (
			'class' => 'hashover-submit hashover-edit-post',
			'type' => 'submit',
			'name' => 'edit',
			'value' => $save_edit,
			'title' => $save_edit
		));

		// Add "Save Edit" element to edit form footer element
		$edit_form_buttons_wrapper->appendChild ($save_edit_button);

		// Create "Delete" button element
		$delete_button = new HTMLTag ('input', array (), false, true);

		// Add ID attribute with JavaScript variable single quote break out
		if (!empty ($permalink)) {
			$delete_button->createAttribute ('id', $this->prefix ('edit-delete-' . $permalink));
		}

		// Continue with other attributes
		$delete_button->createAttributes (array (
			'class' => 'hashover-submit hashover-edit-delete',
			'type' => 'submit',
			'name' => 'delete',
			'value' => $delete_comment,
			'title' => $delete_comment
		));

		// Add "Delete" element to edit form footer element
		$edit_form_buttons_wrapper->appendChild ($delete_button);

		// Add edit form buttons wrapper to edit form footer element
		$edit_form_footer->appendChild ($edit_form_buttons_wrapper);

		// Add form buttons to edit form element
		$edit_form->appendChild ($edit_form_footer);

		return $edit_form->innerHTML;
	}

	// Creates thread hyperlink element
	public function threadLink ($url = '{url}', $title = '{title}')
	{
		// Create hyperlink element
		$thread_link = new HTMLTag ('a', array (
			'rel' => 'nofollow',
			'href' => $url,
			'innerHTML' => $title
		), false);

		return $thread_link->asHTML ();
	}
}
