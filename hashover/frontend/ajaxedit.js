// For editing comments (ajaxedit.js)
HashOver.prototype.AJAXEdit = function (json, permalink, destination, isReply)
{
	// Get old comment element nodes
	var comment = this.elements.get (permalink, true);
	var oldNodes = comment.childNodes;
	var oldComment = this.permalinks.getComment (permalink, this.instance.comments.primary);

	// Get new comment element nodes
	var newNodes = this.HTMLToNodeList (this.comments.parse (json.comment));
	    newNodes = newNodes[0].childNodes;

	// Replace old comment with edited comment
	for (var i = 0, il = newNodes.length; i < il; i++) {
		if (typeof (oldNodes[i]) === 'object'
		    && typeof (newNodes[i]) === 'object')
		{
			comment.replaceChild (newNodes[i], oldNodes[i]);
		}
	}

	// Add controls back to the comment
	this.addControls (json.comment);

	// Update old in array comment with edited comment
	for (var attribute in json.comment) {
		oldComment[attribute] = json.comment[attribute];
	}
};
