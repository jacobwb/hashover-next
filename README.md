HashOver 2.0 (development)
========

**HashOver** is a PHP comment system intended as a replacement for services like Disqus. HashOver is free and open source software, under the [GNU Affero General Public License](http://www.gnu.org/licenses/agpl.html). HashOver adds a "comment section" to any website, by placing a few simple lines of JavaScript or PHP to the source code of any webpage. HashOver is a self-hosted system and allows completely anonymous comments to be posted, the only required information is the comment itself.


Notable Features
---
| General                    | Customization           | Advanced                         |
| :------------------------- | :---------------------- | :------------------------------- |
| Threaded replies           | Multiple themes         | Allows limited use of HTML       |
| Comment editing & deletion | Customizable HTML       | Multiple comment sorting methods |
| Likes & Dislikes           | Comment layout template | Spam filtering                   |
| Popular comments section   | Customizable CSS        | Notification emails              |
| Multiple languages         | File format plugins     | Comment RSS feeds                |
| Automatic URL links        | Authentication plugins  | Referrer checking                |
| Administration             |                         | Comment permalinks               |
| Avatar icons               |                         | IP address blocking              |
| Display remote images      |                         |                                  |


Required libraries/compilation
---
Although most PHP installations include everything HashOver requires, depending on your setup you may need to install some libraries and/or ensure PHP was compiled with support for the following libraries.

| Feature                               | Library name | Debian/Ubuntu package name |
| :------------------------------------ | :----------- | :------------------------- |
| XML file format support               | libxml       | php-xml                    |
| Multi-byte character support          |              | php-mbstring               |
| User information encryption           | libmcrypt    | php-mcrypt                 |
|                                       |              |                            |
| JSON file format support (optional)   |              | php-json                   |
| SQLite file format support (optional) | libsqlite3-0 | php-sqlite3                |


Important differences from version 1.0
---
- `hashover.php` is no longer used in JavaScript tags, the file `/hashover/hashover.js` is used instead.

  So change:

  ```html
  <script type="text/javascript" src="/hashover.php"></script>
  ```

  To:

  ```html
  <script type="text/javascript" src="/hashover/hashover.js"></script>
  ```

- HashOver 2.0 is object oriented, many things have changed places and been renamed. For normal users, about the only thing that is important to know is that the `secrets.php` file was merged with the `settings.php` file.


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

Information and Documentation
---
Forthcoming.

Contributing
---
When sending a "Pull Request", committing code, or otherwise sending, submitting, or transmitting code in any other way, please place a GNU Affero General Public License notice or any compatible license notice at the top of the code (if one isn't already present) and assign your contribution's copyright to yourself and "Jacob Barkdull". This gives me the necessary rights to distribute your contribution in HashOver under the GNU Affero General Public License. If you only assign yourself as copyright holder, your contribution will be rejected.

HashOver is partially written in JavaScript, plain, standard, non-jQuery JavaScript. If your contribution improves or adds new functionality to the JavaScipt portions of HashOver, your contribution must also be written in plain, standard, non-jQuery JavaScript. Contributions using or assuming the presence of jQuery, Underscore, AngularJS, Prototype, React, Node.js, or any other abstraction layer, library, and/or framework will be rejected.
