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


// Generates an array of various settings information
function ui_array (Setup $setup)
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
			'description' => 'Language',
			'type' => 'select',
			'value' => $setup->language,

			'options' => array (
				'auto' => 'auto',
				'en' => 'English',
				'da' => 'Danish',
				'de' => 'German',
				'es' => 'Spanish',
				'fa' => 'Persian',
				'fr' => 'French',
				'jp' => 'Japanese',
				'ko' => 'Korean',
				'lt' => 'Lithuanian',
				'nl' => 'Dutch',
				'pl' => 'Polish',
				'pt-br' => 'Brazilian Portuguese',
				'ro' => 'Romanian',
				'tr' => 'Turkish',
				'zh-cn' => 'Simplified Chinese'
			)
		),
		'theme' => array (
			'description' => 'Theme',
			'type' => 'select',
			'value' => $setup->theme,
			'options' => $themes
		),
		'uses-moderation' => array (
			'description' => 'Comments posted by normal users require moderation',
			'type' => 'checkbox',
			'value' => $setup->usesModeration
		),
		'pends-user-edits' => array (
			'description' => 'Comments edited by normal users require additional moderation',
			'type' => 'checkbox',
			'value' => $setup->pendsUserEdits
		),
		'data-format' => array (
			'description' => 'Comment data format',
			'type' => 'select',
			'value' => $setup->dataFormat,

			'options' => array (
				'xml' => 'XML',
				'json' => 'JSON',
				'sql' => 'SQL'
			)
		),
		'default-name' => array (
			'description' => 'Default commenter name',
			'type' => 'text',
			'value' => $setup->defaultName
		),
		'allows-images' => array (
			'description' => 'Allow display of images in comments',
			'type' => 'checkbox',
			'value' => $setup->allowsImages
		),
		'allows-login' => array (
			'description' => 'Allow users to login',
			'type' => 'checkbox',
			'value' => $setup->allowsLogin
		),
		'allows-likes' => array (
			'description' => 'Allow users to like comments',
			'type' => 'checkbox',
			'value' => $setup->allowsLikes
		),
		'allows-dislikes' => array (
			'description' => 'Allow users to dislike comments',
			'type' => 'checkbox',
			'value' => $setup->allowsDislikes
		),
		'uses-ajax' => array (
			'description' => 'Enable asynchronous JavaScript features',
			'type' => 'checkbox',
			'value' => $setup->usesAjax
		),
		'collapses-interface' => array (
			'description' => 'Collapse entire HashOver user interface',
			'type' => 'checkbox',
			'value' => $setup->collapsesInterface
		),
		'collapses-comments' => array (
			'description' => 'Collapse a configurable number of comments',
			'type' => 'checkbox',
			'value' => $setup->collapsesComments
		),
		'collapse-limit' => array (
			'description' => 'Number of comments to collapse',
			'type' => 'number',
			'value' => $setup->collapseLimit
		),
		'reply-mode' => array (
			'description' => 'Display mode of comment threads',
			'type' => 'select',
			'value' => $setup->replyMode,

			'options' => array (
				'thread' => 'Threaded',
				'stream' => 'Stream'
			)
		),
		'stream-depth' => array (
			'description' => 'Number of reply indentions before stream is flattened',
			'type' => 'number',
			'value' => $setup->streamDepth
		),
		'popularity-threshold' => array (
			'description' => 'Net number of likes a comment needs to be popular',
			'type' => 'number',
			'value' => $setup->popularityThreshold
		),
		'popularity-limit' => array (
			'description' => 'Number of popular comments to display',
			'type' => 'number',
			'value' => $setup->popularityLimit
		),
		'uses-markdown' => array (
			'description' => 'Enable Markdown support',
			'type' => 'checkbox',
			'value' => $setup->usesMarkdown
		),
		'server-timezone' => array (
			'description' => 'Server timezone',
			'type' => 'text',
			'value' => $setup->serverTimezone
		),
		'uses-user-timezone' => array (
			'description' => 'Display dates/times in user\'s timezone (JavaScript-mode)',
			'type' => 'checkbox',
			'value' => $setup->usesUserTimezone
		),
		'uses-short-dates' => array (
			'description' => 'Enable shorter dates/times (example "1 day ago")',
			'type' => 'checkbox',
			'value' => $setup->usesShortDates
		),
		'time-format' => array (
			'description' => 'Time format, use "H:i" for 24-hour format',
			'documentation' => 'http://php.net/manual/en/function.date.php',
			'type' => 'text',
			'value' => $setup->timeFormat
		),
		'date-format' => array (
			'description' => 'Date format',
			'documentation' => 'http://php.net/manual/en/function.date.php',
			'type' => 'text',
			'value' => $setup->dateFormat
		),
		'displays-title' => array (
			'description' => 'Enable display of page title',
			'type' => 'checkbox',
			'value' => $setup->displaysTitle
		),
		'form-position' => array (
			'description' => 'Position for primary comment form',
			'type' => 'select',
			'value' => $setup->formPosition,

			'options' => array (
				'top' => 'Top',
				'bottom' => 'Bottom'
			)
		),
		'uses-auto-login' => array (
			'description' => 'Automatically log users in when they post comments',
			'type' => 'checkbox',
			'value' => $setup->usesAutoLogin
		),
		'shows-reply-count' => array (
			'description' => 'Display reply count separately from total count',
			'type' => 'checkbox',
			'value' => $setup->showsReplyCount
		),
		'count-includes-deleted' => array (
			'description' => 'Include deleted comments comment counts',
			'type' => 'checkbox',
			'value' => $setup->countIncludesDeleted
		),
		'icon-mode' => array (
			'description' => 'Avatar icon display mode',
			'type' => 'select',
			'value' => $setup->iconMode,

			'options' => array (
				'image' => 'Image',
				'count' => 'Count',
				'none' => 'None'
			)
		),
		'icon-size' => array (
			'description' => 'Avatar icon size',
			'type' => 'number',
			'value' => $setup->iconSize
		),
		'image-format' => array (
			'description' => 'Format for icons and other images',
			'type' => 'select',
			'value' => $setup->imageFormat,

			'options' => array (
				'png' => 'PNG',
				'svg' => 'SVG'
			)
		),
		'uses-labels' => array (
			'description' => 'Display labels above inputs',
			'type' => 'checkbox',
			'value' => $setup->usesLabels
		),
		'uses-cancel-buttons' => array (
			'description' => 'Whether forms have cancel buttons',
			'type' => 'checkbox',
			'value' => $setup->usesCancelButtons
		),
		'appends-css' => array (
			'description' => 'Automatically add HashOver CSS to page',
			'type' => 'checkbox',
			'value' => $setup->appendsCss
		),
		'appends-rss' => array (
			'description' => 'Add HashOver RSS Feed links to page',
			'type' => 'checkbox',
			'value' => $setup->appendsRss
		),
		'login-method' => array (
			'description' => 'User login system',
			'type' => 'select',
			'value' => $setup->loginMethod,
			'options' => array ('defaultLogin' => 'Default Login')
		),
		'sets-cookies' => array (
			'description' => 'Enable cookies',
			'type' => 'checkbox',
			'value' => $setup->setsCookies
		),
		'secure-cookies' => array (
			'description' => 'Use secure HTTPS-only cookies',
			'type' => 'checkbox',
			'value' => $setup->secureCookies
		),
		'stores-ip-address' => array (
			'description' => 'Enable storage of user IP addresses',
			'type' => 'checkbox',
			'value' => $setup->storesIpAddress
		),
		'allows-user-replies' => array (
			'description' => 'Set user e-mail as "Reply-To" in reply notifications',
			'type' => 'checkbox',
			'value' => $setup->allowsUserReplies
		),
		'noreply-email' => array (
			'description' => 'E-mail address used when no e-mail is given',
			'type' => 'text',
			'value' => $setup->noreplyEmail
		),
		'spam-batabase' => array (
			'description' => 'SPAM database location',
			'type' => 'select',
			'value' => $setup->spamDatabase,

			'options' => array (
				'remote' => 'StopForumSpam.com',
				'local' => 'Local CSV file'
			)
		),
		'spam-check-modes' => array (
			'description' => 'Modes to perform SPAM check under',
			'type' => 'select',
			'value' => $setup->spamCheckModes,

			'options' => array (
				'json' => 'JSON',
				'php' => 'PHP',
				'both' => 'Both'
			)
		),
		'gravatar-force' => array (
			'description' => 'Use themed Gravatar images instead of avatars',
			'type' => 'checkbox',
			'value' => $setup->gravatarForce
		),
		'gravatar-default' => array (
			'description' => 'Default Gravatar theme to use',
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
		'minifies-javascript' => array (
			'description' => 'Enable JavaScript minification',
			'type' => 'checkbox',
			'value' => $setup->minifiesJavascript
		),
		'minify-level' => array (
			'description' => 'JavaScript minification level',
			'type' => 'select',
			'cast' => 'number',
			'value' => $setup->minifyLevel,

			'options' => array (
				1 => 'Basic (removes code comments)',
				2 => 'Low (removes whitespace + Basic)',
				3 => 'Medium (removes newlines + Low)',
				4 => 'High (removes extra bits + Medium)'
			)
		),
		'allow-local-metadata' => array (
			'description' => 'Allow page metadata to be updated from localhost',
			'type' => 'checkbox',
			'value' => $setup->allowLocalMetadata
		)
	);
}

try {
	// View setup
	require (realpath ('../view-setup.php'));

	// Get array of UI elements to create
	$ui = ui_array ($hashover->setup);

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
					$settings[$name] = (string)($_POST[$name]);
					break;
				}
			}
		}

		// Save the settings to the JSON settings file
		if ($data_files->saveJSON ($settings_file, $settings)) {
			// Redirect with success indicator
			header ('Location: index.php?status=success');
		} else {
			// Redirect with failure indicator
			header ('Location: index.php?status=failure');
		}

		// Exit after redirect
		exit;
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
		// Create table row
		$tr = new HTMLTag ('tr');

		// Create setting description cell
		$description = new HTMLTag ('td');

		// Create description label
		$label = new HTMLTag ('label', array (
			'for' => $name,
			'innerHTML' => $setting['description']
		), false);

		// Check for documentation URL
		if (!empty ($setting['documentation'])) {
			// Create documentation link
			$docs = new HTMLTag ('a', array (
				'href' => $setting['documentation'],
				'target' => '_blank',
				'innerHTML' => 'documentation'
			), false);

			// Append documentation in parentheses
			$label->appendInnerHTML ('(' . $docs->asHTML () . ')');
		}

		// Append label to description cell
		$description->appendChild ($label);

		// Append description cell to settings table row
		$tr->appendChild ($description);

		// Create setting value cell
		$field = new HTMLTag ('td');

		switch ($setting['type']) {
			case 'checkbox': {
				// Create checkbox for enabling/disabling the setting
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

				foreach ($setting['options'] as $value => $text) {
					// Create setting option
					$option = new HTMLTag ('option', array (
						'value' => $value,
						'innerHTML' => $text
					), false);

					// Select proper option
					if ($value === $setting['value']) {
						$option->createAttribute ('selected', 'true');
					}

					// Append option to menu
					$select->appendChild ($option);
				}

				// Append dropdown menu to wrapper element
				$element->appendChild ($select);

				break;
			}
		}

		// Append the setting value element to the setting value cell
		$field->appendChild ($element);

		// Append the setting value cell to the settings table row
		$tr->appendChild ($field);

		// Add row to settings table
		$table->appendChild ($tr);
	}

	// Page title based on the status indicator
	switch (!empty ($_GET['status']) ? $_GET['status'] : 'default') {
		case 'success': {
			$title = 'Settings - Saved!';
			break;
		}

		case 'failure': {
			$title = 'Settings - Failure!';
			break;
		}

		default: {
			$title = 'Settings';
			break;
		}
	}

	// Template data
	$template = array (
		'title'		=> $title,
		'logout'	=> $logout->asHTML ("\t\t\t"),
		'sub-title'	=> 'Change various settings',
		'settings'	=> $table->asHTML ("\t\t\t"),
		'save-button'	=> 'Save Settings'
	);

	// Load and parse HTML template
	echo $hashover->templater->parseTemplate ('settings.html', $template);

} catch (\Exception $error) {
	$misc = new Misc ('php');
	$message = $error->getMessage ();
	$misc->displayError ($message);
}
