HashOver 2.0 (development)
===
**HashOver** is a PHP comment system intended as a replacement for services like
Disqus. HashOver is free and open source software, under the
[GNU Affero General Public License](http://www.gnu.org/licenses/agpl.html).
HashOver adds a "comment section" to any website, by placing a few simple lines
of JavaScript or PHP to the source code of any webpage. HashOver is a
self-hosted system and allows completely anonymous comments to be posted, the
only required information is the comment itself.


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
Although most PHP installations include everything HashOver requires by default,
depending on your setup you may need to install some modules/extensions and/or
ensure PHP was compiled with support for the following modules.

Feature                          | Module name(s)          | Debian/Ubuntu package name(s)
-------------------------------- | ----------------------- | -----------------------------
Date and Time                    | date                    | *part of PHP core*
Document Object Model            | dom                     | *part of PHP core*
Regular Expressions              | pcre                    | *part of PHP core*
User information encryption      | openssl                 | *part of PHP core*
Multi-byte character support     | mbstring                | php-mbstring
Internationalisation             | intl                    | php-intl
XML data storage format support  | xml, libxml, SimpleXML  | php-xml
JSON data storage format support | json                    | php-json

**The following modules are optional**

Feature                          | Module name(s)          | Debian/Ubuntu package name(s)
-------------------------------- | ----------------------- | -----------------------------
PHP Data Objects                 | PDO                     | php-sqlite3, php-mysql
SQLite file format support       | pdo_sqlite, sqlite3     | php-sqlite3


Checking for required modules
---
On UNIX (GNU, BSD, etc) you may list installed modules with this command:
```
php -m
```

On Windows the command to list installed modules is:
```
php.exe -m
```


Important differences from version 1.0
---
- `hashover.php` is no longer used in JavaScript tags, the file
  `/hashover/comments.php` is used instead.

  So change:

  ```html
  <script type="text/javascript" src="/hashover.php"></script>
  ```

  To:

  ```html
  <script type="text/javascript" src="/hashover/comments.php"></script>
  ```


Important recent changes to version 2.0
---
- The `pages` directory is now `comments/threads`, this change will cause
  existing comments to not appear until you move the directories under `pages`
  into the new `comments/threads` directory.

- All JSON config files have been moved to the new `config` directory, you will
  need to move the files `hashover/blocklist.json` and
  `hashover/ignored-queries.json` into the new `config` directory. If these
  files do not exist, you don't need to do anything as these config files are
  completely optional.

- The previously removed `secrets.php` file has returned. The required setup
  information, namely the notification e-mail address, encryption key, and the
  admin username and password are now stored in this `secrets.php` file located
  at `/hashover/backend/classes/secrets.php`.
 
  You will need to move the values of the public properties
  `$notificationEmail`, `$encryptionKey`, `$adminName`, and `$adminPassword` in
  the `settings.php` file into the new protected properties in the `secrets.php`
  file, and remove these properties from `settings.php` or replace the
  `settings.php` file entirely, as its source code is publicly viewable, whereas
  the source code of `secrets.php` is not.


HashOver on Windows
---
Windows will be officially supported by HashOver 2.0, there should not be any
major issues with using HashOver on Windows. However, HashOver is primarily
developed on and for UNIX operating systems, which are the primary operating
systems used by the majority of web servers. If you have any issues with
HashOver on Windows, please report them.


Information and Documentation
---
[Official HashOver 2.0 Documentation](https://docs.barkdull.org/hashover-v2)
