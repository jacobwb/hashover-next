// Add Like/Dislike link and count to template (addratings.js)
HashOverLatest.prototype.comments.addRatings = function (comment, template, action, commentKey)
{
	// Reference to the parent object
	var hashover = this.parent;

	// Check if the comment has been likes/dislikes
	if (comment[action + 's'] !== undefined) {
		// Add likes/dislikes to HTML template
		template[action + 's'] = comment[action + 's'];

		// Get "X Like/Dislike(s)" locale
		var plural = (comment[action + 's'] === 1 ? 0 : 1);
		var count = comment[action + 's'] + ' ' + hashover.locale[action][plural];
	}

	// Add like count to HTML template
	template[action + '-count'] = hashover.strings.parseTemplate (hashover.ui[action + '-count'], {
		permalink: commentKey,
		text: count || ''
	});
};
