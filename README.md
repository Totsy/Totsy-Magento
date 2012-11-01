Totsy Magento
=============

totsy.com
---------
Where the savvy mom shops - Totsy offers moms on-the-go and moms-to-be access to brand-specific sales, up to 90% off retail, just for them and the kids, ages 0-8.

Getting Started
---------------
This code base is meant to be copied on top of a vanilla Magento installation. After cloning the repository, the stock Magento Enterprise archive must be extracted on top (without overwriting existing files).

Clone the Totsy-Magento repository, and then extract the Magento Enterprise (gzipped) tarball into the working copy:

    $ git clone <repository-url> totsy.com
    $ cd totsy.com
    $ tar xkfz <path-to-magento-enterprise>/enterprise.tar.gz --strip-components=1

The last thing that you will need is a valid `app/etc/local.xml` to configure your instance of Totsy-Magento.

Unit Testing
------------
A suite of [PHPUnit](http://www.phpunit.de) unit tests is included, and use the [EcomDev_PHPUnit](https://github.com/IvanChepurnyi/EcomDev_PHPUnit) Magento module for accomplishing unit testing goals within the Magento framework.

To run the unit tests, ensure you have configured `app/etc/local.xml` and `app/etc/local.xml.phpunit` (which should be configured to connect to an empty test database) and then run:

    $ phpunit UnitTests.php

This will take a few minutes the first time you run the test suite, in order to build the test database (configured in the `app/etc/local.xml.phpunit` file).
