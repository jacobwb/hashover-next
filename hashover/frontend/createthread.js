// Create a new comment thread/section (createthread.js)
HashOver.prototype.createThread = function (id, options, instance)
{
	// Reference to this object
	var hashover = this;

	// Arguments to this method
	var args = arguments;

	// Check if we're on the first instance or if the backend is ready
	if (HashOver.backendReady === true || HashOver.instanceCount === 1) {
		// If so, create the thread when the page is ready
		HashOver.onReady (function () {
			HashOver.instantiator.apply (hashover, args);
		});
	} else {
		// If not, check again in 10 milliseconds
		setTimeout (function () {
			hashover.createThread.apply (hashover, args);
		}, 10);
	}
};
