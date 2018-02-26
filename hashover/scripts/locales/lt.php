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

// Lithuanian text for forms, buttons, links, and tooltips
// Translated by vKaotik
// Translated for HashOver Comment system.
// 19-May-2017

// Lietuviškas tekstas formoms, mygtukams, nuorodoms ir t.t
// Išvertė vKaotik
// Išversta sistemai HashOver.
// 2017-05-19
$locale = array (
	'comment-form'		=> 'Palikti atsiliepimą...',
	'reply-form'		=> 'Atsakyti į komentarą..',
	'comment-formatting'	=> 'Formatavimas',
	'accepted-format'	=> 'Priimtinas formatavimas:  %s',
	'accepted-html'		=> '&lt;b&gt;, &lt;strong&gt;, &lt;u&gt;, &lt;i&gt;, &lt;em&gt;, &lt;s&gt;, &lt;big&gt;, &lt;small&gt;, &lt;sup&gt;, &lt;sub&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; įterps kodą, URL magiškai taps nuorodomis, ir[img]Nuoroda[/img] įkels įšorinį paveikslėlį.',
	'accepted-markdown'	=> '**Paryškintas**, _apatinė linija_, *itališkas*, ~~perbrėžti~~, `paryškinti`, ```code``` įterpia kodą. HTML ir Markdown gali būti naudojami kartu jūsų komentare.',
	'post-button'		=> 'Palikti atsiliepimą',
	'login'			=> 'Prisijungti',
	'login-tip'		=> 'Prisijungti',
	'logout'		=> 'Atsijungti',
	'be-first-name'		=> 'Jokių komentarų apie šį žmogų nėra. Būk pirmas!',
	'pending-name'		=> 'Vykdoma...',
	'deleted-name'		=> 'Ištrintas...',
	'error-name'		=> 'Klaida...',
	'be-first-note'		=> 'Jokių komentarų apie šį žmogų nėra. Būk pirmas!',
	'pending-note'		=> 'Šis komentaras laukia patvirtinimo.',
	'deleted-note'		=> 'Šis komentaras buvo ištrintas.',
	'error-note'		=> 'Klaida parsiunčiant komentarus..',
	'options'		=> 'Nustatymai',
	'cancel'		=> 'Atšaukti',
	'reply-to-comment'	=> 'Atsakyti į komentarą',
	'edit-your-comment'	=> 'Redaguoti komentarą',
	'optional'		=> 'Pasirinktinai',
	'required'		=> 'Būtinas',
	'name'			=> 'Vardas',
	'name-tip'		=> 'Vardas (%s)',
	'password'		=> 'Slaptažodis',
	'password-tip'		=> 'Slaptažodis (%s, komentarų redagavimui.)',
	'confirm-password'	=> 'Patvirtinti slaptažodį',
	'email'			=> 'El.Pašto adresas',
	'email-tip'		=> 'El.Pašto adresas (%s, būtinas naujienom paštu.)',
	'website'		=> 'Puslapis',
	'website-tip'		=> 'Puslapis (%s)',
	'logged-in'		=> 'Sėkmingai prisijungėte!',
	'logged-out'		=> 'Sėkmingai atsijungėte!',
	'comment-needed'	=> 'Komentaras tuščias.',
	'reply-needed'		=> 'Komentaras tuščias.',
	'field-needed'		=> 'Šis "%s" laukelis yra būtinas.',
	'post-fail'		=> 'Klaida! Nepakanka privilegijų komentuoti..',
	'comment-deleted'	=> 'Komentaras ištrintas!',
	'post-reply'		=> 'Rašyti atsakymą',
	'delete'		=> 'Ištrinti',
	'subscribe'		=> 'Siųsti naujienas paštu',
	'subscribe-tip'		=> 'Užsiprenumeruoti naujienas paštu',
	'edit-comment'		=> 'Redaguoti',
	'status'		=> 'Statusas',
	'status-approved'	=> 'Patvirtinta',
	'status-pending'	=> 'Laukia patvirtinimo',
	'status-deleted'	=> 'Nepatvirtintas',
	'save-edit'		=> 'Išsaugoti pakeitimus',
	'no-email-warning'	=> 'Neįvesdami el.pašto, negausite naujienų iš šio puslapio.',
	'invalid-email'		=> 'El.Pašto adresas neteisingas.',
	'delete-comment'	=> 'Ar jūs tikrai norite ištrinti šį komentarą?',
	'post-comment-on'	=> array ('Rašyti atsiliepimą', 'Rašyti atsiliepimą apie "%s"'),
	'popular-comments'	=> array ('Populiariausias atsiliepimas', 'Populiariausi atsiliepimai'),
	'showing-comments'	=> array ('Rodomas %d komentaras', 'Rodomi %d komentarai'),
	'count-link'		=> array ('%d Komentaras', '%d Komentarai'),
	'count-replies'		=> array ('%d įskaitant atsakymą', '%d įskaitant atsakymus'),
	'sort'			=> 'Rūšiuoti',
	'sort-ascending'	=> 'Eilės tvarka',
	'sort-descending'	=> 'Atvirkštine tvarka',
	'sort-by-date'		=> 'Naujiausi viršuje',
	'sort-by-likes'		=> 'Pagal teigiamus',
	'sort-by-replies'	=> 'Pagal atsakymus',
	'sort-by-discussion'	=> 'Pagal diskusijas',
	'sort-by-popularity'	=> 'Pagal populiarumą',
	'sort-by-name'		=> 'Pagal komentuotoją',
	'sort-threads'		=> 'Temas',
	'thread'		=> 'Į viršų',
	'thread-tip'		=> 'Į viršų',
	'comments'		=> 'Komentarai',
	'replies'		=> 'Atsakymai',
	'edit'			=> 'Redaguoti',
	'reply'			=> 'Atsakyti',
	'like'			=> array ('Teigiamas', 'Teigiami'),
	'liked'			=> 'Teigiamas',
	'unlike'		=> 'Nuimti teigiamą įvertinimą',
	'like-comment'		=> 'Teigiamas',
	'liked-comment'		=> 'Nuimti vertinimą',
	'dislike'		=> array ('Neigiamas', 'Neigiami'),
	'disliked'		=> 'Neigiamas',
	'dislike-comment'	=> 'Neigiamas vertinimas',
	'disliked-comment'	=> 'Nuimti vertinimą',
	'commenter-tip'		=> 'Neįvedę el.pašto , negausite naujienų paštu.',
	'subscribed-tip'	=> 'gaus naujienas paštu',
	'unsubscribed-tip'	=> 'negauna naujienų paštu',
	'show-other-comments'	=> array ('Rodyti %d kitą komentarą', 'Rodyti %d kitus komentarus'),
	'show-number-comments'	=> array ('Rodyti %d komentarą', 'Rodyti %d komentarus'),
	'date-time'		=> '%s \a\t %s',
	'date-years'		=> array ('Prieš %d metus', 'Prieš %d metus'),
	'date-months'		=> array ('Prieš %d mėnesį', 'Prieš %d mėnesius'),
	'date-days'		=> array ('Prieš %d-ą dieną', 'Prieš %d dienas'),
	'date-today'		=> '%s šiandieną',
	'date-day-names'	=> array ('Sekmadienis', 'Pirmadienis', 'Antradienis', 'Trečiadienis', 'Ketvirtadienis', 'Penktadienis', 'Šeštadienis'),
	'date-month-names'	=> array ('Sausis', 'Vasaris', 'Kovas', 'Balandis', 'Gegužė', 'Biržėlis', 'Liepa', 'Rugpjūtis', 'Rugsėjis', 'Spalis', 'Lapkritis', 'Gruodis'),
	'untitled'		=> 'Be vardo',
	'external-image-tip'	=> 'Spausti kad peržiūrėti paveikslėlį',
	'loading'		=> 'Kraunama..',
	'click-to-close'	=> 'Uždaryti',
	'hashover-comments'	=> 'HashOver sistema',
	'rss-feed'		=> 'RSS',
	'source-code'		=> 'Atviras Kodas'
);
