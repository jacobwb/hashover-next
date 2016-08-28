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

// Polish text for forms, buttons, links, and tooltips
$locale = array (
	'comment-form'		=> 'Napisz komentarz...',
	'reply-form'		=> 'Napisz odpowiedź...',
	'form-tip'		=> 'Akceptowany HTML: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapes HTML, URLe automatycznie zamieniają się w linki, a [img]URL tutaj[/img] wyświetli zewnętrzny obrazek.',
	'post-button'		=> 'Wyślij komentarz',
	'login'			=> 'Zaloguj Się',
	'login-tip'		=> 'Zaloguj Się (opcjonalne)',
	'logout'		=> 'Wyloguj Się',
	'pending-note'		=> 'Ten komentarz jest oczekujące na zatwierdzenie.',
	'deleted-note'		=> 'Komentarz został usunięty.',
	'comment-pending'	=> 'Oczekujące...',
	'comment-deleted'	=> 'Komentarz usunięty!',
	'options'		=> 'Opcje',
	'cancel'		=> 'Anuluj',
	'reply-to-comment'	=> 'Odpowiedz na komentarz',
	'edit-your-comment'	=> 'Edytuj komentarz',
	'optional'		=> 'Opcjonalne',
	'required'		=> 'Wymagane',
	'name'			=> 'Imię',
	'name-tip'		=> 'Imię (%s)',
	'password'		=> 'Hasło',
	'password-tip'		=> 'Hasło (%s, pozwala edytować lub usunąć to komentarz)',
	'confirm-password'	=> 'Potwierdź hasło',
	'email'			=> 'Adres E-mail',
	'email-tip'		=> 'Adres E-mail (%s, dla powiadomień e-mail)',
	'website'		=> 'Strona www',
	'website-tip'		=> 'Strona www (%s)',
	'logged-in'		=> 'Zostałeś pomyślnie zalogowany!',
	'logged-out'		=> 'Zostałeś pomyślnie wylogowany!',
	'comment-needed'	=> 'Wpisz komentarz do właściwego pola. Użyj formularza poniżej.',
	'reply-needed'		=> 'Wpisz odpowiedź do właściwego pola. Użyj formularza poniżej.',
	'field-needed'		=> '%s jest wymagane.',
	'post-fail'		=> 'Komentarz nie wysłany! Nie masz wystarczających uprawnień.',
	'post-reply'		=> 'Wyślij odpowiedź',
	'delete'		=> 'Skasuj',
	'subscribe'		=> 'Subskrybuj',
	'subscribe-tip'		=> 'Subskrybuj aby otrzymywać powiadomienia E-mailem',
	'edit-comment'		=> 'Edytuj komentarz',
	'save-edit'		=> 'Zapisz Edytuj',
	'no-email-warning'	=> 'Nie będziesz otrzymywał powiadomień o odpowiedziach na Twój komentarz bez podania e-maila.',
	'invalid-email'		=> 'Adres e-mail jest niewłaściwy.',
	'delete-comment'	=> 'Czy na pewno chcesz usunąć komentarz?',
	'post-comment-on'	=> array ('Wyślij komentarz', 'Wyślij komentarz na "%s"'),
	'popular-comments'	=> array ('Najbardziej Popularny Komentarz', 'Najbardziej Popularne Komentarze'),
	'showing-comments'	=> array ('Wyświetlanie %d Komentarza', 'Wyświetlanie %d Komentarzy'),
	'count-link'		=> array ('%d Komentarz', '%d Komentarze'),
	'count-replies'		=> array ('%d liczenie odpowiedzi', '%d liczenie odpowiedzi'),
	'sort'			=> 'Wyświetl wg',
	'sort-ascending'	=> 'Kolejności',
	'sort-descending'	=> 'Odwrotnej Kolejności',
	'sort-by-date'		=> 'Najnowsze pierwsze',
	'sort-by-likes'		=> 'Wg likes',
	'sort-by-replies'	=> 'Wg odpowiedzi',
	'sort-by-discussion'	=> 'Wg dyskusji',
	'sort-by-popularity'	=> 'Wg popularności',
	'sort-by-name'		=> 'Wg autora',
	'sort-threads'		=> 'Nici',
	'thread'		=> 'W odpowiedzi na %s',
	'thread-tip'		=> 'Przejdź do początku',
	'comments'		=> 'Komentarze',
	'replies'		=> 'Odpowiedzi',
	'edit'			=> 'Edytuj',
	'reply'			=> 'Odpowiedz',
	'like'			=> array ('Like', 'Likes'),
	'liked'			=> 'Liked',
	'unlike'		=> 'Unlike',
	'like-comment'		=> '\'Like\' ten komentarz',
	'liked-comment'		=> 'Polubiłeś \'Liked\' ten komentarz',
	'dislike'		=> array ('Dislike', 'Dislikes'),
	'disliked'		=> 'Disliked',
	'dislike-comment'	=> '\'Dislike\' Ten Komentarz',
	'disliked-comment'	=> 'Polubiłeś \'Disliked\' ten komentarz',
	'commenter-tip'		=> 'Nie będziesz otrzymywać powiadomień e-mailem',
	'subscribed-tip'	=> 'będzie powiadomiony e-mailem',
	'unsubscribed-tip'	=> 'nie będzie powiadomiony e-mailem',
	'first-comment'		=> 'Napisz pierwszy komentarz!',
	'show-other-comments'	=> array ('Show %d Inny Komentarz', 'Show %d Inne Komentarze'),
	'show-number-comments'	=> array ('Show %d Komentarz', 'Show %d Komentarze'),
	'date-years'		=> array ('%d rok temu', '%d lat temu'),
	'date-months'		=> array ('%d miesiąc temu', '%d miesięcy temu'),
	'date-days'		=> array ('%d dzień temu', '%d dni temu'),
	'date-today'		=> '%s dzisiaj',
	'untitled'		=> 'Bez tytułu',
	'external-image-tip'	=> 'Kliknij aby zobaczyć obraz zewnętrzny',
	'loading'		=> 'Załadunek...',
	'click-to-close'	=> 'Kliknij aby zamknąć',
	'hashover-comments'	=> 'HashOver Komentarze',
	'rss-feed'		=> 'Kanału RSS',
	'source-code'		=> 'Kod Źródłowy'
);
