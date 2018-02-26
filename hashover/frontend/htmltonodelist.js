// Converts an HTML string to DOM NodeList (htmltonodelist.js)
HashOver.prototype.HTMLToNodeList = function (html)
{
	return this.elements.create ('div', { innerHTML: html }).childNodes;
};
