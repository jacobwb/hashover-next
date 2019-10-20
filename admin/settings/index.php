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


// Generates an array of various settings information
function ui_array (Setup $setup, Locale $locale)
{
	// Theme names
	$themes = array ();

	// Themes directory
	$themes_directory = $setup->getAbsolutePath ('themes');

	// Get each theme directory name
	foreach (glob ($themes_directory . '/*', GLOB_ONLYDIR) as $directory) {
		$theme = basename ($directory);
		$themes[$theme] = $theme;
	}

	// Form fields options
	$field_options = array (
		'on'		=> $locale->text['enabled'],
		'required'	=> $locale->text['required'],
		'off'		=> $locale->text['disabled'],
	);

	// Return array of settings allowed to be changed
	return array (
		'general' => array (
			'language' => array (
				'type' => 'select',
				'value' => $setup->language,

				'options' => array (
					'en-us' => 'English',
					'pt-br' => 'Brazilian Portuguese',
					'da-dk' => 'Danish',
					'nl-nl' => 'Dutch',
					'eo-eo' => 'Esperanto',
					'fr-fr' => 'French',
					'de-de' => 'German',
					'el-el' => 'Greek',
					'ja-jp' => 'Japanese',
					'ko-kr' => 'Korean',
					'lt-lt' => 'Lithuanian',
					'fa-ir' => 'Persian',
					'pl-pl' => 'Polish',
					'ro-ro' => 'Romanian',
					'ru-ru' => 'Russian',
					'zh-cn' => 'Simplified Chinese',
					'es-es' => 'Spanish',
					'tr-tr' => 'Turkish'
				)
			),

			'theme' => array (
				'type' => 'select',
				'value' => $setup->theme,
				'options' => $themes
			),

			'default-sorting' => array (
				'type' => 'select',
				'value' => $setup->defaultSorting,

				'options' => array (
					'ascending'	=> $locale->text['sort-ascending'],
					'descending'	=> $locale->text['sort-descending'],
					'by-date'	=> $locale->text['sort-by-date'],
					'by-likes'	=> $locale->text['sort-by-likes'],
					'by-replies'	=> $locale->text['sort-by-replies'],
					'by-name'	=> $locale->text['sort-by-name'],

					'sort-threads' => array (
						'threaded-descending'	=> $locale->text['sort-descending'],
						'threaded-by-date'	=> $locale->text['sort-by-date'],
						'threaded-by-likes'	=> $locale->text['sort-by-likes'],
						'by-popularity'		=> $locale->text['sort-by-popularity'],
						'by-discussion'		=> $locale->text['sort-by-discussion'],
						'threaded-by-name'	=> $locale->text['sort-by-name']
					)
				)
			),

			'uses-markdown' => array (
				'type' => 'checkbox',
				'value' => $setup->usesMarkdown
			),

			'uses-ajax' => array (
				'type' => 'checkbox',
				'value' => $setup->usesAjax
			),

			'shows-reply-count' => array (
				'type' => 'checkbox',
				'value' => $setup->showsReplyCount
			),

			'allows-images' => array (
				'type' => 'checkbox',
				'value' => $setup->allowsImages
			),

			'allows-likes' => array (
				'type' => 'checkbox',
				'value' => $setup->allowsLikes
			),

			'allows-dislikes' => array (
				'type' => 'checkbox',
				'value' => $setup->allowsDislikes
			)
		),

		'moderation' => array (
			'uses-moderation' => array (
				'type' => 'checkbox',
				'value' => $setup->usesModeration
			),

			'pends-user-edits' => array (
				'type' => 'checkbox',
				'value' => $setup->pendsUserEdits
			)
		),

		'e-mail' => array (
			'mail-type' => array (
				'type' => 'select',
				'value' => $setup->mailType,

				'options' => array (
					'text' => 'Text',
					'html' => 'HTML'
				)
			),

			'mailer' => array (
				'type' => 'select',
				'value' => $setup->mailer,

				'options' => array (
					'sendmail' => 'Sendmail',
					'smtp' => 'SMTP'
				)
			),

			'subscribes-user' => array (
				'type' => 'checkbox',
				'value' => $setup->subscribesUser
			),

			'allows-user-replies' => array (
				'type' => 'checkbox',
				'value' => $setup->allowsUserReplies
			)
		),

		'cookies' => array (
			'sets-cookies' => array (
				'type' => 'checkbox',
				'value' => $setup->setsCookies
			),

			'cookie-expiration' => array (
				'type' => 'text',
				'documentation'=> 'https://www.php.net/manual/en/datetime.formats.relative.php',
				'value' => $setup->cookieExpiration
			),

			'secure-cookies' => array (
				'type' => 'checkbox',
				'value' => $setup->secureCookies
			)
		),

		'comment-collapsing' => array (
			'collapses-interface' => array (
				'type' => 'checkbox',
				'value' => $setup->collapsesInterface
			),

			'collapses-comments' => array (
				'type' => 'checkbox',
				'value' => $setup->collapsesComments
			),

			'collapse-limit' => array (
				'type' => 'number',
				'value' => $setup->collapseLimit
			)
		),

		'popular-comments' => array (
			'popularity-threshold' => array (
				'type' => 'number',
				'value' => $setup->popularityThreshold
			),

			'popularity-limit' => array (
				'type' => 'number',
				'value' => $setup->popularityLimit
			)
		),

		'spam-protection' => array (
			'spam-database' => array (
				'type' => 'select',
				'value' => $setup->spamDatabase,

				'options' => array (
					'remote' => 'StopForumSpam.com',
					'local' => 'Local CSV file'
				)
			),

			'spam-check-modes' => array (
				'type' => 'select',
				'value' => $setup->spamCheckModes,

				'options' => array (
					'both' => 'Both',
					'json' => 'JavaScript',
					'php' => 'PHP'
				)
			)
		),

		'avatars' => array (
			'icon-mode' => array (
				'type' => 'select',
				'value' => $setup->iconMode,

				'options' => array (
					'image' => 'Image',
					'count' => 'Count',
					'none' => 'None'
				)
			),

			'icon-size' => array (
				'type' => 'number',
				'value' => $setup->iconSize
			),

			'gravatar-default' => array (
				'type' => 'select',
				'value' => $setup->gravatarDefault,

				'options' => array (
					'custom' => 'Custom',
					'identicon' => 'Identicon',
					'monsterid' => 'Monsterid',
					'wavatar' => 'Wavatar',
					'retro' => 'Retro'
				)
			),

			'gravatar-force' => array (
				'type' => 'checkbox',
				'value' => $setup->gravatarForce
			)
		),

		'form-fields' => array (
			'form-position' => array (
				'type' => 'select',
				'value' => $setup->formPosition,

				'options' => array (
					'top' => 'Top',
					'bottom' => 'Bottom'
				)
			),

			'name-field' => array (
				'type' => 'select',
				'value' => $setup->nameField,
				'options' => $field_options
			),

			'password-field' => array (
				'type' => 'select',
				'value' => $setup->passwordField,
				'options' => $field_options
			),

			'email-field' => array (
				'type' => 'select',
				'value' => $setup->emailField,
				'options' => $field_options
			),

			'website-field' => array (
				'type' => 'select',
				'value' => $setup->websiteField,
				'options' => $field_options
			),

			'displays-title' => array (
				'type' => 'checkbox',
				'value' => $setup->displaysTitle
			),

			'uses-cancel-buttons' => array (
				'type' => 'checkbox',
				'value' => $setup->usesCancelButtons
			),

			'uses-labels' => array (
				'type' => 'checkbox',
				'value' => $setup->usesLabels
			)
		),

		'date-time' => array (
			'date-pattern' => array (
				'documentation' => 'http://userguide.icu-project.org/formatparse/datetime',
				'type' => 'text',
				'value' => $setup->datePattern
			),

			'time-pattern' => array (
				'type' => 'text',
				'value' => $setup->timePattern
			),

			'server-timezone' => array (
				'documentation' => 'https://php.net/manual/en/timezones.php',
				'type' => 'text',
				'value' => $setup->serverTimezone
			),

			'uses-user-timezone' => array (
				'type' => 'checkbox',
				'value' => $setup->usesUserTimezone
			),

			'uses-short-dates' => array (
				'type' => 'checkbox',
				'value' => $setup->usesShortDates
			)
		),

		'login' => array (
			'login-method' => array (
				'type' => 'select',
				'value' => $setup->loginMethod,

				'options' => array (
					'DefaultLogin' => 'Cookies Login',
					'SessionLogin' => 'Session Login'
				)
			),

			'allows-login' => array (
				'type' => 'checkbox',
				'value' => $setup->allowsLogin
			),

			'uses-auto-login' => array (
				'type' => 'checkbox',
				'value' => $setup->usesAutoLogin
			)
		),

		'technical' => array (
			'data-format' => array (
				'type' => 'select',
				'value' => $setup->dataFormat,

				'options' => array (
					'xml' => 'XML',
					'json' => 'JSON',
					'sql' => 'SQL'
				)
			),

			'default-name' => array (
				'type' => 'text',
				'value' => $setup->defaultName
			),

			'reply-mode' => array (
				'type' => 'select',
				'value' => $setup->replyMode,

				'options' => array (
					'thread' => 'Threaded',
					'stream' => 'Stream'
				)
			),

			'stream-depth' => array (
				'type' => 'number',
				'value' => $setup->streamDepth
			),

			'image-format' => array (
				'type' => 'select',
				'value' => $setup->imageFormat,

				'options' => array (
					'png' => 'PNG',
					'svg' => 'SVG'
				)
			),

			'appends-css' => array (
				'type' => 'checkbox',
				'value' => $setup->appendsCss
			),

			'appends-rss' => array (
				'type' => 'checkbox',
				'value' => $setup->appendsRss
			),

			'counts-deletions' => array (
				'type' => 'checkbox',
				'value' => $setup->countsDeletions
			),

			'local-metadata' => array (
				'type' => 'checkbox',
				'value' => $setup->localMetadata
			),

			'stores-ip-address' => array (
				'type' => 'checkbox',
				'value' => $setup->storesIpAddress
			)
		),

		'JavaScript' => array (
			'minifies-javascript' => array (
				'type' => 'checkbox',
				'value' => $setup->minifiesJavascript
			),

			'minify-level' => array (
				'type' => 'select',
				'cast' => 'number',
				'value' => $setup->minifyLevel,

				'options' => array (
					1 => 'Basic (removes code comments)',
					2 => 'Low (removes whitespace + Basic)',
					3 => 'Medium (removes newlines + Low)',
					4 => 'High (removes extra bits + Medium)'
				)
			)
		)
	);
}

// Creates a select element with setting options
function create_select ($hashover, $name, array $setting)
{
	// Create wrapper element for dropdown menu
	$element = new HTMLTag ('span', array (
		'class' => 'select-wrapper'
	));

	// Create dropdown menu
	$select = new HTMLTag ('select', array (
		'id' => $name,
		'name' => $name,
		'size' => 1
	));

	// Run through setting options
	foreach ($setting['options'] as $value => $data) {
		// Check if the current option is an array
		if (is_array ($data)) {
			// If so, add an option group spacer to menu
			$select->appendChild (new HTMLTag ('optgroup', array (
				'label' => '&nbsp;'
			)));

			// Create an option group with localized label
			$optgroup = new HTMLTag ('optgroup', array (
				'label' => $hashover->locale->text[$value]
			));

			// Run through each optgroup option
			foreach ($data as $opt_value => $opt_text) {
				// Create setting option
				$option = new HTMLTag ('option', array (
					'value' => $opt_value,
					'innerHTML' => $opt_text
				), false);

				// Select proper option
				if ($opt_value === $setting['value']) {
					$option->createAttribute ('selected', 'true');
				}

				// Append option to optgroup
				$optgroup->appendChild ($option);
			}

			// And append optgroup to menu
			$select->appendChild ($optgroup);
		} else {
			// If not, create setting option
			$option = new HTMLTag ('option', array (
				'value' => $value,
				'innerHTML' => $data
			), false);

			// Select proper option
			if ($value === $setting['value']) {
				$option->createAttribute ('selected', 'true');
			}

			// Append option to menu
			$select->appendChild ($option);
		}
	}

	// Append dropdown menu to wrapper element
	$element->appendChild ($select);

	return $element;
}

// Creates a paragraph element for a setting value
function create_paragraph ($hashover, $name, array $setting)
{
	// Create setting description and value paragraph
	$paragraph = new HTMLTag ('p');

	// Setting description locale string
	$text = $hashover->locale->text['setting-' . $name];

	// Create description label
	$label = new HTMLTag ('label', array (
		'for' => $name,
		'innerHTML' => $text
	), false);

	// Get documentation locale string
	$docs = $hashover->locale->text['documentation'];

	// Create documentation link
	$docs_link = new HTMLTag ('a', array (
		'class' => 'docs-link',
		'href' => '../docs/en-us/settings/#' . $name,
		'target' => '_blank',
		'title' => $docs,
		'innerHTML' => '?'
	), false);

	// Append documentation link to description label
	$label->appendChild ($docs_link);

	// Handle specific setting types
	switch ($setting['type']) {
		// Create checkbox for enabling/disabling the setting
		case 'checkbox': {
			// Create checkbox type input element
			$element = new HTMLTag ('input', array (
				'id' => $name,
				'type' => 'checkbox',
				'name' => $name
			), false, true);

			// Set check based on current setting
			if ($setting['value'] !== false) {
				$element->createAttribute ('checked', 'true');
			}

			break;
		}

		// Create text/number box for entering the setting value
		case 'number' : case 'text': {
			// Add class to make label a block element
			$label->createAttribute ('class', 'block');

			// And create number or text type input element
			$element = new HTMLTag ('input', array (
				'id' => $name,
				'type' => $setting['type'],
				'name' => $name,
				'value' => $setting['value'],
				'size' => ($setting['type'] === 'text') ? '25' : '10'
			), false, true);

			break;
		}

		// Create dropdown menu for selecting the setting value
		case 'select': {
			// Add class to make label a block element
			$label->createAttribute ('class', 'block');

			// Create select element
			$element = create_select ($hashover, $name, $setting);

			break;
		}
	}

	// Check if setting is a checkbox
	if ($setting['type'] === 'checkbox') {
		// If so, append setting value element to paragraph
		$paragraph->appendChild ($element);

		// Then append label element to paragraph
		$paragraph->appendChild ($label);
	} else {
		// If not, append label element to paragraph
		$paragraph->appendChild ($label);

		// Then append setting value element to paragraph
		$paragraph->appendChild ($element);
	}

	return $paragraph;
}

try {
	// Do some standard HashOver setup work
	require (realpath ('../../backend/standard-setup.php'));

	// View setup
	require (realpath ('../view-setup.php'));

	// Get array of UI elements to create
	$ui = ui_array ($hashover->setup, $hashover->locale);

	// Check if the form has been submitted
	if (isset ($_POST['save'])) {
		// Settings JSON file path
		$settings_file = $hashover->setup->getAbsolutePath ('config/settings.json');

		// Read JSON settings file
		$json = $data_files->readJSON ($settings_file);

		// Existing JSON settings or an empty array
		$settings = ($json !== false) ? $json : array ();

		// Run through configurable settings
		foreach ($ui as $items) {
			// Run through each settings category
			foreach ($items as $name => $setting) {
				// Use specified type or optional cast
				$type = !empty ($setting['cast']) ? 'cast' : 'type';

				// Handle specific setting types
				switch ($setting[$type]) {
					// Set value to boolean based on POST data
					case 'checkbox': {
						$settings[$name] = isset ($_POST[$name]);
						break;
					}

					// Cast number values to integers
					case 'number': {
						$settings[$name] = (int)($_POST[$name]);
						break;
					}

					// All other values are strings
					default: {
						// Check if setting has a value
						if (!empty ($_POST[$name])) {
							// If so, cast it to string before setting it
							$settings[$name] = (string)($_POST[$name]);
						} else {
							// If not, remove the setting entirely
							unset ($settings[$name]);
						}

						break;
					}
				}
			}
		}

		// Check if the user login is admin
		if ($hashover->login->verifyAdmin () === true) {
			// If so, attempt to save the settings
			$saved = $data_files->saveJSON ($settings_file, $settings);

			// If saved successfully, redirect with success indicator
			if ($saved === true) {
				redirect ('./?status=success');
			}
		}

		// Otherwise, redirect with failure indicator
		redirect ('./?status=failure');
	}

	// Otherwise, create settings div
	$div = new HTMLTag ('div', array (
		'id' => 'settings'
	));

	// Create settings divs
	foreach ($ui as $locale => $settings) {
		// Replace key with locale string if one exists
		if (!empty ($hashover->locale->text[$locale])) {
			$locale = $hashover->locale->text[$locale];
		}

		// Append settings category text to settings div
		$div->appendChild (new HTMLTag ('p', array (
			'class' => 'settings-category',
			'innerHTML' => $locale
		), false));

		// Create settings items element
		$items = new HTMLTag ('div', array (
			'class' => 'settings'
		));

		// Append each settings category items to div
		foreach ($settings as $name => $setting) {
			$items->appendChild (create_paragraph ($hashover, $name, $setting));
		}

		// Append settings items element to div
		$div->appendChild ($items);
	}

	// Template data
	$template = array (
		'title'		=> $hashover->locale->text['settings'],
		'sub-title'	=> $hashover->locale->text['settings-sub'],
		'settings'	=> $div->asHTML ("\t"),
		'save-button'	=> $hashover->locale->text['save']
	);

	// Load and parse HTML template
	echo parse_templates ('admin', 'settings.html', $template, $hashover);

} catch (\Exception $error) {
	echo Misc::displayException ($error);
}
