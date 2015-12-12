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

// Turkish text for forms, buttons, links, and tooltips
$locale = array (
	'comment-form'		=> 'Yorumunuzu buraya yazın (diğer alanları doldurmanız zorunlu değildir)',
	'reply-form'		=> 'Cevabınızı buraya yazın (diğer alanları doldurmanız zorunlu değildir)',
	'form-tip'		=> 'Accepted HTML: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapes HTML, URLs automagically become links, and [img]URL here[/img] will display an external image.',
	'post-button'		=> 'Gönder',
	'login'			=> 'Giriş Yap',
	'login-tip'		=> 'Giriş yap (opsiyonel)',
	'logout'		=> 'Çıkış Yap',
	'pending-note'		=> 'Bu yorum onayı bekliyor.',
	'deleted-note'		=> 'Bu yorum silindi.',
	'comment-pending'	=> 'Bekleyen...',
	'comment-deleted'	=> 'Yorum silindi!',
	'options'		=> 'Ayarlar',
	'cancel'		=> 'İptal',
	'reply-to-comment'	=> 'Yorumu cevapla',
	'edit-your-comment'	=> 'Yorumunuzu değiştirin',
	'name'			=> 'İsim',
	'name-tip'		=> 'İsim (opsiyonel)',
	'password'		=> 'Şifre',
	'password-tip'		=> 'Şifre (sadece yorumlarınızı değiştirmek ve silmek için gerekli olabilir)',
	'confirm-password'	=> 'Şifreyi Onayla',
	'email'			=> 'E-posta adresi',
	'email-tip'		=> 'E-posta adresi (opsiyonel, e-posta ile uyarılar için.)',
	'website'		=> 'Website',
	'website-tip'		=> 'Website (opsiyonel)',
	'logged-in'		=> 'Giriş yaptınız!',
	'logged-out'		=> 'Çıkış yaptınız!',
	'comment-needed'	=> 'Düzgün bir yorum olmadı bu? Lütfen aşağıdaki formu kullanın.',
	'reply-needed'		=> 'Düzgün bir cevap olmadı bu? Lütfen aşağıdaki formu kullanın.',
	'post-fail'		=> 'Gönderirken hatalar oluştu. Yeterli izinlere sahip değilsiniz.',
	'post-reply'		=> 'Cevapla',
	'delete'		=> 'Sil',
	'subscribe'		=> 'Kayıt ol',
	'subscribe-tip'		=> 'E-posta uyarılarına kayıt ol',
	'edit-comment'		=> 'Yorumu değiştir',
	'save-edit'		=> 'Değişiklikleri kaydet',
	'no-email-warning'	=> 'E-posta girişi yapmanız yorumunuza gelen cevaplara uyarı alamayacaksınız.',
	'invalid-email'		=> 'Girdiğiniz e-posta adresi geçerli değil.',
	'delete-comment'	=> 'Bu yorumu silmek istediğinizden emin misiniz',
	'post-comment-on'	=> array ('Bir yorum yap', 'Bir yorum yap on "%s"'),
	'popular-comments'	=> array ('En popüler yorum', 'En popüler yorumlar'),
	'showing-comments'	=> array ('%d tane yorum gösteriliyor', '%d yorum gösteriliyor'),
	'count-link'		=> array ('%d yorum', '%d yorum'),
	'count-replies'		=> array ('%d cevap', '%d cevap'),
	'sort'			=> 'Sırala',
	'sort-ascend'		=> 'Sırayla',
	'sort-descend'		=> 'Ters',
	'sort-byname'		=> 'Yorum yapana göre',
	'sort-bydate'		=> 'Tarihe göre (yeni olan önce)',
	'sort-bylikes'		=> 'Beğenilmeye göre',
	'threaded'		=> 'İç içe',
	'thread'		=> '%s\'a cevaben',
	'thread-tip'		=> 'Konunun başına dön',
	'replies'		=> 'Cevap',
	'edit'			=> 'Düzenle',
	'reply'			=> 'Cevap',
	'like'			=> array ('Beğenme', 'Beğenme'),
	'liked'			=> 'Beğendi',
	'unlike'		=> 'Unlike',
	'like-comment'		=> 'Bu yorumu \'beğen\'',
	'liked-comment'		=> 'Bu yorumu \'beğendiniz\'',
	'dislike'		=> array ('Hoşlanmayan', 'Hoşlanmayan'),
	'disliked'		=> 'Beğenmedi',
	'dislike-comment'	=> 'Bu yorumu \'beğenme\' ',
	'disliked-comment'	=> 'Bu yorumu  \'beğenmediniz\'',
	'commenter-tip'		=> 'E-posta ile bilgilendirilmeyeceksiniz',
	'subscribed-tip'	=> 'e-posta ile bilgilendirilecek.',
	'unsubscribed-tip'	=> 'e-posta ile bilgilendirmeye kayıt olmamış',
	'first-comment'		=> 'İlk yorumu siz yapın!',
	'show-other-comments'	=> array ('Diğer %d yorumu göster', 'Diğer %d yorumu göster'),
	'show-number-comments'	=> array ('Diğer %d yorumu göster', 'Diğer %d yorumu göster'),
	'date-years'		=> array ('%d yıl önce', '%d yıl önce'),
	'date-months'		=> array ('%d ay önce', '%d ay önce'),
	'date-days'		=> array ('%d gün önce', '%d gün önce'),
	'date-today'		=> '%s bugün',
	'untitled'		=> 'Başlıksız'
);
