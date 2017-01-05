<?php

// Copyright (C) 2016-2017 Jacob Barkdull
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

// Dutch text for forms, buttons, links, and tooltips
$locale = array (
	'comment-form'		=> 'Schrijf uw reactie hier ...',
	'reply-form'		=> 'Schrijf uw antwoord hier ...',
	'form-tip'		=> 'Toegelaten HTML: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; ontsnapt HTML, URLs automatisch worden links, en [img]URL hier[/img] zal tonen een extern beeld.',
	'post-button'		=> 'Verstuur Reactie',
	'login'			=> 'Inloggen',
	'login-tip'		=> 'Inloggen (Optioneel)',
	'logout'		=> 'Uitloggen',
	'pending-name'		=> 'Onderweg...',
	'deleted-name'		=> 'Verwijderd...',
	'error-name'		=> 'Fout...',
	'pending-note'		=> 'Dit reactie wacht op goedkeuring.',
	'deleted-note'		=> 'Dit reactie is verwijderd.',
	'error-note'		=> 'Er ging iets mis. Kon niet halen deze reactie.',
	'options'		=> 'Opties',
	'cancel'		=> 'Annuleer',
	'reply-to-comment'	=> 'Reageer op reactie',
	'edit-your-comment'	=> 'Bewerk uw reactie',
	'optional'		=> 'Optioneel',
	'required'		=> 'Verplicht',
	'name'			=> 'Naam',
	'name-tip'		=> 'Naam (%s)',
	'password'		=> 'Wachtwoord',
	'password-tip'		=> 'Wachtwoord (%s, maakt het mogelijk om uw reactie te bewerken)',
	'confirm-password'	=> 'Bevestig wachtwoord',
	'email'			=> 'E-mail adres',
	'email-tip'		=> 'E-mail adres (%s, voor e-mail notificaties)',
	'website'		=> 'Website',
	'website-tip'		=> 'Website (%s)',
	'logged-in'		=> 'Inloggen gelukt!',
	'logged-out'		=> 'Uitloggen gelukt!',
	'comment-needed'	=> 'U heeft geen reactie ingevuld, gebruik het formulier hieronder.',
	'reply-needed'		=> 'U heeft geen antwoord ingevuld, gebruik het formulier hieronder.',
	'field-needed'		=> 'Het veld "%s" is verplicht.',
	'post-fail'		=> 'Mislukking! U gebrek voldoende toestemming.',
	'comment-deleted'	=> 'Reactie Verwijderd!',
	'post-reply'		=> 'Stuur antwoord',
	'delete'		=> 'Verwijder',
	'subscribe'		=> 'Herinner mij wanneer een antwoorden wordt geplaatst',
	'subscribe-tip'		=> 'Meld aan voor herinneringen',
	'edit-comment'		=> 'Bewerk reactie',
	'status-approved'	=> 'Goedgekeurd',
	'status-pending'	=> 'In afwachting van goedkeuring',
	'status-deleted'	=> 'Gemarkeerd verwijderd',
	'save-edit'		=> 'Bewaar aanpassing',
	'no-email-warning'	=> 'U zult geen reactie krijgen wanneer een reactie wordt geplaatst als u geen e-mail adres invult (maar uw reactie wordt gewoon geplaatst).',
	'invalid-email'		=> 'Het opgegeven email adres is niet geldig.',
	'delete-comment'	=> 'Weet u zeker dat u het reactie wilt verwijderen?',
	'post-comment-on'	=> array ('Plaats een reactie', 'Plaats een reactie op "%s"'),
	'popular-comments'	=> array ('Meest populaire reactie', 'Meest populaire reacties'),
	'showing-comments'	=> array ('%d reactie geplaatst', '%d reacties geplaatst'),
	'count-link'		=> array ('%d reactie', '%d reacties'),
	'count-replies'		=> array ('%d tellen antwoord', '%d tellen antwoorden'),
	'sort'			=> 'Sorteer',
	'sort-ascending'	=> 'In volgorde',
	'sort-descending'	=> 'In omgekeerde volgorde',
	'sort-by-date'		=> 'Nieuwste eerst',
	'sort-by-likes'		=> 'Door likes',
	'sort-by-replies'	=> 'Door antwoorden',
	'sort-by-discussion'	=> 'Door discussie',
	'sort-by-popularity'	=> 'Door populariteit',
	'sort-by-name'		=> 'Door commenter',
	'sort-threads'		=> 'Threads',
	'thread'		=> 'In antwoord op %s',
	'thread-tip'		=> 'Spring naar top van thread',
	'comments'		=> 'Reacties',
	'replies'		=> 'Antwoorden',
	'edit'			=> 'Bewerk',
	'reply'			=> 'Antwoord',
	'like'			=> array ('Like', 'Likes'),
	'liked'			=> 'Liked',
	'unlike'		=> 'Unlike',
	'like-comment'		=> '\'Like\' dit reactie',
	'liked-comment'		=> 'Unlike dit reactie',
	'dislike'		=> array ('Dislike', 'Dislikes'),
	'disliked'		=> 'Disliked',
	'dislike-comment'	=> '\'Dislike\' dit reactie',
	'disliked-comment'	=> 'You \'Disliked\' dit reactie',
	'commenter-tip'		=> 'U zult geen notificatie krijgen via e-mail',
	'subscribed-tip'	=> 'zal per email op de hoogte worden gebracht',
	'unsubscribed-tip'	=> 'is niet geabonneerd op notificaties',
	'first-comment'		=> 'Nog geen reacties.',
	'show-other-comments'	=> array ('Toon %d andere reactie', 'Toon %d andere reacties'),
	'show-number-comments'	=> array ('Toon %d reactie', 'Toon %d reacties'),
	'date-years'		=> array ('%d jaar geleden', '%d jaar geleden'),
	'date-months'		=> array ('%d maand geleden', '%d maanden geleden'),
	'date-days'		=> array ('%d dag geleden', '%d dagen geleden'),
	'date-today'		=> '%s vandaag',
	'untitled'		=> 'Untitled',
	'external-image-tip'	=> 'Klik om te bekijken extern beeld',
	'loading'		=> 'Loading ...',
	'click-to-close'	=> 'Klik om te sluiten',
	'hashover-comments'	=> 'HashOver Reacties',
	'rss-feed'		=> 'RSS Feed',
	'source-code'		=> 'Broncode'
);
