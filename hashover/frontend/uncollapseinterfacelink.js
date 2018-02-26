// Creates the interface uncollapse button (uncollapseinterfacelink.js)
HashOver.prototype.uncollapseInterfaceLink = function ()
{
	// Decide button text
	var uncollapseLocale = (this.instance['total-count'] >= 1) ? 'show-number-comments' : 'post-comment-on';
	var uncollapseText = this.instance[uncollapseLocale];

	// Create hyperlink to uncollapse the comment interface
	var uncollapseInterfaceLink = this.elements.create ('a', {
		href: '#',
		className: 'hashover-more-link',
		title: uncollapseText,
		textContent: uncollapseText,

		onclick: function () {
			// Add class to hide the uncollapse interface hyperlink
			hashover.classes.add (this, 'hashover-hide-more-link');

			// Element to unhide
			var uncollapseIDs = ['form-section', 'comments-section', 'end-links'];

			setTimeout (function () {
				// Remove the uncollapse interface hyperlink from page
				if (hashover.instance['main-element'].contains (uncollapseInterfaceLink) === true) {
					hashover.instance['main-element'].removeChild (uncollapseInterfaceLink);
				}

				// Show hidden form elements
				for (var i = 0, il = uncollapseIDs.length; i < il; i++) {
					hashover.elements.exists (uncollapseIDs[i], function (element) {
						element.style.display = '';
					});
				}

				// Show popular comments section
				if (hashover.setup['collapse-limit'] > 0) {
					hashover.elements.exists ('popular-section', function (popularSection) {
						popularSection.style.display = '';
					});
				}
			}, 350);

			return false;
		}
	});

	// Add uncollapse hyperlink to HashOver div
	hashover.instance['main-element'].appendChild (uncollapseInterfaceLink);
};
