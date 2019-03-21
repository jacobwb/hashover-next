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


class Email extends Secrets
{
	// E-mail delivery method class
	protected $mailer;

	public function __construct (Setup $setup)
	{
		// Name of mailer class to instantiate
		$mail_class = 'HashOver\\' . $setup->mailer;

		// Instantiate mailer class
		$this->mailer = new $mail_class ($setup);

		// Setup SMTP Mailer
		if (mb_strtolower ($setup->mailer) !== 'sendmail') {
			// Set SMTP server host
			$this->mailer->setHost ($this->smtpHost);

			// Set SMTP server port number
			$this->mailer->setPort ($this->smtpPort);

			// Set SMTP server encryption method
			$this->mailer->setCrypto ($this->smtpCrypto);

			// Set whether SMTP server requires authentication
			$this->mailer->setAuth ($this->smtpAuth);

			// Set SMTP server login user
			$this->mailer->setUser ($this->smtpUser);

			// Set SMTP server login password
			$this->mailer->setPassword ($this->smtpPassword);
		}
	}

	// Call mailer method
	public function to ($email, $name = null)
	{
		$this->mailer->to ($email, $name);
	}

	// Call mailer method
	public function from ($email, $name = null)
	{
		$this->mailer->from ($email, $name);
	}

	// Call mailer method
	public function subject ($text)
	{
		$this->mailer->subject ($text);
	}

	// Call mailer method
	public function replyTo ($email, $name = null)
	{
		$this->mailer->replyTo ($email, $name);
	}

	// Call mailer method
	public function text ($text)
	{
		$this->mailer->text ($text);
	}

	// Call mailer method
	public function html ($html)
	{
		$this->mailer->html ($html);
	}

	// Call mailer method
	public function send ()
	{
		$this->mailer->send ();
	}
}
