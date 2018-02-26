// Process all HashOver count links (processlinks.js)
HashOverCountLink.prototype.processLinks = function ()
{
	// Get count link class elements
	var countLinks = document.getElementsByClassName ('hashover-count-link');

	// Run through the count links
	for (var i = 0, il = countLinks.length, link; i < il; i++) {
		link = countLinks[i];

		// Instantiate new count link object
		this.getCommentCount (link, {
			url: link.href
		});
	}
};
