(This template is based on the README.md file used by Phix and its own components. Edit / replace to suit).

ComponentName
==============

**ComponentName** is a simple-to-use PHP component that provides ...?

System-Wide Installation
------------------------

ComponentName should be installed using the [PEAR Installer](http://pear.php.net). This installer is the PHP community's de-facto standard for installing PHP components.

    sudo pear channel-discover <your pear channel>
    sudo pear install --alldeps <your channel>/ComponentName

As A Dependency On Your Component
---------------------------------

If you are creating a component that relies on ComponentName, please make sure that you add ComponentName to your component's package.xml file:

```xml
<dependencies>
  <required>
    <package>
      <name>ComponentName</name>
      <channel><!-- your PEAR channel --></channel>
      <min><!-- your component's min version, e.g. 1.0.0 --></min>
      <max><!-- your component's max version that guarantees backwards compatibility, e.g. 1.999.9999 --></max>
    </package>
  </required>
</dependencies>
```

Usage
-----

The best documentation for ComponentName are the unit tests, which are shipped in the package.  You will find them installed into your PEAR repository, which on Linux systems is normally /usr/share/php/test.

Development Environment
-----------------------

If you want to patch or enhance this component, you will need to create a suitable development environment. The easiest way to do that is to install phix4componentdev:

    # phix4componentdev
    sudo apt-get install php5-xdebug
    sudo apt-get install php5-imagick
    sudo pear channel-discover pear.phix-project.org
    sudo pear -D auto_discover=1 install -Ba phix/phix4componentdev

You can then clone the git repository:

    # ComponentName
    git clone <your Git repo's URL>

Then, install a local copy of this component's dependencies to complete the development environment:

    # build vendor/ folder
    phing build-vendor

To make life easier for you, common tasks (such as running unit tests, generating code review analytics, and creating the PEAR package) have been automated using [phing](http://phing.info).  You'll find the automated steps inside the build.xml file that ships with the component.

Run the command 'phing' in the component's top-level folder to see the full list of available automated tasks.

License
-------

See LICENSE.txt for full license details.
