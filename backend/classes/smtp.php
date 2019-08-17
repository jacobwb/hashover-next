<?php namespace HashOver;

// Copyright (C) 2018-2019 Jacob Barkdull
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


class SMTP
{
	// Unix domain socket connection file pointer
	protected $fp;

	// Time until connection gives up
	protected $timeout = 30;

	// Local host to greet connection with
	protected $localhost = 'localhost';

	// Server authentication credentials
	protected $host;
	protected $port;
	protected $crypto;
	protected $auth;
	protected $user;
	protected $password;

	// Email data to send
	protected $to = array ();
	protected $from = array ();
	protected $reply = array ();
	protected $subject;
	protected $text;
	protected $html;

	// Type of content being sent
	protected $type = 'text';

	// Sets SMTP host server
	public function setHost ($host)
	{
		$this->host = $host;
	}

	// Sets SMTP server port
	public function setPort ($port)
	{
		$this->port = $port;
	}

	// Sets SMTP cryptography
	public function setCrypto ($crypto)
	{
		$this->crypto = $crypto;
	}

	// Sets SMTP authentication
	public function setAuth ($auth)
	{
		$this->auth = $auth;
	}

	// Sets SMTP server user
	public function setUser ($user)
	{
		$this->user = $user;
	}

	// Sets SMTP server password
	public function setPassword ($password)
	{
		$this->password = $password;
	}

	// Sets who we're sending email to
	public function to ($email, $name = null)
	{
		$this->to['email'] = $email;
		$this->to['name'] = $name;
	}

	// Sets where recipient can reply to
	public function replyTo ($email, $name = null)
	{
		$this->reply['email'] = $email;
		$this->reply['name'] = $name;
	}

	// Sets who email is coming from
	public function from ($email, $name = null)
	{
		// Set "from" email address and name
		$this->from['email'] = $email;
		$this->from['name'] = $name;

		// Set "reply-to" the same way
		if (empty ($this->reply['email'])) {
			$this->replyTo ($email, $name);
		}
	}

	// Sets subject line
	public function subject ($text)
	{
		$this->subject = strip_tags ($text);
	}

	// Makes content comply to RFC-821
	protected function rfc ($content)
	{
		// Line ending styles to convert
		$styles = array ("\r\n", "\r");

		// Convert line endings to UNIX-style
		$content = str_replace ($styles, "\n", $content);

		// Wordwrap content to 998 characters
		$content = wordwrap ($content, 998, "\n");

		// Split content by lines
		$lines = explode ("\n", $content);

		// Initial output
		$output = '';

		// Run through lines
		foreach ($lines as $line) {
			// RFC 821 section 4.5.2
			if (!empty ($line) and $line[0] === '.') {
				$line = '.' . $line;
			}

			// Add line to output
			$output .= $line . "\r\n";
		}

		// And return final output
		return $output;
	}

	// Converts message to plain text
	protected function plainText ($text)
	{
		// Strip HTML tags
		$text = strip_tags ($text);

		// Convert HTML entities to normal characters
		$text = html_entity_decode ($text, ENT_COMPAT, 'UTF-8');

		// Make text comply to RFC-821
		$text = $this->rfc ($text);

		// Encode text in quoted-printable format
		$text = quoted_printable_encode ($text);

		// And return text
		return $text;
	}

	// Sets message body to plain text
	public function text ($text)
	{
		// Set text property
		$this->text = $this->plainText ($text);

		// And set type to text
		$this->type = 'text';
	}

	// Sets message body to HTML
	public function html ($html)
	{
		// Conform HTML to comply with RFC-821
		$this->html = $this->rfc ($html);

		// Encode HTML in quoted-printable format
		$this->html = quoted_printable_encode ($this->html);

		// Set automatic text version of message
		if (empty ($this->text)) {
			$this->text = $this->plainText ($html);
		}

		// And set type to HTML
		$this->type = 'html';
	}

	// Sets message body
	public function body ($text, $html = false)
	{
		// Set body as HTML if told to
		if ($html === true) {
			return $this->html ($text);
		}

		// Otherwise, set body as plain text
		return $this->text ($text);
	}

	// Gets connection response
	protected function getResponse ()
	{
		// Initial response
		$response = '';

		// Get response in 4KB chunks
		while ($data = @fgets ($this->fp, 4096)) {
			// Add current lines to response
			$response .= $data;

			// End loop if 4th character is a space
			if (isset ($data[3]) and $data[3] == ' ') {
				break;
			}
		}

		// And return response
		return $response;
	}

	// Gets code from connection response
	protected function getCode ()
	{
		// Get response code
		$response = $this->getResponse ();

		// Filter code from response
		$code = substr ($response, 0, 3);

		// And return code as integer
		return (int) ($code);
	}

	// Sends request data to SMTP server
	protected function request ($data)
	{
		fwrite ($this->fp, $data . "\r\n");
	}

	// Connects to server
	protected function smtpConnect ()
	{
		// Prepend proper URL scheme if we're using SSL
		if ($this->crypto === 'ssl') {
			$this->host = 'ssl://' . $this->host;
		}

		// Check if stream sockets are available
		if (function_exists ('stream_socket_client')) {
			// Create a stream context
			$socket_context = stream_context_create ();

			// Open Unix domain stream connection
			$this->fp = @stream_socket_client (
				$this->host . ':' . $this->port,
				$errno,
				$errstr,
				$this->timeout,
				STREAM_CLIENT_CONNECT,
				$socket_context
			);
		} else {
			// Open Unix domain socket connection
			$this->fp = @fsockopen (
				$this->host,
				$this->port,
				$errno,
				$errstr,
				$this->timeout
			);
		}

		// Return false if connection is not a resource
		if (!is_resource ($this->fp)) {
			return false;
		}

		// Return false if connection failed
		if ($this->getCode () !== 220) {
			return false;
		}

		// Decide greeting
		$greeting = $this->auth ? 'EHLO' : 'HELO';

		// Send greeting to server
		$this->request ($greeting . ' ' . $this->localhost);

		// Return false if greeting failed
		if ($this->getCode () !== 250) {
			return false;
		}

		// Check if we are using TLS
		if ($this->crypto === 'tls') {
			// If so, send TLS handshake to server
			$this->request ('STARTTLS');

			// Return false if TLS handshake failed
			if ($this->getCode () !== 220) {
				return false;
			}

			// Type of encryption of stream
			$crypto_type = STREAM_CRYPTO_METHOD_TLS_CLIENT;

			// PHP 5.6 backwards compatibility
			if (defined ('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
				$crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
				$crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
			}

			// Turn on encryption
			stream_socket_enable_crypto ($this->fp, true, $crypto_type);

			// Send greeting again
			$this->request ($greeting . ' ' . $this->localhost);

			// Return false if greeting failed
			if ($this->getCode () !== 250) {
				return false;
			}
		}

		// Check if authorization is required
		if ($this->auth === true) {
			// If so, send request for login
			$this->request ('AUTH LOGIN');

			// Return false if login authentication failed
			if ($this->getCode () !== 334) {
				return false;
			}

			// Send user name for login
			$this->request (base64_encode ($this->user));

			// Return false if user name login failed
			if ($this->getCode () !== 334) {
				return false;
			}

			// Send password for login
			$this->request (base64_encode ($this->password));

			// Return false if password login failed
			if ($this->getCode () !== 235) {
				return false;
			}
		}

		// Otherwise, return true
		return true;
	}

	// Encodes given text as MIME "encoded word"
	protected function encode ($text)
	{
		return mb_encode_mimeheader ($text);
	}

	// Converts to/from/reply-to to a formatted string
	public function format (array $recipient)
	{
		// Check if a name was given
		if (!empty ($recipient['name'])) {
			// If so, get encoded name
			$name = $this->encode ($recipient['name']);

			// And construct email address in "name <email>" format
			$address = $name . ' <' . $recipient['email'] . '>';
		} else {
			// If not, use email address as-is
			$address = $recipient['email'];
		}

		// And return email address
		return $address;
	}

	// Creates SMTP transport
	protected function smtpTransport ()
	{
		// Initial transport headers
		$headers = array ();

		// Add recipient headers
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-Transfer-Encoding: 7bit';
		$headers[] = 'To: ' . $this->format ($this->to);
		$headers[] = 'From: ' . $this->format ($this->from);
		$headers[] = 'Reply-To: ' . $this->format ($this->reply);
		$headers[] = 'Subject: ' . $this->encode ($this->subject);
		$headers[] = 'Date: ' . date ('r');

		// Check if message type is text
		if ($this->type === 'text') {
			// If so, only add headers for text version
			$headers[] = 'Content-Type: text/plain; charset="UTF-8"';
			$headers[] = 'Content-Transfer-Encoding: quoted-printable';
			$headers[] = '';
			$headers[] = $this->text;
		} else {
			// If not, create unique boundary
			$boundary = md5 (uniqid (time ()));

			// Add multipart headers
			$headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
			$headers[] = '';
			$headers[] = 'This is a multi-part message in MIME format.';

			// Start multipart boundary
			$headers[] = '--' . $boundary;

			// Add headers for text version
			$headers[] = 'Content-Type: text/plain; charset="UTF-8"';
			$headers[] = 'Content-Transfer-Encoding: quoted-printable';
			$headers[] = '';
			$headers[] = $this->text;

			// Add another multipart boundary
			$headers[] = '--' . $boundary;

			// Add headers for HTML version
			$headers[] = 'Content-Type: text/html; charset="UTF-8"';
			$headers[] = 'Content-Transfer-Encoding: quoted-printable';
			$headers[] = '';
			$headers[] = $this->html;

			// And end multipart boundary
			$headers[] = '--' . $boundary . '--';
		}

		// Add final period to end message data
		$headers[] = '.';

		// Convert headers to string
		$transport = implode ("\r\n", $headers);

		// And return final headers for transport
		return $transport;
	}

	// Sends full SMTP request
	protected function smtpDeliver ()
	{
		// Send who email is coming from
		$this->request ('MAIL FROM: <' . $this->from['email'] . '>');

		// Return false if sender address failed
		if ($this->getCode () !== 250) {
			return false;
		}

		// Send recipient email address
		$this->request ('RCPT TO: <' . $this->to['email'] . '>');

		// Return false if recipient address failed
		if ($this->getCode () !== 250) {
			return false;
		}

		// Send intent to begin data
		$this->request ('DATA');

		// Return false if data intent failed
		if ($this->getCode () !== 354) {
			return false;
		}

		// Send message data
		$this->request ($this->smtpTransport ());

		// Return false if message data failed
		if ($this->getCode () === 250) {
			return false;
		}

		// Otherwise, return ture
		return true;
	}

	// Disconnects from server
	protected function smtpDisconnect ()
	{
		// Do nothing if connection is not a resource
		if (!is_resource ($this->fp)) {
			return;
		}

		// Send intent to close connection
		$this->request ('QUIT');

		// Ignore response
		$this->getResponse ();

		// And close connection
		fclose ($this->fp);
	}

	// Sends an email
	public function send ()
	{
		// Check if we can connect to server
		if ($this->smtpConnect () === true) {
			// If so, send email
			$result = $this->smtpDeliver ();
		} else {
			// If not, assume failure
			$result = false;
		}

		// Disconnect from server
		$this->smtpDisconnect ();

		// And return SMTP delivery result
		return $result;
	}
}
