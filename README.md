Apix, a RESTful (micro)framework.
===========================================

This is a quick and dirty getting started for Apix, the RESTful (micro)framework
to expose, at will, your web services.

Its main goal is to serve your public and private APIs over a compliant (and
Strict) RESTful interface.

Out of the box, Apix:

* Supports many data inputs (such as XML, JSON, CSV, ...).
* Provides various output representation (such as XML, JSONP, HTML, PHP, ...).
* Handles Authentification, ACL, Logging.
* Has local and shared caching features for your served resources.
* Uses annotations to document and set your services.
* ...

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
things, to run many concurrent and independent version of your API on the same
server.

Here is a basic usage to handle a HTTP GET Request:

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

A route defines a resource, once matched the corresponding resource's controller
is invoked.

Any returned value from a resource's controller, generally an associative array,
will become the main subject of the response.

Essentially, a route is made of:  

1.  A route pattern:
    Dynamic routing

2.  A route method:
    TODO List of supported HTTP methods 

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
your own classes.
See the Configuration chapter for detailled informations.

### Configurations ###

TODO: For now, check the comments in the 'config.dist.php' file.

TODO: explain the resource class defintion. For now refers to 

### Console ###

The Phar file contain a built-in console server. There are many commands/options
to use (and more to come).

Try invoking the api.phar file on the command line as per the followng:

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