All script is running with parameters, which is data file's path.

e.g.
php importCustomerCsvWithUpdate.php customer20120430/customer.csv

Before running, check $mageFilename in each script to make sure the Mage.php path is correct.

P.S. Base on /var/www/magento.totsy.com/ folder.

-----------------------------------------------------------------------------

customer/

- importCustomerCsvWithUpdate.php
Import customers. Create/update customers.
It will create "failed_customer.csv" for those customers which havn't been imported sucessfully.

- importCustomerAddressCsv.php
Import customers' addresses. It will try to load email with gmail trimmer function.
It will create "failed_address_data.csv" for those addresses which havn't been imported sucessfully.

- importAffiliatesRecord.php
Import affiliate record data into affiliate_record table.

- importAffiliatesTracking.php
Import customer tracking data into customertracking_record table.
It will try to load email with gmail trimmer function.

-----------------------------------------------------------------------------

customer/credits

For importing reward points and credits. Please see instruction inside.

-----------------------------------------------------------------------------

customer/invitation

For importing invitations. Please see instruction inside.

-----------------------------------------------------------------------------

product/

- importProductsCsv.php
Import products.

e.g.
php importProductsCsv.php product_data_sample.csv