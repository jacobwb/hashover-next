<?php

// Copyright (C) 2015-2016 Jacob Barkdull
// This file is part of HashOver.
//
// I, Jacob Barkdull, hereby release this work into the public domain.
// This applies worldwide. If this is not legally possible, I grant any
// entity the right to use this work for any purpose, without any
// conditions, unless such conditions are required by law.


// Display source code
if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
	if (isset ($_GET['source'])) {
		header ('Content-type: text/plain; charset=UTF-8');
		exit (file_get_contents (basename (__FILE__)));
	}
}

// English text for forms, buttons, links, and tooltips
$locale = array (
	'comment-form'		=> 'Type comment here...',
	'reply-form'		=> 'Type reply here...',
	'form-tip'		=> 'Accepted HTML: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapes HTML, URLs automagically become links, and [img]URL here[/img] will display an external image.',
	'post-button'		=> 'Post Comment',
	'login'			=> 'Login',
	'login-tip'		=> 'Login (optional)',
	'logout'		=> 'Logout',
	'pending-note'		=> 'This comment is pending approval.',
	'deleted-note'		=> 'This comment has been deleted.',
	'comment-pending'	=> 'Pending...',
	'comment-deleted'	=> 'Comment Deleted!',
	'options'		=> 'Options',
	'cancel'		=> 'Cancel',
	'reply-to-comment'	=> 'Reply to comment',
	'edit-your-comment'	=> 'Edit your comment',
	'optional'		=> 'Optional',
	'required'		=> 'Required',
	'name'			=> 'Name',
	'name-tip'		=> 'Name (%s)',
	'password'		=> 'Password',
	'password-tip'		=> 'Password (%s, allows you to edit or delete this comment)',
	'confirm-password'	=> 'Confirm Password',
	'email'			=> 'E-mail Address',
	'email-tip'		=> 'E-mail Address (%s, for e-mail notifications)',
	'website'		=> 'Website',
	'website-tip'		=> 'Website (%s)',
	'logged-in'		=> 'You have been successfully logged in!',
	'logged-out'		=> 'You have been successfully logged out!',
	'comment-needed'	=> 'You failed to enter a proper comment. Use the form below.',
	'reply-needed'		=> 'You failed to enter a proper reply. Use the form below.',
	'field-needed'		=> 'The %s field is required.',
	'post-fail'		=> 'Failure! You lack sufficient permission.',
	'post-reply'		=> 'Post Reply',
	'delete'		=> 'Delete',
	'subscribe'		=> 'Notify me of replies',
	'subscribe-tip'		=> 'Subscribe to e-mail notifications',
	'edit-comment'		=> 'Edit comment',
	'save-edit'		=> 'Save Edit',
	'no-email-warning'	=> 'You will not receive notification of replies to your comment without supplying an e-mail.',
	'invalid-email'		=> 'The e-mail address you entered is invalid.',
	'delete-comment'	=> 'Are you sure you want to delete this comment?',
	'post-comment-on'	=> array ('Post a comment', 'Post a comment on "%s"'),
	'popular-comments'	=> array ('Most Popular Comment', 'Most Popular Comments'),
	'showing-comments'	=> array ('Showing %d Comment', 'Showing %d Comments'),
	'count-link'		=> array ('%d Comment', '%d Comments'),
	'count-replies'		=> array ('%d counting reply', '%d counting replies'),
	'sort'			=> 'Sort',
	'sort-ascending'	=> 'In order',
	'sort-descending'	=> 'In reverse order',
	'sort-by-date'		=> 'Newest first',
	'sort-by-likes'		=> 'By likes',
	'sort-by-replies'	=> 'By replies',
	'sort-by-discussion'	=> 'By discussion',
	'sort-by-popularity'	=> 'By popularity',
	'sort-by-name'		=> 'By commenter',
	'sort-threads'		=> 'Threads',
	'thread'		=> 'In reply to %s',
	'thread-tip'		=> 'Jump to top of thread',
	'comments'		=> 'Comments',
	'replies'		=> 'Replies',
	'edit'			=> 'Edit',
	'reply'			=> 'Reply',
	'like'			=> array ('Like', 'Likes'),
	'liked'			=> 'Liked',
	'unlike'		=> 'Unlike',
	'like-comment'		=> '\'Like\' this comment',
	'liked-comment'		=> 'Unlike this comment',
	'dislike'		=> array ('Dislike', 'Dislikes'),
	'disliked'		=> 'Disliked',
	'dislike-comment'	=> '\'Dislike\' this comment',
	'disliked-comment'	=> 'You \'Disliked\' this comment',
	'commenter-tip'		=> 'You will not be notified via e-mail',
	'subscribed-tip'	=> 'will be notified via e-mail',
	'unsubscribed-tip'	=> 'is not subscribed to e-mail notifications',
	'first-comment'		=> 'Be the first to comment!',
	'show-other-comments'	=> array ('Show %d Other Comment', 'Show %d Other Comments'),
	'show-number-comments'	=> array ('Show %d Comment', 'Show %d Comments'),
	'date-years'		=> array ('%d year ago', '%d years ago'),
	'date-months'		=> array ('%d month ago', '%d months ago'),
	'date-days'		=> array ('%d day ago', '%d days ago'),
	'date-today'		=> '%s today',
	'untitled'		=> 'Untitled',
	'external-image-tip'	=> 'Click to view external image',
	'loading'		=> 'Loading...',
	'click-to-close'	=> 'Click to close',
	'hashover-comments'	=> 'HashOver Comments',
	'rss-feed'		=> 'RSS Feed',
	'source-code'		=> 'Source Code'
);
