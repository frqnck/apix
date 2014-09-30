APIx changelog
==============

Version 0.3.8
-------------
* Added Service 'logger' PSR3 logger aware using by default Apix\Log.
* Plugin\Cache unit-tested and updated to Apix\Cache v1.2
* Plugin\Auth unit-tested.
* Many bug fixes and additional unit-tests.
* Added Scrutinizer checks.

Version 0.3.7
-------------
* Added an internal cache (reflection/parsing of annotation).
* Added CORS (Cross-Origin Resource Sharing) plugin. Fully implementing (well
  trying to) http://www.w3.org/TR/cors
* Improvement to the Exception handler [https://github.com/frqnck/apix/issues/3].
* Many bug fixes and additional unit-tests.

Version 0.3.6
-------------
* Plugin\Cache adapter files have been separated from the main distribution in
  favor of Apix\Cache (see https://github.com/frqnck/apix-cache).
* Modified config.dist.php accordingly.

Version 0.3.5
-------------
* Some modifications to the Auth plugin and related files.
* Added a Session class to hold current user/session data.
* Modified the examples in 'config.dist.php'.
* Renamed Services to Service (singularize),and added set() and has() methods.

Version 0.3.4
-------------
* maintenace release, internal changes.

Version 0.3.3
-------------
* Removed HTTP/Request2 (pear) dependencies.
* Added zlib to the system-checker.
* Added Pharstrap to run the tests against the Phar file.

Version 0.3.2
-------------
* Major changes to the plugin system (listeners).
* Revisted existing plugins.
* Added OutputDebug and OutputSign.

Version 0.3.1
-------------
* Major changes to the plugin system (listeners).
* Revisted existing plugins.
* Added OutputDebug and OutputSign.

Version 0.2.1
-------------
* Added Cache plugin with APC and Redis adapters.
* Added Tidy plugin.
* Renamed 'Listeners' to 'Plugins'.

Version 0.1.3
-------------
* Improved Auth listener.
* Improved the console and system-checker.

Version 0.1.2
-------------
* Added Regex to the URL routing.

Version 0.1.1
-------------
* Namespace renamed to Apix.
* Code cleanup and fixes.
* Request::getParam will now decode URL-encoded strings by default.
* Added additional tests and minor changes.

Version 0.0.3 (*)
-------------
* Initial internal release.
* phar-server release.
* Added a readme file.

Version 0.0.2
-------------
* Added listeners for auth/acl.
* Added Console enviroment.
* Added Phar archive.

Version 0.0.1
-------------
* Initial release.


<pre>
  _|_|    _|_|    _|     _|      _|
_|    _| _|    _|         _|    _|
_|    _| _|    _| _|        _|_|
_|_|_|_| _|_|_|   _| _|_|   _|_|
_|    _| _|       _|      _|    _|
_|    _| _|       _|     _|      _|
</pre>
