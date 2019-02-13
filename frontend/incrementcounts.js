// Increase comment counts (incrementcounts.js)
HashOver.prototype.incrementCounts = function (isReply)
{
	// Count top level comments
	if (isReply === false) {
		this.instance['primary-count']++;
	}

	// Increase all count
	this.instance['total-count']++;
};
