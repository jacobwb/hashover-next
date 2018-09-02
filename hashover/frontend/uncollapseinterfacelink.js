// Creates the interface uncollapse button (uncollapseinterfacelink.js)
HashOver.prototype.uncollapseInterfaceLink = function ()
{
	// Main element
	var main = this.instance['main-element'];

	// Check if there is one or more comments
	if (this.instance['total-count'] >= 1) {
		// If so, use "Show X More Comments" locale
		var text = this.instance['show-number-comments'];
	} else {
		// If not, show "Post Comment on ..." locale
		var text = this.instance['post-comment-on'];
	}

	// Create hyperlink to uncollapse the interface
	main.appendChild (this.elements.create ('a', {
		id: 'hashover-uncollapse-interface-link',
		className: 'hashover-more-link',
		href: '#',
		title: text,
		text: text,

		// Uncollapse the interface when clicked
		onclick: function () {
			hashover.uncollapseInterface ();
			return false;
		}
	}));
};
