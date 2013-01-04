Totsy Magento
=============

totsy.com
---------
Where the savvy mom shops - Totsy offers moms on-the-go and moms-to-be access to brand-specific sales, up to 90% off retail, just for them and the kids, ages 0-8.

Getting Started
---------------
This code base is meant to be merged with the base Magento Enterprise installation. After cloning the repository, the stock Magento Enterprise archive must be extracted on top (without overwriting existing files).

From a directory in which you have permissions to write (i.e. your home directory), clone the Totsy-Magento repository

    $ git clone git@github.com:Totsy/Totsy-Magento.git <yourname>.totsy.com/current
    $ cd <yourname>.totsy.com/current

Unpack the magento core enterprise files

    $ tar xf /usr/share/magento/magento-enterprise-1.11.1.tar.bz2 --strip-components=1 --skip-old-files

Setup the application configuration, and the shared `media` directory

    $ ln -sf /etc/magento/enterprise.xml app/etc/enterprise.xml
    $ ln -sf /etc/magento/local.xml app/etc/local.xml
    $ ln -sf /srv/share/media/ media

Return to the root where you created the working copy, and move it to the deployment directory

    $ cd ../..
    $ sudo mv <yourname>.totsy.com /var/www/

Unit Testing
------------
A suite of [PHPUnit](http://www.phpunit.de) unit tests is included, and use the [EcomDev_PHPUnit](https://github.com/IvanChepurnyi/EcomDev_PHPUnit) Magento module for accomplishing unit testing goals within the Magento framework.

To run the unit tests, ensure you have configured `app/etc/local.xml` and `app/etc/local.xml.phpunit` (which should be configured to connect to an empty test database) and then run:

    $ phpunit UnitTests.php

This will take a few minutes the first time you run the test suite, in order to build the test database (configured in the `app/etc/local.xml.phpunit` file).
