Totsy Magento
=============

totsy.com
---------
Where the savvy mom shops - Totsy offers moms on-the-go and moms-to-be access to brand-specific sales, up to 90% off retail, just for them and the kids, ages 0-8.

Getting Started
---------------
Clone the Totsy-Magento repository, and then extract the Magento Enterprise (gzipped) tarball into the working copy:

    $ git clone <repository-url> totsy-magento
    $ cd totsy-magento
    $ tar xfz <path-to-magento-enterprise>/enterprise.tar.gz --strip-components=1
    $ git reset --hard HEAD

The last thing that you will need is a valid `app/etc/local.xml` to configure your instance of Totsy-Magento.

