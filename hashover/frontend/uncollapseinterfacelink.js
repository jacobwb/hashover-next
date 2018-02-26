// Creates the interface uncollapse button (uncollapseinterfacelink.js)
HashOver.prototype.uncollapseInterfaceLink = function ()
{
	// Decide button text
	var uncollapseLocale = (this.instance['total-count'] >= 1) ? 'show-number-comments' : 'post-comment-on';
	var uncollapseText = this.instance[uncollapseLocale];

	// Create hyperlink to uncollapse the comment interface
	var uncollapseLink = this.elements.create ('a', {
		id: 'hashover-uncollapse-interface-link',
		className: 'hashover-more-link',
		href: '#',
		title: uncollapseText,
		textContent: uncollapseText,

		onclick: function () {
			hashover.uncollapseInterface ();
			return false;
		}
	});

	// Add uncollapse hyperlink to HashOver div
	hashover.instance['main-element'].appendChild (uncollapseLink);
};
