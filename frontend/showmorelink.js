// Creates "Show X Other Comments" button (showmorelink.js)
HashOver.prototype.showMoreLink = function ()
{
	// Reference to this object
	var hashover = this;

	// Check whether there are more than the collapse limit
	if (this.instance['total-count'] > this.setup['collapse-limit']) {
		// If so, create "More Comments" hyperlink
		this.instance['more-link'] = this.createElement ('a', {
			className: 'hashover-more-link',
			rel: 'nofollow',
			href: '#',
			title: this.instance['more-link-text'],
			textContent: this.instance['more-link-text'],

			onclick: function () {
				return hashover.showMoreComments (this);
			}
		});

		// Sort section element
		var sortSection = this.instance['sort-section'];

		// Sort section child elements
		var comments = sortSection.children;

		// Store last hidden comment for later use
		this.instance['last-shown-comment'] = comments[comments.length - 1];

		// Add more button link after sort div
		sortSection.appendChild (this.instance['more-link']);

		// And consider comments collapsed
		this.instance['showing-more'] = false;
	} else {
		// If not, consider all comments shown
		this.instance['showing-more'] = true;
	}
};

// Re-appends "Show X Other Comments" button (showmorelink.js)
HashOver.prototype.reappendMoreLink = function ()
{
	// Get show more comments link
	var moreLink = this.instance['more-link'];

	// Get showing all comment indicator
	var showingMore = this.instance['showing-more'];

	// Check if show more link exists and comments are still collapsed
	if (moreLink !== undefined && showingMore === false) {
		// If so, get sort section element
		var sortSection = this.instance['sort-section'];

		// Get last show comment before all comments were shown
		var lastShown = this.instance['last-shown-comment'];

		// And insert link after last shown comment
		sortSection.insertBefore (moreLink, lastShown.nextSibling)
	}
};
