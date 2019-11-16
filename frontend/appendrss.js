// Appends HashOver comments RSS feed to page head (appendrss.js)
HashOver.prototype.appendRSS = function ()
{
	// Get the page head
	var head = document.head || document.getElementsByTagName ('head')[0];

	// Get encoded page URL
	var pageURL = encodeURIComponent (this.instance['page-url']);

	// Create link element for comment RSS feed
	var rss = this.createElement ('link', {
		rel: 'alternate',
		href: this.setup['rss-api'] + '?url=' + pageURL,
		type: 'application/rss+xml',
		title: 'Comments'
	});

	// Append comment RSS feed link element to page head
	head.appendChild (rss);
};
