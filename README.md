php-webdriver
=============

This library is a client for Selenium WebDriver.  It is forked from [Facebook's php-webdriver project](https://github.com/facebook/php-webdriver).  The main changes are bug fixes, PSR0-autoloading support, and publishing it as a PEAR package (and as a Composer package soon!).

Internally, it is implemented as a very thin wrapper around the [JsonWireProtocol ](http://code.google.com/p/selenium/wiki/JsonWireProtocol) that your code needs to use

System-Wide Installation
------------------------

php-webdriver should be installed using the [PEAR Installer](http://pear.php.net). This installer is the PHP community's de-facto standard for installing PHP components.

    sudo pear channel-discover datasift.github.com/pear
    sudo pear install --alldeps datasift/php-webdriver

As A Dependency On Your Component
---------------------------------

If you are creating a component that relies on php-webdriver, please make sure that you add php-webdriver to your component's package.xml file:

```xml
<dependencies>
  <required>
    <package>
      <name>php-webdriver</name>
      <channel>datasift.github.com/pear</channel>
      <min>1.0.0</min>
      <max>1.999.9999</max>
    </package>
  </required>
</dependencies>
```

Usage
-----

For now, refer to the original documentation from [Facebook's php-webdriver project](https://github.com/facebook/php-webdriver).  We hope to publish better documentation when time allows.

Development Environment
-----------------------

If you want to patch or enhance this component, you will need to create a suitable development environment. The easiest way to do that is to install [phix4componentdev](http://phix-project.org).

You can then clone the git repository:

    git clone https://github.com/datasift/php-webdriver

Then, install a local copy of this component's dependencies to complete the development environment:

    # build vendor/ folder
    phing build-vendor

To make life easier for you, common tasks (such as running unit tests, generating code review analytics, and creating the PEAR package) have been automated using [phing](http://phing.info).  You'll find the automated steps inside the build.xml file that ships with the component.

Run the command 'phing' in the component's top-level folder to see the full list of available automated tasks.

License
-------

See LICENSE.txt for full license details.
