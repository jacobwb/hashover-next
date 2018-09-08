// Create a new comment thread/section (createthread.js)
HashOver.prototype.createThread = function (options)
{
	// Reference to this object
	var hashover = this;

	// Self-executing backend wait loop
	(function backendWait () {
		// Check if we're on the first instance or if the backend is ready
		if (HashOver.backendReady === true || HashOver.instanceCount === 0) {
			// If so, execute the real constructor
			HashOver.instantiator.call (hashover, options);
		} else {
			// If not, check again in 100 milliseconds
			setTimeout (backendWait, 100);
		}
	}) ();
};
