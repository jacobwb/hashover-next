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
	public $cookies;
	public $login;
	public $commentCounts;
	public $pageTitle;
	public $pageURL;
	public $postCommentOn;
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
		$this->cookies = new Cookies ($setup);
		$this->commentCounts = $counts;
		$this->pageTitle = $this->setup->pageTitle;
		$this->pageURL = $this->setup->pageURL;

		if ($this->mode !== 'php') {
			$this->pageTitle = $this->misc->jsEscape ($this->pageTitle);
			$this->pageURL = $this->misc->jsEscape ($this->pageURL);
		}

		// Set the field to emphasize after a failed post
		if (!empty ($_COOKIE['failed-on'])) {
			$this->emphasizedField = $this->cookies->getValue ('failed-on');
		}

		// "Post a comment" locale strings
		$post_comment_on = $this->locale->get ('post-comment-on');
		$this->postCommentOn = $post_comment_on[0];

		// Add optional "on <page title>" to "Post a comment" title
		if ($this->setup->displaysTitle !== false and !empty ($this->pageTitle)) {
			$this->postCommentOn = sprintf ($post_comment_on[1], $this->pageTitle);
		}

		// Create default login inputs elements
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
	protected function loginInputs ($permalink = '', $editForm = false, $name = '', $website = '')
	{
		$permalink = !empty ($permalink) ? '-' . $this->injectVar ($permalink) : '';

		// Login input attribute information
		$login_input_attributes = array (
			'name' => array (
				'wrapper-class' => 'hashover-name-input',
				'label-class' => 'hashover-name-label',
				'placeholder' => $this->locale->get ('name'),
				'input-id' => 'hashover-main-name' . $permalink,
				'input-type' => 'text',
				'input-name' => 'name',
				'input-title' => $this->locale->get ('name-tip'),
				'input-value' => $this->misc->makeXSSsafe ($this->login->name)
			),

			'password' => array (
				'wrapper-class' => 'hashover-password-input',
				'label-class' => 'hashover-password-label',
				'placeholder' => $this->locale->get ('password'),
				'input-id' => 'hashover-main-password' . $permalink,
				'input-type' => 'password',
				'input-name' => 'password',
				'input-title' => $this->locale->get ('password-tip'),
				'input-value' => ''
			),

			'email' => array (
				'wrapper-class' => 'hashover-email-input',
				'label-class' => 'hashover-email-label',
				'placeholder' => $this->locale->get ('email'),
				'input-id' => 'hashover-main-email' . $permalink,
				'input-type' => 'email',
				'input-name' => 'email',
				'input-title' => $this->locale->get ('email-tip'),
				'input-value' => $this->misc->makeXSSsafe ($this->login->email)
			),

			'website' => array (
				'wrapper-class' => 'hashover-website-input',
				'label-class' => 'hashover-website-label',
				'placeholder' => $this->locale->get ('website'),
				'input-id' => 'hashover-main-website' . $permalink,
				'input-type' => 'url',
				'input-name' => 'website',
				'input-title' => $this->locale->get ('website-tip'),
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
					'class' => $attributes['label-class'],
					'innerHTML' => $attributes['placeholder']
				), false);

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
				'value' => $attributes['input-value'],
				'title' => $attributes['input-title'],
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

	protected function getAvatarBackground ($avatar_src)
	{
		// Background image CSS
		$background_image = 'background-image: url(\'%s\');';

		// Escape background image in JavaScript mode
		if ($this->mode !== 'php') {
			$background_image = $this->misc->jsEscape ($background_image);
		}

		// Inject avatar URL into background image CSS
		return sprintf ($background_image, $avatar_src);
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
			$avatar = new HTMLTag ('div', array (
				'style' => $this->getAvatarBackground ($avatar_src)
			), false);
		} else {
			// Avatars set to count
			// Create element displaying comment number user will be
			$avatar = new HTMLTag ('span', array (
				'innerHTML' => $text
			), false);
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

	// Creates wrapper element to name element
	public function nameWrapper ($name_Link, $name_class)
	{
		$name_wrapper = new HTMLTag ('span', array (
			'class' => 'hashover-comment-name ' . $this->injectVar ($name_class),
			'innerHTML' => $this->injectVar ($name_Link)
		), false);

		return $name_wrapper->asHTML ();
	}

	// Creates name hyperlink/span element
	public function nameElement ($element, $name, $permalink, $href = '')
	{
		$name_text = $this->injectVar ($name);
		$name_class = 'hashover-name-' . $this->injectVar ($permalink);

		// Decide what kind of element to create
		switch ($element) {
			case 'a': {
				// A hyperlink pointing to the user's input URL
				$name_link = new HTMLTag ('a', array (
					'href' => $this->injectVar ($href),
					'class' => $name_class,
					'rel' => 'noopener noreferrer',
					'target' => '_blank',
					'title' => $name_text,
					'innerHTML' => $name_text
				), false);

				break;
			}

			case 'span': {
				// A plain wrapper element
				$name_link = new HTMLTag ('span', array (
					'class' => $name_class,
					'innerHTML' => $name_text
				), false);

				break;
			}
		}

		return $name_link->asHTML ();
	}

	// Creates "Top of Thread" hyperlink element
	public function threadLink ($permalink, $parent, $name)
	{
		// Get locale string
		$thread_locale = $this->locale->get ('thread');

		// Inject OP's name into the locale
		$inner_html = sprintf ($thread_locale, $this->injectVar ($name));

		// Create hyperlink element
		$thread_link = new HTMLTag ('a', array (
			'href' => '#' . $this->injectVar ($parent),
			'id' => 'hashover-thread-link-' . $this->injectVar ($permalink),
			'class' => 'hashover-thread-link',
			'title' => $this->locale->get ('thread-tip'),
			'innerHTML' => $inner_html
		), false);

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
		// Create hyperlink element
		$date_link = $this->queryLink ($this->setup->filePath);

		// Append more attributes
		$date_link->appendAttributes (array (
			'href' => '#' . $this->injectVar ($permalink),
			'class' => 'hashover-date-permalink',
			'title' => 'Permalink',
			'innerHTML' => $this->injectVar ($date)
		), false);

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
			'class' => $class,
			'innerHTML' => $this->injectVar ($text)
		), false);

		return $count->asHTML ();
	}

	// Creates "Like"/"Dislike" hyperlink element
	public function likeLink ($type, $permalink, $class, $title, $text)
	{
		// Create hyperlink element
		$link = new HTMLTag ('a', array (
			'href' => '#',
			'id' => 'hashover-' . $type . '-' . $this->injectVar ($permalink),
			'class' => $this->injectVar ($class),
			'title' => $this->injectVar ($title),
			'innerHTML' => $this->injectVar ($text)
		), false);

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
	public function cancelLink ($permalink, $for, $class = '')
	{
		$cancel_link = $this->queryLink ($this->setup->filePath);
		$cancel_locale = $this->locale->get ('cancel');

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

	public function userAvatar ($text, $href, $src)
	{
		// If avatars set to images
		if ($this->setup->iconMode !== 'none') {
			// Create wrapper element for avatar image
			$avatar_wrapper = new HTMLTag ('span', array (
				'class' => 'hashover-avatar'
			), false);

			if ($this->setup->iconMode === 'image') {
				// Create avatar image element
				$comments_avatar = new HTMLTag ('div', array (
					'style' => $this->getAvatarBackground ($this->injectVar ($src))
				), false);
			} else {
				// Avatars set to count
				// Create element displaying comment number user will be
				$comments_avatar = new HTMLTag ('a', array (
					'href' => '#' . $this->injectVar ($href),
					'title' => 'Permalink',
					'innerHTML' => $this->injectVar ($text)
				), false);
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

	protected function acceptedFormatCell ($format, $locale_key)
	{
		$title = new HTMLTag ('p', array ('class' => 'hashover-title'));
		$accepted_format = sprintf ($this->locale->get ('accepted-format'), $format);
		$title->innerHTML ($accepted_format);

		$paragraph = new HTMLTag ('p');
		$paragraph->innerHTML ($this->locale->get ($locale_key));

		return new HTMLTag ('div', array (
			'children' => array ($title, $paragraph)
		));
	}

	protected function commentForm (HTMLTag $form, $type, $placeholder, $text, $permalink = '')
	{
		$permalink = !empty ($permalink) ? '-' . $this->injectVar ($permalink) : '';
		$title_locale = ($type === 'reply') ? 'reply-form' : 'comment-form';

		// Create textarea
		$textarea = new HTMLTag ('textarea', array (
			'id' => 'hashover-' . $type . '-comment' . $permalink,
			'class' => 'hashover-textarea hashover-' . $type . '-textarea',
			'cols' => '63',
			'name' => 'comment',
			'rows' => '6',
			'title' => $this->locale->get ($title_locale)
		), false);

		// Set the placeholder attribute if one is given
		if (!empty ($placeholder)) {
			$textarea->createAttribute ('placeholder', $placeholder);
		}

		if ($type === 'main') {
			// Add a class for indicating a post failure
			if ($this->emphasizedField === 'comment') {
				$textarea->appendAttribute ('class', 'hashover-emphasized-input');
			}

			// If the comment was a reply, have the textarea use the reply textarea locale
			if (!empty ($_COOKIE['replied'])) {
				$reply_form_placeholder = $this->locale->get ('reply-form');
				$textarea->createAttribute ('placeholder', $reply_form_placeholder);
			}
		}

		// Set textarea content if given any text
		if (!empty ($text)) {
			$textarea->innerHTML ($text);
		}

		// Add textarea element to form element
		$form->appendChild ($textarea);

		// Create element for various messages when needed
		if ($type !== 'main') {
			$message = new HTMLTag ('div', array (
				'id' => 'hashover-' . $type . '-message-container' . $permalink,
				'class' => 'hashover-message',

				'children' => array (
					new HTMLTag ('div', array (
						'id' => 'hashover-' . $type . '-message' . $permalink
					))
				)
			));

			// Add message element to form element
			$form->appendChild ($message);
		}

		// Create accepted HTML message element
		$accepted_html_message = new HTMLTag ('div', array (
			'id' => 'hashover-' . $type . '-formatting-message' . $permalink,
			'class' => 'hashover-formatting-message',

			'children' => array (
				new HTMLTag ('div', array (
					'class' => 'hashover-formatting-table',

					'children' => array (
						$this->acceptedFormatCell ('HTML', 'accepted-html'),
						$this->acceptedFormatCell ('Markdown', 'accepted-markdown')
					)
				))
			)
		));

		// Ensure the accepted HTML message is open in PHP mode
		if ($this->mode === 'php') {
			$accepted_html_message->appendAttribute ('class', 'hashover-message-open');
			$accepted_html_message->appendAttribute ('class', 'hashover-php-message-open');
		}

		// Add accepted HTML message element to form element
		$form->appendChild ($accepted_html_message);
	}

	protected function pageInfoFields (HTMLTag $form)
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

	protected function acceptedHTML ($type, $permalink = '')
	{
		$permalink = !empty ($permalink) ? '-' . $this->injectVar ($permalink) : '';
		$accepted_format = $this->locale->get ('comment-formatting');

		// Create accepted HTML message revealer hyperlink
		$accepted_html = new HTMLTag ('span', array (
			'id' => 'hashover-' . $type . '-formatting' . $permalink,
			'class' => 'hashover-fake-link hashover-formatting',
			'title' => $accepted_format,
			'innerHTML' => $accepted_format
		));

		// Return the hyperlink
		return $accepted_html;
	}

	public function initialHTML (array $popular_list, $hashover_wrapper = true)
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
		));

		// Hide primary form wrapper if comments are to be initially hidden
		if ($this->mode === 'javascript' and $this->setup->collapsesUI === true) {
			$form_section->createAttribute ('style', 'display: none;');
		}

		// Create element for "Post Comment" title
		$post_title = new HTMLTag ('span', array (
			'class' => array (
				'hashover-title',
				'hashover-main-title',
				'hashover-dashed-title'
			),

			'innerHTML' => $this->postCommentOn
		));

		// Add "Post Comment" element to primary form wrapper
		$form_section->appendChild ($post_title);

		// Create element for various messages when needed
		$message_container = new HTMLTag ('div', array (
			'id' => 'hashover-message-container',
			'class' => 'hashover-title hashover-message'
		));

		// Create element for message text
		$message_element = new HTMLTag ('div', array (
			'id' => 'hashover-message'
		));

		// Check if message cookie is set
		if (!empty ($_COOKIE['message']) or !empty ($_COOKIE['error'])) {
			// If so, set the message element to open in PHP mode
			if ($this->mode === 'php') {
				$message_container->appendAttribute ('class', array (
					'hashover-message-open',
					'hashover-php-message-open'
				));
			}

			// Check if the message is a normal message
			if (!empty ($_COOKIE['message'])) {
				// If so, get an XSS safe version of the message
				$message = $this->misc->makeXSSsafe ($this->cookies->getValue ('message'));
			} else {
				// If not, get an XSS safe version of the error message
				$message = $this->misc->makeXSSsafe ($this->cookies->getValue ('error'));

				// And set a class to the message element indicating an error
				$message_container->appendAttribute ('class', 'hashover-message-error');
			}

			// And put current message into message element
			$message_element->innerHTML ($message);
		}

		// Add message text element to message element
		$message_container->appendChild ($message_element);

		// Add message element to primary form wrapper
		$form_section->appendChild ($message_container);

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
			$main_form_column_spanner = new HTMLTag ('div', array (
				'class' => 'hashover-comment-name hashover-top-name'
			), false);

			// Check if user gave website
			if (!empty ($user_website)) {
				if ($is_twitter === false) {
					$name_class = 'hashover-name-website';
				}

				// Create link to user's website
				$main_form_hyperlink = new HTMLTag ('a', array (
					'href' => $user_website,
					'rel' => 'noopener noreferrer',
					'target' => '_blank',
					'title' => $user_name,
					'innerHTML' => $user_name
				), false);

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

		// Post button locale
		$post_button = $this->locale->get ('post-button');

		// Create label element for comment textarea
		if ($this->setup->usesLabels === true) {
			$main_comment_label = new HTMLTag ('label', array (
				'for' => 'hashover-main-comment',
				'class' => 'hashover-comment-label',
				'innerHTML' => $post_button
			), false);

			// Add comment label to form element
			$main_form->appendChild ($main_comment_label);
		}

		// Get comment text if a comment cookie is set
		$comment_text = $this->misc->makeXSSsafe ($this->cookies->getValue ('comment'));

		// Comment form placeholder text
		$comment_form = $this->locale->get ('comment-form');

		// Create main textarea element and add it to form element
		$this->commentForm ($main_form, 'main', $comment_form, $comment_text);

		// Add page info fields to main form
		$this->pageInfoFields ($main_form);

		// Check if comment is a failed reply
		if (!empty ($_COOKIE['replied'])) {
			// If so, get the comment being replied to
			$replied = $this->cookies->getValue ('replied');

			// Create hidden reply to input element
			$reply_to_input = new HTMLTag ('input', array (
				'type' => 'hidden',
				'name' => 'reply-to',
				'value' => $this->misc->makeXSSsafe ($replied)
			), false, true);

			// And add hidden reply to input element to form element
			$main_form->appendChild ($reply_to_input);
		}

		// Create wrapper element for main form footer
		$main_form_footer = new HTMLTag ('div', array (
			'class' => 'hashover-form-footer'
		));

		// Create wrapper for form links
		$main_form_links_wrapper = new HTMLTag ('span', array (
			'class' => 'hashover-form-links'
		));

		// Add checkbox label element to main form buttons wrapper element
		if ($this->setup->fieldOptions['email'] !== false) {
			if ($this->login->userIsLoggedIn === false or !empty ($this->login->email)) {
				$main_form_links_wrapper->appendChild ($this->subscribeLabel ());
			}
		}

		// Create and add accepted HTML revealer hyperlink
		if ($this->mode === 'javascript') {
			$main_form_links_wrapper->appendChild ($this->acceptedHTML ('main'));
		}

		// Add main form links wrapper to main form footer element
		$main_form_footer->appendChild ($main_form_links_wrapper);

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

		if (!empty ($popular_list)) {
			// Create wrapper element for popular comments
			$popular_section = new HTMLTag ('div', array (
				'id' => 'hashover-popular-section'
			), false);

			// Hide popular comments wrapper if comments are to be initially hidden
			if ($this->mode === 'javascript') {
				if ($this->setup->collapsesUI === true or $this->setup->collapseLimit <= 0) {
					$popular_section->createAttribute ('style', 'display: none;');
				}
			}

			// Create wrapper element for popular comments title
			$pop_count_wrapper = new HTMLTag ('div', array (
				'class' => 'hashover-dashed-title'
			));

			// Create element for popular comments title
			$pop_count_element = new HTMLTag ('span', array (
				'class' => 'hashover-title'
			));

			// Add popular comments title text
			$popPlural = (count ($popular_list) !== 1) ? 1 : 0;
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
			'class' => 'hashover-count-sort hashover-dashed-title'
		));

		// Create element for comment count
		$count_element = new HTMLTag ('span', array (
			'id' => 'hashover-count'
		));

		// Add comment count to comment count element
		if ($this->commentCounts['total'] > 1) {
			$count_element->innerHTML ($this->commentCounts['show-count']);
		}

		// Add comment count element to wrapper element
		$count_sort_wrapper->appendChild ($count_element);

		// JavaScript mode specific HTML
		if ($this->mode === 'javascript') {
			// Hide wrapper if comments are to be initially hidden
			if ($this->setup->collapsesUI === true) {
				$comments_section->createAttribute ('style', 'display: none;');
			}

			// Hide comment count if collapse limit is set at zero
			if ($this->setup->collapseLimit <= 0 or $this->commentCounts['total'] <= 1) {
				$count_sort_wrapper->createAttribute ('style', 'display: none;');
			}

			if ($this->commentCounts['total'] > 2) {
				// Create wrapper element for sort dropdown menu
				$sort_wrapper = new HTMLTag ('span', array (
					'id' => 'hashover-sort',
					'class' => 'hashover-select-wrapper'
				));

				// Create sort dropdown menu element
				$sort_select = new HTMLTag ('select', array (
					'id' => 'hashover-sort-select',
					'name' => 'sort',
					'size' => '1',
					'title' => $this->locale->get ('sort')
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
						'value' => $sort_options[$i]['value'],
						'innerHTML' => $sort_options[$i]['innerHTML']
					), false);

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
						'value' => $threaded_sort_options[$i]['value'],
						'innerHTML' => $threaded_sort_options[$i]['innerHTML']
					), false);

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
		if ($this->mode === 'javascript' and $this->setup->collapsesUI === true) {
			$end_links_wrapper->createAttribute ('style', 'display: none;');
		}

		// HashOver Comments hyperlink text
		$homepage_link_text = $this->locale->get ('hashover-comments');

		// Create link back to HashOver homepage (fixme! get a real page!)
		$homepage_link = new HTMLTag ('a', array (
			'href' => 'http://tildehash.com/?page=hashover',
			'id' => 'hashover-home-link',
			'target' => '_blank',
			'title' => $homepage_link_text,
			'innerHTML' => $homepage_link_text
		), false);

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

				// RSS Feed hyperlink text
				$rss_link_text = $this->locale->get ('rss-feed');

				$rss_link->createAttributes (array (
					'id' => 'hashover-rss-link',
					'target' => '_blank',
					'title' => $rss_link_text,
					'innerHTML' => $rss_link_text
				));

				// Add RSS hyperlink to end links array
				$end_links[] = $rss_link->asHTML ();
			}
		}

		// Source Code hyperlink text
		$source_link_text = $this->locale->get ('source-code');

		// Create link to HashOver source code (fixme! can be done better)
		$source_link = new HTMLTag ('a', array (
			'href' => $this->setup->httpScripts . '/hashover.php?source',
			'id' => 'hashover-source-link',
			'rel' => 'hashover-source',
			'target' => '_blank',
			'title' => $source_link_text,
			'innerHTML' => $source_link_text
		), false);

		// Add source code hyperlink to end links array
		$end_links[] = $source_link->asHTML ();

		if ($this->mode === 'javascript') {
			// Create link to HashOver JavaScript source code
			$javascript_link = new HTMLTag ('a', array (
				'href' => $this->setup->httpScripts . '/hashover-javascript.php',
				'id' => 'hashover-javascript-link',
				'rel' => 'hashover-javascript',
				'target' => '_blank',
				'title' => 'JavaScript'
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
			'title' => $cancel_locale,
			'innerHTML' => $cancel_locale
		));

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
			$reply_login_inputs = $this->loginInputs ($permalink);
			$reply_form->appendChild ($reply_login_inputs);
		}

		// Create label element for comment textarea
		if ($this->setup->usesLabels === true) {
			$reply_comment_label = new HTMLTag ('label', array (
				'for' => 'hashover-reply-comment-' . $this->injectVar ($permalink),
				'class' => 'hashover-comment-label',
				'innerHTML' => $this->locale->get ('reply-to-comment')
			), false);

			// Add comment label to form element
			$reply_form->appendChild ($reply_comment_label);
		}

		// Reply form locale
		$reply_form_placeholder = $this->locale->get ('reply-form');

		// Create reply textarea element and add it to form element
		$this->commentForm ($reply_form, 'reply', $reply_form_placeholder, '', $permalink);

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

		// Create reply form footer element
		$reply_form_footer = new HTMLTag ('div', array (
			'class' => 'hashover-form-footer'
		));

		// Create wrapper for form links
		$reply_form_links_wrapper = new HTMLTag ('span', array (
			'class' => 'hashover-form-links'
		));

		// Add checkbox label element to reply form footer element
		if ($this->setup->fieldOptions['email'] !== false) {
			if ($this->login->userIsLoggedIn === false or !empty ($this->login->email)) {
				$reply_form_links_wrapper->appendChild ($this->subscribeLabel ($permalink, 'reply', $subscribed));
			}
		}

		// Create and add accepted HTML revealer hyperlink
		if ($this->mode === 'javascript') {
			$reply_form_links_wrapper->appendChild ($this->acceptedHTML ('reply', $permalink));
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
			$reply_post_button->createAttribute ('id', 'hashover-reply-post-' . $this->injectVar ($permalink));
		}

		// Post reply locale
		$post_reply = $this->locale->get ('post-reply');

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
			'class' => 'hashover-title hashover-dashed-title',
			'innerHTML' => $edit_comment
		), false);

		if ($this->login->userIsAdmin === true) {
			// Create status dropdown wrapper element
			$edit_status_wrapper = new HTMLTag ('span', array (
				'class' => 'hashover-edit-status',
				'innerHTML' => $this->locale->get ('status')
			), false);

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
		$edit_login_inputs = $this->loginInputs ($permalink, true, $name, $website);
		$edit_form->appendChild ($edit_login_inputs);

		// Create label element for comment textarea
		if ($this->setup->usesLabels === true) {
			$edit_comment_label = new HTMLTag ('label', array (
				'for' => 'hashover-edit-comment-' . $this->injectVar ($permalink),
				'class' => 'hashover-comment-label',
				'innerHTML' => $this->locale->get ('edit-your-comment')
			), false);

			// Add comment label to form element
			$edit_form->appendChild ($edit_comment_label);
		}

		// Comment form placeholder text
		$edit_placeholder = $this->locale->get ('comment-form');

		// Edit form textarea text value
		$edit_body = $this->injectVar ($body);

		// Create edit textarea element and add it to form element
		$this->commentForm ($edit_form, 'edit', $edit_placeholder, $edit_body, $permalink);

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

		// Create wrapper element for edit form buttons
		$edit_form_footer = new HTMLTag ('div', array (
			'class' => 'hashover-form-footer'
		));

		// Create wrapper for form links
		$edit_form_links_wrapper = new HTMLTag ('span', array (
			'class' => 'hashover-form-links'
		));

		// Add checkbox label element to edit form buttons wrapper element
		if ($this->setup->fieldOptions['email'] !== false) {
			$edit_form_links_wrapper->appendChild ($this->subscribeLabel ($permalink, 'edit', $subscribed));
		}

		// Create and add accepted HTML revealer hyperlink
		if ($this->mode === 'javascript') {
			$edit_form_links_wrapper->appendChild ($this->acceptedHTML ('edit', $permalink));
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
		// Check if JavaScript minification is enabled
		if ($this->setup->minifiesJavaScript === true and $this->setup->minifyLevel >= 3) {
			// If so, remove whitespace collapsing code to a single line
			$html = str_replace (array ("\t", PHP_EOL), array ('', ' '), $html);
		} else {
			// If not, convert literal tabs to JavaScript tabs
			$html = str_replace ("\t", '\t', $html);
		}

		// Trim newlines from start of end of input HTML
		$html = trim ($html, "\r\n");

		// Split the HTML into an array of lines
		$lines = explode (PHP_EOL, $html);
		$line_count = count ($lines);

		// Initial output HTML
		$var = '';

		if ($line_count > 0) {
			// Variable declaration code line
			$var .= $indent . 'var ' . $var_name . ' = \'' . $lines[0];
			$var .= (($line_count > 1) ? '\n' : '') . '\';' . PHP_EOL;

			// Run through the rest of the lines
			for ($i = 1; $i < $line_count; $i++) {
				// Skip empty lines
				if (trim ($lines[$i]) === '') {
					continue;
				}

				// Append indentation
				$var .= $indent;

				// Append variable concatenation code
				$var .= '    ' . $var_name . ' += \'';

				// Append the current line
				$var .= $lines[$i];

				// And close concatenation code
				$var .= '\n\';' . PHP_EOL;
			}
		}

		return $var;
	}
}
