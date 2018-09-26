// Real constructor (instantiator.js)
HashOver.instantiator = function (id, options)
{
	// Reference to this object
	var hashover = this;

	// Backend request path
	var requestPath = HashOver.backendPath + '/comments-ajax.php';

	// Get backend queries
	var queries = HashOver.getBackendQueries (options);

	// Handle backend request
	this.ajax ('POST', requestPath, queries, function (json) {
		// Handle error messages
		if (json.message !== undefined) {
			hashover.displayError (json);
			return;
		}

		// Set the backend information
		if (HashOver.backendReady !== true) {
			// Locales from backend
			HashOver.prototype.locale = json.locale;

			// Setup information from backend
			HashOver.prototype.setup = json.setup;

			// Templatify UI HTML from backend
			HashOver.prototype.ui = hashover.strings.templatify (json.ui);

			// Mark backend as ready
			HashOver.backendReady = true;
		}

		// Thread information from backend
		hashover.instance = json.instance;

		// Initial number of collapsed comments
		hashover.instance.collapseLimit = 0;

		// Backend execution time and memory usage statistics
		hashover.statistics = json.statistics;

		// Initiate HashOver
		hashover.init (id);
	}, true);

	// Set instance number
	this.instanceNumber = HashOver.instanceCount;

	// Store options and queries
	this.options = options;
	this.queries = queries;

	// And increment instance count
	HashOver.instanceCount++;
};
