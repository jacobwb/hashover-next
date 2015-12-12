<?php

// Copyright (C) 2015 Jacob Barkdull
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
	'comment-form'		=> 'Type Comment Here (other fields optional)',
	'reply-form'		=> 'Type Reply Here (other fields optional)',
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
	'reply-to-comment'	=> 'Reply To Comment',
	'edit-your-comment'	=> 'Edit Your Comment',
	'name'			=> 'Name',
	'name-tip'		=> 'Name (optional)',
	'password'		=> 'Password',
	'password-tip'		=> 'Password (optional, allows you to edit or delete this comment)',
	'confirm-password'	=> 'Confirm Password',
	'email'			=> 'E-mail Address',
	'email-tip'		=> 'E-mail Address (optional, for e-mail notifications)',
	'website'		=> 'Website',
	'website-tip'		=> 'Website (optional)',
	'logged-in'		=> 'You have been successfully logged in!',
	'logged-out'		=> 'You have been successfully logged out!',
	'comment-needed'	=> 'You failed to enter a proper comment. Use the form below.',
	'reply-needed'		=> 'You failed to enter a proper reply. Use the form below.',
	'post-fail'		=> 'Failure! You lack sufficient permission.',
	'post-reply'		=> 'Post Reply',
	'delete'		=> 'Delete',
	'subscribe'		=> 'Notify me of replies',
	'subscribe-tip'		=> 'Subscribe to e-mail notifications',
	'edit-comment'		=> 'Edit Comment',
	'save-edit'		=> 'Save Edit',
	'no-email-warning'	=> 'You will not receive notification of replies to your comment without supplying an e-mail.',
	'invalid-email'		=> 'The e-mail address you entered is invalid.',
	'delete-comment'	=> 'Are you sure you want to delete this comment?',
	'post-comment-on'	=> array ('Post a Comment', 'Post a Comment on "%s"'),
	'popular-comments'	=> array ('Most Popular Comment', 'Most Popular Comments'),
	'showing-comments'	=> array ('Showing %d Comment', 'Showing %d Comments'),
	'count-link'		=> array ('%d Comment', '%d Comments'),
	'count-replies'		=> array ('%d counting reply', '%d counting replies'),
	'sort'			=> 'Sort',
	'sort-ascend'		=> 'In Order',
	'sort-descend'		=> 'In Reverse Order',
	'sort-byname'		=> 'By Commenter',
	'sort-bydate'		=> 'Newest First',
	'sort-bylikes'		=> 'By Popularity',
	'threaded'		=> 'Threaded',
	'thread'		=> 'In reply to %s',
	'thread-tip'		=> 'Jump to top of thread',
	'replies'		=> 'Replies',
	'edit'			=> 'Edit',
	'reply'			=> 'Reply',
	'like'			=> array ('Like', 'Likes'),
	'liked'			=> 'Liked',
	'unlike'		=> 'Unlike',
	'like-comment'		=> '\'Like\' This Comment',
	'liked-comment'		=> 'Unlike This Comment',
	'dislike'		=> array ('Dislike', 'Dislikes'),
	'disliked'		=> 'Disliked',
	'dislike-comment'	=> '\'Dislike\' This Comment',
	'disliked-comment'	=> 'You \'Disliked\' This Comment',
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
	'untitled'		=> 'Untitled'
);
