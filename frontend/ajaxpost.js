// Increase comment counts (ajaxpost.js)
HashOver.prototype.incrementCounts = function (type)
{
	// Count top level comments
	if (type !== 'reply') {
		this.instance['primary-count']++;
	}

	// Increase all count
	this.instance['total-count']++;
};

// For posting comments (ajaxpost.js)
HashOver.prototype.AJAXPost = function (json, permalink, type)
{
	// Reference to this object
	var hashover = this;

	// Check if comment is a reply
	if (type === 'reply') {
		// If so, get element of comment being replied to
		var dest = this.getElement (permalink);
	} else {
		// If not, use sort section element
		var dest = this.instance['sort-section'];
	}

	// Get primary comments in order
	var comments = this.instance.comments.primary;

	// Check if there are no comments
	if (this.instance['total-count'] === 0) {
		// If so, replace "Be the first to comment!"
		this.instance.comments.primary[0] = json.comment;

		// And place comment on page
		dest.innerHTML = this.parseComment (json.comment);
	} else {
		// If not, add comment to comments array
		this.addComments (json.comment, type);

		// Fetch parent comment by its permalink
		var parent = this.permalinkComment (
			this.permalinkParent (json.comment.permalink),
			this.instance.comments.primary
		);

		// Parse comment
		var comment = this.parseComment (json.comment, parent);

		// Get comment child elements
		var elements = this.htmlChildren (comment);

		// Check if the comment is a reply and has a permalink
		if (type === 'reply' && permalink !== undefined) {
			// If so, store indicator of when comment is out of stream depth
			var outOfDepth = (permalink.split ('r').length > this.setup['stream-depth']);

			// Check if we are in stream mode and out of the allowed depth
			if (this.setup['stream-mode'] === true && outOfDepth === true) {
				// If so, append comment to parent element
				dest.parentNode.insertBefore (elements[0], dest.nextSibling);
			} else {
				// If not, append to destination element
				dest.appendChild (elements[0]);
			}
		} else {
			// If not, append to destination element
			dest.appendChild (elements[0]);
		}
	}

	// Add class to indicate comment is out of order
	if (type !== 'reply' && this.instance['showing-more'] === false) {
		this.elementExists (json.comment.permalink, function (comment) {
			hashover.classes.add (comment, 'hashover-disjoined');
		});
	}

	// Add controls to the new comment
	this.addControls (json.comment);

	// Update comment count
	this.getElement('count').textContent = json.count;
	this.incrementCounts (type);
};
