// Real constructor (instantiator.js)
HashOver.instantiator = function (id, options, instance)
{
	// Reference to this object
	var hashover = this;

	// Check if we are instantiating a specific instance
	var specific = this.rx.integer.test (instance);

	// Use given instance or instance count
	var instance = specific ? instance : HashOver.instanceCount;

	// Backend request path
	var requestPath = HashOver.backendPath + '/comments-ajax.php';

	// Get backend queries
	var backendQueries = HashOver.getBackendQueries (options, instance);

	// Add current client time to queries
	var queries = backendQueries.concat ([
		'time=' + HashOver.getClientTime ()
	]);

	// Set instance number
	this.instanceNumber = instance;

	// Store options and backend queries
	this.options = options;
	this.queries = backendQueries;

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

		// And log backend execution time and memory usage in console
		console.log (hashover.strings.sprintf (
			'HashOver: backend %d ms, %s', [
				json.statistics['execution-time'],
				json.statistics['script-memory']
			]
		));

		// Initiate HashOver
		hashover.init (id);
	}, true);

	// And increment instance count where appropriate
	if (specific === false) {
		HashOver.instanceCount++;
	}
};
