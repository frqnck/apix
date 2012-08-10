Apix, a RESTful (micro)framework.
===========================================

This is a quick and dirty getting started for Apix, the RESTful (micro)framework
to expose, at will, your web services over HTTP.

Its main goal is to serve your public and private APIs over a compliant (and
strict) RESTful interface.

Out of the box, Apix features:

* Light weight micro framework -- fully customisable. 
* Can be as RESTful as you wish, as strict or as lax as your need it to be.
* Powerful and fully customisable routing mechanisms.
* Handles most HTTP method, such as GET, POST, PUT, DELETE, HEAD, OPTIONS and PATCH (TRACE to some extend). 
* Provides method override usign X-HTTP-Method-Override (as per Google recomendation) and/or using a query params (customisable).
* Supports many data inputs, such as XML, JSON, CSV, ...
* Provides various output representation, such as XML, JSONP, HTML, PHP, ...
* Additional data formaters can be added as per your requirments. 
* Support content negotiation (which can also be ovveriden in different ways). 
* Output the resource documention on demand, using 'GET /help' or the HTTP method OPTIONS.
* HTTP cacheable -- provides HEAD test.
* Uses annotations to document and set your services and its behaviours.
* Plugggable archicture.
* Bundle with many adapters for:   
    * Authentification and ACL 
    * Logging
    * Caching, local and shared
* Based upon the relevant RFCs, such as
[rfc2616] [rfc2616],
[rfc2617] [rfc2617],
[rfc2388] [rfc2388],
[rfc2854] [rfc2854],
[rfc4627] [rfc4627],
[rfc4329] [rfc4329],
[rfc2046] [rfc2046],
[rfc3676] [rfc3676],
[rfc3023] [rfc3023].
* Follows PSR-0, PSR-1 and PSR-2.
* Fully tested (come bundles with unit-tests, integration-tests and functional-tests).
* Has its own command line interface.
* TODO: self generated API resources testing.
* TODO: add support for WSDL 2.0
* TODO: eventually SOAP (and XML_RPC) bridge.

[rfc2616]: http://www.ietf.org/rfc/rfc2616  "Hypertext Transfer Protocol -- HTTP/1.1"
[rfc2617]: http://www.ietf.org/rfc/rfc2617  "HTTP Authentication: Basic and Digest Access Authentication"
[rfc2388]: http://www.ietf.org/rfc/rfc2388  "Returning Values from Forms:  multipart/form-data"
[rfc2854]: http://www.ietf.org/rfc/rfc2854  "The 'text/html' Media Type"
[rfc4627]: http://www.ietf.org/rfc/rfc4627  "The application/json Media Type for JavaScript Object Notation (JSON)"
[rfc4329]: http://www.ietf.org/rfc/rfc4329  "Scripting Media Types"
[rfc2046]: http://www.ietf.org/rfc/rfc2046  "Multipurpose Internet Mail Extensions"
[rfc3676]: http://www.ietf.org/rfc/rfc3676  "The Text/Plain Format and DelSp Parameters"
[rfc3023]: http://www.ietf.org/rfc/rfc3023  "XML Media Types"

## Installation ##

Apix is available through different channels:

* [Phar file] [1] (recommended)
* [PEAR] [2]
* [Composer] [3]
* [Github] [4]

Apix requires PHP 5.3 or later.

## Basic Usage ##
The easiest by far is to use the Phar distribution so all the dependencies and
autoloading requirments are taken care of. The Phar method allows, among other
things, to run many concurrent version on the same server which ease development.

Here is a basic usage showing a GET handler:

```php  
   <?php  

    require 'apix.phar';  

    $api = new Apix\Server();

    $api->onRead('/hello/:name', function($name) {  
        return array('Hello ' . $name);  
    });  

    $api->run();  
```




### Routing ###

A route defines a specific resource entity, once matched the corresponding resource's controller and dedicated handlers
are invoked.

Any returned value emanating from a resource's controller, generally an associative array,
will become the main subject of the response.

Essentially, a route is made of:  

1.  A **route path** corresponding to a Request-URI.  
    * It may represent a specific and _static_ resource entity, such as:  
        <pre>/search/paris/bars</pre>  
    * It may also be _dynamic_, and include one or many variables, such as:
        <pre>/search/:city/:category</pre>  
   

2.  A **route method** corresponding to a HTTP verb and repreneted as eithe a public method from a user defined class or called atruntime using a closure. The methods available are:    
    * POST: onCreate()
    * GET: onRead()
    * PUT: onUpdate()
    * DELETE: onDelete()
    * PATCH: onModify()
    * OPTIONS: onHelp()
    * HEAD: onTest()
    * TRACE: onTrace()

## Advanced Usage ##

### Botstrap ###

To boostrap an Apix server, add the required file and create an instance of the
Apix\Server, for instance:

    ```php  
        <?php  
            require 'apix.phar';  

            $api = new Apix\Server(require 'my_config.php');

            $api->run();
    ```

In the example above, 'my_config.php' is injected into the Apix Server and
contains dedicated settings.
Note that the configuration file can also contain resource definitions mapped to
your own classes. See the Configuration chapter for detailled informations.

### Configurations ###

Check the comments in the 'config.dist.php' file.

TODO: explain the resource class defintion. For now refers to 

### Console ###

The Phar file contain a built-in console server. There are many commands/options
to use.

Try invoking the api.phar file on the command line as per the following:

```cli
$ php apix.phar --help
```

For instance, to extract the distribution data, use the --extractdist:

```cli
$ php apix.phar --extractdist
```

### Web server configuration ###

Use one of the vhost file provided within the distribution and follow the
relevant instructions provided in the comments to set your web server.

<pre>
  _|_|    _|_|    _|     _|      _|  
_|    _| _|    _|         _|    _|  
_|    _| _|    _| _|        _|_|  
_|_|_|_| _|_|_|   _| _|_|   _|_|  
_|    _| _|       _|      _|    _|  
_|    _| _|       _|     _|      _|  
</pre>


[1]: http://www.info.com/         "Dowload the Phar file."
[2]: http://www.info.com/todo     "TODO: PEAR"
[3]: http://www.info.com/todo     "TODO: Composer"
[4]: http://www.info.com/todo     "TODO: Github"
[5]: http://www.info.com/todo     "TODO"