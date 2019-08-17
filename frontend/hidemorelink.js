// For showing more comments, via AJAX or removing a class (hidemorelink.js)
HashOver.prototype.hideMoreLink = function (callback)
{
	// Reference to this object
	var hashover = this;

	// Sort section element
	var sortSection = this.instance['sort-section'];

	// More link element
	var moreLink = this.instance['more-link'];

	// Add class to hide the more hyperlink
	this.classes.add (this.instance['more-link'], 'hashover-hide-more-link');

	// Wait for hiding transition to end
	setTimeout (function () {
		// Remove the more hyperlink from page
		moreLink.parentNode.removeChild (moreLink);

		// Show comment count and sort options
		hashover.getElement('count-wrapper').style.display = '';

		// Show popular comments section
		hashover.elementExists ('popular-section', function (popularSection) {
			popularSection.style.display = '';
		});

		// Callback to remove specific class names
		var classRemover = function (element, elements, i, className) {
			hashover.classes.remove (element, className);
		};

		// Remove hidden comment class from comments
		hashover.eachClass (sortSection, 'hashover-hidden', classRemover);

		// Execute callback function
		if (typeof (callback) === 'function') {
			callback ();
		}
	}, 350);
};
