<?php namespace HashOver;

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

class HTMLOutput
{
	public $setup;
	public $mode;
	public $locale;
	public $avatars;
	public $misc;
	public $login;
	public $commentCounts;
	public $pageTitle;
	public $pageURL;
	public $popularComments;
	public $comments;

	protected $emphasizedField;
	protected $defaultLoginInputs;

	public function __construct (Setup $setup, array $counts)
	{
		$this->setup = $setup;
		$this->mode = $setup->usage['mode'];
		$this->locale = new Locale ($setup);
		$this->login = new Login ($setup);
		$this->avatars = new Avatars ($setup);
		$this->misc = new Misc ($this->mode);
		$this->commentCounts = $counts;
		$this->pageTitle = $this->setup->pageTitle;
		$this->pageURL = $this->setup->pageURL;

		if ($this->mode !== 'php') {
			$this->pageTitle = addcslashes ($this->pageTitle, "\\'");
			$this->pageURL = addcslashes ($this->pageURL, "\\'");
		}

		// Set the field to emphasize after a failed post
		if (!empty ($_COOKIE['failed-on'])) {
			$this->emphasizedField = $_COOKIE['failed-on'];
		}

		$this->defaultLoginInputs = $this->loginInputs ();
	}

	protected function injectVar ($var)
	{
		// Return variable as JavaScript concatenation statement
		if ($this->mode !== 'php') {
			if (!empty ($var)) {
				return '\' + ' . $var . ' + \'';
			}
		}

		// Return variable normally by default
		return $var;
	}

	// Re-encode a URL
	protected function safeURLEncode ($url)
	{
		return urlencode (urldecode ($url));
	}

	// Creates input elements for user login information
	protected function loginInputs ($editForm = false, $name = '', $website = '')
	{
		// Login input attribute information
		$login_input_attributes = array (
			'name' => array (
				'wrapper-class' => 'hashover-name-input',
				'label-class' => 'hashover-name-label',
				'placeholder' => $this->locale->get ('name'),
				'input-id' => 'hashover-main-name',
				'input-type' => 'text',
				'input-name' => 'name',
				'input-title' => $this->locale->text['name-tip'],
				'input-value' => $this->misc->makeXSSsafe ($this->login->name)
			),

			'password' => array (
				'wrapper-class' => 'hashover-password-input',
				'label-class' => 'hashover-password-label',
				'placeholder' => $this->locale->get ('password'),
				'input-id' => 'hashover-main-password',
				'input-type' => 'password',
				'input-name' => 'password',
				'input-title' => $this->locale->text['password-tip'],
				'input-value' => ''
			),

			'email' => array (
				'wrapper-class' => 'hashover-email-input',
				'label-class' => 'hashover-email-label',
				'placeholder' => $this->locale->get ('email'),
				'input-id' => 'hashover-main-email',
				'input-type' => 'email',
				'input-name' => 'email',
				'input-title' => $this->locale->text['email-tip'],
				'input-value' => $this->misc->makeXSSsafe ($this->login->email)
			),

			'website' => array (
				'wrapper-class' => 'hashover-website-input',
				'label-class' => 'hashover-website-label',
				'placeholder' => $this->locale->get ('website'),
				'input-id' => 'hashover-main-website',
				'input-type' => 'url',
				'input-name' => 'website',
				'input-title' => $this->locale->text['website-tip'],
				'input-value' => $this->misc->makeXSSsafe ($this->login->website)
			)
		);

		// Change input values to specified values
		if ($editForm === true) {
			$login_input_attributes['name']['input-value'] = $this->injectVar ($name);
			$login_input_attributes['password']['placeholder'] = $this->locale->get ('confirm-password');
			$login_input_attributes['password']['input-title'] = $this->locale->get ('confirm-password');
			$login_input_attributes['website']['input-value'] = $this->injectVar ($website);
		}

		// Create wrapper element for styling login inputs
		$login_inputs = new HTMLTag ('div', array (
			'class' => 'hashover-inputs'
		));

		// Create and append login input elements to main form inputs wrapper element
		foreach ($login_input_attributes as $field => $attributes) {
			// Skip disabled input tags
			if ($this->setup->fieldOptions[$field] === false) {
				continue;
			}

			// Create cell element for inputs
			$input_cell = new HTMLTag ('div', array (
				'class' => 'hashover-input-cell'
			));

			if ($this->setup->usesLabels === true) {
				// Create label element for input
				$label = new HTMLTag ('label', array (
					'for' => $attributes['input-id'],
					'class' => $attributes['label-class']
				), false);

				// Add label text
				$label->innerHTML ($attributes['placeholder']);

				// Add label to cell element
				$input_cell->appendChild ($label);
			}

			// Create wrapper element for input
			$input_wrapper = new HTMLTag ('div', array (
				'class' => $attributes['wrapper-class']
			));

			// Add a class for indicating a required field
			if ($this->setup->fieldOptions[$field] === 'required') {
				$input_wrapper->appendAttribute ('class', 'hashover-required-input');
			}

			// Add a class for indicating a post failure
			if ($this->emphasizedField === $field) {
				$input_wrapper->appendAttribute ('class', 'hashover-emphasized-input');
			}

			// Create input element
			$input = new HTMLTag ('input', array (
				'id' => $attributes['input-id'],
				'class' => 'hashover-input-info',
				'type' => $attributes['input-type'],
				'name' => $attributes['input-name'],
				'title' => $attributes['input-title'],
				'value' => $attributes['input-value'],
				'placeholder' => $attributes['placeholder']
			), false, true);

			// Add input to wrapper element
			$input_wrapper->appendChild ($input);

			// Add input to cell element
			$input_cell->appendChild ($input_wrapper);

			// Add input cell to main inputs wrapper element
			$login_inputs->appendChild ($input_cell);
		}

		return $login_inputs;
	}

	protected function avatar ($text)
	{
		// If avatars set to images
		if ($this->setup->iconMode === 'image') {
			// Logged in
			if ($this->login->userIsLoggedIn === true) {
				// Image source is avatar image
				$hash = !empty ($this->login->email) ? md5 (mb_strtolower (trim ($this->login->email))) : '';
				$avatar_src = $this->avatars->getGravatar ($hash);
			} else {
				// Logged out
				// Image source is local default image
				$avatar_src = $this->setup->httpImages . '/first-comment.' . $this->setup->imageFormat;
			}

			// Create avatar image element
			$avatar = new HTMLTag ('div', array (), false);
			$background_image = 'background-image: url(\'' . $avatar_src . '\');';

			if ($this->mode !== 'php') {
				$avatar->createAttribute ('style', addcslashes ($background_image, "\\'"));
			} else {
				$avatar->createAttribute ('style', $background_image);
			}
		} else {
			// Avatars set to count
			// Create element displaying comment number user will be
			$avatar = new HTMLTag ('span', array (), false);
			$avatar->innerHTML ($text);
		}

		return $avatar;
	}

	// Creates a wrapper element for each comment
	public function commentWrapper ($permalink, $classes = '', $innerHTML = '')
	{
		$comment_wrapper = new HTMLTag ('div', array (
			'id' => $this->injectVar ($permalink),
			'class' => 'hashover-comment'
		), false);

		if ($this->mode !== 'php') {
			$comment_wrapper->appendAttribute ('class', $this->injectVar ($classes), false);
			$comment_wrapper->innerHTML ($this->injectVar ($innerHTML));

			return $comment_wrapper->asHTML ();
		}

		return $comment_wrapper;
	}

	// Creates parent element to name element
	public function nameWrapper ($nameLink, $nameClass)
	{
		$name_wrapper = new HTMLTag ('span', array (), false);
		$name_wrapper->createAttribute ('class', 'hashover-comment-name');
		$name_wrapper->appendAttribute ('class', $this->injectVar ($nameClass));
		$name_wrapper->innerHTML ($this->injectVar ($nameLink));

		return $name_wrapper->asHTML ();
	}

	// Creates name hyperlink/span element
	public function nameElement ($element, $name, $permalink, $href = '')
	{
		switch ($element) {
			case 'a': {
				$name_link = new HTMLTag ('a', array (
					'href' => $this->injectVar ($href),
					'rel' => 'noopener noreferrer',
					'target' => '_blank'
				), false);

				break;
			}

			case 'span': {
				$name_link = new HTMLTag ('span', array (), false);
				break;
			}
		}

		$name_link->createAttribute ('class', 'hashover-name-' . $this->injectVar ($permalink));
		$name_link->innerHTML ($this->injectVar ($name));

		return $name_link->asHTML ();
	}

	// Creates "Top of Thread" hyperlink element
	public function threadLink ($permalink, $parent, $name)
	{
		$thread_link = new HTMLTag ('a', array (
			'href' => '#' . $this->injectVar ($parent),
			'id' => 'hashover-thread-link-' . $this->injectVar ($permalink),
			'class' => 'hashover-thread-link',
			'title' => $this->locale->get ('thread-tip')
		), false);

		$thread_locale = $this->locale->get ('thread');
		$inner_html = sprintf ($thread_locale, $this->injectVar ($name));
		$thread_link->innerHTML ($inner_html);

		return $thread_link->asHTML ();
	}

	// Creates hyperlink with URL queries to link reference
	protected function queryLink ($href = '', array $queries = array ())
	{
		$link = new HTMLTag ('a', array ('href' => $href), false);
		$queries = array_merge ($this->setup->URLQueryList, $queries);

		// Add URL queries to link reference
		if (!empty ($queries)) {
			$link->appendAttribute ('href', '?' . implode ('&', $queries), false);
		}

		return $link;
	}

	// Creates date/permalink hyperlink element
	public function dateLink ($permalink, $date)
	{
		$date_link = new HTMLTag ('a', array (
			'href' => '#' . $this->injectVar ($permalink),
			'class' => 'hashover-date-permalink',
			'title' => 'Permalink'
		), false);

		$date_link->innerHTML ($this->injectVar ($date));

		return $date_link->asHTML ();
	}

	// Creates element to hold a count of likes/dislikes each comment has
	public function likeCount ($type, $permalink, $text)
	{
		// CSS class
		$class = 'hashover-' . $type;

		// Create element
		$count = new HTMLTag ('span', array (
			'id' => $class . '-' . $this->injectVar ($permalink),
			'class' => $class
		), false);

		// Count text
		$count->innerHTML ($this->injectVar ($text));

		return $count->asHTML ();
	}

	// Creates "Like" hyperlink element
	public function likeLink ($type, $permalink, $class, $title, $text)
	{
		$link = new HTMLTag ('a', array (
			'href' => '#',
			'id' => 'hashover-' . $type . '-' . $this->injectVar ($permalink),
			'class' => $this->injectVar ($class),
			'title' => $this->injectVar ($title)
		), false);

		$link->innerHTML ($this->injectVar ($text));

		return $link->asHTML ();
	}

	// Creates a form control hyperlink element
	public function formLink ($type, $permalink, $class = '', $title = '')
	{
		$form = 'hashover-' . $type;
		$permalink = $this->injectVar ($permalink);
		$link = $this->queryLink ('', array ($form . '=' . $permalink));
		$title_locale = ($type === 'reply') ? 'reply-to-comment' : 'edit-your-comment';

		// Create more attributes
		$link->createAttributes (array (
			'id' => $form. '-link-' . $permalink,
			'class' => 'hashover-comment-' . $type,
			'title' => $this->locale->get ($title_locale)
		));

		// Append href attribute
		$link->appendAttribute ('href', '#' . $form . '-' . $permalink, false);

		// Append attributes
		if ($type === 'reply') {
			$link->appendAttributes (array (
				'class' => $this->injectVar ($class),
				'title' => '- ' . $this->injectVar ($title)
			));
		}

		// Add link text
		$link->innerHTML ($this->locale->get ($type));

		return $link->asHTML ();
	}

	// Creates "Cancel" hyperlink element
	public function cancelLink ($permalink, $for, $class)
	{
		$cancel_link = $this->queryLink ($this->setup->filePath);
		$cancel_locale = $this->locale->get ('cancel');

		// Create more attributes
		$cancel_link->createAttributes (array (
			'class' => 'hashover-comment-' . $for,
			'title' => $cancel_locale
		));

		// Append attributes
		$cancel_link->appendAttribute ('href', '#' . $permalink, false);
		$cancel_link->appendAttribute ('class', $class);

		// Add "Cancel" hyperlink text
		$cancel_link->innerHTML ($cancel_locale);

		return $cancel_link->asHTML ();
	}

	public function userAvatar ($text, $href, $src)
	{
		// If avatars set to images
		if ($this->setup->iconMode !== 'none') {
			// Create wrapper element for avatar image
			$avatar_wrapper = new HTMLTag ('span', array (
				'class' => 'hashover-avatar'
			), false);

			if ($this->setup->iconMode === 'image') {
				if ($this->mode !== 'php') {
					$background_image = 'background-image: url(\\\'' . $this->injectVar ($src) . '\\\');';
				} else {
					$background_image = 'background-image: url(\'' . $this->injectVar ($src) . '\');';
				}

				// Create avatar image element
				$comments_avatar = new HTMLTag ('div', array (
					'style' => $background_image
				), false);
			} else {
				// Avatars set to count
				// Create element displaying comment number user will be
				$comments_avatar = new HTMLTag ('a', array (
					'href' => '#' . $this->injectVar ($href),
					'title' => 'Permalink'
				), false);

				// Add count text
				$comments_avatar->innerHTML ($this->injectVar ($text));
			}

			// Add comments avatar to avatar image wrapper element
			$avatar_wrapper->appendChild ($comments_avatar);

			return $avatar_wrapper->asHTML ();
		}
	}

	protected function subscribeLabel ($id = '', $class = 'main', $checked = true)
	{
		// Create subscribe checkbox label element
		$subscribe_label = new HTMLTag ('label', array (
			'for' => 'hashover-subscribe',
			'class' => 'hashover-' . $class . '-label',
			'title' => $this->locale->get ('subscribe-tip')
		));

		if (!empty ($id)) {
			$subscribe_label->appendAttribute ('for', '-' . $this->injectVar ($id), false);
		}

		// Create subscribe element checkbox
		$subscribe = new HTMLTag ('input', array (
			'id' => 'hashover-subscribe',
			'type' => 'checkbox',
			'name' => 'subscribe'
		), false, true);

		if (!empty ($id)) {
			$subscribe->appendAttribute ('id', '-' . $this->injectVar ($id), false);
		}

		// Check checkbox
		if ($checked === true) {
			$subscribe->createAttribute ('checked', 'true');
		}

		// Add subscribe checkbox element to subscribe checkbox label element
		$subscribe_label->appendChild ($subscribe);

		// Add text to subscribe checkbox label element
		$subscribe_label->appendInnerHTML ($this->locale->get ('subscribe'));

		return $subscribe_label;
	}

	public function pageInfoFields ($form)
	{
		// Create hidden page URL input element
		$url_input = new HTMLTag ('input', array (
			'type' => 'hidden',
			'name' => 'url',
			'value' => $this->pageURL
		), false, true);

		// Add hidden page URL input element to form element
		$form->appendChild ($url_input);

		// Create hidden page title input element
		$title_input = new HTMLTag ('input', array (
			'type' => 'hidden',
			'name' => 'title',
			'value' => $this->pageTitle
		), false, true);

		// Add hidden page title input element to form element
		$form->appendChild ($title_input);

		// Check if the script is being remotely accessed
		if ($this->setup->remoteAccess === true) {
			// Create hidden input element indicating remote access
			$remote_access_input = new HTMLTag ('input', array (
				'type' => 'hidden',
				'name' => 'remote-access',
				'value' => 'true'
			), false, true);

			// Add remote access input element to form element
			$form->appendChild ($remote_access_input);
		}
	}

	public function initialHTML (array $popularList, $hashover_wrapper = true)
	{
		// Create element that HashOver comments will appear in
		$hashover_element = new HTMLTag ('div', array (
			'id' => 'hashover'
		), false);

		// Add class for differentiating desktop and mobile styling
		if ($this->setup->isMobile === true) {
			$hashover_element->appendAttribute ('class', 'hashover-mobile');
		} else {
			$hashover_element->appendAttribute ('class', 'hashover-desktop');
		}

		// Add class to indicate user login status
		if ($this->login->userIsLoggedIn === true) {
			$hashover_element->appendAttribute ('class', 'hashover-logged-in');
		} else {
			$hashover_element->appendAttribute ('class', 'hashover-logged-out');
		}

		// Create element for jump anchor
		$jump_anchor = new HTMLTag ('span', array (
			'id' => 'comments'
		));

		// Add jump anchor to HashOver element
		$hashover_element->appendChild ($jump_anchor);

		// Create primary form wrapper element
		$form_section = new HTMLTag ('div', array (
			'id' => 'hashover-form-section'
		), false);

		// Hide primary form wrapper if comments are to be initially hidden
		if ($this->setup->collapsesUI === true) {
			$form_section->createAttribute ('style', 'display: none;');
		}

		// Create element for "Post Comment" title
		$post_title = new HTMLTag ('span', array (
			'class' => 'hashover-title'
		));

		// Append attributes
		$post_title->appendAttribute ('class', 'hashover-main-title');
		$post_title->appendAttribute ('class', 'hashover-dashed-title');

		// "Post Comment on" locale string
		$post_comment_on = $this->locale->get ('post-comment-on');

		// Add optional "on <page title>" to "Post Comment" title
		if ($this->setup->displaysTitle === false or empty ($this->pageTitle)) {
			$post_title->innerHTML ($post_comment_on[0]);
		} else {
			$post_title->innerHTML (sprintf ($post_comment_on[1], $this->pageTitle));
		}

		// Add "Post Comment" element to primary form wrapper
		$form_section->appendChild ($post_title);

		// Create element for various messages when needed
		$message_element = new HTMLTag ('div', array (
			'id' => 'hashover-message',
			'class' => 'hashover-title hashover-message'
		));

		// Check if message cookie is set
		if (!empty ($_COOKIE['message']) or !empty ($_COOKIE['error'])) {
			if ($this->mode === 'php') {
				$message_element->appendAttribute ('class', 'hashover-message-open');
			}

			if (!empty ($_COOKIE['message'])) {
				$message = $this->misc->makeXSSsafe ($_COOKIE['message']);
			} else {
				$message = $this->misc->makeXSSsafe ($_COOKIE['error']);
				$message_element->appendAttribute ('class', 'hashover-message-error');
			}

			// If so, put current message into message element
			$message_element->innerHTML ($message);
		}

		// Add message element to primary form wrapper
		$form_section->appendChild ($message_element);

		// Create main HashOver form
		$main_form = new HTMLTag ('form', array (
			'id' => 'hashover-form',
			'class' => 'hashover-balloon',
			'name' => 'hashover-form',
			'action' => $this->setup->httpScripts . '/postcomments.php',
			'method' => 'post'
		));

		// Create wrapper element for styling inputs
		$main_inputs = new HTMLTag ('div', array (
			'class' => 'hashover-inputs'
		));

		// If avatars are enabled
		if ($this->setup->iconMode !== 'none') {
			// Create avatar element for main HashOver form
			$main_avatar = new HTMLTag ('div', array (
				'class' => 'hashover-avatar-image'
			));

			// Add count element to avatar element
			$main_avatar->appendChild ($this->avatar ($this->commentCounts['primary']));

			// Add avatar element to inputs wrapper element
			$main_inputs->appendChild ($main_avatar);
		}

		// Logged in
		if ($this->login->userIsLoggedIn === true) {
			if (!empty ($this->login->name)) {
				$user_name = $this->misc->makeXSSsafe ($this->login->name);
			} else {
				$user_name = $this->setup->defaultName;
			}

			$user_website = $this->misc->makeXSSsafe ($this->login->website);
			$name_class = 'hashover-name-plain';
			$is_twitter = false;

			// Check if user's name is a Twitter handle
			if ($user_name[0] === '@') {
				$user_name = mb_substr ($user_name, 1);
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
			$main_form_column_spanner = new HTMLTag ('div', array (), false);
			$main_form_column_spanner->createAttribute ('class', 'hashover-comment-name');
			$main_form_column_spanner->appendAttribute ('class', 'hashover-top-name');

			// Check if user gave website
			if (!empty ($user_website)) {
				if ($is_twitter === false) {
					$name_class = 'hashover-name-website';
				}

				// Create link to user's website
				$main_form_hyperlink = new HTMLTag ('a', array (
					'href' => $user_website,
					'rel' => 'noopener noreferrer',
					'target' => '_blank'
				), false);

				// Add user's name to hyperlink
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
		$required_fields = new HTMLTag ('div', array (
			'id' => 'hashover-requiredFields'
		));

		$fake_fields = array (
			'summary' => 'text',
			'age' => 'hidden',
			'lastname' => 'text',
			'address' => 'text',
			'zip' => 'hidden',
		);

		// Create and append fake input elements to fake required fields
		foreach ($fake_fields as $name => $type) {
			$fake_input = new HTMLTag ('input', array (
				'type' => $type,
				'name' => $name,
				'value' => ''
			), false, true);

			// Add fake summary input element to fake required fields
			$required_fields->appendInnerHTML ($fake_input->asHTML ());
		}

		// Add fake input elements to form element
		$main_form->appendChild ($required_fields);

		// Comment form placeholder text
		$comment_form = $this->locale->get ('comment-form');

		// Create label element for comment textarea
		if ($this->setup->usesLabels === true) {
			$main_comment_label = new HTMLTag ('label', array (
				'for' => 'hashover-main-comment',
				'class' => 'hashover-comment-label'
			), false);

			// Add label text
			$main_comment_label->innerHTML ($comment_form);

			// Add comment label to form element
			$main_form->appendChild ($main_comment_label);
		}

		// Create main textarea
		$main_textarea = new HTMLTag ('textarea', array (
			'id' => 'hashover-main-comment',
			'class' => 'hashover-textarea hashover-main-textarea',
			'cols' => '63',
			'name' => 'comment',
			'rows' => '5',
			'title' => $this->locale->get ('form-tip'),
			'placeholder' => $comment_form
		));

		// Add a class for indicating a post failure
		if ($this->emphasizedField === 'comment') {
			$main_textarea->appendAttribute ('class', 'hashover-emphasized-input');

			// If the comment was a reply, have the main textarea use the reply textarea locale
			if (!empty ($_COOKIE['replied'])) {
				$reply_form_placeholder = $this->locale->get ('reply-form');
				$main_textarea->createAttribute ('placeholder', $reply_form_placeholder);
			}
		}

		// Add main textarea element to form element
		$main_form->appendChild ($main_textarea);

		// Add page info fields to main form
		$this->pageInfoFields ($main_form);

		// Create hidden reply to input element
		if (!empty ($_COOKIE['replied'])) {
			$reply_to_input = new HTMLTag ('input', array (
				'type' => 'hidden',
				'name' => 'reply-to',
				'value' => $this->misc->makeXSSsafe ($_COOKIE['replied'])
			), false, true);

			// Add hidden reply to input element to form element
			$main_form->appendChild ($reply_to_input);
		}

		// Create wrapper element for main form footer
		$main_form_footer = new HTMLTag ('div', array (
			'class' => 'hashover-form-footer'
		));

		// Add checkbox label element to main form buttons wrapper element
		if ($this->setup->fieldOptions['email'] !== false) {
			if ($this->login->userIsLoggedIn === false or !empty ($this->login->email)) {
				$main_form_footer->appendChild ($this->subscribeLabel ());
			}
		}

		// Create wrapper for form buttons
		$main_form_buttons_wrapper = new HTMLTag ('span', array (
			'class' => 'hashover-form-buttons'
		));

		// Create "Login" / "Logout" button element
		if ($this->setup->allowsLogin !== false or $this->login->userIsLoggedIn === true) {
			$login_button = new HTMLTag ('input', array (
				'id' => 'hashover-login-button',
				'class' => 'hashover-submit',
				'type' => 'submit'
			), false, true);

			// Check login state
			if ($this->login->userIsLoggedIn === true) {
				// Logged in
				$login_button->appendAttribute ('class', 'hashover-logout');
				$logout_locale = $this->locale->get ('logout');

				// Create logged in attributes
				$login_button->createAttributes (array (
					'name' => 'logout',
					'value' => $logout_locale,
					'title' => $logout_locale
				));
			} else {
				// Logged out
				$login_button->appendAttribute ('class', 'hashover-login');

				// Create logged out attributes
				$login_button->createAttributes (array (
					'name' => 'login',
					'value' => $this->locale->get ('login'),
					'title' => $this->locale->get ('login-tip')
				));
			}

			// Add "Login" / "Logout" element to main form footer element
			$main_form_buttons_wrapper->appendChild ($login_button);
		}

		// Post button locale
		$post_button = $this->locale->get ('post-button');

		// Create "Post Comment" button element
		$main_post_button = new HTMLTag ('input', array (
			'id' => 'hashover-post-button',
			'class' => 'hashover-submit hashover-post-button',
			'type' => 'submit',
			'name' => 'post',
			'value' => $post_button,
			'title' => $post_button
		), false, true);

		// Add "Post Comment" element to main form buttons wrapper element
		$main_form_buttons_wrapper->appendChild ($main_post_button);

		// Add main form button wrapper to main form footer element
		$main_form_footer->appendChild ($main_form_buttons_wrapper);

		// Add main form footer to main form element
		$main_form->appendChild ($main_form_footer);

		// Add main form element to primary form wrapper
		$form_section->appendChild ($main_form);

		// Check if form position setting set to 'top'
		if ($this->setup->formPosition !== 'bottom') {
			// Add primary form wrapper to HashOver element
			$hashover_element->appendChild ($form_section);
		}

		if (!empty ($popularList)) {
			// Create wrapper element for popular comments
			$popular_section = new HTMLTag ('div', array (
				'id' => 'hashover-popular-section'
			), false);

			// Create wrapper element for popular comments title
			$pop_count_wrapper = new HTMLTag ('div', array (
				'class' => 'hashover-dashed-title'
			));

			// Create element for popular comments title
			$pop_count_element = new HTMLTag ('span', array (
				'class' => 'hashover-title'
			));

			// Add popular comments title text
			$popPlural = (count ($popularList) !== 1) ? 1 : 0;
			$popular_comments_locale = $this->locale->get ('popular-comments');
			$pop_count_element->innerHTML ($popular_comments_locale[$popPlural]);

			// Add popular comments title element to wrapper element
			$pop_count_wrapper->appendChild ($pop_count_element);

			// Add popular comments title wrapper element to popular comments section
			$popular_section->appendChild ($pop_count_wrapper);

			// Create element for popular comments to appear in
			$popular_comments = new HTMLTag ('div', array (
				'id' => 'hashover-top-comments'
			), false);

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
		$comments_section = new HTMLTag ('div', array (
			'id' => 'hashover-comments-section'
		), false);

		// Create wrapper element for comment count and sort dropdown menu
		$count_sort_wrapper = new HTMLTag ('div', array (
			'id' => 'hashover-count-wrapper',
			'class' => 'hashover-sort-count hashover-dashed-title'
		));

		// Hide wrapper if comments are to be initially hidden
		if ($this->setup->collapsesUI === true) {
			$count_sort_wrapper->createAttribute ('style', 'display: none;');
		}

		// Create element for comment count
		$count_element = new HTMLTag ('span', array (
			'id' => 'hashover-count'
		));

		if ($this->commentCounts['total'] > 1) {
			// Hide comment count if collapse limit is set at zero
			if ($this->mode === 'javascript') {
				if ($this->setup->collapseLimit <= 0) {
					$count_element->createAttribute ('style', 'display: none;');
				}
			}

			// Add comment count to comment count element
			$count_element->innerHTML ($this->commentCounts['show-count']);
		}

		// Add comment count element to wrapper element
		$count_sort_wrapper->appendChild ($count_element);

		// JavaScript mode specific HTML
		if ($this->commentCounts['total'] > 2 and $this->mode === 'javascript') {
			// Create wrapper element for sort dropdown menu
			$sort_wrapper = new HTMLTag ('span', array (
				'id' => 'hashover-sort',
				'class' => 'hashover-select-wrapper'
			));

			// Hide comment count if collapse limit is set at zero
			if ($this->setup->collapseLimit <= 0) {
				$sort_wrapper->createAttribute ('style', 'display: none;');
			}

			// Create sort dropdown menu element
			$sort_select = new HTMLTag ('select', array (
				'id' => 'hashover-sort-select',
				'name' => 'sort',
				'size' => '1'
			));

			// Array of select tag sort options
			$sort_options = array (
				array ('value' => 'ascending', 'innerHTML' => $this->locale->get ('sort-ascending')),
				array ('value' => 'descending', 'innerHTML' => $this->locale->get ('sort-descending')),
				array ('value' => 'by-date', 'innerHTML' => $this->locale->get ('sort-by-date')),
				array ('value' => 'by-likes', 'innerHTML' => $this->locale->get ('sort-by-likes')),
				array ('value' => 'by-replies', 'innerHTML' => $this->locale->get ('sort-by-replies')),
				array ('value' => 'by-name', 'innerHTML' => $this->locale->get ('sort-by-name'))
			);

			// Create sort options for sort dropdown menu element
			for ($i = 0, $il = count ($sort_options); $i < $il; $i++) {
				$option = new HTMLTag ('option', array (
					'value' => $sort_options[$i]['value']
				), false);

				// Add option text
				$option->innerHTML ($sort_options[$i]['innerHTML']);

				// Add sort option element to sort dropdown menu
				$sort_select->appendChild ($option);
			}

			// Create empty option group as spacer
			$spacer_optgroup = new HTMLTag ('optgroup', array (
				'label' => '&nbsp;'
			));

			// Add spacer option group to sort dropdown menu
			$sort_select->appendChild ($spacer_optgroup);

			// Create option group for threaded sort options
			$threaded_optgroup = new HTMLTag ('optgroup', array (
				'label' => $this->locale->get ('sort-threads')
			));

			// Array of select tag threaded sort options
			$threaded_sort_options = array (
				array ('value' => 'threaded-descending', 'innerHTML' => $this->locale->get ('sort-descending')),
				array ('value' => 'threaded-by-date', 'innerHTML' => $this->locale->get ('sort-by-date')),
				array ('value' => 'threaded-by-likes', 'innerHTML' => $this->locale->get ('sort-by-likes')),
				array ('value' => 'by-popularity', 'innerHTML' => $this->locale->get ('sort-by-popularity')),
				array ('value' => 'by-discussion', 'innerHTML' => $this->locale->get ('sort-by-discussion')),
				array ('value' => 'threaded-by-name', 'innerHTML' => $this->locale->get ('sort-by-name'))
			);

			// Create sort options for sort dropdown menu element
			for ($i = 0, $il = count ($threaded_sort_options); $i < $il; $i++) {
				$option = new HTMLTag ('option', array (
					'value' => $threaded_sort_options[$i]['value']
				), false);

				// Add option text
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

		// Create element that will hold the comments
		$sort_div = new HTMLTag ('div', array (
			'id' => 'hashover-sort-div'
		), false);

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
		$end_links_wrapper = new HTMLTag ('div', array (
			'id' => 'hashover-end-links'
		));

		// Hide end links wrapper if comments are to be initially hidden
		if ($this->setup->collapsesUI === true) {
			$end_links_wrapper->createAttribute ('style', 'display: none;');
		}

		// Create link back to HashOver homepage (fixme! get a real page!)
		$homepage_link = new HTMLTag ('a', array (
			'href' => 'http://tildehash.com/?page=hashover',
			'id' => 'hashover-home-link',
			'target' => '_blank'
		), false);

		// Add homepage hyperlink text
		$homepage_link->innerHTML ($this->locale->get ('hashover-comments'));

		// Add link back to HashOver homepage to end links wrapper element
		$end_links_wrapper->innerHTML ($homepage_link->asHTML () . ' &#8210;');

		// End links array
		$end_links = array ();

		if ($this->commentCounts['total'] > 1) {
			if ($this->setup->displaysRSSLink === true
			    and $this->setup->APIStatus ('rss') !== 'disabled')
			{
				// Create RSS feed link
				$rss_link = new HTMLTag ('a', array (), false);
				$rss_link->createAttribute ('href', $this->setup->httpRoot . '/api/rss.php');
				$rss_link->appendAttribute ('href', '?url=' . $this->safeURLEncode ($this->setup->pageURL), false);

				$rss_link->createAttributes (array (
					'id' => 'hashover-rss-link',
					'target' => '_blank'
				));

				// Add RSS hyperlink text
				$rss_link->innerHTML ($this->locale->get ('rss-feed'));

				// Add RSS hyperlink to end links array
				$end_links[] = $rss_link->asHTML ();
			}
		}

		// Create link to HashOver source code (fixme! can be done better)
		$source_link = new HTMLTag ('a', array (
			'href' => $this->setup->httpScripts . '/hashover.php?source',
			'id' => 'hashover-source-link',
			'rel' => 'hashover-source',
			'target' => '_blank'
		), false);

		// Add source code hyperlink text
		$source_link->innerHTML ($this->locale->get ('source-code'));

		// Add source code hyperlink to end links array
		$end_links[] = $source_link->asHTML ();

		if ($this->mode === 'javascript') {
			// Create link to HashOver JavaScript source code
			$javascript_link = new HTMLTag ('a', array (
				'href' => $this->setup->httpScripts . '/hashover-javascript.php',
				'id' => 'hashover-javascript-link',
				'rel' => 'hashover-javascript',
				'target' => '_blank'
			), false);

			// Append attributes
			$javascript_link->appendAttribute ('href', '?url=' . $this->safeURLEncode ($this->setup->pageURL), false);
			$javascript_link->appendAttribute ('href', '&title=' . $this->safeURLEncode ($this->setup->pageTitle), false);

			if (!empty ($_GET['hashover-script'])) {
				$hashover_script = $this->misc->makeXSSsafe ($this->safeURLEncode ($_GET['hashover-script']));
				$javascript_link->appendAttribute ('href', '&hashover-script=' . $hashover_script, false);
			}

			// Add JavaScript code hyperlink text
			$javascript_link->innerHTML ('JavaScript');

			// Add JavaScript hyperlink to end links array
			$end_links[] = $javascript_link->asHTML ();
		}

		// Add end links to end links wrapper element
		$end_links_wrapper->appendInnerHTML (implode (' &middot;' . PHP_EOL, $end_links));

		// Add end links wrapper element to HashOver element
		$hashover_element->appendChild ($end_links_wrapper);

		// Return all HTML with the HashOver wrapper element
		if ($hashover_wrapper === true) {
			return $hashover_element->asHTML ();
		}

		// Return just the HashOver wrapper element's innerHTML
		return $hashover_element->innerHTML;
	}

	public function cancelButton ($type, $permalink)
	{
		$permalink = $this->injectVar ($permalink);
		$cancel_button = $this->queryLink ($this->setup->filePath);
		$class = 'hashover-' . $type . '-cancel';
		$action_type = 'hashover-' . $type;
		$cancel_locale = $this->locale->get ('cancel');

		// Add ID attribute with JavaScript variable single quote break out
		if (!empty ($permalink)) {
			$cancel_button->createAttribute ('id', $class . '-' . $permalink);
		}

		// Append href attribute
		$cancel_button->appendAttribute ('href', '#' . $permalink, false);

		// Create more attributes
		$cancel_button->createAttributes (array (
			'class' => 'hashover-submit ' . $class,
			'title' => $cancel_locale
		));

		// Add "Cancel" hyperlink text
		$cancel_button->innerHTML ($cancel_locale);

		return $cancel_button;
	}

	public function replyForm ($permalink = '', $file = '', $subscribed = true)
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
			$reply_form->appendChild ($this->defaultLoginInputs);
		}

		// Reply form locale
		$reply_form_placeholder = $this->locale->get ('reply-form');

		// Create label element for comment textarea
		if ($this->setup->usesLabels === true) {
			$reply_comment_label = new HTMLTag ('label', array (
				'for' => 'hashover-reply-comment',
				'class' => 'hashover-comment-label'
			), false);

			// Add comment label text
			$reply_comment_label->innerHTML ($reply_form_placeholder);

			// Add comment label to form element
			$reply_form->appendChild ($reply_comment_label);
		}

		// Create reply textarea
		$reply_textarea = new HTMLTag ('textarea', array (
			'id' => 'hashover-reply-comment',
			'class' => 'hashover-textarea hashover-reply-textarea',
			'cols' => '62',
			'name' => 'comment',
			'rows' => '5',
			'title' => $this->locale->get ('form-tip'),
			'placeholder' => $reply_form_placeholder
		));

		// Add reply textarea element to form element
		$reply_form->appendChild ($reply_textarea);

		// Add page info fields to reply form
		$this->pageInfoFields ($reply_form);

		// Create hidden reply to input element
		if (!empty ($file)) {
			$reply_to_input = new HTMLTag ('input', array (
				'type' => 'hidden',
				'name' => 'reply-to',
				'value' => $this->injectVar ($file)
			), false, true);

			// Add hidden reply to input element to form element
			$reply_form->appendChild ($reply_to_input);
		}

		// Create element for various messages when needed
		$reply_message = new HTMLTag ('div', array (
			'id' => 'hashover-reply-message-' . $this->injectVar ($permalink),
			'class' => 'hashover-message'
		));

		// Add message element to reply form element
		$reply_form->appendChild ($reply_message);

		// Create reply form footer element
		$reply_form_footer = new HTMLTag ('div', array (
			'class' => 'hashover-form-footer'
		));

		// Add checkbox label element to reply form footer element
		if ($this->setup->fieldOptions['email'] !== false) {
			if ($this->login->userIsLoggedIn === false or !empty ($this->login->email)) {
				$reply_form_footer->appendChild ($this->subscribeLabel ($permalink, 'reply', $subscribed));
			}
		}

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
		$post_reply = $this->locale->get ('post-reply');

		// Add ID attribute with JavaScript variable single quote break out
		if (!empty ($permalink)) {
			$reply_post_button->createAttribute ('id', 'hashover-reply-post-' . $this->injectVar ($permalink));
		}

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

	public function editForm ($permalink, $file, $name = '', $website = '', $body, $status = '', $subscribed = true)
	{
		// "Edit Comment" locale string
		$edit_comment = $this->locale->get ('edit-comment');

		// "Save Edit" locale string
		$save_edit = $this->locale->get ('save-edit');

		// "Cancel" locale string
		$cancel_edit = $this->locale->get ('cancel');

		// "Delete" locale string
		$delete_comment = $this->locale->get ('delete');

		// Create wrapper element
		$edit_form = new HTMLTag ('div');

		// Create edit form title element
		$edit_form_title = new HTMLTag ('div', array (
			'class' => 'hashover-title hashover-dashed-title'
		), false);

		// Add edit form title text
		$edit_form_title->innerHTML ($edit_comment);

		if ($this->login->userIsAdmin === true) {
			// Create status dropdown wrapper element
			$edit_status_wrapper = new HTMLTag ('span', array (
				'class' => 'hashover-edit-status'
			), false);

			// Add status dropdown text
			$edit_status_wrapper->innerHTML ($this->locale->get ('status'));

			// Create select wrapper element
			$edit_status_select_wrapper = new HTMLTag ('span', array (
				'class' => 'hashover-select-wrapper'
			), false);

			// Status dropdown menu options
			$status_options = array (
				'approved' => $this->locale->get ('status-approved'),
				'pending' => $this->locale->get ('status-pending'),
				'deleted' => $this->locale->get ('status-deleted')
			);

			// Create status dropdown menu element
			$edit_status_dropdown = new HTMLTag ('select', array (
				'id' => 'hashover-edit-status-' . $this->injectVar ($permalink),
				'name' => 'status',
				'size' => '1'
			));

			foreach ($status_options as $value => $inner_html) {
				// Create status dropdown menu option element
				$edit_status_option = new HTMLTag ('option', array (
					'value' => $value
				));

				// Add status dropdown menu option text
				$edit_status_option->innerHTML ($inner_html);

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
		$edit_form->appendChild ($this->loginInputs (true, $name, $website));

		// Create label element for comment textarea
		if ($this->setup->usesLabels === true) {
			$edit_comment_label = new HTMLTag ('label', array (
				'for' => 'hashover-edit-comment',
				'class' => 'hashover-comment-label'
			), false);

			// Add edit label text
			$edit_comment_label->innerHTML ($edit_comment);

			// Add comment label to form element
			$edit_form->appendChild ($edit_comment_label);
		}

		// Create edit textarea
		$edit_textarea = new HTMLTag ('textarea', array (
			'id' => 'hashover-edit-comment',
			'class' => 'hashover-textarea hashover-edit-textarea',
			'cols' => '62',
			'name' => 'comment',
			'rows' => '10',
			'title' => $this->locale->get ('form-tip')
		), false);

		// Add edit textarea text
		$edit_textarea->innerHTML ($this->injectVar ($body));

		// Add edit textarea element to form element
		$edit_form->appendChild ($edit_textarea);

		// Add page info fields to edit form
		$this->pageInfoFields ($edit_form);

		// Create hidden comment file input element
		$edit_file_input = new HTMLTag ('input', array (
			'type' => 'hidden',
			'name' => 'file',
			'value' => $this->injectVar ($file)
		), false, true);

		// Add hidden page title input element to form element
		$edit_form->appendChild ($edit_file_input);

		// Create element for various messages when needed
		$edit_message = new HTMLTag ('div', array (
			'id' => 'hashover-edit-message-' . $this->injectVar ($permalink),
			'class' => 'hashover-message'
		));

		// Add message element to edit form element
		$edit_form->appendChild ($edit_message);

		// Create wrapper element for edit form buttons
		$edit_form_footer = new HTMLTag ('div', array (
			'class' => 'hashover-form-footer'
		));

		// Add checkbox label element to edit form buttons wrapper element
		if ($this->setup->fieldOptions['email'] !== false) {
			$edit_form_footer->appendChild ($this->subscribeLabel ($permalink, 'edit', $subscribed));
		}

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
			$save_edit_button->createAttribute ('id', 'hashover-edit-post-' . $this->injectVar ($permalink));
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
			$delete_button->createAttribute ('id', 'hashover-edit-delete-' . $this->injectVar ($permalink));
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

	public function asJSVar ($html, $var_name, $indent = "\t")
	{
		if ($this->setup->minifiesJavaScript === true and $this->setup->minifyLevel >= 3) {
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
