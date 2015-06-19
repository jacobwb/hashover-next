<?php

	// Copyright (C) 2015 Jacob Barkdull
	//
	//	I, Jacob Barkdull, hereby release this work into the public domain. 
	//	This applies worldwide. If this is not legally possible, I grant any 
	//	entity the right to use this work for any purpose, without any 
	//	conditions, unless such conditions are required by law.


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		}
	}

	// Polish text for forms, buttons, links, and tooltips
	$locale = array (
		'comment_form'	=> 'Napisz komentarz (pozostałe pola opcjonalne)',
		'reply_form'	=> 'Napisz odpowiedź (pozostałe pola opcjonalne)',
		'post_button'	=> 'Wyślij komentarz',
		'login'		=> 'Zaloguj Się',
		'login_tip'	=> 'Zaloguj Się (opcjonalne)',
		'logout'	=> 'Wyloguj Się',
		'pending_note'	=> 'Ten komentarz jest oczekujące na zatwierdzenie.',
		'deleted_note'	=> 'Komentarz został usunięty.',
		'cmt_pending'	=> 'Oczekujące',
		'cmt_deleted'	=> 'Komentarz usunięty!',
		'options'	=> 'Opcje',
		'cancel'	=> 'Anuluj',
		'reply_to_cmt'	=> 'Odpowiedz na komentarz',
		'edit_your_cmt'	=> 'Edytuj Komentarz',
		'name'		=> 'Imię',
		'name_tip'	=> 'Imię (opcjonalne)',
		'password'	=> 'Hasło',
		'password_tip'	=> 'Hasło (opcjonalne, pozwala edytować lub usunąć to komentarz)',
		'email'		=> 'Adres E-mail',
		'email_tip'	=> 'Adres E-mail (opcjonalne, dla powiadomień e-mail)',
		'website'	=> 'Strona www',
		'website_tip'	=> 'Strona www (opcjonalne)',
		'logged_in'	=> 'Zostałeś pomyślnie zalogowany!',
		'logged_out'	=> 'Zostałeś pomyślnie wylogowany!',
		'cmt_needed'	=> 'Wpisz komentarz do właściwego pola. Użyj formularza poniżej.',
		'reply_needed'	=> 'Wpisz odpowiedź do właściwego pola. Użyj formularza poniżej.',
		'post_fail'	=> 'Komentarz nie wysłany! Nie masz wystarczających uprawnień.',
		'cmt_tip'	=> 'Akceptowany HTML: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapes HTML, URLe automatycznie zamieniają się w linki, a [img]URL tutaj[/img] wyświetli zewnętrzny obrazek.',
		'post_reply'	=> 'Wyślij odpowiedź',
		'delete'	=> 'Skasuj',
		'subscribe'	=> 'Subskrybuj',
		'subscribe_tip'	=> 'Subskrybuj aby otrzymywać powiadomienia E-mailem',
		'edit_cmt'	=> 'Edytuj komentarz',
		'save_edit'	=> 'Zapisz Edytuj',
		'no_email_warn'	=> 'Nie będziesz otrzymywał powiadomień o odpowiedziach na Twój komentarz bez podania e-maila.',
		'invalid_email'	=> 'Adres e-mail jest niewłaściwy.',
		'delete_cmt'	=> 'Czy na pewno chcesz usunąć komentarz?',
		'post_cmt_on'	=> array ('Wyślij komentarz', ' na "_TITLE_"'),
		'popular_cmts'	=> array ('Najbardziej Popularny Komentarz', 'Najbardziej Popularne Komentarze'),
		'showing_cmts'	=> array ('Wyświetlanie _NUM_ Komentarza', 'Wyświetlanie _NUM_ Komentarzy'),
		'count_link'	=> array ('_NUM_ Komentarz', '_NUM_ Komentarze'),
		'count_replies'	=> array ('_NUM_ liczenie odpowiedzi', '_NUM_ liczenie odpowiedzi'),
		'sort'		=> 'Wyświetl wg',
		'sort_ascend'	=> 'Kolejności',
		'sort_descend'	=> 'Odwrotnej Kolejności',
		'sort_byname'	=> 'Komentującego',
		'sort_bydate'	=> 'Daty (najnowsze pierwsze)',
		'sort_bylikes'	=> 'Polubień',
		'threaded'	=> 'Struktura Drzewa',
		'thread'	=> 'Początek',
		'thread_tip'	=> 'Przejdź do początku',
		'replies'	=> 'Odpowiedzi',
		'edit'		=> 'Edytuj',
		'reply'		=> 'Odpowiedz',
		'like'		=> array ('Like', 'Likes'),
		'liked'		=> 'Liked',
		'unlike'	=> 'Unlike',
		'like_cmt'	=> '\'Like\' Ten Komentarz',
		'liked_cmt'	=> 'Polubiłeś \'Liked\' Ten Komentarz',
		'dislike'	=> array ('Dislike', 'Dislikes'),
		'disliked'	=> 'Disliked',
		'dislike_cmt'	=> '\'Dislike\' Ten Komentarz',
		'disliked_cmt'	=> 'Polubiłeś \'Disliked\' Ten Komentarz',
		'op_cmt_note'	=> 'Nie będziesz otrzymywać powiadomień e-mailem',
		'subbed_note'	=> 'będzie powiadomiony e-mailem',
		'unsubbed_note'	=> 'nie będzie powiadomiony e-mailem',
		'first_cmt'	=> 'Napisz pierwszy komentarz!',
		'other_cmts'	=> array ('Show _NUM_ Inny Komentarz', 'Show _NUM_ Inne Komentarze'),
		'show_num_cmts'	=> array ('Show _NUM_ Komentarz', 'Show _NUM_ Komentarze'),
		'date_years'	=> array ('_NUM_ rok temu', '_NUM_ lat temu'),
		'date_months'	=> array ('_NUM_ miesiąc temu', '_NUM_ miesięcy temu'),
		'date_days'	=> array ('_NUM_ dzień temu', '_NUM_ dni temu'),
		'date_today'	=> '_TIME_ dzisiaj'
	);

?>
