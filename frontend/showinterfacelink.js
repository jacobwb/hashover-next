// Creates show interface button (showinterfacelink.js)
HashOver.prototype.showInterfaceLink = function ()
{
	// Reference to this object
	var hashover = this;

	// Main element
	var main = this.instance['main-element'];

	// Check if there is one or more comments
	if (this.instance['total-count'] >= 1) {
		// If so, use "Show X More Comments" locale
		var text = this.instance['show-comments'];
	} else {
		// If not, show "Post Comment on ..." locale
		var text = this.instance['post-a-comment'];
	}

	// Create hyperlink that shows interface
	main.appendChild (this.createElement ('a', {
		id: this.prefix ('show-interface-link'),
		className: 'hashover-more-link',
		rel: 'nofollow',
		href: '#',
		title: text,
		text: text,

		// Show interface when clicked
		onclick: function () {
			hashover.showInterface ();
			return false;
		}
	}));
};
