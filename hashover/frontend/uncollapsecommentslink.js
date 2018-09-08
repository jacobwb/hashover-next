// Creates the "Show X Other Comments" button (uncollapsecommentslink.js)
HashOver.prototype.uncollapseCommentsLink = function ()
{
	// Reference to this object
	var hashover = this;

	// Check whether there are more than the collapse limit
	if (this.instance['total-count'] > this.setup['collapse-limit']) {
		// If so, create "More Comments" hyperlink
		this.instance['more-link'] = this.createElement ('a', {
			href: '#',
			className: 'hashover-more-link',
			title: this.instance['more-link-text'],
			textContent: this.instance['more-link-text'],

			onclick: function () {
				return hashover.showMoreComments (this);
			}
		});

		// Sort section element
		var sortSection = this.instance['sort-section'];

		// Add more button link after sort div
		sortSection.parentNode.appendChild (this.instance['more-link']);

		// And consider comments collapsed
		this.instance['showing-more'] = false;
	} else {
		// If not, consider all comments shown
		this.instance['showing-more'] = true;
	}
};
