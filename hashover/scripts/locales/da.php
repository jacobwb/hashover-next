<?php

// Copyright (C) 2015-2017 Jacob Barkdull
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

// Danish text for forms, buttons, links, and tooltips
$locale = array (
	'comment-form'		=> 'Skriv kommentar her...',
	'reply-form'		=> 'Skriv svar her...',
	'form-tip'		=> 'Akseptabel HTML: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; undslipper HTML, URL\'er blir til links automatisk, og [img]URL her[/img] vil vise et eksternt billede.',
	'post-button'		=> 'Send Kommentar',
	'login'			=> 'Login',
	'login-tip'		=> 'Login (ikke påkrævet)',
	'logout'		=> 'Logout',
	'pending-name'		=> 'Venter...',
	'deleted-name'		=> 'Slettet...',
	'error-name'		=> 'Fejl...',
	'pending-note'		=> 'Denne kommentar venter på godkendelse.',
	'deleted-note'		=> 'Denne kommentar er slettet.',
	'error-note'		=> 'Et eller andet gik galt. Kunne ikke hente kommentaren.',
	'options'		=> 'Valgmuligheder',
	'cancel'		=> 'Fortryd',
	'reply-to-comment'	=> 'Svar på kommentar',
	'edit-your-comment'	=> 'Rediger din kommentar',
	'optional'		=> 'Valgfri',
	'required'		=> 'Påkrævet',
	'name'			=> 'Navn',
	'name-tip'		=> 'Navn (%s)',
	'password'		=> 'Pasord',
	'password-tip'		=> 'Pasord (%s, gør det muligt for dig, at redigere eller slette denne kommentar)',
	'confirm-password'	=> 'Bekræft Pasord',
	'email'			=> 'Email Addresse',
	'email-tip'		=> 'Email Addresse (%s, for varsling via email)',
	'website'		=> 'Websted',
	'website-tip'		=> 'Websted (%s)',
	'logged-in'		=> 'Du er blevet logget ind!',
	'logged-out'		=> 'Du er blevet logget ud!',
	'comment-needed'	=> 'Du fik aldrig skrevet en kommentar. Brug venligst tekst-feltet under.',
	'reply-needed'		=> 'Du fik aldrig skrevet et svar. Brug venligst tekst-feltet under.',
	'field-needed'		=> 'Feltet "%s" er påkrævet.',
	'post-fail'		=> 'Fejl! Du har ikke de nødvendige rettigheder.',
	'comment-deleted'	=> 'Kommentar Slettet!',
	'post-reply'		=> 'Send Svar',
	'delete'		=> 'Slet',
	'subscribe'		=> 'Påmind mig om svar',
	'subscribe-tip'		=> 'Tilmeld email varsling',
	'edit-comment'		=> 'Rediger kommentar',
	'status-approved'	=> 'Godkendt',
	'status-pending'	=> 'Afventer godkendelse',
	'status-deleted'	=> 'Markerede slettet',
	'save-edit'		=> 'Gem Redigering',
	'no-email-warning'	=> 'Du vil ikke modtage varslinger af svar til din kommentar uden at oplyse en email.',
	'invalid-email'		=> 'Email addressen du skrev er ugyldig.',
	'delete-comment'	=> 'Er du sikker på, at du vil slette denne kommentar?',
	'post-comment-on'	=> array ('Skriv en kommentar', 'Skriv en kommentar til "%s"'),
	'popular-comments'	=> array ('Populære Kommentarer', 'Populære Kommentarer'),
	'showing-comments'	=> array ('Viser %d Kommentar', 'Viser %d Kommentarer'),
	'count-link'		=> array ('%d Kommentar', '%d Kommentarer'),
	'count-replies'		=> array ('%d med svar', '%d med svar'),
	'sort'			=> 'Sorter',
	'sort-ascending'	=> 'I rækkefølge',
	'sort-descending'	=> 'I omvendt rækkefølge',
	'sort-by-date'		=> 'Nyeste først',
	'sort-by-likes'		=> 'Flest likes',
	'sort-by-replies'	=> 'Flest svar',
	'sort-by-discussion'	=> 'Efter diskussion',
	'sort-by-popularity'	=> 'Efter popularitet',
	'sort-by-name'		=> 'Efter navn',
	'sort-threads'		=> 'Tråde',
	'thread'		=> 'I svar til %s',
	'thread-tip'		=> 'Hop til toppen af tråden',
	'comments'		=> 'Kommentarer',
	'replies'		=> 'Svar',
	'edit'			=> 'Rediger',
	'reply'			=> 'Svar',
	'like'			=> array ('Synes om', 'Synes om'),
	'liked'			=> 'Synes godt om',
	'unlike'		=> 'Synes ikke om',
	'like-comment'		=> '\'Synes godt om\' denne kommentar',
	'liked-comment'		=> 'Synes ikke godt om denne kommentar',
	'dislike'		=> array ('Synes ikke om', 'Synes ikke om'),
	'disliked'		=> 'Syntes ikke om',
	'dislike-comment'	=> '\'Synes ikke om\' denne kommentar',
	'disliked-comment'	=> 'Du \'Syntes ikke om\' denne kommentar',
	'commenter-tip'		=> 'Du vil ikke blive varslet via email',
	'subscribed-tip'	=> 'vil blive varslet via email',
	'unsubscribed-tip'	=> 'er ikke tilmeldt email varsling',
	'first-comment'		=> 'Vær den første til at kommentere!',
	'show-other-comments'	=> array ('Vis %d Yderlig Kommentar', 'Vis %d Yderligere Kommentarer'),
	'show-number-comments'	=> array ('Vis %d Kommentar', 'Vis %d Kommentar'),
	'date-years'		=> array ('%d år siden', '%d år siden'),
	'date-months'		=> array ('%d måned siden', '%d måneder siden'),
	'date-days'		=> array ('%d dag siden', '%d dage siden'),
	'date-today'		=> '%s i dag',
	'untitled'		=> 'Unavngivet',
	'external-image-tip'	=> 'Klik for at se ekstern billede',
	'loading'		=> 'Henter...',
	'click-to-close'	=> 'Klik for at lukke',
	'hashover-comments'	=> 'HashOver Kommentarer',
	'rss-feed'		=> 'RSS Feed',
	'source-code'		=> 'Kilde Kode'
);
