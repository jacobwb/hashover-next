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


// Check if request is for JSONP
if (isset ($_GET['jsonp'])) {
	// If so, setup HashOver for JavaScript
	require ('javascript-setup.php');
} else {
	// If not, setup HashOver for JSON
	require ('json-setup.php');
}

try {
	// Instantiate HashOver class
	$hashover = new \HashOver ('json');

	// Throw exception if requested by remote server
	$hashover->setup->refererCheck ();

	// Set page URL from POST/GET data
	$hashover->setup->setPageURL ('request');

	// Set page title from POST/GET data
	$hashover->setup->setPageTitle ('request');

	// Set thread name from POST/GET data
	$hashover->setup->setThreadName ('request');

	// Set website from POST/GET data
	$hashover->setup->setWebsite ('request');

	// Set instance from POST/GET data
	$hashover->setup->setInstance ('request');

	// Load user settings
	$hashover->setup->loadFrontendSettings ();

	// Initiate, parse, and finalize comment processing
	$hashover->initiate ();
	$hashover->parsePrimary ();
	$hashover->parsePopular ();
	$hashover->finalize ();

	// Set/update default page metadata
	$hashover->defaultMetadata ();

	// Page, setup, and comment data array
	$data = array ();

	// Check if backend sorting and collapsing is enabled
	if ($hashover->setup->collapsesComments === true
	    and $hashover->setup->usesAjax === true)
	{
		// If so, sort the comments first
		$hashover->sortPrimary ();

		// Then collapse the comments
		$hashover->collapseComments ();
	}

	// Check if we're preparing HashOver
	if ($hashover->setup->getRequest ('prepare') !== false) {
		// If so, add locales to data
		$data['locale'] = array (
			'cancel'		=> $hashover->locale->text['cancel'],
			'dislike-comment'	=> $hashover->locale->text['dislike-comment'],
			'disliked-comment'	=> $hashover->locale->text['disliked-comment'],
			'disliked'		=> $hashover->locale->text['disliked'],
			'dislike'		=> $hashover->locale->text['dislike'],
			'dislikes'		=> $hashover->locale->text['dislikes'],
			'external-image-tip'	=> $hashover->locale->text['external-image-tip'],
			'field-needed'		=> $hashover->locale->text['field-needed'],
			'like-comment'		=> $hashover->locale->text['like-comment'],
			'liked-comment'		=> $hashover->locale->text['liked-comment'],
			'liked'			=> $hashover->locale->text['liked'],
			'like'			=> $hashover->locale->text['like'],
			'likes'			=> $hashover->locale->text['likes'],
			'today'			=> $hashover->locale->text['date-today'],
			'unlike'		=> $hashover->locale->text['unlike'],
			'commenter-tip'		=> $hashover->locale->text['commenter-tip'],
			'subscribed-tip'	=> $hashover->locale->text['subscribed-tip'],
			'unsubscribed-tip'	=> $hashover->locale->text['unsubscribed-tip'],
			'replies'		=> $hashover->locale->text['replies'],
			'reply'			=> $hashover->locale->text['reply'],
			'no-email-warning'	=> $hashover->locale->text['no-email-warning'],
			'invalid-email'		=> $hashover->locale->text['invalid-email'],
			'reply-needed'		=> $hashover->locale->text['reply-needed'],
			'comment-needed'	=> $hashover->locale->text['comment-needed'],
			'delete-comment'	=> $hashover->locale->text['delete-comment'],
			'loading'		=> $hashover->locale->text['loading'],
			'click-to-close'	=> $hashover->locale->text['click-to-close'],
			'email'			=> $hashover->locale->text['email'],
			'name'			=> $hashover->locale->text['name'],
			'password'		=> $hashover->locale->text['password'],
			'website'		=> $hashover->locale->text['website']
		);

		// Add setup information to data
		$data['setup'] = array (
			'server-eol'		=> PHP_EOL,
			'collapse-limit'	=> $hashover->setup->collapseLimit,
			'default-sorting'	=> $hashover->setup->defaultSorting,
			'default-name'		=> $hashover->setup->defaultName,
			'user-is-logged-in'	=> $hashover->login->userIsLoggedIn,
			'user-is-admin'		=> $hashover->login->userIsAdmin,
			'http-root'		=> $hashover->setup->httpRoot,
			'http-backend'		=> $hashover->setup->httpBackend,
			'allows-dislikes'	=> $hashover->setup->allowsDislikes,
			'allows-likes'		=> $hashover->setup->allowsLikes,
			'image-extensions'	=> $hashover->setup->imageTypes,
			'image-placeholder'	=> $hashover->setup->getImagePath ('place-holder'),
			'stream-mode'		=> ($hashover->setup->replyMode === 'stream'),
			'stream-depth'		=> $hashover->setup->streamDepth,
			'theme-css'		=> $hashover->setup->getThemePath ('comments.css'),
			'rss-api'		=> $hashover->setup->getHttpPath ('api/rss.php'),
			'image-format'		=> $hashover->setup->imageFormat,
			'device-type'		=> ($hashover->setup->isMobile === true) ? 'mobile' : 'desktop',
			'collapses-interface'	=> $hashover->setup->collapsesInterface,
			'collapses-comments'	=> $hashover->setup->collapsesComments,
			'allows-images'		=> $hashover->setup->allowsImages,
			'uses-markdown'		=> $hashover->setup->usesMarkdown,
			'uses-cancel-buttons'	=> $hashover->setup->usesCancelButtons,
			'uses-auto-login'	=> $hashover->setup->usesAutoLogin,
			'uses-ajax'		=> $hashover->setup->usesAjax,
			'allows-login'		=> $hashover->setup->allowsLogin,
			'form-fields'		=> $hashover->setup->formFields
		);

		// And add UI HTML to data
		$data['ui'] = array (
			'user-avatar'		=> $hashover->ui->userAvatar (),
			'name-link'		=> $hashover->ui->nameElement ('a'),
			'name-span'		=> $hashover->ui->nameElement ('span'),
			'parent-link'		=> $hashover->ui->parentThreadLink (),
			'edit-link'		=> $hashover->ui->formLink ('{href}', 'edit'),
			'reply-link'		=> $hashover->ui->formLink ('{href}', 'reply'),
			'like-link'		=> $hashover->ui->likeLink ('like'),
			'dislike-link'		=> $hashover->ui->likeLink ('dislike'),
			'like-count'		=> $hashover->ui->likeCount ('likes'),
			'dislike-count'		=> $hashover->ui->likeCount ('dislikes'),
			'name-wrapper'		=> $hashover->ui->nameWrapper (),
			'date-link'		=> $hashover->ui->dateLink (),
			'comment-wrapper'	=> $hashover->ui->commentWrapper (),
			'theme'			=> $hashover->templater->parseTheme ('comments.html'),
			'reply-form'		=> $hashover->ui->replyForm (),
			'edit-form'		=> $hashover->ui->editForm ()
		);
	}

	// HashOver instance information
	$data['instance'] = array (
		'primary-count'		=> $hashover->thread->primaryCount - 1,
		'total-count'		=> $hashover->thread->totalCount - 1,
		'page-url'		=> $hashover->setup->pageURL,
		'page-title'		=> $hashover->setup->pageTitle,
		'thread-name'		=> $hashover->setup->threadName,
		'file-path'		=> $hashover->setup->filePath,
		'initial-html'		=> $hashover->ui->initialHTML (false),
		'comments'		=> $hashover->comments
	);

	// Count according to `$showsReplyCount` setting
	$show_comments = $hashover->getCommentCount ('show-comments', 'show-comment');

	// Add locales for show interface button
	if ($hashover->setup->collapsesInterface !== false) {
		$data['instance']['post-a-comment'] = $hashover->ui->postComment;
		$data['instance']['show-comments'] = $show_comments;
	}

	// Text for "Show X Other Comment(s)" link
	if ($hashover->setup->collapsesComments !== false) {
		// Check if at least 1 comment is to be shown
		if ($hashover->setup->collapseLimit >= 1) {
			// Shorter variables
			$total_count = $hashover->thread->totalCount;
			$collapse_limit = $hashover->setup->collapseLimit;

			// Get number of comments after collapse limit
			$other_count = ($total_count - 1) - $collapse_limit;

			// Subtract deleted comment counts
			if ($hashover->setup->countsDeletions === false) {
				$other_count -= $hashover->thread->collapsedDeletedCount;
			}

			// Check if there is more than one other comment
			if ($other_count !== 1) {
				// If so, use the "Show X Other Comments" locale
				$more_link_text = $hashover->locale->text['show-other-comments'];
			} else {
				// If not, use the "Show X Other Comment" locale
				$more_link_text = $hashover->locale->text['show-other-comment'];
			}

			// And inject the count into the locale string
			$more_link_text = sprintf ($more_link_text, $other_count);
		} else {
			// If not, show count according to `$showsReplyCount` setting
			$more_link_text = $show_comments;
		}

		// Add "Show X Other Comment(s)" link to instance
		$data['instance']['more-link-text'] = $more_link_text;
	}

	// Generate statistics
	$hashover->statistics->executionEnd ();

	// HashOver statistics
	$data['statistics'] = array (
		'execution-time'	=> $hashover->statistics->executionTime,
		'script-memory'		=> $hashover->statistics->scriptMemory,
		'system-memory'		=> $hashover->statistics->systemMemory
	);

	// Return JSON or JSONP function call
	echo Misc::jsonData ($data);

} catch (\Exception $error) {
	echo Misc::displayException ($error, 'json');
}
