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

// Romanian text for forms, buttons, links, and tooltips
$locale = array(
	'comment-form'		=> 'Scrie comentariu aici...',
	'reply-form'		=> 'Scrie reply aici...',
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
	'optional'		=> 'Optionale',
	'required'		=> 'Obligatoriu',
	'name'			=> 'Nume',
	'name-tip'		=> 'Nume (%s)',
	'password'		=> 'Parola',
	'password-tip'		=> 'Parola (%s, permite să editați sau să ștergeți acest comentariu)',
	'confirm-password'	=> 'Confirmă Parola',
	'email'			=> 'Adresa E-mail',
	'email-tip'		=> 'Adresa E-mail (%s, pentru notificări prin e-mail)',
	'website'		=> 'Website',
	'website-tip'		=> 'Website (%s)',
	'logged-in'		=> 'Conectare reusita!',
	'logged-out'		=> 'Conectare eșec!',
	'comment-needed'	=> 'Tu nu a reușit să introduceți un comentariu adecvat. Foloseste formularul de mai jos.',
	'reply-needed'		=> 'Tu nu a reușit să introduceți un reply adecvat. Foloseste formularul de mai jos.',
	'field-needed'		=> '%s Câmpul de Este obligatoriu.',
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
	'sort-ascending'	=> 'Ascendent',
	'sort-descending'	=> 'Descendent',
	'sort-by-date'		=> 'Cele mai noi',
	'sort-by-likes'		=> 'Dupa Like-uri',
	'sort-by-replies'	=> 'Dupa răspunsuri',
	'sort-by-discussion'	=> 'Dupa discuții',
	'sort-by-popularity'	=> 'Dupa popularitate',
	'sort-by-name'		=> 'Dupa user',
	'sort-threads'		=> 'Fire',
	'thread'		=> 'Ca răspuns la %s',
	'thread-tip'		=> 'Top inceput comentariu',
	'comments'		=> 'Comentarii',
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
	'untitled'		=> 'Fără Titlu',
	'external-image-tip'	=> 'Click pentru a vizualiza imaginea externă',
	'loading'		=> 'Se incarca...',
	'click-to-close'	=> 'Click pentru a închide',
	'hashover-comments'	=> 'HashOver Comentarii',
	'rss-feed'		=> 'RSS Feed',
	'source-code'		=> 'Cod Sursa'
);
