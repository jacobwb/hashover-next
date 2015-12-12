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

// Romanian text for forms, buttons, links, and tooltips
$locale = array(
	'comment-form'		=> 'Scrie comentariu aici(celelalte campuri sunt optionale)',
	'reply-form'		=> 'Scrie reply aici (celelalte campuri sunt optionale)',
	'form-tip'		=> 'Caractere HTML acceptate: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapes HTML, URL automat devin link si [img]URL[/img] se deschid in alt tab.',
	'post-button'		=> 'Post Comment',
	'login'			=> 'Conectează-te',
	'login-tip'		=> 'Conectează-te (optionale)',
	'logout'		=> 'Deconectează-te',
	'pending-note'		=> 'This comment is pending approval.',
	'deleted-note'		=> 'Comentariu sters.',
	'comment-pending'	=> 'În așteptarea...',
	'comment-deleted'	=> 'Comentariu sters!',
	'options'		=> 'Optiuni',
	'cancel'		=> 'Renunta',
	'reply-to-comment'	=> 'Reply la comentariu',
	'edit-your-comment'	=> 'Editare comentariu',
	'name'			=> 'Nume',
	'name-tip'		=> 'Nume (optionale)',
	'password'		=> 'Parola',
	'password-tip'		=> 'Parola (optionale, permite să editați sau să ștergeți acest comentariu)',
	'confirm-password'	=> 'Confirmă Parola',
	'email'			=> 'Adresa E-mail',
	'email-tip'		=> 'Adresa E-mail (optionale, pentru notificări prin e-mail)',
	'website'		=> 'Website',
	'website-tip'		=> 'Website (optionale)',
	'logged-in'		=> 'Conectare reusita!',
	'logged-out'		=> 'Conectare eșec!',
	'comment-needed'	=> 'Tu nu a reușit să introduceți un comentariu adecvat. Foloseste formularul de mai jos.',
	'reply-needed'		=> 'Tu nu a reușit să introduceți un reply adecvat. Foloseste formularul de mai jos.',
	'post-fail'		=> 'Eșec! Tu lipse permisiunea suficientă.',
	'post-reply'		=> 'Adauga Reply',
	'delete'		=> 'Sterge',
	'subscribe'		=> 'Notifica-ma de raspunsuri',
	'subscribe-tip'		=> 'Subscribe la notificari pe mail',
	'edit-comment'		=> 'Editare comentariu',
	'save-edit'		=> 'Salveaza',
	'no-email-warning'	=> 'Fara adresa de e-mail, NU vei primi notificari cand cineva raspunde la comentariul tau!',
	'invalid-email'		=> 'Cele adresa de e-mail pe care ați introdus nu este valid.',
	'delete-comment'	=> 'Sigur doresti stergerea comentariului?',
	'post-comment-on'	=> array ('Adauga comentariu', 'Adauga comentariu la "%s"'),
	'popular-comments'	=> array ('Cele mai populare Comentariu', 'Cele mai populare Comentarii'),
	'showing-comments'	=> array ('Arata %d Comentariu', 'Arata %d Comentarii'),
	'count-link'		=> array ('%d Comentariu', '%d Comentarii'),
	'count-replies'		=> array ('%d numărare răspuns', '%d numărare răspunsuri'),
	'sort'			=> 'Sortare',
	'sort-ascend'		=> 'Ascendent',
	'sort-descend'		=> 'Descendent',
	'sort-byname'		=> 'Dupa user',
	'sort-bydate'		=> 'Dupa data (cele mai noi)',
	'sort-bylikes'		=> 'Dupa Like-uri',
	'threaded'		=> 'Structură de Arbore',
	'thread'		=> 'Ca răspuns la %s',
	'thread-tip'		=> 'Top inceput comentariu',
	'replies'		=> 'răspunsuri',
	'edit'			=> 'Editare',
	'reply'			=> 'Reply',
	'like'			=> array ('Like', 'Like-uri'),
	'liked'			=> 'Liked',
	'unlike'		=> 'Unlike',
	'like-comment'		=> '\'Like\' acest comentariu',
	'liked-comment'		=> 'Tu \'Liked\' acest comentariu',
	'dislike'		=> array ('Dislike', 'Dislike-uri'),
	'disliked'		=> 'Disliked',
	'dislike-comment'	=> '\'Dislike\' acest comentariu',
	'disliked-comment'	=> 'Tu \'Disliked\' acest comentariu',
	'commenter-tip'		=> 'Tu nu va fi notificat prin e-mail',
	'subscribed-tip'	=> 'va fi notificat prin e-mail',
	'unsubscribed-tip'	=> 'nu este abonat la notificări prin e-mail',
	'first-comment'		=> 'Fii primul care comenteaza!',
	'show-other-comments'	=> array ('Arata %d Alte Comentariu', 'Arata %d Alte Comentarii'),
	'show-number-comments'	=> array ('Arata %d Comentariu', 'Arata %d Comentarii'),
	'date-years'		=> array ('%d an in urma', '%d ani in urma'),
	'date-months'		=> array ('%d lună în urmă', '%d luni în urmă'),
	'date-days'		=> array ('%d zi în urmă', '%d zile în urmă'),
	'date-today'		=> '%s astăzi',
	'untitled'		=> 'Fără Titlu'
);
