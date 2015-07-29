<?php

	// Copyright (C) 2015 Jacob Barkdull
	//
	//	I, Jacob Barkdull, hereby release this work into the public domain. 
	//	This applies worldwide. If this is not legally possible, I grant any 
	//	entity the right to use this work for any purpose, without any 
	//	conditions, unless such conditions are required by law.


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) == basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		}
	}

	// Turkish text for forms, buttons, links, and tooltips
	$locale = array (
		'comment_form'		=> 'Yorumunuzu buraya yazın (diğer alanları doldurmanız zorunlu değildir)',
		'reply_form'		=> 'Cevabınızı buraya yazın (diğer alanları doldurmanız zorunlu değildir)',
		'form_tip'		=> 'Accepted HTML: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapes HTML, URLs automagically become links, and [img]URL here[/img] will display an external image.',
		'post_button'		=> 'Gönder',
		'login'			=> 'Giriş Yap',
		'login_tip'		=> 'Giriş yap (opsiyonel)',
		'logout'		=> 'Çıkış Yap',
		'pending_note'		=> 'Bu yorum onayı bekliyor.',
		'deleted_note'		=> 'Bu yorum silindi.',
		'comment_pending'	=> 'Bekleyen',
		'comment_deleted'	=> 'Yorum silindi!',
		'options'		=> 'Ayarlar',
		'cancel'		=> 'İptal',
		'reply_to_comment'	=> 'Yorumu cevapla',
		'edit_your_comment'	=> 'Yorumunuzu değiştirin',
		'name'			=> 'İsim',
		'name_tip'		=> 'İsim (opsiyonel)',
		'password'		=> 'Şifre',
		'password_tip'		=> 'Şifre (sadece yorumlarınızı değiştirmek ve silmek için gerekli olabilir)',
		'confirm_password'	=> 'Şifreyi Onayla',
		'email'			=> 'E-posta adresi',
		'email_tip'		=> 'E-posta adresi (opsiyonel, e-posta ile uyarılar için.)',
		'website'		=> 'Website',
		'website_tip'		=> 'Website (opsiyonel)',
		'logged_in'		=> 'Giriş yaptınız!',
		'logged_out'		=> 'Çıkış yaptınız!',
		'comment_needed'	=> 'Düzgün bir yorum olmadı bu? Lütfen aşağıdaki formu kullanın.',
		'reply_needed'		=> 'Düzgün bir cevap olmadı bu? Lütfen aşağıdaki formu kullanın.',
		'post_fail'		=> 'Gönderirken hatalar oluştu. Yeterli izinlere sahip değilsiniz.',
		'post_reply'		=> 'Cevapla',
		'delete'		=> 'Sil',
		'subscribe'		=> 'Kayıt ol',
		'subscribe_tip'		=> 'E-posta uyarılarına kayıt ol',
		'edit_comment'		=> 'Yorumu değiştir',
		'save_edit'		=> 'Değişiklikleri kaydet',
		'no_email_warning'	=> 'E-posta girişi yapmanız yorumunuza gelen cevaplara uyarı alamayacaksınız.',
		'invalid_email'		=> 'Girdiğiniz e-posta adresi geçerli değil.',
		'delete_comment'	=> 'Bu yorumu silmek istediğinizden emin misiniz',
		'post_comment_on'	=> array ('Bir yorum yap', ' on "_TITLE_"'),
		'popular_comments'	=> array ('En popüler yorum', 'En popüler yorumlar'),
		'showing_comments'	=> array ('_NUM_ tane yorum gösteriliyor', '_NUM_ yorum gösteriliyor'),
		'count_link'		=> array ('_NUM_ yorum', '_NUM_ yorum'),
		'count_replies'		=> array ('_NUM_ cevap', '_NUM_ cevap'),
		'sort'			=> 'Sırala',
		'sort_ascend'		=> 'Sırayla',
		'sort_descend'		=> 'Ters',
		'sort_byname'		=> 'Yorum yapana göre',
		'sort_bydate'		=> 'Tarihe göre (yeni olan önce)',
		'sort_bylikes'		=> 'Beğenilmeye göre',
		'threaded'		=> 'İç içe',
		'thread'		=> 'Konunun başı',
		'thread_tip'		=> 'Konunun başına dön',
		'replies'		=> 'Cevap',
		'edit'			=> 'Düzenle',
		'reply'			=> 'Cevap',
		'like'			=> array ('Beğenme', 'Beğenme'),
		'liked'			=> 'Beğendi',
		'unlike'		=> 'Unlike',
		'like_comment'		=> 'Bu yorumu \'beğen\'',
		'liked_comment'		=> 'Bu yorumu \'beğendiniz\'',
		'dislike'		=> array ('Hoşlanmayan', 'Hoşlanmayan'),
		'disliked'		=> 'Beğenmedi',
		'dislike_comment'	=> 'Bu yorumu \'beğenme\' ',
		'disliked_comment'	=> 'Bu yorumu  \'beğenmediniz\'',
		'commenter_tip'		=> 'E-posta ile bilgilendirilmeyeceksiniz',
		'subscribed_tip'	=> 'e-posta ile bilgilendirilecek.',
		'unsubscribed_tip'	=> 'e-posta ile bilgilendirmeye kayıt olmamış',
		'first_comment'		=> 'İlk yorumu siz yapın!',
		'show_other_comments'	=> array ('Diğer _NUM_ yorumu göster', 'Diğer _NUM_ yorumu göster'),
		'show_number_comments'	=> array ('Diğer _NUM_ yorumu göster', 'Diğer _NUM_ yorumu göster'),
		'date_years'		=> array ('_NUM_ yıl önce', '_NUM_ yıl önce'),
		'date_months'		=> array ('_NUM_ ay önce', '_NUM_ ay önce'),
		'date_days'		=> array ('_NUM_ gün önce', '_NUM_ gün önce'),
		'date_today'		=> '_TIME_ bugün'
	);

?>
