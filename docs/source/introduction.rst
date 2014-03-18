Introduction
============

APIx is a (micro-)framework to build RESTful Web services. It will run alognside your existing framework/application with minimum fuss.

Some of its features:

* Supports **many data inputs** such as GET/POST parameters, XML, JSON, CSV, ...
* Provides **various output representation** such as XML, JSONP, HTML, PHP, ...
* Provides **on-demand resources documention**, using GET /help or 'OPTIONS'.
* Uses **annotations to document** and **set service behaviours**.
* Handles **most HTTP methods**, including PUT, DELETE, HEAD, OPTIONS and PATCH (TRACE to some extent).
* Bundled with **many plugins and adapters** including:

  * Basic HTTP Authentication
  * Digest HTTP Authentication
  * Caching through Redis, APC, Memcached, PDO, etc
  * Extensible Plugin architecture

* **Follows the standards** such as:
  
  * :rfc:`2616` - Hypertext Transfer Protocol -- HTTP/1.1
  * :rfc:`2617` - HTTP Authentication: Basic and Digest Access Authentication
  * :rfc:`2388` - Returning Values from Forms (multipart/form-data)
  * :rfc:`2854` - The 'text/html' Media Type
  * :rfc:`4627` - The application/json Media Type for JavaScript Object Notation (JSON)
  * :rfc:`4329` - Scripting Media Types
  * :rfc:`2046` - Multipurpose Internet Mail Extensions
  * :rfc:`3676` - The Text/Plain Format and DelSp Parameters
  * :rfc:`3023` - XML Media Types
  * etc...
  
* Provides **method-override** usign X-HTTP-Method-Override (Google recommendation) and/or using a query-param (customisable).
* Supports **content negotiation** (which can also be overriden).
* Take advantages of network caches -- supports HEAD test.
* Available as a standalone phar__ file, composer__, pear__ package, or via github__.

.. __: http://api.ouarz.net/v1/download/apix.phar
.. __: http://https://packagist.org/packages/apix/apix
.. __: http://pear.ouarz.net
.. __: https://github.com/frqnck/apix

Installation
------------

There are several options for installing APIx.  We recommend the **phar** method
for optimal speed.  However, APIx is also available via **composer** for easy
integration into your project.

Apix requires PHP 5.3 or later.

PHAR
~~~~

Download `apix.phar <http://api.ouarz.net/v1/download/apix.phar>`_ and include it
in your project like this:

.. code-block:: php

    include '/path/to/apix.phar';
    $apix = new Apix\Server;

The apix.phar file also contains a CLI interface that can be used to self-update.

.. code-block:: bash

    $ php apix.phar --selfupdate

Composer
~~~~~~~~

Integrate APIx into your existing Composer project by adding the following to your
composer.json file:

.. code-block:: javascript

    {
      "require": {
        "apix/apix": "0.3.*"
      }
    }

.. code-block:: php

    include "vendor/autoload.php";
    $apix = new Apix\Server;
