HashOver 2.0 (development)
========

<b>HashOver</b> is a PHP comment system intended as a replacement for services like Disqus. HashOver is free and open source software, under the <a href="http://www.gnu.org/licenses/agpl.html" target="_blank">GNU Affero General Public License</a>. HashOver adds a "comment section" to any website, by placing a few simple lines of JavaScript or PHP to the source code of any webpage. HashOver is a self-hosted system and allows completely anonymous comments to be posted, the only required information is the comment itself.

<b>Notable Features:</b>
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
</table>

<b>Information and Documentation:</b>
---
http://tildehash.com/?page=hashover

<b>Focus of this release:</b>
---
HashOver <a href="https://github.com/jacobwb/hashover" target="_blank">version 1.0</a> consists of code written by <a href="http://tildehash.com/?page=author" target="blank">one person</a> over the course of five years, come March the 29th 2014. Moreover, HashOver was my first serious use of JavaScript and my first PHP project of such complexity. Those two facts should trigger obvious concerns about HashOver's performance, efficiency, and security. With that in mind, version 2.0 will be the next release, skipping 1.x releases all together, and will focus on improving nothing but the following areas.
<ul>
	<li>Security</li>
	<li>Performance</li>
	<li>Code efficiency</li>
	<li>Deployment</li>
	<li>Data storage format [<a href="https://github.com/jacobwb/hashover-next/issues/32">#32</a>]</li>
	<li>Backwards and forwards compatibility</li>
	<li>Operating system support</li>
	<li>Aesthetics and graphics [<a href="https://github.com/jacobwb/hashover-next/issues/4">#4</a>] [<a href="https://github.com/jacobwb/hashover-next/issues/11">#11</a>]</li>
	<li>Graphic scalability on (<i style="font-family: monospace;">X<sup style="font-size: 10px;">i</sup></i>)HDPI displays [<a href="https://github.com/jacobwb/hashover-next/issues/4">#4</a>] [<a href="https://github.com/jacobwb/hashover-next/issues/11">#11</a>]</li>
	<li>Code readability</li>
	<li>Bug fixes</li>
</ul>
		
This means the possibility of new features in version 2.0 is next to null, and contributions via GitHub and/or e-mail that add new features will be rejected, at least for the time being. Improvements to existing functionality and aesthetics will be accepted. New features will be accepted and available in version 2.x releases.

<b>Contributing:</b>
---
When sending a "Pull Request", committing code, or otherwise sending, submitting, or transmitting code in any other way, please assign your contribution's copyright to "Jacob Barkdull", and place a GNU Affero General Public License or any compatible license notice at the top of the code, if one isn't already present. This gives me necessary rights to distribute your contribution in HashOver under the GNU Affero General Public License. If you only assign yourself as copyright holder, your contribution will be rejected.

HashOver makes use of JavaScript, plain, standard, non-jQuery JavaScript. If your contribution improves or adds new functionality to the JavaScipt portions of HashOver, your contribution must also be written in plain, standard, non-jQuery JavaScript. Code contributions using or assuming the presence of jQuery, Underscore, AccDC, Ample SDK, AngularJS, CupQ, DHTMLX, Dojo, Echo3, Enyo, Ext JS, midori, MochiKit, MooTools, PhoneJS, Prototype, qooxdoo, Rialto Toolkit, Rico, script.aculo.us, Wakanda, Web Atoms JS, Webix, YUI, or any other abstraction layer, library, and/or framework will be rejected.
