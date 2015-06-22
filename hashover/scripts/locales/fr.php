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

	// French text for forms, buttons, links, and tooltips
	$locale = array (
		'comment_form'		=> 'Écrivez ici votre commentaire (les autres champs sont facultatifs)',
		'reply_form'		=> 'Écrivez ici votre réponse (les autres champs sont facultatifs)',
		'form_tip'		=> 'HTML accepté: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; échappe le HTML, les URLs sont transformées en liens, et [img]URL ici[/img] fait apparaître une image externe.',
		'post_button'		=> 'Publier ce commentaire',
		'login'			=> 'Connecté',
		'login_tip'		=> 'Connecté (optionnel)',
		'logout'		=> 'Déconnexion',
		'pending_note'		=> 'Ce commentaire est en attente d\'approbation.',
		'deleted_note'		=> 'Ce commentaire a été supprimé.',
		'comment_pending'	=> 'En attente',
		'comment_deleted'	=> 'Commentaire supprimé!',
		'options'		=> 'Options',
		'cancel'		=> 'Annuler',
		'reply_to_comment'	=> 'Répondre au commentaire',
		'edit_your_comment'	=> 'Éditer votre commentaire',
		'name'			=> 'Nom',
		'name_tip'		=> 'Nom (optionnel)',
		'password'		=> 'Mot de passe',
		'password_tip'		=> 'Mot de passe (optionnel, vous permet d\'éditer ou de supprimer ce commentaire)',
		'confirm_password'	=> 'Confirmer le mot de passe',
		'email'			=> 'Adresse E-mail',
		'email_tip'		=> 'Adresse E-mail (optionnel, pour les notifications e-mail)',
		'website'		=> 'Site Internet',
		'website_tip'		=> 'Site Internet (optionnel)',
		'logged_in'		=> 'Vous avez connecté avec succès!',
		'logged_out'		=> 'Vous avez deconnecté avec succès!',
		'comment_needed'	=> 'Vous avez échoué à entrer un commentaire approprié. Utilisez le formulaire ci-dessous.',
		'reply_needed'		=> 'Vous avez échoué à entrer un réponse approprié. Utilisez le formulaire ci-dessous.',
		'post_fail'		=> 'Impossible de publier ce commentaire ! Vous n\'avez pas les permissions suffisantes.',
		'post_reply'		=> 'Publier cette réponse',
		'delete'		=> 'Supprimer',
		'subscribe'		=> 'Avertissez-moi des réponses',
		'subscribe_tip'		=> 'Souscrire aux notifications par e-mail',
		'edit_comment'		=> 'Éditer ce commentaire',
		'save_edit'		=> 'Enregistrer cette modification',
		'no_email_warning'	=> 'Vous ne recevrez pas de notifications en cas de réponse si vous ne fournissez pas d\'e-mail.',
		'invalid_email'		=> 'L\'adresse e-mail que vous avez entré n\'est pas valide.',
		'delete_comment'	=> 'Confirmez-vous la suppression de ce commentaire ?',
		'post_comment_on'	=> array ('Poster un Commentaire', ' sur "_TITLE_"'),
		'popular_comments'	=> array ('Commentaire les Plus Populaires', 'Commentaires les Plus Populaires'),
		'showing_comments'	=> array ('Montrer _NUM_ Commentaire', 'Montrer _NUM_ Commentaires'),
		'count_link'		=> array ('_NUM_ Commentaire', '_NUM_ Commentaires'),
		'count_replies'		=> array ('_NUM_ compter réponse', '_NUM_ compter réponses'),
		'sort'			=> 'Trier',
		'sort_ascend'		=> 'Dans l\'ordre',
		'sort_descend'		=> 'Dans l\'ordre inverse',
		'sort_byname'		=> 'Par nom du commentateur',
		'sort_bydate'		=> 'La Plus Récente en Premier',
		'sort_bylikes'		=> 'Par Popularité',
		'threaded'		=> 'Structure de l\'arbre',
		'thread'		=> 'Haut du fil',
		'thread_tip'		=> 'Aller en haut de la discussion',
		'replies'		=> 'Réponses',
		'edit'			=> 'Éditer',
		'reply'			=> 'Répondre',
		'like'			=> array ('J\'aime', 'Goûts'),
		'liked'			=> 'Aimez',
		'unlike'		=> 'Défaire',
		'like_comment'		=> '\'J\'aime\' ce commentaire',
		'liked_comment'		=> 'Défaire \'J\'aime\'',
		'dislike'		=> array ('Détesté', 'dégoûts'),
		'disliked'		=> 'Détester',
		'dislike_comment'	=> '\'Détesté\' ce commentaire',
		'disliked_comment'	=> 'Vous \'Détester\' ce commentaire',
		'commenter_tip'		=> 'Vous serez notifié par e-mail',
		'subscribed_tip'	=> 'sera notifié par e-mail',
		'unsubscribed_tip'	=> 'n\'a pas souscrit aux notifications',
		'first_comment'		=> 'Soyez le premier à commenter!',
		'show_other_comments'	=> array ('Afficher _NUM_ Autre Commentaire', 'Afficher _NUM_ Autres Commentaires'),
		'show_number_comments'	=> array ('Afficher _NUM_ Commentaire', 'Afficher _NUM_ Commentaires'),
		'date_years'		=> array ('Il ya _NUM_ an', 'Il ya _NUM_ ans'),
		'date_months'		=> array ('Il ya _NUM_ un mois', 'Il ya _NUM_ mois'),
		'date_days'		=> array ('Il ya _NUM_ jour', 'Il ya _NUM_ jours'),
		'date_today'		=> '_TIME_ aujourd\'hui'
	);

?>
