APIx, RESTful services for PHP [![Build Status](https://travis-ci.org/frqnck/apix.png?branch=master)](https://travis-ci.org/frqnck/apix)
===========================================

[![Latest Stable Version](https://poser.pugx.org/apix/apix/v/stable.svg)](https://packagist.org/packages/apix/apix)  [![Build Status](https://scrutinizer-ci.com/g/frqnck/apix/badges/build.png?b=master)](https://scrutinizer-ci.com/g/frqnck/apix/build-status/master)  [![Code Quality](https://scrutinizer-ci.com/g/frqnck/apix/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/frqnck/apix/?branch=master)  [![Code Coverage](https://scrutinizer-ci.com/g/frqnck/apix/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/frqnck/apix/?branch=master)  [![License](https://poser.pugx.org/apix/apix/license.svg)](https://packagist.org/packages/apix/apix)

APIx is a (micro-)framework to build RESTful Web services. It will run alognside your existing framework/application with minimum fuss.

Some of its features:

* Supports **many data inputs** such as GET/POST parameters, XML, JSON, CSV, ...
* Provides **various output representation** such as XML, JSONP, HTML, PHP, ...
* Provides **on-demand resources documention**, using GET /help or 'OPTIONS'.
* Uses **annotations to document** and **set service behaviours**.
* Handles **most HTTP methods**, including PUT, DELETE, HEAD, OPTIONS and PATCH (TRACE to some extend).
* Bundle with **many plugins and adapters** for Authentification and ACL, caching...
* **Follows the standards** such as [rfc2616] [rfc2616], [rfc2617] [rfc2617],
[rfc2388] [rfc2388], [rfc2854] [rfc2854], [rfc4627] [rfc4627], [rfc4329] [rfc4329],
[rfc2046] [rfc2046], [rfc3676] [rfc3676], [rfc3023] [rfc3023], etc...
* Provides **method-override** usign X-HTTP-Method-Override (Google recommendation) and/or using a query-param (customisable).
* Supports **content negotiation** (which can also be overriden).
* Take advantages of network caches -- supports HEAD test.
* Available as a standalone **[PHAR][phar]** file, or via **[Composer][composer]** or as a **[PEAR] [pear]** package.
* Continuous integration against PHP **5.x**, and **7.x**.
* Read the [documentation][doc]!

Todo:
* Self-generated API resources testing.
* Add support for WSDL 2.0 / WADL.
* Eventually SOAP (and XML_RPC) bridging.

Feel free to comment, send pull requests and patches...

Basic usage
-----------

```php
<?php
    require 'apix.phar';

    // Instantiate the server (using the default config)
    $api = new Apix\Server;

    // Create a GET handler $name is required
    $api->onRead('/hello/:name', function($name) {
        return array('Hello ' . $name);
    });

    $api->run();
```

Another example using annotations.
    
```php
    // $type and $stuff are required parameters.
    // $optional is not mandatory.
    $api->onRead('/search/:type/with/:stuff/:optional',
        /**
         * Search for things by type that have stuff.
         *
         * @param     string  $type         A type of thing to search upon
         * @param     string  $stuff        One or many stuff to filter against
         * @param     string  $optional     An optional field
         * @return    array
         * @api_auth  groups=clients,employes,admins users=franck,jon
         * @api_cache ttl=12mins tags=searches,indexes
         */
        function($type, $stuff, $optional = null) {
            // some logic
            return $results;
        }
    );

    $api->run();
```

Advanced usage
--------------

### Routing

A route defines the path to a resource, once matched the corresponding resource's controller and dedicated handlers are invoked.

Any returned value emanating from a resource's controller, generally an associative array, will become the main subject of the response.

Essentially, a route is made of:

1.  A **route controller** that corresponds to a HTTP header method:

<pre>
    onCreate()   ->   POST          |        onModify()   ->   PATCH
    onRead()     ->   GET           |        onHelp()     ->   OPTIONS
    onUpdate()   ->   PUT           |        onTest()     ->   HEAD
    onDelete()   ->   DELETE        |        onTrace()    ->   TRACE
</pre>

2.  A **route path** corresponding to a Request-URI.
    * It may represent a specific and _static_ resource entity, such as:
        <pre>/search/france/paris</pre>
    * It may also be _dynamic_, and may include one or many variables indicated by a colon `:`, such as:
        <pre>/search/:country/:city</pre>

### Controller definitions

A resource controller may be declared as either:

* a public method from some user defined classes,
* a closure/lambda function, defined at runtime (Sinatra style).

It will use:

*   variable name to inherit values from the route's path,
    e.g. `$name` inherited from `/category/:name`.

*   type hinting to inject any of the current scope Apix's objects,
    e.g. `Request`, `Response`, etc...

    See Apix's own [Documentation] [apixdoc] for what's available.

Here is an example showing these in context:

```php
    $api->onRead('/category/:name', function(Request $request, $name) {

        // retrieve a named param
        $page = (int) $request->getParam('page');

        // retrieve the body params, parsed from XML, JSON, ...
        $params = $request->getBodyParams();

        ...

        return $list_defs;
    });

```

### Configuration

Check the inline comments in the `config.dist.php` file shipped with the distribution.

### Bootstrap

To boostrap an Apix server, add the required file and create an instance of the
`Apix\Server`.

A dedicated configuration file can be injected to an Apix server:

```php
    <?php
        require 'apix.phar';

        $api = new Apix\Server(require 'my_config.php');

        $api->run();
```

### PHAR Console

Apix PHAR distribution contains a built-in console. Try invoking the `api.phar` file on the command line as follow:

```cli
$ php apix.phar --help
```

### Web server configuration

Use one of the vhost file provided within the distribution and follow the
relevant instructions provided in the comments to set your web server environement.

TODO: Add ngynx and lighttpd files to the distrib.

### Annotations

Annotations can be used to define many aspects of a resource entity.

Here is a self explanatory example:

```php
    <?php
        $api->onRead('/download/:app/version/:version',
            /**
             * Retrieve the named sotfware
             * Anyone can use this resource entity to download apps. If no
             * version is specified the latest revision will be returned.
             *
             * @param     string    $app        The name of the app
             * @param     string    $version    The version number.
             * @return    array     A response array.
             *
             * @api_auth  groups=public
             * @api_cache ttl=1week tags=downloads
             */
            function($app, $version=null) {
                // ...
                return array(
                    $app => 'the version string of software.'
                );
            }
        );

        $api->onCreate('/upload/:software',
            /**
             * Upload a new software
             * Admin users use this resource entity to upload new software.
             *
             * @param      Request  $request   The Server Request object.
             * @param      string   $software
             * @return     array    A response array.
             *
             * @api_auth   groups=admin users=franck
             * @api_cache  purge=downloads
             */
            function(Request $request, $software) {
                // ...
            }
        );

        $api->run();
```

Installation
------------

Apix requires PHP 5.3 or later.

* [`Phar file`] [phar] (recommended)

* If you are creating a component that relies on Apix locally:

  * either update your **`composer.json`** file:

    ```json
    {
      "require": {
        "apix/apix": "0.3.*"
      }
    }
    ```

  * or update your **`package.xml`** file as follow:

```xml
    <dependencies>
      <required>
        <package>
          <name>apix_server</name>
          <channel>pear.ouarz.net</channel>
          <min>1.0.0</min>
          <max>1.999.9999</max>
        </package>
      </required>
    </dependencies>
```
* For a system-wide installation using PEAR:

```
    sudo pear channel-discover pear.ouarz.net
    sudo pear install --alldeps ouarz/apix
```
For more details see [pear.ouarz.net](http://pear.ouarz.net).

Testing
-------

To run the unit test suite simply run **`phpunit`** from within the main dir.

Integration and functional tests are also available in the `src/tests`.

License
-------
APIx is licensed under the New BSD license -- see the [LICENSE.txt][licence] for the full license details.

<pre>
  _|_|    _|_|    _|     _|      _|
_|    _| _|    _|         _|    _|
_|    _| _|    _| _|        _|_|
_|_|_|_| _|_|_|   _| _|_|   _|_|
_|    _| _|       _|      _|    _|
_|    _| _|       _|     _|      _|
</pre>

[licence]: https://github.com/frqnck/apix/blob/master/LICENSE.txt "APIx License."
[doc]: http://apix.readthedocs.org/en/latest/        "APIx Official Documentaion."
[phar]: http://api.ouarz.net/v1/download/apix.phar   "Download the Phar file."
[pear]: http://pear.ouarz.net                        "PEAR (TODO add to OUARZ)"
[composer]: https://packagist.org/packages/apix/apix "Composer (TODO add to composer)"
[github]: https://github.com/frqnck/apix             "Github"
[apixdoc]: http://frqnck.github.io/apix              "Apix's Documentation"
[rfc2616]: http://www.ietf.org/rfc/rfc2616           "Hypertext Transfer Protocol -- HTTP/1.1"
[rfc2617]: http://www.ietf.org/rfc/rfc2617           "HTTP Authentication: Basic and Digest Access Authentication"
[rfc2388]: http://www.ietf.org/rfc/rfc2388           "Returning Values from Forms:  multipart/form-data"
[rfc2854]: http://www.ietf.org/rfc/rfc2854           "The 'text/html' Media Type"
[rfc4627]: http://www.ietf.org/rfc/rfc4627           "The application/json Media Type for JavaScript Object Notation (JSON)"
[rfc4329]: http://www.ietf.org/rfc/rfc4329           "Scripting Media Types"
[rfc2046]: http://www.ietf.org/rfc/rfc2046           "Multipurpose Internet Mail Extensions"
[rfc3676]: http://www.ietf.org/rfc/rfc3676           "The Text/Plain Format and DelSp Parameters"
[rfc3023]: http://www.ietf.org/rfc/rfc3023           "XML Media Types"
