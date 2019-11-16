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


class Sendmail
{
	// Email data to send
	protected $to = array ();
	protected $from = array ();
	protected $reply = array ();
	protected $subject;
	protected $text;

	// Type of content being sent
	protected $type = 'text';

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

	// Converts text to CRLF line ending format
	protected function toCRLF ($text)
	{
		return preg_replace ('/\r\n|\r|\n/', "\r\n", $text);
	}

	// Converts message to plain text
	protected function plainText ($text)
	{
		// Strip HTML tags
		$text = strip_tags ($text);

		// Convert to CRLF line ending format
		$text = $this->toCRLF ($text);

		// Convert HTML entities to normal characters
		$text = html_entity_decode ($text, ENT_COMPAT, 'UTF-8');

		// Encode text in quoted-printable format
		$text = quoted_printable_encode ($text);

		// And return text
		return $text;
	}

	// Sets message body
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
		// Convert to CRLF line ending format
		$this->html = $this->toCRLF ($html);

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
			// If so, encode name
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

	// Creates mail headers
	protected function getHeaders ($boundary)
	{
		// Initial headers data
		$data = array ();

		// Add recipient headers
		$data[] = 'MIME-Version: 1.0';

		// Set From address if one is present
		if (!empty ($this->from['email'])) {
			$data[] = 'From: ' . $this->format ($this->from);
		}

		// Set Reply-To address if one is present
		if (!empty ($this->reply['email'])) {
			$data[] = 'Reply-To: ' . $this->format ($this->reply);
		}

		// Check if message type is text
		if ($this->type === 'text') {
			// If so, add plain text header
			$data[] = 'Content-Type: text/plain; charset="UTF-8"';
			$data[] = 'Content-Transfer-Encoding: quoted-printable';
		} else {
			// If not, add multipart header
			$data[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
		}

		// Convert headers data to string
		$headers = implode ("\r\n", $data);

		// And return headers as string
		return $headers;
	}

	// Creates mail message
	protected function getMessage ($boundary)
	{
		// Initial message data
		$data = array ();

		// Check if message type is text
		if ($this->type === 'text') {
			// If so, only add text version
			$data[] = $this->text;
		} else {
			// If not, start multipart boundary
			$data[] = '--' . $boundary;

			// Add text version
			$data[] = 'Content-Type: text/plain; charset="UTF-8"';
			$data[] = 'Content-Transfer-Encoding: quoted-printable';
			$data[] = '';
			$data[] = $this->text;

			// Add another multipart boundary
			$data[] = '--' . $boundary;

			// Add HTML version
			$data[] = 'Content-Type: text/html; charset="UTF-8"';
			$data[] = 'Content-Transfer-Encoding: quoted-printable';
			$data[] = '';
			$data[] = $this->html;

			// And end multipart boundary
			$data[] = '--' . $boundary . '--';
		}

		// Convert message data to string
		$headers = implode ("\r\n", $data);

		// And return message as string
		return $headers;
	}

	// Sends an email
	public function send ()
	{
		// Get email address we're sending email to
		$to = $this->to['email'];

		// Get quoted-printable encoded subject
		$subject = $this->encode ($this->subject);

		// Create unique boundary
		$boundary = md5 (uniqid (time ()));

		// Get message body
		$message = $this->getMessage ($boundary);

		// Get email headers
		$headers = $this->getHeaders ($boundary);

		// Check if a From email address is set
		if (!empty ($this->from['email'])) {
			// If so, set envelope sender address with -f option
			$params = '-f ' . $this->from['email'];

			// And actually send the email
			mail ($to, $subject, $message, $headers, $params);
		} else {
			// If not, send email normally
			mail ($to, $subject, $message, $headers);
		}
	}
}
