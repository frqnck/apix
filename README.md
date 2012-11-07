Apix, a RESTful (micro)framework
================================

This is a draft intended as a quick and dirty getting started guide.

Apix main goal is to serve your public and private APIs over a compliant (and
strict) RESTful interface.

Out of the box, Apix features:

* Light weight micro framework -- fully customisable.
* Can be as RESTful as you wish, as strict or as lax as your need it to be.
* Powerful and fully customisable routing mechanisms.
* Handles most HTTP methods, including PUT, DELETE, HEAD, OPTIONS and PATCH (TRACE to some extend).
* Provides method override usign X-HTTP-Method-Override (Google recommendation) and/or using a query params (customisable).
* Supports many data inputs, such as XML, JSON, CSV, ...
* Provides various output representation, such as XML, JSONP, HTML, PHP, ...
* Support content negotiation (which can also be overriden in different ways).
* Provides resource(s) documention on demand, using 'GET /help' or the HTTP method OPTIONS.
* HTTP cacheable -- supports HEAD test.
* Uses annotations to document and set your services and its behaviours.
* Pluggeable archicture.
* Bundle with many plugins/adapters for Authentification and ACL, logging, caching...
* Command line interface for maintenance, testing...
* Comes bundle with unit-tests, integration-tests and functional-tests.
* Based upon the relevant RFCs, such as [rfc2616] [rfc2616], [rfc2617] [rfc2617],
[rfc2388] [rfc2388], [rfc2854] [rfc2854], [rfc4627] [rfc4627], [rfc4329] [rfc4329],
[rfc2046] [rfc2046], [rfc3676] [rfc3676], [rfc3023] [rfc3023].
* TODO: Follows PSR-0, PSR-1 and PSR-2.
* TODO: self generated API resources testing.
* TODO: add support for WSDL 2.0
* TODO: eventually SOAP (and XML_RPC) bridge.

## Installation ##

Apix is available through different channels:

* [`Phar file`] [phar] (recommended)
* [`PEAR`] [pear]
* [`Composer`] [composer]
* [`Github`] [github]

Apix requires PHP 5.3 or later.

## Basic Usage ##
The easiest by far is to use the [`Phar`] [phar] distribution so all the dependencies and
autoloading requirments are taken care of.

Here is a basic usage:

    ```php
        <?php
        require 'apix.phar';

        $api = new Apix\Server;

        $api->onRead('/hello/:name', function($name) {
            return array('Hello ' . $name);
        });

        $api->run();
    ```

### Routing ###

A route defines the path to a resource, once matched the corresponding resource's controller and dedicated handlers are invoked.

Any returned value emanating from a resource's controller, generally an associative array, will become the main subject of the response.

Essentially, a route is made of:

1.  A **route controller** that corresponds to a HTTP header method as per the table below:

       <pre>
onCreate()   =>   POST          |        onModify()   =>   PATCH
onRead()     =>   GET           |        onHelp()     =>   OPTIONS
onUpdate()   =>   PUT           |        onTest()     =>   HEAD
onDelete()   =>   DELETE        |        onTrace()    =>   TRACE
</pre>

2.  A **route path** corresponding to a Request-URI.
    * It may represent a specific and _static_ resource entity, such as:
        <pre>/search/france/paris</pre>
    * It may also be _dynamic_, and may include one or many variables indicated by a colon `:`, such as:
        <pre>/search/:country/:city</pre>

### Controller definitions ###
A resource controller may be declared as either:

* a public method from some user defined classes,
* a closure/lambda function, defined at runtime.

It will use:

*   variable name to inherit values from the route's path,
    e.g. `$name` inherited from `/category/:name`.

*   type hinting to inject any of the current scope Apix's objects,
    e.g. `Request`, `Response`, etc...

    See Apix's own [API Documentation] [apidoc] for what's available.

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

## Advanced usage ##

### Bootstrap ###

To boostrap an Apix server, add the required file and create an instance of the
`Apix\Server`.

A dedicated configuration file can be injected to an Apix server:

    ```php
        <?php
            require 'apix.phar';

            $api = new Apix\Server(require 'my_config.php');

            $api->run();
    ```

### Configuration ###

Check the inline comments in the `config.dist.php` file shipped with the distribution.

### Console ###

Apix contains a built-in console. Try invoking the `api.phar` file on the command line as follow:

```cli
$ php apix.phar --help
```

### Web server configuration ###

Use one of the vhost file provided within the distribution and follow the
relevant instructions provided in the comments to set your web server environement.

### Annotations ###

Annotations are use to define many aspects of your resource entity.

Here is a self explanatory example:

    ```php
        <?php
            require_once 'apix.phar';

            $api = new Apix\Server;

            /**
             * Title: Software version
             * Description: Returns the lastest version of the :software
             *
             * @param       string          $software
             * @return      array           A response array.
             * @api_role    public          Available to all!
             * @api_cache   10w some_name   Cache for a maximum of 10 weeks
             *                              and tag cache buffer as 'some_name'.
             */
            $api->onRead('/version/:software', function($software) {
                // ...
                return array(
                    $software => 'the version string of software.'
                );
            });

            /**
             * Sotware uploader
             * Uses to upload a new software :software
             *
             * @param               Request  $request   The Server Request object.
             * @param               string   $software
             * @return              array               A response array.
             * @api_role            admin               Require admin priviledge
             * @api_purge_cache     some_name           Purge the cache of all the
             *                                          'some_name' tagged entries.
             */
            $api->onCreate('/upload/:software', function(Request $request, $software) {
                // ...
            });


            $api->run();
    ```

## Testing ##
PHP5.3: status
PHP5.4: status

The idea is to get 100% code-coverage -- nearly there.

### Unit test ###
To run unit test simply run # phpunit from the within the main dir.

### Integration test ###
TODO
### Functional test ###
TODO

<pre>
  _|_|    _|_|    _|     _|      _|
_|    _| _|    _|         _|    _|
_|    _| _|    _| _|        _|_|
_|_|_|_| _|_|_|   _| _|_|   _|_|
_|    _| _|       _|      _|    _|
_|    _| _|       _|     _|      _|
</pre>

[phar]: http://www.info.com/todo            "Dowload the Phar file."
[pear]: http://www.info.com/todo            "TODO: PEAR"
[composer]: http://www.info.com/todo        "TODO: Composer"
[github]: http://www.info.com/todo          "TODO: Github"
[apidoc]: http://www.info.com/todo          "Apix's API Documentation"
[rfc2616]: http://www.ietf.org/rfc/rfc2616  "Hypertext Transfer Protocol -- HTTP/1.1"
[rfc2617]: http://www.ietf.org/rfc/rfc2617  "HTTP Authentication: Basic and Digest Access Authentication"
[rfc2388]: http://www.ietf.org/rfc/rfc2388  "Returning Values from Forms:  multipart/form-data"
[rfc2854]: http://www.ietf.org/rfc/rfc2854  "The 'text/html' Media Type"
[rfc4627]: http://www.ietf.org/rfc/rfc4627  "The application/json Media Type for JavaScript Object Notation (JSON)"
[rfc4329]: http://www.ietf.org/rfc/rfc4329  "Scripting Media Types"
[rfc2046]: http://www.ietf.org/rfc/rfc2046  "Multipurpose Internet Mail Extensions"
[rfc3676]: http://www.ietf.org/rfc/rfc3676  "The Text/Plain Format and DelSp Parameters"
[rfc3023]: http://www.ietf.org/rfc/rfc3023  "XML Media Types"