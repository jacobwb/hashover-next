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

		// Sort comments if sort method drop down menu exists
		this.elementExists ('sort-select', function (sortSelect) {
			comments = hashover.sortComments (comments, sortSelect.value);
		});

		// And append comments
		this.appendComments (comments);
	}

	// Add controls to the new comment
	this.addControls (json.comment);

	// Update comment count
	this.getElement('count').textContent = json.count;
	this.incrementCounts (type);
};
