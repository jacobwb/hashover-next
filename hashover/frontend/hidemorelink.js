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

	setTimeout (function () {
		// Remove the more hyperlink from page
		moreLink.parentNode.removeChild (moreLink);

		// Show comment count and sort options
		hashover.elements.get('count-wrapper').style.display = '';

		// Show popular comments section
		hashover.elements.exists ('popular-section', function (popularSection) {
			popularSection.style.display = '';
		});

		// Callback to remove specific class names
		var classRemover = function (element, elements, i, className) {
			hashover.classes.remove (element, className);
		};

		// Remove hidden comment class from comments
		hashover.elements.eachClass (sortSection, 'hashover-hidden', classRemover);

		// Remove out of order class from comments
		hashover.elements.eachClass (sortSection, 'hashover-disjoined', classRemover);

		// Execute callback function
		if (typeof (callback) === 'function') {
			callback ();
		}
	}, 350);
};
