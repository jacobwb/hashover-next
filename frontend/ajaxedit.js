// For editing comments (ajaxedit.js)
HashOver.prototype.AJAXEdit = function (json, permalink)
{
	// Get old comment element
	var comment = this.getElement (permalink);

	// Get old comment from primary comments
	var oldItem = this.permalinkComment (permalink, this.instance.comments.primary);

	// Get new comment child elements
	var newComment = this.htmlChildren (this.parseComment (json.comment));

	// Get old and new comment elements
	var newElements = newComment[0].children;
	var oldElements = comment.children;

	// Replace old comment with edited comment
	for (var i = newElements.length - 1; i >= 0; i--) {
		comment.replaceChild (newElements[i], oldElements[i]);
	}

	// Add controls back to the comment
	this.addControls (json.comment);

	// Update primary comments with edited comment
	for (var attribute in json.comment) {
		if (json.comment.hasOwnProperty (attribute) === true) {
			oldItem[attribute] = json.comment[attribute];
		}
	}
};
