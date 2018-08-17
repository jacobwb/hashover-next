<?php namespace HashOver;

// Copyright (C) 2018 Jacob Barkdull
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
	// E-mail data to send
	protected $to;
	protected $from;
	protected $subject;
	protected $reply;
	protected $text;

	// Type of content being sent
	protected $type = 'text';

	// Sets who we're sending the e-mail to
	public function to ($email)
	{
		$this->to = $email;
	}

	// Sets who the e-mail is coming from
	public function from ($email)
	{
		// Set "from" e-mail address
		$this->from = $email;

		// Set "reply-to" the same way
		if (empty ($this->reply)) {
			$this->replyTo ($email);
		}
	}

	// Sets subject line
	public function subject ($text)
	{
		$this->subject = $text;
	}

	// Sets where the recipient can reply to
	public function replyTo ($email)
	{
		$this->reply = $email;
	}

	// Converts message to plain text
	protected function plainText ($text)
	{
		// Strip HTML tags
		$text = strip_tags ($text);

		// Convert HTML entities to normal characters
		$text = html_entity_decode ($text, ENT_COMPAT, 'UTF-8');

		// Wordwrap text to 70 characters
		$text = wordwrap ($text, 70, "\n");

		return $text;
	}

	// Sets the message body
	public function text ($text)
	{
		// Set text property
		$this->text = $this->plainText ($text);

		// And set type to text
		$this->type = 'text';
	}

	// Sets the message body to HTML
	public function html ($html)
	{
		// Set body property
		$this->html = $html;

		// Set automatic text version of the message
		if (empty ($this->text)) {
			$this->text = $this->plainText ($html);
		}

		// And set type to HTML
		$this->type = 'html';
	}

	// Sets the message body
	public function body ($text, $html = false)
	{
		// Set body as HTML if told to
		if ($html === true) {
			return $this->html ($text);
		}

		// Otherwise, set body as plain text
		return $this->text ($text);
	}

	// Creates mail headers
	protected function getHeaders ($boundary)
	{
		// Initial headers data
		$data = array ();

		// Add recipient headers
		$data[] = 'MIME-Version: 1.0';
		$data[] = 'From: ' . $this->from;
		$data[] = 'Reply-To: ' . $this->reply;

		// Check if the message type is text
		if ($this->type === 'text') {
			// If so, add plain text header
			$data[] = 'Content-Type: text/plain; charset="UTF-8"';
		} else {
			// If not, add multipart header
			$data[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
		}

		// Convert data to string
		$headers = implode ("\r\n", $data);

		return $headers;
	}

	// Creates mail message
	protected function getMessage ($boundary)
	{
		// Initial message data
		$data = array ();

		// Check if the message type is text
		if ($this->type === 'text') {
			// If so, only add the text version
			$data[] = $this->text;
		} else {
			// If not, start multipart boundary
			$data[] = '--' . $boundary;

			// Add the text version
			$data[] = 'Content-Type: text/plain; charset="UTF-8"';
			$data[] = '';
			$data[] = $this->text;

			// Add another multipart boundary
			$data[] = '--' . $boundary;

			// Add the HTML version
			$data[] = 'Content-Type: text/html; charset="UTF-8"';
			$data[] = '';
			$data[] = $this->html;

			// And end multipart boundary
			$data[] = '--' . $boundary . '--';
		}

		// Convert data to string
		$headers = implode ("\r\n", $data);

		return $headers;
	}

	// Sends an e-mail
	public function send ()
	{
		// Create unique boundary
		$boundary = md5 (uniqid (time ()));

		// Get mail headers
		$headers = $this->getHeaders ($boundary);

		// Get message
		$message = $this->getMessage ($boundary);

		// Send mail
		mail ($this->to, $this->subject, $message, $headers);
	}
}
