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
		} else {
			exit ('<b>HashOver</b>: This is a class file.');
		}
	}

	class HTMLOutput
	{
		public $setup;
		public $readComments;
		public $locales;
		public $commentCount;
		public $popularList;
		public $popularComments;
		public $comments;

		protected $addcslashes;
		protected $pageTitle;
		protected $pageURL;
		protected $defaultLoginInputs;

		public
		function __construct (ReadComments $read_comments, Locales $locales, $show_count, array $popularList)
		{
			$this->setup = $read_comments->setup;
			$this->readComments = $read_comments;
			$this->locales = $locales;
			$this->commentCount = $show_count;
			$this->popularList = $popularList;
			$this->addcslashes = ($this->setup->mode !== 'php');
			$this->pageTitle = $this->setup->pageTitle;
			$this->pageURL = $this->setup->pageURL;

			if ($this->setup->mode !== 'php') {
				$this->pageTitle = addcslashes ($this->pageTitle, "'");
				$this->pageURL = addcslashes ($this->pageURL, "'");
			}

			$this->defaultLoginInputs = $this->loginInputs ();
		}

		public
		function injectVar ($var)
		{
			// Return variable as JavaScript concatenation statement
			if ($this->setup->mode !== 'php') {
				if (!empty ($var)) {
					return '\' + ' . $var . ' + \'';
				}
			}

			// Return variable normally by default
			return $var;
		}

		// Re-encode a URL
		protected
		function safeURLEncode ($url)
		{
			return urlencode (urldecode ($url));
		}

		// Creates a inputs elements for user login information
		protected
		function loginInputs ($editForm = false, $name = '', $website = '')
		{
			// Login input attribute information
			$login_input_attributes = array (
				'name' => array (
					'enabled' => $this->setup->allowsNames,
					'wrapper_class' => 'hashover-name-input',
					'label_class' => 'hashover-name-label',
					'placeholder' => $this->locales->locale ('name', $this->addcslashes),
					'input_id' => 'hashover-main-name',
					'input_type' => 'text',
					'input_name' => 'name',
					'input_title' => $this->locales->locale ('name_tip', $this->addcslashes),
					'input_value' => $this->setup->userName
				),
				'password' => array (
					'enabled' => ($this->setup->allowsPasswords and $this->setup->allowsNames),
					'wrapper_class' => 'hashover-password-input',
					'label_class' => 'hashover-password-label',
					'placeholder' => $this->locales->locale ('password', $this->addcslashes),
					'input_id' => 'hashover-main-password',
					'input_type' => 'password',
					'input_name' => 'password',
					'input_title' => $this->locales->locale ('password_tip', $this->addcslashes),
					'input_value' => ''
				),
				'email' => array (
					'enabled' => $this->setup->allowsEmails,
					'wrapper_class' => 'hashover-email-input',
					'label_class' => 'hashover-email-label',
					'placeholder' => $this->locales->locale ('email', $this->addcslashes),
					'input_id' => 'hashover-main-email',
					'input_type' => 'text',
					'input_name' => 'email',
					'input_title' => $this->locales->locale ('email_tip', $this->addcslashes),
					'input_value' => $this->setup->userEmail
				),
				'website' => array (
					'enabled' => $this->setup->allowsWebsites,
					'wrapper_class' => 'hashover-website-input',
					'label_class' => 'hashover-website-label',
					'placeholder' => $this->locales->locale ('website', $this->addcslashes),
					'input_id' => 'hashover-main-website',
					'input_type' => 'text',
					'input_name' => 'website',
					'input_title' => $this->locales->locale ('website_tip', $this->addcslashes),
					'input_value' => $this->setup->userWebsite
				)
			);

			// Change input values to specified values
			if ($editForm === true) {
				$login_input_attributes['name']['input_value'] = $this->injectVar ($name);
				$login_input_attributes['website']['input_value'] = $this->injectVar ($website);
				$login_input_attributes['password']['placeholder'] = $this->locales->locale ('confirm_password', $this->addcslashes);
				$login_input_attributes['password']['input_title'] = $this->locales->locale ('confirm_password', $this->addcslashes);
			}

			// Create wrapper element for styling login inputs
			$login_inputs = new HTMLTag ('div');
			$login_inputs->createAttribute ('class', 'hashover-inputs');

			// Create and append login input elements to main form inputs wrapper element
			foreach ($login_input_attributes as $attributes) {
				// Skip disabled input tags
				if ($attributes['enabled'] === false) {
					continue;
				}

				// Create wrapper element for inputs
				$input_wrapper = new HTMLTag ('div');
				$input_wrapper->createAttribute ('class', $attributes['wrapper_class']);

				// Create label element for input
				if ($this->setup->usesLabels === true) {
					$label = new HTMLTag ('label', false, false);
					$label->createAttribute ('for', $attributes['input_id']);
					$label->createAttribute ('class', $attributes['label_class']);
					$label->innerHTML ($attributes['placeholder']);

					// Add label to wrapper element
					$input_wrapper->appendChild ($label);
				}

				// Create input element
				$input = new HTMLTag ('input', true);
				$input->createAttribute ('id', $attributes['input_id']);
				$input->createAttribute ('class', 'hashover-input-info');
				$input->createAttribute ('type', $attributes['input_type']);
				$input->createAttribute ('name', $attributes['input_name']);
				$input->createAttribute ('title', $attributes['input_title']);
				$input->createAttribute ('value', $attributes['input_value']);
				$input->createAttribute ('placeholder', $attributes['placeholder']);

				// Add input to wrapper element
				$input_wrapper->appendChild ($input);

				// Add input wrapper to main inputs wrapper element
				$login_inputs->appendChild ($input_wrapper);
			}

			return $login_inputs;
		}

		protected
		function avatar ($text)
		{
			// If avatars set to images
			if ($this->setup->iconMode === 'image') {
				$avatar_src = $this->setup->httpDirectory;

				// Logged in
				if ($this->setup->userIsLoggedIn === true and !empty ($this->setup->userEmail)) {
					// Image source is local script that handles avatar images
					$avatar_src .= '/scripts/avatars.php';
					$avatar_src .= '?hash=' . md5 (strtolower (trim ($this->setup->userEmail)));
				} else {
					// Logged out
					// Image source is local default image
					$avatar_src .= '/images/' . $this->setup->imageFormat . 's/';
					$avatar_src .= 'first-comment.' . $this->setup->imageFormat;
				}

				// Create avatar image element
				$avatar = new HTMLTag ('div', false, false);
				$background_image = 'background-image: url(\'' . $avatar_src . '\');';

				if ($this->setup->mode !== 'php') {
					$avatar->createAttribute ('style', addcslashes ($background_image, "'"));
				} else {
					$avatar->createAttribute ('style', $background_image);
				}
			} else {
				// Avatars set to count
				// Create element displaying comment number user will be
				$avatar = new HTMLTag ('span', false, false);
				$avatar->innerHTML ($text);
			}

			return $avatar;
		}

		// Creates a wrapper element for each comment
		public
		function commentWrapper ($permalink, $class = '', $innerHTML = '')
		{
			$comment_wrapper = new HTMLTag ('div', false, false);
			$comment_wrapper->createAttribute ('id', $this->injectVar ($permalink));
			$comment_wrapper->createAttribute ('class', 'hashover-comment');

			if ($this->setup->mode !== 'php') {
				$comment_wrapper->appendAttribute ('class', $this->injectVar ($class), false);
				$comment_wrapper->innerHTML ($this->injectVar ($innerHTML));

				return $comment_wrapper->asHTML ();
			}

			return $comment_wrapper;
		}

		// Creates parent element to name element
		public
		function nameWrapper ($nameLink, $nameClass)
		{
			$name_wrapper = new HTMLTag ('span', false, false);
			$name_wrapper->createAttribute ('class', 'hashover-comment-name');
			$name_wrapper->appendAttribute ('class', $this->injectVar ($nameClass));
			$name_wrapper->innerHTML ($this->injectVar ($nameLink));

			return $name_wrapper->asHTML ();
		}

		// Creates name hyperlink/span element
		public
		function nameElement ($element, $name, $permalink, $href = '')
		{
			switch ($element) {
				case 'a': {
					$name_link = new HTMLTag ('a', false, false);
					$name_link->createAttribute ('href', $this->injectVar ($href));
					break;
				}

				case 'span': {
					$name_link = new HTMLTag ('span', false, false);
					break;
				}
			}

			$name_link->createAttribute ('target', '_blank');
			$name_link->createAttribute ('class', 'hashover-name-' . $this->injectVar ($permalink));
			$name_link->innerHTML ($this->injectVar ($name));

			return $name_link->asHTML ();
		}

		// Creates "Top of Thread" hyperlink element
		public
		function threadLink ($parent)
		{
			$thread_link = new HTMLTag ('a', false, false);
			$thread_link->createAttribute ('href', '#' . $this->injectVar ($parent));
			$thread_link->createAttribute ('class', 'hashover-thread-link');
			$thread_link->createAttribute ('title', $this->locales->locale ('thread_tip', $this->addcslashes));
			$thread_link->innerHTML ($this->locales->locale ('thread', $this->addcslashes));

			return $thread_link->asHTML ();
		}

		// Creates date/permalink hyperlink element
		public
		function dateLink ($permalink, $date)
		{
			$date_link = new HTMLTag ('a', false, false);
			$date_link->createAttribute ('href', '#' . $this->injectVar ($permalink));
			$date_link->createAttribute ('class', 'hashover-date-permalink');
			$date_link->createAttribute ('title', 'Permalink');
			$date_link->innerHTML ($this->injectVar ($date));

			return $date_link->asHTML ();
		}

		// Creates element to hold a count of likes each comment has
		public
		function likeCount ($permalink, $text)
		{
			$like_count = new HTMLTag ('span', false, false);
			$like_count->createAttribute ('id', 'hashover-likes-' . $this->injectVar ($permalink));
			$like_count->createAttribute ('class', 'hashover-likes');
			$like_count->innerHTML ($this->injectVar ($text));

			return $like_count->asHTML ();
		}

		// Creates element to hold a count of likes each comment has
		public
		function dislikeCount ($permalink, $text)
		{
			$dislike_count = new HTMLTag ('span', false, false);
			$dislike_count->createAttribute ('id', 'hashover-dislikes-' . $this->injectVar ($permalink));
			$dislike_count->createAttribute ('class', 'hashover-dislikes');
			$dislike_count->innerHTML ($this->injectVar ($text));

			return $dislike_count->asHTML ();
		}

		// Creates "Like" hyperlink element
		public
		function likeLink ($permalink, $class, $title, $text)
		{
			$like_link = new HTMLTag ('a', false, false);
			$like_link->createAttribute ('href', '#');
			$like_link->createAttribute ('id', 'hashover-like-' . $this->injectVar ($permalink));
			$like_link->createAttribute ('class', $this->injectVar ($class));
			$like_link->createAttribute ('title', $this->injectVar ($title));
			$like_link->innerHTML ($this->injectVar ($text));

			return $like_link->asHTML ();
		}

		// Creates "Dislike" hyperlink element
		public
		function dislikeLink ($permalink, $class, $title, $text)
		{
			$dislike_link = new HTMLTag ('a', false, false);
			$dislike_link->createAttribute ('href', '#');
			$dislike_link->createAttribute ('id', 'hashover-dislike-' . $this->injectVar ($permalink));
			$dislike_link->createAttribute ('class', $this->injectVar ($class));
			$dislike_link->createAttribute ('title', $this->injectVar ($title));
			$dislike_link->innerHTML ($this->injectVar ($text));

			return $dislike_link->asHTML ();
		}

		// Creates "Reply" hyperlink element
		public
		function replyLink ($permalink, $class, $title)
		{
			$reply_link = new HTMLTag ('a', false, false);
			$reply_link->createAttribute ('href', '?');

			// Add URL queries to link reference
			if (!empty ($this->setup->URLQueries)) {
				$reply_link->appendAttribute ('href', $this->safeURLEncode ($this->setup->URLQueries) . '&', false);
			}

			$reply_link->appendAttribute ('href', 'hashover_reply=' . $this->injectVar ($permalink), false);
			$reply_link->appendAttribute ('href', '#hashover-reply-' . $this->injectVar ($permalink), false);
			$reply_link->createAttribute ('id', 'hashover-reply-link-' . $this->injectVar ($permalink));
			$reply_link->createAttribute ('class', 'hashover-comment-reply');
			$reply_link->appendAttribute ('class', $this->injectVar ($class));
			$reply_link->createAttribute ('title', $this->locales->locale ('reply_to_comment', true));
			$reply_link->appendAttribute ('title', '- ' . $this->injectVar ($title));
			$reply_link->innerHTML ($this->locales->locale ('reply', true));

			return $reply_link->asHTML ();
		}

		// Creates "Edit" hyperlink element
		public
		function editLink ($permalink)
		{
			$edit_link = new HTMLTag ('a', false, false);
			$edit_link->createAttribute ('href', '?');

			// Add URL queries to link reference
			if (!empty ($this->setup->URLQueries)) {
				$edit_link->appendAttribute ('href', $this->safeURLEncode ($this->setup->URLQueries) . '&', false);
			}

			$edit_link->appendAttribute ('href', 'hashover_edit=' . $this->injectVar ($permalink), false);
			$edit_link->appendAttribute ('href', '#hashover-edit-' . $this->injectVar ($permalink), false);
			$edit_link->createAttribute ('id', 'hashover-edit-link-' . $this->injectVar ($permalink));
			$edit_link->createAttribute ('class', 'hashover-comment-edit hashover-edit');
			$edit_link->createAttribute ('title', $this->locales->locale ('edit_your_comment', true));
			$edit_link->innerHTML ($this->locales->locale ('edit', true));

			return $edit_link->asHTML ();
		}

		// Creates "Cancel" hyperlink element
		public
		function cancelLink ($permalink, $for, $class)
		{
			$cancel_link = new HTMLTag ('a', false, false);
			$cancel_link->createAttribute ('href', $this->setup->parsedURL['path']);
			$cancel_link->appendAttribute ('href', '#' . $permalink, false);

			// Add URL queries to link reference
			if (!empty ($this->setup->URLQueries)) {
				$cancel_link->appendAttribute ('href', '?' . $this->safeURLEncode ($this->setup->URLQueries), false);
			}

			// Continue with other attibutes
			$cancel_link->createAttribute ('class', 'hashover-comment-' . $for);
			$cancel_link->appendAttribute ('class', $class);
			$cancel_link->createAttribute ('title', $this->locales->locale ('cancel', $this->addcslashes));
			$cancel_link->innerHTML ($this->locales->locale ('cancel', $this->addcslashes));

			return $cancel_link->asHTML ();
		}

		public
		function userAvatar ($text, $href, $src)
		{
			// If avatars set to images
			if ($this->setup->iconMode !== 'none') {
				// Create wrapper element for avatar image
				$avatar_wrapper = new HTMLTag ('span', false, false);
				$avatar_wrapper->createAttribute ('class', 'hashover-avatar');

				if ($this->setup->iconMode === 'image') {
					if ($this->setup->mode !== 'php') {
						$background_image = 'background-image: url(\\\'' . $this->injectVar ($src) . '\\\');';
					} else {
						$background_image = 'background-image: url(\'' . $this->injectVar ($src) . '\');';
					}

					// Create avatar image element
					$comments_avatar = new HTMLTag ('div', false, false);
					$comments_avatar->createAttribute ('style', $background_image);
				} else {
					// Avatars set to count
					// Create element displaying comment number user will be
					$comments_avatar = new HTMLTag ('a', false, false);
					$comments_avatar->createAttribute ('href', '#' . $this->injectVar ($href));
					$comments_avatar->createAttribute ('title', 'Permalink');
					$comments_avatar->innerHTML ($this->injectVar ($text));
				}

				// Add comments avatar to avatar image wrapper element
				$avatar_wrapper->appendChild ($comments_avatar);

				return $avatar_wrapper->asHTML ();
			}
		}

		protected
		function subscribeLabel ($id = '', $class = 'main', $checked = true)
		{
			// Create subscribe checkbox label element
			$subscribe_label = new HTMLTag ('label');
			$subscribe_label->createAttribute ('for', 'hashover-subscribe');

			if (!empty ($id)) {
				$subscribe_label->appendAttribute ('for', '-' . $this->injectVar ($id), false);
			}

			$subscribe_label->createAttribute ('class', 'hashover-' . $class . '-label');
			$subscribe_label->createAttribute ('title', $this->locales->locale ('subscribe_tip', $this->addcslashes));

			// Create subscribe element checkbox
			$subscribe = new HTMLTag ('input', true);
			$subscribe->createAttribute ('id', 'hashover-subscribe');

			if (!empty ($id)) {
				$subscribe->appendAttribute ('id', '-' . $this->injectVar ($id), false);
			}

			$subscribe->createAttribute ('type', 'checkbox');
			$subscribe->createAttribute ('name', 'subscribe');

			// Check checkbox
			if ($checked === true) {
				$subscribe->createAttribute ('checked', 'true');
			}

			// Add subscribe checkbox element to subscribe checkbox label element
			$subscribe_label->appendChild ($subscribe);

			// Add text to subscribe checkbox label element
			$subscribe_label->appendInnerHTML ($this->locales->locale ('subscribe', $this->addcslashes));

			return $subscribe_label;
		}

		public
		function initialHTML ($hashover_wrapper = true)
		{
			// Create element that HashOver comments will appear in
			$hashover_element = new HTMLTag ('div', false, false);
			$hashover_element->createAttribute ('id', 'hashover');

			// Add class for differentiating desktop and mobile styling
			if ($this->setup->isMobile === true) {
				$hashover_element->appendAttribute ('class', 'hashover-mobile');
			} else {
				$hashover_element->appendAttribute ('class', 'hashover-desktop');
			}

			// Add class to indicate user login status
			if ($this->setup->userIsLoggedIn === true) {
				$hashover_element->appendAttribute ('class', 'hashover-logged-in');
			} else {
				$hashover_element->appendAttribute ('class', 'hashover-logged-out');
			}

			// Create element for jump anchor
			$jump_anchor = new HTMLTag ('span');
			$jump_anchor->createAttribute ('id', 'comments');

			// Add jump anchor to HashOver element
			$hashover_element->appendChild ($jump_anchor);

			// Create primary form wrapper element
			$form_section = new HTMLTag ('div', false, false);
			$form_section->createAttribute ('id', 'hashover-form-section');

			// Create element for "Post Comment" title
			$post_title = new HTMLTag ('span');
			$post_title->createAttribute ('class', 'hashover-title');
			$post_title->appendAttribute ('class', 'hashover-main-title');
			$post_title->appendAttribute ('class', 'hashover-dashed-title');
			$post_comment_locale = $this->locales->locale ('post_comment_on', $this->addcslashes);
			$post_title->innerHTML ($post_comment_locale[0]);

			// Add optional "on <page title>" to "Post Comment" title
			if ($this->setup->displaysTitle === true) {
				$on_title = str_replace ('_TITLE_', $this->pageTitle, $post_comment_locale[1]);
				$post_title->appendInnerHTML ($on_title, false);
			}

			// Add "Post Comment" element to primary form wrapper
			$form_section->appendChild ($post_title);

			// Create element for various messages when needed
			$message_element = new HTMLTag ('div');
			$message_element->createAttribute ('id', 'hashover-message');
			$message_element->createAttribute ('class', 'hashover-title');
			$message_element->appendAttribute ('class', 'hashover-message');

			// Check if message cookie is set
			if (!empty ($_COOKIE['message']) or !empty ($_COOKIE['error'])) {
				if ($this->setup->mode === 'php') {
					$message_element->appendAttribute ('class', 'hashover-message-open');
				}

				if (!empty ($_COOKIE['message'])) {
					$message = $_COOKIE['message'];
				} else {
					$message = $_COOKIE['error'];
					$message_element->appendAttribute ('class', 'hashover-message-error');
				}

				// If so, put current message into message element
				$message_element->innerHTML ($message);
			}

			// Add message element to primary form wrapper
			$form_section->appendChild ($message_element);

			// Create main HashOver form
			$main_form = new HTMLTag ('form');
			$main_form->createAttribute ('id', 'hashover-form');
			$main_form->createAttribute ('class', 'hashover-balloon');
			$main_form->createAttribute ('name', 'hashover-form');
			$main_form->createAttribute ('action', $_SERVER['PHP_SELF']);
			$main_form->createAttribute ('method', 'post');

			// Create wrapper element for styling inputs
			$main_inputs = new HTMLTag ('div');
			$main_inputs->createAttribute ('class', 'hashover-inputs');

			// If avatars are enabled
			if ($this->setup->iconMode !== 'none') {
				// Create avatar element for main HashOver form
				$main_avatar = new HTMLTag ('div');
				$main_avatar->createAttribute ('class', 'hashover-avatar-image');

				// Add count element to avatar element
				$main_avatar->appendChild ($this->avatar ($this->readComments->primaryCount));

				// Add avatar element to inputs wrapper element
				$main_inputs->appendChild ($main_avatar);
			}

			// Logged in
			if ($this->setup->userIsLoggedIn === true) {
				if (!empty ($this->setup->userName)) {
					$user_name = $this->setup->userName;
				} else {
					$user_name = $this->setup->defaultName;
				}

				$user_website =& $this->setup->userWebsite;
				$name_class = 'hashover-name-plain';
				$is_twitter = false;

				// Check if user's name is a Twitter handle
				if ($user_name[0] === '@') {
					$user_name = substr ($user_name, 1);
					$name_class = 'hashover-name-twitter';
					$is_twitter = true;
					$name_length = mb_strlen ($user_name);

					if ($name_length > 1 and $name_length <= 30) {
						if (empty ($user_website)) {
							$user_website = 'http://twitter.com/' . $user_name;
						}
					}
				}

				// Create element for logged user's name
				$main_form_column_spanner = new HTMLTag ('div', false, false);
				$main_form_column_spanner->createAttribute ('class', 'hashover-comment-name');
				$main_form_column_spanner->appendAttribute ('class', 'hashover-top-name');

				// Check if user gave website
				if (!empty ($user_website)) {
					if ($is_twitter === false) {
						$name_class = 'hashover-name-website';
					}

					// Create link to user's website
					$main_form_hyperlink = new HTMLTag ('a', false, false);
					$main_form_hyperlink->createAttribute ('href', $user_website);
					$main_form_hyperlink->createAttribute ('target', '_blank');
					$main_form_hyperlink->innerHTML ($user_name);

					// Add username hyperlink to main form column spanner
					$main_form_column_spanner->appendChild ($main_form_hyperlink);
				} else {
					// No website
					$main_form_column_spanner->innerHTML ($user_name);
				}

				// Set classes user's name spanner
				$main_form_column_spanner->appendAttribute ('class', $name_class);

				// Add main form column spanner to inputs wrapper element
				$main_inputs->appendChild ($main_form_column_spanner);
			} else {
				// Logged out
				// Use default login inputs
				$main_inputs->appendInnerHTML ($this->defaultLoginInputs->innerHTML);
			}

			// Add inputs wrapper to form element
			$main_form->appendChild ($main_inputs);

			// Create fake "required fields" element as a SPAM trap
			$required_fields = new HTMLTag ('div');
			$required_fields->createAttribute ('id', 'hashover-requiredFields');

			$fake_fields = array (
				'summary' => 'text',
				'age' => 'hidden',
				'lastname' => 'text',
				'address' => 'text',
				'zip' => 'hidden',
			);

			// Create and append fake input elements to fake required fields
			foreach ($fake_fields as $name => $type) {
				$fake_input = new HTMLTag ('input', true);
				$fake_input->createAttribute ('type', $type);
				$fake_input->createAttribute ('name', $name);
				$fake_input->createAttribute ('value');

				// Add fake summary input element to fake required fields
				$required_fields->appendChild ($fake_input);
			}

			// Add fake input elements to form element
			$main_form->appendChild ($required_fields);

			// Create label element for comment textarea
			if ($this->setup->usesLabels === true) {
				$main_comment_label = new HTMLTag ('label', false, false);
				$main_comment_label->createAttribute ('for', 'hashover-main-comment');
				$main_comment_label->createAttribute ('class', 'hashover-comment-label');
				$main_comment_label->innerHTML ($this->locales->locale ('comment_form', $this->addcslashes));

				// Add comment label to form element
				$main_form->appendChild ($main_comment_label);
			}

			// Create main textarea
			$main_textarea = new HTMLTag ('textarea');
			$main_textarea->createAttribute ('id', 'hashover-main-comment');
			$main_textarea->createAttribute ('class', 'hashover-textarea');
			$main_textarea->appendAttribute ('class', 'hashover-main-textarea');
			$main_textarea->createAttribute ('cols', '63');
			$main_textarea->createAttribute ('name', 'comment');
			$main_textarea->createAttribute ('rows', '5');
			$main_textarea->createAttribute ('title', $this->locales->locale ('form_tip', $this->addcslashes));
			$main_textarea->createAttribute ('placeholder', $this->locales->locale ('comment_form', $this->addcslashes));

			// Add a red border to main textarea upon posting error
			if (isset ($_COOKIE['success']) and $_COOKIE['success'] === 'no') {
				$main_textarea->createAttribute ('style', 'border: 2px solid #FF0000 !important;');
				$main_textarea->createAttribute ('placeholder', $this->locales->locale ('reply_form', $this->addcslashes));
			}

			// Add main textarea element to form element
			$main_form->appendChild ($main_textarea);

			// Create hidden page title input element
			$main_page_title_input = new HTMLTag ('input', true);
			$main_page_title_input->createAttribute ('type', 'hidden');
			$main_page_title_input->createAttribute ('name', 'title');
			$main_page_title_input->createAttribute ('value', $this->pageTitle);

			// Add hidden page title input element to form element
			$main_form->appendChild ($main_page_title_input);

			// Create hidden page URL input element
			$main_page_url_input = new HTMLTag ('input', true);
			$main_page_url_input->createAttribute ('type', 'hidden');
			$main_page_url_input->createAttribute ('name', 'url');
			$main_page_url_input->createAttribute ('value', $this->pageURL);

			// Add hidden page title input element to form element
			$main_form->appendChild ($main_page_url_input);

			// Create hidden reply to input element
			if (!empty ($_COOKIE['replied'])) {
				$reply_to_input = new HTMLTag ('input', true);
				$reply_to_input->createAttribute ('type', 'hidden');
				$reply_to_input->createAttribute ('name', 'reply_to');
				$reply_to_input->createAttribute ('value', $_COOKIE['replied']);

				// Add hidden reply to input element to form element
				$main_form->appendChild ($reply_to_input);
			}

			// Create wrapper element for main form footer
			$main_form_footer = new HTMLTag ('div');
			$main_form_footer->createAttribute ('class', 'hashover-form-footer');

			// Add checkbox label element to main form buttons wrapper element
			if ($this->setup->allowsEmails !== false) {
				if ($this->setup->userIsLoggedIn === false or !empty ($this->setup->userEmail)) {
					$main_form_footer->appendChild ($this->subscribeLabel ());
				}
			}

			// Create wrapper for form buttons
			$main_form_buttons_wrapper = new HTMLTag ('span');
			$main_form_buttons_wrapper->createAttribute ('class', 'hashover-form-buttons');

			// Create "Login" / "Logout" button element
			if ($this->setup->allowsLogin !== false or $this->setup->userIsLoggedIn === true) {
				$login_button = new HTMLTag ('input', true);
				$login_button->createAttribute ('id', 'hashover-login-button');
				$login_button->createAttribute ('class', 'hashover-submit');
				$login_button->createAttribute ('type', 'submit');

				// Logged in
				if ($this->setup->userIsLoggedIn === true) {
					$login_button->appendAttribute ('class', 'hashover-logout');
					$login_button->createAttribute ('name', 'logout');
					$login_button->createAttribute ('value', $this->locales->locale ('logout', $this->addcslashes));
					$login_button->createAttribute ('title', $this->locales->locale ('logout', $this->addcslashes));
				} else {
					// Logged out
					$login_button->appendAttribute ('class', 'hashover-login');
					$login_button->createAttribute ('name', 'login');
					$login_button->createAttribute ('value', $this->locales->locale ('login', $this->addcslashes));
					$login_button->createAttribute ('title', $this->locales->locale ('login_tip', $this->addcslashes));
				}

				// Add "Login" / "Logout" element to main form footer element
				$main_form_buttons_wrapper->appendChild ($login_button);
			}

			// Create "Post Comment" button element
			$main_post_button = new HTMLTag ('input', true);
			$main_post_button->createAttribute ('id', 'hashover-post-button');
			$main_post_button->createAttribute ('class', 'hashover-submit');
			$main_post_button->appendAttribute ('class', 'hashover-post-button');
			$main_post_button->createAttribute ('type', 'submit');
			$main_post_button->createAttribute ('name', 'post');
			$main_post_button->createAttribute ('value', $this->locales->locale ('post_button', $this->addcslashes));
			$main_post_button->createAttribute ('title', $this->locales->locale ('post_button', $this->addcslashes));

			// Add "Post Comment" element to main form buttons wrapper element
			$main_form_buttons_wrapper->appendChild ($main_post_button);

			// Add main form button wrapper to main form footer element
			$main_form_footer->appendChild ($main_form_buttons_wrapper);

			// Add main form footer to main form element
			$main_form->appendChild ($main_form_footer);

			// Add main form element to primary form wrapper
			$form_section->appendChild ($main_form);

			// Check if form position setting set to 'top'
			if ($this->setup->formPosition === 'top') {
				// Add primary form wrapper to HashOver element
				$hashover_element->appendChild ($form_section);
			}

			if (!empty ($this->popularList)) {
				// Create wrapper element for popular comments
				$popular_section = new HTMLTag ('div', false, false);
				$popular_section->createAttribute ('id', 'hashover-popular-section');

				// Create wrapper element for popular comments title
				$pop_count_wrapper = new HTMLTag ('div');
				$pop_count_wrapper->createAttribute ('class', 'hashover-dashed-title');

				// Create element for popular comments title
				$pop_count_element = new HTMLTag ('span');
				$pop_count_element->createAttribute ('class', 'hashover-title');
				$popPlural = (count ($this->popularList) !== 1) ? 1 : 0;
				$popular_comments_locale = $this->locales->locale ('popular_comments', $this->addcslashes);
				$pop_count_element->innerHTML ($popular_comments_locale[$popPlural]);

				// Add popular comments title element to wrapper element
				$pop_count_wrapper->appendChild ($pop_count_element);

				// Add popular comments title wrapper element to popular comments section
				$popular_section->appendChild ($pop_count_wrapper);

				// Create element for popular comments to appear in
				$popular_comments = new HTMLTag ('div', false, false);
				$popular_comments->createAttribute ('id', 'hashover-top-comments');

				// Add comments to HashOver element
				if (!empty ($this->popularComments)) {
					$popular_comments->innerHTML (trim ($this->popularComments));
				}

				// Add popular comments element to popular comments section
				$popular_section->appendChild ($popular_comments);

				// Add popular comments section to HashOver element
				$hashover_element->appendChild ($popular_section);
			}

			// Create wrapper element for comments
			$comments_section = new HTMLTag ('div', false, false);
			$comments_section->createAttribute ('id', 'hashover-comments-section');

			// Create wrapper element for comment count and sort dropdown menu
			if ($this->readComments->totalCount > 1) {
				$count_sort_wrapper = new HTMLTag ('div');
				$count_sort_wrapper->createAttribute ('class', 'hashover-sort-count');
				$count_sort_wrapper->appendAttribute ('class', 'hashover-dashed-title');

				// Create element for comment count
				$count_element = new HTMLTag ('span');
				$count_element->createAttribute ('id', 'hashover-count');
				$count_element->innerHTML ($this->commentCount);

				// Hide comment count if collapse limit is set at zero
				if ($this->setup->mode === 'javascript') {
					if ($this->setup->collapseLimit <= 0) {
						$count_element->createAttribute ('style', 'display: none;');
					}
				}

				// Add comment count element to wrapper element
				$count_sort_wrapper->appendChild ($count_element);

				// JavaScript mode specific HTML
				if ($this->readComments->totalCount > 2 and $this->setup->mode === 'javascript') {
					// Create wrapper element for sort dropdown menu
					$sort_wrapper = new HTMLTag ('span');
					$sort_wrapper->createAttribute ('id', 'hashover-sort');

					// Hide comment count if collapse limit is set at zero
					if ($this->setup->collapseLimit <= 0) {
						$sort_wrapper->createAttribute ('style', 'display: none;');
					}

					// Create sort dropdown menu element
					$sort_select = new HTMLTag ('select');
					$sort_select->createAttribute ('id', 'hashover-sort-select');
					$sort_select->createAttribute ('name', 'sort');
					$sort_select->createAttribute ('size', '1');

					// Array of select tag sort options
					$sort_options = array (
						array ('value' => 'ascending', 'innerHTML' => $this->locales->locale ('sort_ascend', $this->addcslashes)),
						array ('value' => 'descending', 'innerHTML' => $this->locales->locale ('sort_descend', $this->addcslashes)),
						array ('value' => 'byDate', 'innerHTML' => $this->locales->locale ('sort_bydate', $this->addcslashes)),
						array ('value' => 'byName', 'innerHTML' => $this->locales->locale ('sort_byname', $this->addcslashes)),
						array ('value' => 'byLikes', 'innerHTML' => $this->locales->locale ('sort_bylikes', $this->addcslashes))
					);

					// Create sort options for sort dropdown menu element
					for ($i = 0, $il = count ($sort_options); $i < $il; $i++) {
						$option = new HTMLTag ('option', false, false);
						$option->createAttribute ('value', $sort_options[$i]['value']);
						$option->innerHTML ($sort_options[$i]['innerHTML']);

						// Add sort option element to sort dropdown menu
						$sort_select->appendChild ($option);
					}

					// Create empty option group as spacer
					$spacer_optgroup = new HTMLTag ('optgroup');
					$spacer_optgroup->createAttribute ('label', '&nbsp;');

					// Add spacer option group to sort dropdown menu
					$sort_select->appendChild ($spacer_optgroup);

					// Create option group for threaded sort options
					$threaded_optgroup = new HTMLTag ('optgroup');
					$threaded_optgroup->createAttribute ('label', $this->locales->locale ('threaded', $this->addcslashes));

					// Array of select tag threaded sort options
					$threaded_sort_options = array (
						array ('value' => 'threadedDescending', 'innerHTML' => $this->locales->locale ('sort_descend', $this->addcslashes)),
						array ('value' => 'threadedByDate', 'innerHTML' => $this->locales->locale ('sort_bydate', $this->addcslashes)),
						array ('value' => 'threadedByName', 'innerHTML' => $this->locales->locale ('sort_byname', $this->addcslashes)),
						array ('value' => 'threadedByLikes', 'innerHTML' => $this->locales->locale ('sort_bylikes', $this->addcslashes))
					);

					// Create sort options for sort dropdown menu element
					for ($i = 0, $il = count ($threaded_sort_options); $i < $il; $i++) {
						$option = new HTMLTag ('option', false, false);
						$option->createAttribute ('value', $threaded_sort_options[$i]['value']);
						$option->innerHTML ($threaded_sort_options[$i]['innerHTML']);

						// Add sort option element to threaded option group
						$threaded_optgroup->appendChild ($option);
					}

					// Add threaded sort options group to sort dropdown menu
					$sort_select->appendChild ($threaded_optgroup);

					// Add sort dropdown menu element to sort wrapper element
					$sort_wrapper->appendChild ($sort_select);

					// Add comment count element to wrapper element
					$count_sort_wrapper->appendChild ($sort_wrapper);
				}

				// Add comment count and sort dropdown menu wrapper to comments section
				$comments_section->appendChild ($count_sort_wrapper);
			}

			// Create element that will hold the comments
			$sort_div = new HTMLTag ('div', false, false);
			$sort_div->createAttribute ('id', 'hashover-sort-div');

			// Add comments to HashOver element
			if (!empty ($this->comments)) {
				$sort_div->innerHTML (trim ($this->comments));
			}

			// Add comments element to comments section
			$comments_section->appendChild ($sort_div);

			// Add comments element to HashOver element
			$hashover_element->appendChild ($comments_section);

			// Check if form position setting set to 'bottom'
			if ($this->setup->formPosition === 'bottom') {
				// Add primary form wrapper to HashOver element
				$hashover_element->appendChild ($form_section);
			}

			// Create end links wrapper element
			$end_links_wrapper = new HTMLTag ('div');
			$end_links_wrapper->createAttribute ('id', 'hashover-end-links');

			// Create link back to HashOver homepage (fixme! get a real page!)
			$hashover_home_link = new HTMLTag ('a', false, false);
			$hashover_home_link->createAttribute ('href', 'http://tildehash.com/?page=hashover');
			$hashover_home_link->createAttribute ('id', 'hashover-home-link');
			$hashover_home_link->createAttribute ('target', '_blank');
			$hashover_home_link->innerHTML ('HashOver Comments');

			// Add link back to HashOver homepage to end links wrapper element
			$end_links_wrapper->appendChild ($hashover_home_link);
			$end_links_wrapper->appendInnerHTML (' &#8210;', false);

			if ($this->readComments->totalCount > 1) {
				if ($this->setup->displaysRSSLink === true) {
					if ($this->setup->APIStatus ('rss') !== 'disabled') {
						// Create RSS feed link
						$hashover_rss_link = new HTMLTag ('a', false, false);
						$hashover_rss_link->createAttribute ('href', $this->setup->httpDirectory . '/api/rss.php');
						$hashover_rss_link->appendAttribute ('href', '?url=' . $this->safeURLEncode ($this->setup->pageURL), false);
						$hashover_rss_link->createAttribute ('id', 'hashover-rss-link');
						$hashover_rss_link->createAttribute ('target', '_blank');
						$hashover_rss_link->innerHTML ('RSS Feed');

						// Add RSS feed link to end links wrapper element
						$end_links_wrapper->appendChild ($hashover_rss_link);
						$end_links_wrapper->appendInnerHTML (' &middot;', false);
					}
				}
			}

			// Create link to HashOver source code (fixme! can be done better)
			$hashover_source_link = new HTMLTag ('a', false, false);
			$hashover_source_link->createAttribute ('href', $this->setup->httpDirectory . '/scripts/hashover.php?source');
			$hashover_source_link->createAttribute ('id', 'hashover-source-link');
			$hashover_source_link->createAttribute ('rel', 'hashover-source');
			$hashover_source_link->createAttribute ('target', '_blank');
			$hashover_source_link->innerHTML ('Source Code');

			// Add link to HashOver source code to end links wrapper element
			$end_links_wrapper->appendChild ($hashover_source_link);

			// Create link to HashOver JavaScript source code
			if ($this->setup->mode === 'javascript') {
				// First add a middot character
				$end_links_wrapper->appendInnerHTML (' &middot;', false);

				$hashover_javascript_link = new HTMLTag ('a', false, false);
				$hashover_javascript_link->createAttribute ('href', $this->setup->httpDirectory . '/scripts/hashover-javascript.php');
				$hashover_javascript_link->appendAttribute ('href', '?url=' . $this->safeURLEncode ($this->setup->pageURL), false);
				$hashover_javascript_link->appendAttribute ('href', '&title=' . $this->safeURLEncode ($this->setup->pageTitle), false);

				if (!empty ($_GET['hashover-script'])) {
					$hashover_javascript_link->appendAttribute ('href', '&hashover-script=' . $this->safeURLEncode ($_GET['hashover-script']), false);
				}

				$hashover_javascript_link->createAttribute ('id', 'hashover-javascript-link');
				$hashover_javascript_link->createAttribute ('rel', 'hashover-javascript');
				$hashover_javascript_link->createAttribute ('target', '_blank');
				$hashover_javascript_link->innerHTML ('JavaScript');

				// Add link to HashOver JavaScript source code to end links wrapper element
				$end_links_wrapper->appendChild ($hashover_javascript_link);
			}

			// Add end links wrapper element to HashOver element
			$hashover_element->appendChild ($end_links_wrapper);

			// Return all HTML with the HashOver wrapper element
			if ($hashover_wrapper === true) {
				return $hashover_element->asHTML ();
			}

			// Return just the HashOver wrapper element's innerHTML
			return $hashover_element->innerHTML;
		}

		public
		function replyForm ($permalink = '', $file = '', $subscribed = true)
		{
			// Create HashOver reply form
			$reply_form = new HTMLTag ('div');
			$reply_form->createAttribute ('class', 'hashover-balloon');

			// If avatars are enabled
			if ($this->setup->iconMode !== 'none') {
				// Create avatar element for HashOver reply form
				$reply_avatar = new HTMLTag ('div');
				$reply_avatar->createAttribute ('class', 'hashover-avatar-image');

				// Add count element to avatar element
				$reply_avatar->appendChild ($this->avatar ('+'));

				// Add avatar element to inputs wrapper element
				$reply_form->appendChild ($reply_avatar);
			}

			// Display default login inputs when logged out
			if ($this->setup->userIsLoggedIn === false) {
				$reply_form->appendChild ($this->defaultLoginInputs);
			}

			// Create label element for comment textarea
			if ($this->setup->usesLabels === true) {
				$reply_comment_label = new HTMLTag ('label', false, false);
				$reply_comment_label->createAttribute ('for', 'hashover-reply-comment');
				$reply_comment_label->createAttribute ('class', 'hashover-comment-label');
				$reply_comment_label->innerHTML ($this->locales->locale ('reply_form', $this->addcslashes));

				// Add comment label to form element
				$reply_form->appendChild ($reply_comment_label);
			}

			// Create reply textarea
			$reply_textarea = new HTMLTag ('textarea');
			$reply_textarea->createAttribute ('id', 'hashover-reply-comment');
			$reply_textarea->createAttribute ('class', 'hashover-textarea');
			$reply_textarea->appendAttribute ('class', 'hashover-reply-textarea');
			$reply_textarea->createAttribute ('cols', '62');
			$reply_textarea->createAttribute ('name', 'comment');
			$reply_textarea->createAttribute ('rows', '5');
			$reply_textarea->createAttribute ('title', $this->locales->locale ('form_tip', $this->addcslashes));
			$reply_textarea->createAttribute ('placeholder', $this->locales->locale ('reply_form', $this->addcslashes));

			// Add reply textarea element to form element
			$reply_form->appendChild ($reply_textarea);

			// Create hidden page title input element
			$reply_page_title_input = new HTMLTag ('input', true);
			$reply_page_title_input->createAttribute ('type', 'hidden');
			$reply_page_title_input->createAttribute ('name', 'title');
			$reply_page_title_input->createAttribute ('value', $this->pageTitle);

			// Add hidden page title input element to form element
			$reply_form->appendChild ($reply_page_title_input);

			// Create hidden page URL input element
			$reply_page_url_input = new HTMLTag ('input', true);
			$reply_page_url_input->createAttribute ('type', 'hidden');
			$reply_page_url_input->createAttribute ('name', 'url');
			$reply_page_url_input->createAttribute ('value', $this->pageURL);

			// Add hidden page title input element to form element
			$reply_form->appendChild ($reply_page_url_input);

			// Create hidden reply to input element
			if (!empty ($file)) {
				$reply_to_input = new HTMLTag ('input', true);
				$reply_to_input->createAttribute ('type', 'hidden');
				$reply_to_input->createAttribute ('name', 'reply_to');

				if ($this->setup->mode !== 'php') {
					$reply_to_input->createAttribute ('value', $this->injectVar ($file));
				} else {
					$reply_to_input->createAttribute ('value', $file);
				}

				// Add hidden reply to input element to form element
				$reply_form->appendChild ($reply_to_input);
			}

			// Create element for various messages when needed
			$reply_message = new HTMLTag ('div');
			$reply_message->createAttribute ('id', 'hashover-message-' . $this->injectVar ($permalink));
			$reply_message->createAttribute ('class', 'hashover-message');

			// Add message element to reply form element
			$reply_form->appendChild ($reply_message);

			// Create reply form footer element
			$reply_form_footer = new HTMLTag ('div');
			$reply_form_footer->createAttribute ('class', 'hashover-form-footer');

			// Add checkbox label element to reply form footer element
			if ($this->setup->allowsEmails !== false) {
				if ($this->setup->userIsLoggedIn === false or !empty ($this->setup->userEmail)) {
					$reply_form_footer->appendChild ($this->subscribeLabel ($permalink, 'reply', $subscribed));
				}
			}

			// Create wrapper for form buttons
			$reply_form_buttons_wrapper = new HTMLTag ('span');
			$reply_form_buttons_wrapper->createAttribute ('class', 'hashover-form-buttons');

			// Create "Cancel" link element
			if ($this->setup->usesCancelButtons === true) {
				$reply_cancel_button = new HTMLTag ('a', false, false);
				$reply_cancel_button->createAttribute ('href', $this->setup->parsedURL['path']);

				// Add URL queries to link reference
				if (!empty ($this->setup->URLQueries)) {
					$reply_cancel_button->appendAttribute ('href', '?' . $this->safeURLEncode ($this->setup->URLQueries), false);
				}

				// Add ID attribute with JavaScript variable single quote break out
				if (!empty ($permalink)) {
					$reply_cancel_button->createAttribute ('id', 'hashover-reply-cancel-' . $this->injectVar ($permalink));
				}

				// Continue with other attibutes
				$reply_cancel_button->appendAttribute ('href', '#' . $this->injectVar ($permalink), false);
				$reply_cancel_button->createAttribute ('class', 'hashover-submit');
				$reply_cancel_button->appendAttribute ('class', 'hashover-reply-cancel');
				$reply_cancel_button->createAttribute ('title', $this->locales->locale ('cancel', $this->addcslashes));
				$reply_cancel_button->innerHTML ($this->locales->locale ('cancel', $this->addcslashes));

				// Add "Cancel" link element to reply form footer element
				$reply_form_buttons_wrapper->appendChild ($reply_cancel_button);
			}

			// Create "Post Comment" button element
			$reply_post_button = new HTMLTag ('input', true);

			// Add ID attribute with JavaScript variable single quote break out
			if (!empty ($permalink)) {
				$reply_post_button->createAttribute ('id', 'hashover-reply-post-' . $this->injectVar ($permalink));
			}

			// Continue with other attibutes
			$reply_post_button->createAttribute ('class', 'hashover-submit');
			$reply_post_button->appendAttribute ('class', 'hashover-reply-post');
			$reply_post_button->createAttribute ('type', 'submit');
			$reply_post_button->createAttribute ('name', 'post');
			$reply_post_button->createAttribute ('value', $this->locales->locale ('post_reply', $this->addcslashes));
			$reply_post_button->createAttribute ('title', $this->locales->locale ('post_reply', $this->addcslashes));

			// Add "Post Comment" element to reply form footer element
			$reply_form_buttons_wrapper->appendChild ($reply_post_button);

			// Add reply form buttons wrapper to reply form footer element
			$reply_form_footer->appendChild ($reply_form_buttons_wrapper);

			// Add reply form footer to reply form element
			$reply_form->appendChild ($reply_form_footer);

			return $reply_form->asHTML ();
		}

		public
		function editForm ($permalink, $file, $name = '', $website = '', $body, $subscribed = true)
		{
			// "Edit Comment" locale string
			$edit_comment = $this->locales->locale ('edit_comment', $this->addcslashes);

			// "Save Edit" locale string
			$save_edit = $this->locales->locale ('save_edit', $this->addcslashes);

			// "Cancel" locale string
			$cancel_edit = $this->locales->locale ('cancel', $this->addcslashes);

			// "Delete" locale string
			$delete_comment = $this->locales->locale ('delete', $this->addcslashes);

			// Create wrapper element
			$edit_form = new HTMLTag ('div');

			// Create edit form title element
			$edit_form_title = new HTMLTag ('div', false, false);
			$edit_form_title->createAttribute ('class', 'hashover-title');
			$edit_form_title->appendAttribute ('class', 'hashover-dashed-title');
			$edit_form_title->innerHTML ($edit_comment);

			// Append edit form title to edit form wrapper
			$edit_form->appendChild ($edit_form_title);

			// Append default login inputs
			$edit_form->appendChild ($this->loginInputs (true, $name, $website));

			// Create label element for comment textarea
			if ($this->setup->usesLabels === true) {
				$edit_comment_label = new HTMLTag ('label', false, false);
				$edit_comment_label->createAttribute ('for', 'hashover-edit-comment');
				$edit_comment_label->createAttribute ('class', 'hashover-comment-label');
				$edit_comment_label->innerHTML ($edit_comment);

				// Add comment label to form element
				$edit_form->appendChild ($edit_comment_label);
			}

			// Create edit textarea
			$edit_textarea = new HTMLTag ('textarea', false, false);
			$edit_textarea->createAttribute ('id', 'hashover-edit-comment');
			$edit_textarea->createAttribute ('class', 'hashover-textarea');
			$edit_textarea->appendAttribute ('class', 'hashover-edit-textarea');
			$edit_textarea->createAttribute ('cols', '62');
			$edit_textarea->createAttribute ('name', 'comment');
			$edit_textarea->createAttribute ('rows', '10');
			$edit_textarea->createAttribute ('title', $this->locales->locale ('form_tip', $this->addcslashes));
			$edit_textarea->innerHTML ($this->injectVar ($body));

			// Add edit textarea element to form element
			$edit_form->appendChild ($edit_textarea);

			// Create hidden page title input element
			$edit_page_title_input = new HTMLTag ('input', true);
			$edit_page_title_input->createAttribute ('type', 'hidden');
			$edit_page_title_input->createAttribute ('name', 'title');
			$edit_page_title_input->createAttribute ('value', $this->pageTitle);

			// Add hidden page title input element to form element
			$edit_form->appendChild ($edit_page_title_input);

			// Create hidden page URL input element
			$edit_page_url_input = new HTMLTag ('input', true);
			$edit_page_url_input->createAttribute ('type', 'hidden');
			$edit_page_url_input->createAttribute ('name', 'url');
			$edit_page_url_input->createAttribute ('value', $this->pageURL);

			// Add hidden page title input element to form element
			$edit_form->appendChild ($edit_page_url_input);

			// Create hidden comment file input element
			$edit_file_input = new HTMLTag ('input', true);
			$edit_file_input->createAttribute ('type', 'hidden');
			$edit_file_input->createAttribute ('name', 'file');
			$edit_file_input->createAttribute ('value', $this->injectVar ($file));

			// Add hidden page title input element to form element
			$edit_form->appendChild ($edit_file_input);

			// Create wrapper element for edit form buttons
			$edit_form_footer = new HTMLTag ('div');
			$edit_form_footer->createAttribute ('class', 'hashover-form-footer');

			// Add checkbox label element to edit form buttons wrapper element
			if ($this->setup->allowsEmails !== false) {
				$edit_form_footer->appendChild ($this->subscribeLabel ($permalink, 'edit', $subscribed));
			}

			// Create wrapper for form buttons
			$edit_form_buttons_wrapper = new HTMLTag ('span');
			$edit_form_buttons_wrapper->createAttribute ('class', 'hashover-form-buttons');

			// Create "Cancel" link element
			if ($this->setup->usesCancelButtons === true) {
				$edit_cancel_button = new HTMLTag ('a', false, false);
				$edit_cancel_button->createAttribute ('href', $this->setup->parsedURL['path']);

				// Add URL queries to link reference
				if (!empty ($this->setup->URLQueries)) {
					$edit_cancel_button->appendAttribute ('href', '?' . $this->safeURLEncode ($this->setup->URLQueries), false);
				}

				// Add ID attribute with JavaScript variable single quote break out
				if (!empty ($permalink)) {
					$edit_cancel_button->createAttribute ('id', 'hashover-edit-cancel-' . $this->injectVar ($permalink));
				}

				// Continue with other attibutes
				$edit_cancel_button->appendAttribute ('href', '#' . $this->injectVar ($permalink), false);
				$edit_cancel_button->createAttribute ('class', 'hashover-submit');
				$edit_cancel_button->appendAttribute ('class', 'hashover-edit-cancel');
				$edit_cancel_button->createAttribute ('title', $cancel_edit);
				$edit_cancel_button->innerHTML ($cancel_edit);

				// Add "Cancel" link element to edit form footer element
				$edit_form_buttons_wrapper->appendChild ($edit_cancel_button);
			}

			// Create "Post Comment" button element
			$save_edit_button = new HTMLTag ('input', true);

			// Add ID attribute with JavaScript variable single quote break out
			if (!empty ($permalink)) {
				$save_edit_button->createAttribute ('id', 'hashover-edit-post-' . $this->injectVar ($permalink));
			}

			// Continue with other attibutes
			$save_edit_button->createAttribute ('class', 'hashover-submit');
			$save_edit_button->appendAttribute ('class', 'hashover-edit-post');
			$save_edit_button->createAttribute ('type', 'submit');
			$save_edit_button->createAttribute ('name', 'edit');
			$save_edit_button->createAttribute ('value', $save_edit);
			$save_edit_button->createAttribute ('title', $save_edit);

			// Add "Save Edit" element to edit form footer element
			$edit_form_buttons_wrapper->appendChild ($save_edit_button);

			// Create "Delete" button element
			$delete_button = new HTMLTag ('input', true);

			// Add ID attribute with JavaScript variable single quote break out
			if (!empty ($permalink)) {
				$delete_button->createAttribute ('id', 'hashover-edit-delete-' . $this->injectVar ($permalink));
			}

			// Continue with other attibutes
			$delete_button->createAttribute ('class', 'hashover-submit');
			$delete_button->appendAttribute ('class', 'hashover-edit-delete');
			$delete_button->createAttribute ('type', 'submit');
			$delete_button->createAttribute ('name', 'delete');
			$delete_button->createAttribute ('value', $delete_comment);
			$delete_button->createAttribute ('title', $delete_comment);

			// Add "Delete" element to edit form footer element
			$edit_form_buttons_wrapper->appendChild ($delete_button);

			// Add edit form buttons wrapper to edit form footer element
			$edit_form_footer->appendChild ($edit_form_buttons_wrapper);

			// Add form buttons to edit form element
			$edit_form->appendChild ($edit_form_footer);

			return $edit_form->innerHTML;
		}

		public
		function asJSVar ($html, $var_name, $indent = "\t")
		{
			if ($this->setup->minifiesJavaScript === true) {
				$html = str_replace (array ("\t", PHP_EOL), array ('', ' '), $html);
			} else {
				$html = str_replace ("\t", '\t', $html);
			}

			$html_lines = explode (PHP_EOL, $html);
			$var = '';

			for ($i = 0, $il = count ($html_lines); $i < $il; $i++) {
				if (trim ($html_lines[$i]) === '') {
					continue;
				}

				$var .= $indent;

				if ($i === 0) {
					$var .= 'var ' . $var_name . ' = \'';
				} else {
					$var .= '    ' . $var_name . ' += \'';
				}

				$var .= $html_lines[$i] . '\n\';' . PHP_EOL;
			}

			return $var;
		}
	}

?>
