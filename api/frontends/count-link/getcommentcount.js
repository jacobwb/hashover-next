// Send AJAX request to backend for a comment count (getcommentcount.js)
HashOverCountLink.prototype.getCommentCount = function (link, settings)
{
	// Reference to this HashOver object
	var hashover = this;

	// Get backend queries
	var queries = ['url=' + encodeURIComponent (link.href)];

	// Get cfg URL queries array
	var cfgQueries = HashOverCountLink.cfgQueries (settings);

	// And add cfg queries
	queries = queries.concat (cfgQueries);

	// Backend request path
	var requestPath = HashOverCountLink.backendPath + '/count-link-ajax.php?' + queries.sort ().join ('&');

	// Handle backend request
	this.ajax ('GET', requestPath, null, function (json) {
		if (json['link-text'] !== undefined) {
			link.textContent = json['link-text'];
		}
	}, true);
};
