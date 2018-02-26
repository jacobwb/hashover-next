HashOver 2.0 (development)
===
**HashOver** is a PHP comment system intended as a replacement for services like Disqus. HashOver is free and open source software, under the [GNU Affero General Public License](http://www.gnu.org/licenses/agpl.html). HashOver adds a "comment section" to any website, by placing a few simple lines of JavaScript or PHP to the source code of any webpage. HashOver is a self-hosted system and allows completely anonymous comments to be posted, the only required information is the comment itself.


Notable Features
---
General                          | Customization           | Advanced
-------------------------------- | ----------------------- | --------------------------------
Threaded replies                 | Multiple themes         | Allows limited use of HTML
Comment editing & deletion       | Customizable HTML       | Multiple comment sorting methods
Likes & Dislikes                 | Comment layout template | Spam filtering
Popular comments section         | Customizable CSS        | Notification emails
Multiple languages               | File format plugins     | Comment RSS feeds
Automatic URL links              | Authentication plugins  | Referrer checking
Administration                   |                         | Comment permalinks
Avatar icons                     |                         | IP address blocking
Display remote images            |                         |


Required modules/compilation
---
Although most PHP installations include everything HashOver requires by default, depending on your setup you may need to install some modules/extensions and/or ensure PHP was compiled with support for the following modules.

Feature                          | Module name(s)          | Debian/Ubuntu package name(s)
-------------------------------- | ----------------------- | -----------------------------
Date and Time                    | date                    | *part of PHP core*
Document Object Model            | dom                     | *part of PHP core*
Regular Expressions              | pcre                    | *part of PHP core*
User information encryption      | openssl                 | *part of PHP core*
Multi-byte character support     | mbstring                | php-mbstring
XML data storage format support  | xml, libxml, SimpleXML  | php-xml
JSON data storage format support | json                    | php-json

**The following modules are optional**

Feature                          | Module name(s)          | Debian/Ubuntu package name(s)
-------------------------------- | ----------------------- | -----------------------------
PHP Data Objects                 | PDO                     | php-sqlite3, php-mysql
SQLite file format support       | pdo_sqlite, sqlite3     | php-sqlite3


Checking for required modules
---
On UNIX (GNU, BSD, etc) you may list which modules you have installed with this command...
```
php -m
```


HashOver on Windows
---
Windows will be officially supported by HashOver 2.0, there should not be any major issues with using HashOver on Windows. However, HashOver is primarily developed on and for UNIX operating systems, which are the primary operating systems used by the majority of web servers. If you have any issues with HashOver on Windows, please report them.

On Windows the command to list installed modules is...
```
php.exe -m
```


Important differences from version 1.0
---
- `hashover.php` is no longer used in JavaScript tags, the file `/hashover/comments.php` is used instead.

  So change:

  ```html
  <script type="text/javascript" src="/hashover.php"></script>
  ```

  To:

  ```html
  <script type="text/javascript" src="/hashover/comments.php"></script>
  ```

- HashOver 2.0 is object oriented, many things have changed places and been renamed. For normal users, about the only thing that is important to know is that the `secrets.php` file was merged with the `settings.php` file.

Important recent changes to version 2.0
---
- The `pages` directory is now `comments/threads`, this change will cause existing comments to not appear until you move the directories under `pages` into the new `comments/threads` directory.

- All JSON config files have been moved to the new `config` directory, you will need to move the files `hashover/blocklist.json` and `hashover/ignored-queries.json` into the new `config` directory. If these files do not exist, you don't need to do anything as these config files are completely optional.

- The previously removed `secrets.php` file has returned. The required setup information, namely the notification e-mail address, encryption key, and the admin username and password are now stored in this `secrets.php` file located at `/hashover/backend/classes/secrets.php`.
 
  You will need to move the values of the public properties `$notificationEmail`, `$encryptionKey`, `$adminName`, and `$adminPassword` in the `settings.php` file into the new `secrets.php` file, and remove these properties from `settings.php` or replace the `settings.php` file entirely, as its source code is publicly viewable, whereas the source code of `secrets.php` is not.


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
When sending a "Pull Request", committing code, or otherwise sending, submitting, or transmitting code in any other way, please place a [GNU Affero General Public License](http://www.gnu.org/licenses/agpl.html) notice or any compatible license notice at the top of the code (if one isn't already present) and assign your contribution's copyright to yourself or "Jacob Barkdull". This gives me the necessary rights to distribute your contribution in HashOver under the GNU Affero General Public License.

HashOver is partially written in JavaScript, plain, standard, non-jQuery JavaScript. If your contribution improves or adds new functionality to the JavaScipt portions of HashOver, your contribution must also be written in plain, standard, non-jQuery JavaScript. Contributions using or assuming the presence of jQuery, Underscore, AngularJS, Prototype, React, Node.js, or any other abstraction layer, library, and/or framework will be rejected.
