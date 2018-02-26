// "Flatten" the comments object (getallcomments.js)
HashOver.prototype.getAllComments = function (comments)
{
	var commentsCopy = this.cloneObject (comments);
	var output = [];

	function descend (comment)
	{
		output.push (comment);

		if (comment.replies !== undefined) {
			for (var reply = 0, total = comment.replies.length; reply < total; reply++) {
				descend (comment.replies[reply]);
			}

			delete comment.replies;
		}
	}

	for (var comment = 0, total = commentsCopy.length; comment < total; comment++) {
		descend (commentsCopy[comment]);
	}

	return output;
};
