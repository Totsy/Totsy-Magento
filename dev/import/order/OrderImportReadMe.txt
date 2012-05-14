# ========== File structure ========== #
The import script, orderImportScript.php,  should be put in:
[MAGENTO_ROOT]/dev/orderimport
This is important for the import script to find Mage.php (require_once '../../app/Mage.php';)

The import input files: order.csv, order_address.csv and order_item.csv should be in the same folder as orderImportScript.php
/log/ folder is welcome but not necessary

# ========== Excecution ========== #
Order import is resource hungry, to avoid unexpected memory leak, the scipt limits each import to be 1000 orders max
You can still put more than 1000 orders in the import script, and specify the starting order as an argument

php orderImportScript.php 0 > log/order_import_0_45_0.log 2>&1
php orderImportScript.php 1000 > log/order_import_0_45_1000.log 2>&1
...

The first import is for order #1 to #1000
The second import is for order #1001 to #2000

Feel free to skip to log or run it in the background. (Note multiple processes running in parallel will not improve the global import script by much)

# ========== Important notes ========== #
There is duplicate detection, so imported orders will not be overwritten. (So if some execution failed, you can start a re-run right away. No need for clean up).
"function _trimGmail($email)" is used to reduce duplicate gmail accounts. This logic is shared with customer import.
There is an "AllErrors.txt" for your entertainment. (Billing and shipping address complement each other is some field is missing. A place holder is used for orders missing the required phone number)