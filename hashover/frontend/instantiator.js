// Real constructor (instantiator.js)
HashOver.instantiator = function (options)
{
	// Get backend queries
	var queries = HashOver.getBackendQueries (options);

	// Check if we're instantiating the first HashOver object
	if (HashOver.prepared !== true) {
		// If so, set query indicating a request for backend information
		queries.push ('prepare=true');
	}

	// Reference to this object
	var hashover = this;

	// Increment HashOver instance count
	HashOver.instanceCount++;

	// Backend request path
	var requestPath = HashOver.backendPath + '/comments-ajax.php';

	// Handle backend request
	this.ajax ('POST', requestPath, queries, function (json) {
		// Handle error messages
		if (json.message !== undefined) {
			hashover.displayError (json);
			return;
		}

		// Set the backend information
		if (HashOver.prepared !== true) {
			// Locales from backend
			HashOver.prototype.locale = json.locale;

			// Setup information from backend
			HashOver.prototype.setup = json.setup;

			// UI HTML from backend
			HashOver.prototype.ui = json.ui;

			// Mark backend as ready
			HashOver.prepared = true;
		}

		// Thread information from backend
		hashover.instance = json.instance;

		// Backend execution time and memory usage statistics
		hashover.statistics = json.statistics;

		// Initiate HashOver
		hashover.init ();
	}, true);

	// Set instance number to current instance count
	this.instanceNumber = HashOver.instanceCount;

	// Add parent proterty to all prototype objects
	for (var name in this) {
		var value = this[name];

		if (value && value.constructor === Object) {
			value.parent = this;
		}
	}
};
