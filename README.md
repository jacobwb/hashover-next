HashOver 2.0 (development)
========

**HashOver** is a PHP comment system intended as a replacement for services like Disqus. HashOver is free and open source software, under the [GNU Affero General Public License](http://www.gnu.org/licenses/agpl.html). HashOver adds a "comment section" to any website, by placing a few simple lines of JavaScript or PHP to the source code of any webpage. HashOver is a self-hosted system and allows completely anonymous comments to be posted, the only required information is the comment itself.

Notable Features
---
<table cellpadding="2" cellspacing="2" width="100%">
	<tbody>
		<tr>
			<td width="38%">
				<ul>
					<li>Restricted use of HTML tags</li>
					<li>Display externally hosted images</li>
					<li>Five comment sorting methods</li>
					<li>Multiple languages</li>
					<li>Spam filtering</li>
					<li>IP address blocking</li>
					<li>Notification emails</li>
				</ul>
			</td>
			<td width="33%">
				<ul>
					<li>Threaded replies</li>
					<li>Avatar icons</li>
					<li>Comment editing &amp; deletion</li>
					<li>Comment RSS feeds</li>
					<li>Likes</li>
					<li>Popular comments</li>
					<li>Comment layout templates</li>
				</ul>
			</td>
			<td valign="top" width="28%">
				<ul>
					<li>Administration</li>
					<li>Automatic URL links</li>
					<li>Customizable HTML</li>
					<li>Customizable CSS</li>
					<li>Referrer checking</li>
					<li>Permalinks</li>
				</ul>
			</td>
		</tr>
	</tbody>
</table><br>

Information and Documentation
---
http://tildehash.com/?page=hashover

** IMPORTANT NOTICE **

This version modifies the file organisation to handle all in one subdirectory `hashover-next` of the root of the website.
All the documentation of the previous version is still valid, but you must correct all the path when you read it, prefixing them with `/hashover-next/`.

For example, to use Hashover-Next with the JavaScript method, you shoud use this code :

	<script type="text/javascript" src="/hashover-next/hashover.js"></script>
	<noscript>You must have JavaScript enabled to use the comments.</noscript>

Focus of this release
---
HashOver [version 1.0](https://github.com/jacobwb/hashover) consists of code written by [one person](http://tildehash.com/?page=author) over the course of five years, come March the 29th 2014. Moreover, HashOver was my first serious use of JavaScript and my first PHP project of such complexity. Those two facts should trigger obvious concerns about HashOver's performance, efficiency, and security. With that in mind, version 2.0 will be the next release, skipping 1.x releases all together, and will focus on improving nothing but the following areas.

- Security
- Performance [[#61](https://github.com/jacobwb/hashover-next/issues/61)]
- Code efficiency [[#62](https://github.com/jacobwb/hashover-next/issues/62)]
- Deployment
- Data storage format [[#32](https://github.com/jacobwb/hashover-next/issues/32)]
- Backwards and forwards compatibility
- Operating system support
- Aesthetics and graphics [[#4](https://github.com/jacobwb/hashover-next/issues/4)] [[#11](https://github.com/jacobwb/hashover-next/issues/11)]
- Graphic scalability on (*x<sup>i</sup>*)HDPI displays [[#4](https://github.com/jacobwb/hashover-next/issues/4)] [[#11](https://github.com/jacobwb/hashover-next/issues/11)]
- Code readability [[#62](https://github.com/jacobwb/hashover-next/issues/62)] [[#63](https://github.com/jacobwb/hashover-next/issues/63)]
- Bug fixes

This means the possibility of new features in version 2.0 is next to null, and contributions via GitHub and/or e-mail that add new features will be rejected, at least for the time being. Improvements to existing functionality and aesthetics will be accepted. New features will be accepted and available in version 2.x releases.

Contributing
---
When sending a "Pull Request", committing code, or otherwise sending, submitting, or transmitting code in any other way, please assign your contribution's copyright to "Jacob Barkdull", and place a GNU Affero General Public License or any compatible license notice at the top of the code, if one isn't already present. This gives me necessary rights to distribute your contribution in HashOver under the GNU Affero General Public License.

If you only assign yourself as copyright holder, your contribution will be rejected.

HashOver makes use of JavaScript, plain, standard, non-jQuery JavaScript. If your contribution improves or adds new functionality to the JavaScipt portions of HashOver, your contribution must also be written in plain, standard, non-jQuery JavaScript.

Code contributions using or assuming the presence of jQuery, Underscore, AccDC, Ample SDK, AngularJS, CupQ, DHTMLX, Dojo, Echo3, Enyo, Ext JS, midori, MochiKit, MooTools, PhoneJS, Prototype, qooxdoo, Rialto Toolkit, Rico, script.aculo.us, Wakanda, Web Atoms JS, Webix, YUI, or any other abstraction layer, library, and/or framework will be rejected.
