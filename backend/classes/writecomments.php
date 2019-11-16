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


class WriteComments extends Secrets
{
	protected $setup;
	protected $formData;
	protected $thread;

	protected $locale;
	protected $login;
	protected $cookies;
	protected $crypto;
	protected $avatar;
	protected $templater;
	protected $mail;

	protected $name = '';
	protected $password = '';
	protected $loginHash = '';
	protected $email = '';
	protected $website = '';

	protected $data = array ();
	protected $urls = array ();

	// Characters to search for and replace with in comments
	protected $dataSearch = array (
		'\\',
		'"',
		'<',
		'>',
		"\r\n",
		"\r",
		"\n",
		'  '
	);

	// Character replacements
	protected $dataReplace = array (
		'&#92;',
		'&quot;',
		'&lt;',
		'&gt;',
		PHP_EOL,
		PHP_EOL,
		PHP_EOL,
		'&nbsp; '
	);

	// HTML tags to allow in comments
	protected $htmlTagSearch = array (
		'b',
		'big',
		'blockquote',
		'code',
		'em',
		'i',
		'li',
		'ol',
		'pre',
		's',
		'small',
		'strong',
		'sub',
		'sup',
		'u',
		'ul'
	);

	// HTML tags to automatically close
	public $closeTags = array (
		'b',
		'big',
		'blockquote',
		'em',
		'i',
		'li',
		'ol',
		'pre',
		's',
		'small',
		'strong',
		'sub',
		'sup',
		'u',
		'ul'
	);

	// Unprotected fields to update when editing a comment
	protected $editableFields = array (
		'body',
		'name',
		'email',
		'encryption',
		'email_hash',
		'notifications',
		'website'
	);

	// Password protected fields
	protected $protectedFields = array (
		'password',
		'login_id'
	);

	// Possible comment status options
	protected $statuses = array (
		'approved',
		'pending',
		'deleted'
	);

	public function __construct (Setup $setup, FormData $form_data, Thread $thread)
	{
		// Store parameters as properties
		$this->setup = $setup;
		$this->formData = $form_data;
		$this->thread = $thread;

		// Instantiate various classes
		$this->locale = new Locale ($setup);
		$this->login = new Login ($setup);
		$this->cookies = new Cookies ($setup, $this->login);
		$this->crypto = new Crypto ();
		$this->avatars = new Avatars ($setup);
		$this->templater = new Templater ($setup);
		$this->mail = new Email ($setup);

		// Setup initial login data
		$this->setupLogin ();
	}

	// Confirms attempted actions are to existing comments
	protected function verifyFile ($file)
	{
		// Attempt to get file
		$comment_file = $this->setup->getRequest ($file);

		// Check if file is set
		if ($comment_file !== false) {
			// Cast file to string
			$comment_file = (string)($comment_file);

			// Return true if POST file is in comment list
			if (in_array ($comment_file, $this->thread->commentList, true)) {
				return $comment_file;
			}

			// Set cookies to indicate failure
			if ($this->formData->viaAJAX !== true) {
				$this->cookies->setFailedOn ('comment', $this->formData->replyTo, false);
			}
		}

		// Throw exception as error message
		throw new \Exception (
			$this->locale->text['comment-needed']
		);
	}

	// Encodes HTML entities
	protected function encodeHTML ($value)
	{
		return htmlentities ($value, ENT_COMPAT, 'UTF-8', false);
	}

	// Sets up necessary login data
	protected function setupLogin ()
	{
		$this->name = $this->encodeHTML ($this->login->name);
		$this->password = $this->encodeHTML ($this->login->password);
		$this->loginHash = $this->encodeHTML ($this->login->loginHash);
		$this->email = $this->encodeHTML ($this->login->email);
		$this->website = $this->encodeHTML ($this->login->website);
	}

	// User comment authentication
	protected function commentAuthentication ()
	{
		// Verify file exists
		$file = $this->verifyFile ('file');

		// Read original comment
		$comment = $this->thread->data->read ($file);

		// Authentication data
		$auth = array (
			// Assume no authorization by default
			'authorized' => false,
			'user-owned' => false,

			// Original comment
			'comment' => $comment
		);

		// Return authorization data if we fail to get comment
		if ($comment === false) {
			return $auth;
		}

		// Check if we have both required passwords
		if (!empty ($this->formData->data['password']) and !empty ($comment['password'])) {
			// If so, get the user input password
			$password = $this->encodeHTML ($this->formData->data['password']);

			// Get the comment password
			$comment_password = $comment['password'];

			// Attempt to compare the two passwords
			$match = $this->crypto->verifyHash ($password, $comment_password);

			// Authenticate if the passwords match
			if ($match === true) {
				$auth['user-owned'] = true;
				$auth['authorized'] = true;
			}
		}

		// Admin is always authorized after strict verification
		if ($this->login->verifyAdmin ($this->password) === true) {
			$auth['authorized'] = true;
		}

		// And return authorization data
		return $auth;
	}

	// Deletes comment
	public function deleteComment ()
	{
		// Check login requirements
		$this->login->checkRequirements ('You must be logged in to delete a comment!');

		// Authenticate user password
		$auth = $this->commentAuthentication ();

		// Check if user is authorized
		if ($auth['authorized'] === true) {
			// If so, strictly verify admin login
			$user_is_admin = $this->login->verifyAdmin ($this->password);

			// Unlink comment file indicator
			$unlink = ($user_is_admin or $this->setup->unlinksFiles === true);

			// Attempt to delete comment file
			$deleted = $this->thread->data->delete ($this->formData->file, $unlink);

			// Check if comment file was deleted successfully
			if ($deleted === true) {
				// If so, remove comment from latest comments metadata
				$this->thread->data->removeFromLatest ($this->formData->file);

				// And return true
				return true;
			}
		}

		// Otherwise, sleep for 5 seconds
		sleep (5);

		// And return false
		return false;
	}

	// Closes all allowed HTML tags
	public function tagCloser ($tags, $html)
	{
		// Run through tags
		for ($tc = 0, $tcl = count ($tags); $tc < $tcl; $tc++) {
			// Count opening and closing tags
			$open_tags = mb_substr_count ($html, '<' . $tags[$tc] . '>');
			$close_tags = mb_substr_count ($html, '</' . $tags[$tc] . '>');

			// Check if opening and closing tags aren't equal
			if ($open_tags !== $close_tags) {
				// Add closing tags to end of comment
				while ($open_tags > $close_tags) {
					$html .= '</' . $tags[$tc] . '>';
					$close_tags++;
				}

				// Remove closing tags for unopened tags
				while ($close_tags > $open_tags) {
					$html = preg_replace ('/<\/' . $tags[$tc] . '>/iS', '', $html, 1);
					$close_tags--;
				}
			}
		}

		// And return HTML with closed tags
		return $html;
	}

	// Extracts URLs for later injection
	protected function urlExtractor ($groups)
	{
		$link_number = count ($this->urls);
		$this->urls[] = $groups[1];

		return 'URL[' . $link_number . ']';
	}

	// Escapes all HTML tags excluding allowed tags
	public function htmlSelectiveEscape ($code)
	{
		// Escape all HTML tags
		$code = str_ireplace ($this->dataSearch, $this->dataReplace, $code);

		// Unescape allowed HTML tags
		foreach ($this->htmlTagSearch as $tag) {
			// Create search array of escaped opening and closing tags
			$escaped_tags = array ('&lt;' . $tag . '&gt;', '&lt;/' . $tag . '&gt;');

			// Create search array of opening and closing tags
			$text_tags = array ('<' . $tag . '>', '</' . $tag . '>');

			// Unescape opening and closed tags
			$code = str_ireplace ($escaped_tags, $text_tags, $code);
		}

		// And return new HTML
		return $code;
	}

	// Escapes HTML inside of <code> tags and markdown code blocks
	protected function codeEscaper ($groups)
	{
		return $groups[1] . htmlspecialchars ($groups[2], null, null, false) . $groups[3];
	}

	// Sets up and tests for necessary comment data
	protected function setupCommentData ($editing = false)
	{
		// Post fails when comment is empty
		if (empty ($this->formData->data['comment'])) {
			// Set cookies to indicate failure
			if ($this->formData->viaAJAX !== true) {
				$this->cookies->setFailedOn ('comment', $this->formData->replyTo);
			}

			// Throw exception about reply requirement
			if (!empty ($this->formData->replyTo)) {
				throw new \Exception (
					$this->locale->text['reply-needed']
				);
			}

			// Throw exception about comment requirement
			throw new \Exception (
				$this->locale->text['comment-needed']
			);
		}

		// Strictly verify if the user is logged in as admin
		if ($this->login->verifyAdmin ($this->password) === true) {
			// If so, check if status is set in POST data is set
			if (!empty ($this->formData->data['status'])) {
				// If so, use status if it is allowed
				if (in_array ($this->formData->data['status'], $this->statuses, true)) {
					$this->data['status'] = $this->formData->data['status'];
				}
			}
		} else {
			// Check if setup is for a comment edit
			if ($editing === true) {
				// If so, pend comment if edit moderation is enabled
				if ($this->setup->pendsUserEdits === true) {
					$this->data['status'] = 'pending';
				}
			} else {
				// If not, pend comment if moderation is enabled
				if ($this->setup->usesModeration === true) {
					$this->data['status'] = 'pending';
				}
			}
		}

		// Check if setup is for a comment edit
		if ($editing === true) {
			// If so, mimic normal user login
			$this->login->prepareCredentials ();
			$this->login->updateCredentials ();
		} else {
			// If not, setup initial login information
			if ($this->login->userIsLoggedIn !== true) {
				$this->login->setCredentials ();
			}
		}

		// Check if required fields have values
		$this->login->validateFields ();

		// Setup login data
		$this->setupLogin ();

		// Trim leading and trailing white space
		$clean_code = $this->formData->data['comment'];

		// URL regular expression
		$url_regex = '/((http|https|ftp):\/\/[a-z0-9-@:;%_\+.~#?&\/=]+)/i';

		// Extract URLs from comment
		$clean_code = preg_replace_callback ($url_regex, 'self::urlExtractor', $clean_code);

		// Escape all HTML tags excluding allowed tags
		$clean_code = $this->htmlSelectiveEscape ($clean_code);

		// Collapse multiple newlines to three maximum
		$clean_code = preg_replace ('/' . PHP_EOL . '{3,}/', str_repeat (PHP_EOL, 3), $clean_code);

		// Close <code> tags
		$clean_code = $this->tagCloser (array ('code'), $clean_code);

		// Escape HTML inside of <code> tags and markdown code blocks
		$clean_code = preg_replace_callback ('/(<code>)(.*?)(<\/code>)/is', 'self::codeEscaper', $clean_code);
		$clean_code = preg_replace_callback ('/(```)(.*?)(```)/is', 'self::codeEscaper', $clean_code);

		// Close remaining tags
		$clean_code = $this->tagCloser ($this->closeTags, $clean_code);

		// Inject original URLs back into comment
		$clean_code = preg_replace_callback ('/URL\[([0-9]+)\]/', function ($groups) {
			$url_key = $groups[1];
			$url = $this->urls[$url_key];

			return $url . ' ';
		}, $clean_code);

		// Store clean code
		$this->data['body'] = $clean_code;

		// Store posting date
		$this->data['date'] = date (DATE_ISO8601);

		// Store name if one is given
		if ($this->setup->nameField !== 'off') {
			if (!empty ($this->name)) {
				$this->data['name'] = $this->name;
			}
		}

		// Store password and login ID if a password is given
		if ($this->setup->passwordField !== 'off') {
			if (!empty ($this->password)) {
				$this->data['password'] = $this->password;
			}
		}

		// Store login ID if login hash is non-empty
		if (!empty ($this->loginHash)) {
			$this->data['login_id'] = $this->loginHash;
		}

		// Check if the e-mail field is enabled
		if ($this->setup->emailField !== 'off') {
			// Check if we have an e-mail address
			if (!empty ($this->email)) {
				// Get encryption info for e-mail
				$encryption_keys = $this->crypto->encrypt ($this->email);

				// Set encrypted e-mail address
				$this->data['email'] = $encryption_keys['encrypted'];

				// Set decryption keys
				$this->data['encryption'] = $encryption_keys['keys'];

				// Set e-mail hash
				$this->data['email_hash'] = md5 (mb_strtolower ($this->email));

				// Get subscription status
				$subscribed = $this->setup->getRequest ('subscribe') ? 'yes' : 'no';

				// And set e-mail subscription if one is given
				$this->data['notifications'] = $subscribed;
			}
		}

		// Store website URL if one is given
		if ($this->setup->websiteField !== 'off') {
			if (!empty ($this->website)) {
				$this->data['website'] = $this->website;
			}
		}

		// Store user IP address if setup to and one is given
		if ($this->setup->storesIpAddress === true) {
			// Check if remote IP address exists
			if (!empty ($_SERVER['REMOTE_ADDR'])) {
				// If so, get XSS safe IP address
				$ip = Misc::makeXSSsafe ($_SERVER['REMOTE_ADDR']);

				// And set the IP address
				$this->data['ipaddr'] = $ip;
			}
		}
	}

	// Converts a file name (1-2) to a permalink (hashover-c1r1)
	protected function filePermalink ($file)
	{
		return 'hashover-c' . str_replace ('-', 'r', $file);
	}

	// Edits a comment
	public function editComment ()
	{
		// Check login requirements
		$this->login->checkRequirements ('You must be logged in to edit a comment!');

		// Authenticate user password
		$auth = $this->commentAuthentication ();

		// Check if user is authorized
		if ($auth['authorized'] === true) {
			// Set initial fields for update
			$update_fields = $this->editableFields;

			// Get file from form data
			$file = $this->formData->file;

			// Setup necessary comment data
			$this->setupCommentData (true);

			// Add status to editable fields if a new status is set
			if (!empty ($this->data['status'])) {
				$update_fields[] = 'status';
			}

			// Only set protected fields for update if passwords match
			if ($auth['user-owned'] === true) {
				$update_fields = array_merge ($update_fields, $this->protectedFields);
			}

			// Run through update fields
			foreach ($update_fields as $key) {
				// Check if field exists
				if (!empty ($this->data[$key])) {
					// If so, update comment data
					$auth['comment'][$key] = $this->data[$key];
				} else {
					// If not, remove it from comment data
					unset ($auth['comment'][$key]);
				}
			}

			// Attempt to write edited comment
			$saved = $this->thread->data->save ($file, $auth['comment'], true);

			// Check if edited comment saved successfully
			if ($saved === true) {
				// If so, return comment information
				return array (
					// Comment filename
					'file' => $file,

					// Comment data
					'comment' => $auth['comment']
				);
			}
		}

		// Otherwise, sleep for 5 seconds
		sleep (5);

		// And return empty array
		return array ();
	}

	// Wordwraps text after adding indentation
	protected function indentWordwrap ($text)
	{
		// Line ending styles to convert
		$styles = array ("\r\n", "\r");

		// Convert line endings to UNIX-style
		$text = str_replace ($styles, "\n", $text);

		// Wordwrap the text to 64 characters long
		$text = wordwrap ($text, 64, "\n", true);

		// Split the text by paragraphs
		$paragraphs = explode ("\n\n", $text);

		// Indent the first line of each paragraph
		array_walk ($paragraphs, function (&$paragraph) {
			$paragraph = '    ' . $paragraph;
		});

		// Indent all other lines of each paragraph
		$paragraphs = str_replace ("\n", "\r\n    ", $paragraphs);

		// Convert paragraphs back to a string
		$text = implode ("\r\n\r\n", $paragraphs);

		// And return indented text
		return $text;
	}

	// Converts text paragraphs to HTML paragraph tags
	protected function paragraphsTags ($text, $indention = '')
	{
		// Initial HTML paragraphs
		$paragraphs = array ();

		// Break comment into paragraphs
		$ps = preg_split ('/(\r\n|\r|\n){2}/S', $text);

		// Wrap each paragraph in <p> tags and place <br> tags after each line
		for ($i = 0, $il = count ($ps); $i < $il; $i++) {
			// Place <br> tags after each line
			$paragraph = preg_replace ('/(\r\n|\r|\n)/S', '<br>\\1', $ps[$i]);

			// Create <p> tag
			$pTag = new HTMLTag ('p', $paragraph);

			// Add paragraph to HTML
			$paragraphs[] = $pTag->asHTML ($indention);
		}

		// Convert paragraphs array to string
		$html = implode ("\r\n\r\n" . $indention, $paragraphs);

		// And return paragraphs HTML
		return $html;
	}

	// Sends a notification e-mail to another commenter
	protected function sendNotifications ($file)
	{
		// Initial comment data
		$data = array ();

		// Shorthand
		$default_name = $this->setup->defaultName;

		// Commenter's name
		$name = $this->name ?: $default_name;

		// Get comment permalink
		$permalink = $this->filePermalink ($file);

		// "New Comment" locale string
		$new_comment = $this->locale->text['new-comment'];

		// E-mail hash for Gravatar or empty for default avatar
		$hash = Misc::getArrayItem ($this->data, 'email_hash') ?: '';

		// Add avatar to data
		$data['avatar'] = $this->avatars->getGravatar ($hash, true, 128);

		// Add name of commenter or configurable default name to data
		$data['name'] = $name;

		// Add domain name to data
		$data['domain'] = $this->setup->website;

		// Add plain text comment to data
		$data['text-comment'] = $this->indentWordwrap ($this->data['body']);

		// Add "From <name>" locale string to data
		$data['from'] = sprintf ($this->locale->text['from'], $name);

		// Add some locale strings to data
		$data['comment'] = $this->locale->text['comment'];
		$data['page'] = $this->locale->text['page'];
		$data['new-comment'] = $new_comment;

		// Add comment permalink to data
		$data['permalink'] = $this->setup->pageURL . '#' . $permalink;

		// Add page URL to data
		$data['url'] = $this->setup->pageURL;

		// Add page URL to data
		$data['title'] = $this->setup->pageTitle;

		// Add message about what website is sending the e-mail to data
		$data['sent-by'] = sprintf ($this->locale->text['sent-by'], $this->setup->website);

		// Attempt to read reply comment
		$reply = $this->thread->data->read ($this->formData->replyTo);

		// Check if the reply comment read successfully
		if ($reply !== false) {
			// If so, decide name of recipient
			$reply_name = Misc::getArrayItem ($reply, 'name') ?: $default_name;

			// Add reply name to data
			$data['reply-name'] = $reply_name;

			// Add "In reply to" locale string to data
			$data['in-reply-to'] = sprintf ($this->locale->text['thread'], $reply_name);

			// Add indented body of recipient's comment to data
			$data['text-reply'] = $this->indentWordwrap ($reply['body']);

			// And add HTML version of the reply comment to data
			if ($this->setup->mailType !== 'text') {
				$data['html-reply'] = $this->paragraphsTags ($reply['body'], "\t\t\t\t");
			}
		}

		// Get and parse plain text e-mail notification
		$text_body = $this->templater->parseTheme ('email-notification.txt', $data);

		// Set subject to "New Comment - <domain here>"
		$this->mail->subject ($new_comment . ' - ' . $this->setup->website);

		// Set plain text version of the message
		$this->mail->text ($text_body);

		// Check if e-mail format is anything other than text
		if ($this->setup->mailType !== 'text') {
			// If so, add HTML version of the message to data
			$data['html-comment'] = $this->paragraphsTags ($this->data['body'], "\t\t\t\t");

			// Get and parse HTML e-mail notification
			$html_body = $this->templater->parseTheme ('email-notification.html', $data);

			// And set HTML version of the message if told to
			$this->mail->html ($html_body);
		}

		// Only send admin notification if it's not admin posting
		if ($this->email !== $this->notificationEmail) {
			// Set e-mail to be sent to admin
			$this->mail->to ($this->notificationEmail);

			// Set e-mail as coming from the posting user
			$this->mail->from ($this->email);

			// And actually send the message
			$this->mail->send ();
		}

		// Do nothing else if reply comment failed to read
		if ($reply === false) {
			return;
		}

		// Do nothing else if reply comment lacks e-mail and decrypt info
		if (empty ($reply['email']) or empty ($reply['encryption'])) {
			return;
		}

		// Do nothing else if reply comment poster disabled notifications
		if (Misc::getArrayItem ($reply, 'notifications') === 'no') {
			return;
		}

		// Otherwise, decrypt reply e-mail address
		$reply_email = $this->crypto->decrypt ($reply['email'], $reply['encryption']);

		// Check if reply e-mail is different than login's and admin's
		if ($reply_email !== $this->email and $reply_email !== $this->notificationEmail) {
			// If so, set message to be sent to reply comment e-mail
			$this->mail->to ($reply_email);

			// Check if users are allowed to reply by email
			if ($this->setup->allowsUserReplies === true) {
				// If so, set e-mail as coming from posting user
				$this->mail->from ($this->email);
			} else {
				// If not, set e-mail as coming from noreply e-mail
				$this->mail->from ($this->noreplyEmail);
			}

			// And actually send the message
			$this->mail->send ();
		}
	}

	// Writes a comment
	protected function writeComment ($comment_file)
	{
		// Attempt to save comment
		$saved = $this->thread->data->save ($comment_file, $this->data);

		// Check if the comment was saved successfully
		if ($saved === true) {
			// If so, add it to latest comments metadata
			$this->thread->data->addLatestComment ($comment_file);

			// Send notification e-mails
			$this->sendNotifications ($comment_file);

			// Set/update user login cookie
			if ($this->setup->usesAutoLogin !== false) {
				if ($this->login->userIsLoggedIn !== true) {
					$this->login->setLogin ();
				}
			}

			// Increase comment count(s) if request is AJAX
			if ($this->formData->viaAJAX === true) {
				$this->thread->countComment ($comment_file);
			}

			// And return comment information
			return array (
				// Comment filename
				'file' => $comment_file,

				// Comment data
				'comment' => $this->data
			);
		}

		// Otherwise, return empty array
		return array ();
	}

	// Posts a comment
	public function postComment ()
	{
		// Check login requirements
		$this->login->checkRequirements ('You must be logged in to comment!');

		// Test for necessary comment data
		$this->setupCommentData ();

		// Set comment file name
		if (isset ($this->formData->replyTo)) {
			// Verify file exists
			$this->verifyFile ('reply-to');

			// Comment number
			$comment_number = $this->thread->threadCount[$this->formData->replyTo];

			// Rename file for reply
			$comment_file = $this->formData->replyTo . '-' . $comment_number;
		} else {
			$comment_file = (string)($this->thread->primaryCount);
		}

		// Check if comment thread exists
		$this->thread->data->checkThread ();

		// Write comment file
		$status = $this->writeComment ($comment_file);

		// And return result of comment file write
		return $status;
	}
}
