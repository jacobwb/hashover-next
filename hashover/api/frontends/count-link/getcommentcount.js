// Send AJAX request to backend for a comment count (getcommentcount.js)
HashOverCountLink.prototype.getCommentCount = function (link, options)
{
	// Reference to this HashOver object
	var hashover = this;

	// Get backend queries
	var queries = ['url=' + options.url];

	// Backend request path
	var requestPath = HashOverCountLink.backendPath + '/count-link-ajax.php';

	// Handle backend request
	this.ajax ('POST', requestPath, queries, function (json) {
		if (json['link-text'] !== undefined) {
			link.textContent = json['link-text'];
		}
	}, true);
};
