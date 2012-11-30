Totsy Magento
=============

totsy.com
---------
Where the savvy mom shops - Totsy offers moms on-the-go and moms-to-be access to brand-specific sales, up to 90% off retail, just for them and the kids, ages 0-8.

Getting Started
---------------
This code base is meant to be copied on top of a vanilla Magento installation. After cloning the repository, the stock Magento Enterprise archive must be extracted on top (without overwriting existing files).

Clone the Totsy-Magento repository, and then extract the Magento Enterprise (gzipped) tarball into the working copy:

    $ cd /var/www/
    $ git clone git@github.com:Totsy/Totsy-Magento.git <your directory>
    $ cd <your directory>

Unpack the magento core enterprise files:

    $ tar xkfj /usr/share/magento/magento-enterprise-1.11.1.tar.bz2 --strip-components=1
    $ cd app/etc/
    $ rm enterprise.xml

Symlink the config files:

    $ ln -s /etc/magento/enterprise.xml enterprise.xml
    $ ln -s /etc/magento/local.xml local.xml
    $ cd <your directory>
    $ ln -s /srv/cache/media/ media

Now visit http://yourname.totsy.com and you should see a working app. Look, you did it!

Unit Testing
------------
A suite of [PHPUnit](http://www.phpunit.de) unit tests is included, and use the [EcomDev_PHPUnit](https://github.com/IvanChepurnyi/EcomDev_PHPUnit) Magento module for accomplishing unit testing goals within the Magento framework.

To run the unit tests, ensure you have configured `app/etc/local.xml` and `app/etc/local.xml.phpunit` (which should be configured to connect to an empty test database) and then run:

    $ phpunit UnitTests.php

This will take a few minutes the first time you run the test suite, in order to build the test database (configured in the `app/etc/local.xml.phpunit` file).
