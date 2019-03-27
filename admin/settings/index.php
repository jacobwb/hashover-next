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

	// Return array of settings allowed to be changed
	return array (
		'language' => array (
			'type' => 'select',
			'value' => $setup->language,

			'options' => array (
				'en' => 'English',
				'pt-br' => 'Brazilian Portuguese',
				'da' => 'Danish',
				'nl' => 'Dutch',
				'fr' => 'French',
				'de' => 'German',
				'el' => 'Greek',
				'jp' => 'Japanese',
				'ko' => 'Korean',
				'lt' => 'Lithuanian',
				'fa' => 'Persian',
				'pl' => 'Polish',
				'ro' => 'Romanian',
				'zh-cn' => 'Simplified Chinese',
				'es' => 'Spanish',
				'tr' => 'Turkish'
			)
		),

		'theme' => array (
			'type' => 'select',
			'value' => $setup->theme,
			'options' => $themes
		),

		'form-position' => array (
			'type' => 'select',
			'value' => $setup->formPosition,

			'options' => array (
				'top' => 'Top',
				'bottom' => 'Bottom'
			)
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
		),

		'displays-title' => array (
			'type' => 'checkbox',
			'value' => $setup->displaysTitle
		),

		'uses-labels' => array (
			'type' => 'checkbox',
			'value' => $setup->usesLabels
		),

		'uses-moderation' => array (
			'type' => 'checkbox',
			'value' => $setup->usesModeration
		),

		'pends-user-edits' => array (
			'type' => 'checkbox',
			'value' => $setup->pendsUserEdits
		),

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

		'noreply-email' => array (
			'type' => 'text',
			'value' => $setup->noreplyEmail
		),

		'subscribes-user' => array (
			'type' => 'checkbox',
			'value' => $setup->subscribesUser
		),

		'allows-user-replies' => array (
			'type' => 'checkbox',
			'value' => $setup->allowsUserReplies
		),

		'sets-cookies' => array (
			'type' => 'checkbox',
			'value' => $setup->setsCookies
		),

		'secure-cookies' => array (
			'type' => 'checkbox',
			'value' => $setup->secureCookies
		),

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
		),

		'popularity-threshold' => array (
			'type' => 'number',
			'value' => $setup->popularityThreshold
		),

		'popularity-limit' => array (
			'type' => 'number',
			'value' => $setup->popularityLimit
		),

		'spam-batabase' => array (
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
		),

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
		),

		'server-timezone' => array (
			'documentation' => 'https://php.net/manual/en/timezones.php',
			'type' => 'text',
			'value' => $setup->serverTimezone
		),

		'time-format' => array (
			'documentation' => 'https://php.net/manual/en/function.date.php',
			'type' => 'text',
			'value' => $setup->timeFormat
		),

		'date-format' => array (
			'documentation' => 'https://php.net/manual/en/function.date.php',
			'type' => 'text',
			'value' => $setup->dateFormat
		),

		'uses-user-timezone' => array (
			'type' => 'checkbox',
			'value' => $setup->usesUserTimezone
		),

		'uses-short-dates' => array (
			'type' => 'checkbox',
			'value' => $setup->usesShortDates
		),

		'login-method' => array (
			'type' => 'select',
			'value' => $setup->loginMethod,

			'options' => array (
				'defaultLogin' => 'Default Login'
			)
		),

		'allows-login' => array (
			'type' => 'checkbox',
			'value' => $setup->allowsLogin
		),

		'uses-auto-login' => array (
			'type' => 'checkbox',
			'value' => $setup->usesAutoLogin
		),

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

		'uses-cancel-buttons' => array (
			'type' => 'checkbox',
			'value' => $setup->usesCancelButtons
		),

		'count-includes-deleted' => array (
			'type' => 'checkbox',
			'value' => $setup->countIncludesDeleted
		),

		'allow-local-metadata' => array (
			'type' => 'checkbox',
			'value' => $setup->allowLocalMetadata
		),

		'stores-ip-address' => array (
			'type' => 'checkbox',
			'value' => $setup->storesIpAddress
		),

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

// Creates a table row element for a setting value
function create_tr ($hashover, $name, array $setting)
{
	// Create table row
	$tr = new HTMLTag ('tr');

	// Create setting description cell
	$description = new HTMLTag ('td');

	// Setting description locale string
	$text = $hashover->locale->text['setting-' . $name];

	// Check for documentation URL
	if (!empty ($setting['documentation'])) {
		// If so, get lower case documentation locale string
		$docs = mb_strtolower ($hashover->locale->text['documentation']);

		// Create documentation link
		$docs = new HTMLTag ('a', array (
			'href' => $setting['documentation'],
			'target' => '_blank',
			'innerHTML' => $docs
		), false);

		// And append documentation link to description label text
		$text .= sprintf (' (%s)', $docs->asHTML ());
	}

	// Create description label
	$label = new HTMLTag ('label', array (
		'for' => $name,
		'innerHTML' => $text
	), false);

	// Append label to description cell
	$description->appendChild ($label);

	// Append description cell to settings table row
	$tr->appendChild ($description);

	// Create setting value cell
	$field = new HTMLTag ('td');

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
			// Create number or text type input element
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
			// Create select element
			$element = create_select ($hashover, $name, $setting);

			break;
		}
	}

	// Append the setting value element to the setting value cell
	$field->appendChild ($element);

	// Append the setting value cell to the settings table row
	$tr->appendChild ($field);
}

try {
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
		foreach ($ui as $name => $setting) {
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

	// Otherwise, create settings table
	$table = new HTMLTag ('table', array (
		'id' => 'settings',
		'class' => 'p-spaced',
		'cellspacing' => '0',
		'cellpadding' => '4'
	));

	// Create settings table
	foreach ($ui as $name => $setting) {
		$table->appendChild (create_tr ($hashover, $name, $setting));
	}

	// Template data
	$template = array (
		'sidebar'	=> $sidebar->asHTML ("\t\t"),
		'title'		=> $hashover->locale->text['settings'],
		'logout'	=> $logout->asHTML ("\t\t\t"),
		'sub-title'	=> $hashover->locale->text['settings-sub'],
		'message'	=> $form_message,
		'settings'	=> $table->asHTML ("\t\t\t"),
		'save-button'	=> $hashover->locale->text['save']
	);

	// Load and parse HTML template
	echo $hashover->templater->parseTemplate ('settings.html', $template);

} catch (\Exception $error) {
	echo Misc::displayError ($error->getMessage ());
}
