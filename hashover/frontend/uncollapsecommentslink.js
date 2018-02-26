// Creates the "Show X Other Comments" button (uncollapsecommentslink.js)
HashOver.prototype.uncollapseCommentsLink = function ()
{
	// Check whether there are more than the collapse limit
	if (this.instance['total-count'] > this.setup['collapse-limit']) {
		// Create element for the comments
		this.instance['more-section'] = this.elements.create ('div', {
			className: 'hashover-more-section'
		});

		// If so, create "More Comments" hyperlink
		this.instance['more-link'] = this.elements.create ('a', {
			href: '#',
			className: 'hashover-more-link',
			title: this.instance['more-link-text'],
			textContent: this.instance['more-link-text'],

			onclick: function () {
				return hashover.showMoreComments (this);
			}
		});

		// Add more button link to sort div
		this.instance['sort-section'].appendChild (this.instance['more-section']);

		// Add more button link to sort div
		this.instance['sort-section'].appendChild (this.instance['more-link']);

		// And consider comments collapsed
		this.instance['showing-more'] = false;
	} else {
		// If not, consider all comments shown
		this.instance['showing-more'] = true;
	}
};
