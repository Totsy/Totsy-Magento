Fast full catalog import / export including these types of data:

  * Products
  * Categories
  * Related products
  * Up-sell products
  * Cross-sell products
  * Grouped products
  * Custom options
  * Configurable products
  * Bundle options
  * Images
  * Downloads
  * Product attributes
  * Product attribute sets

Features:

  * Create, Update, Delete records
  * Rename records (product SKU, option default title, etc.)
  * Ignore empty or commented out rows
  * No internal entity IDs exported/needed
  * Recognize and skip records that do not require update
  * [Leeloo Dallas] Multi-pass strategy to allow free row ordering for dependent records
  * Import/export from/to remote location using FTP, SFTP, HTTP, etc.
  * Stable small footprint memory usage
  * Save set of import/export profiles and run with one click
  * On-demand and scheduled profiles
  * When importing products, automatically create categories and attribute options when needed (optional)

Roadmap:

  * Access import/export functions using Magento API (SOAP, XMLRPC)
  * Upload/download all your data in one archive, we'll take care of the rest
  * Import/export category attributes
  * Import/export files (images, downloads)
  * Other types of data in Magento
  * When importing products, automatically create dropdown attribute values when needed (optional)

Usage ideas:

  * Quick data entry
  * Make installation independent catalog backups
  * Mass update your catalog in Excel (export, edit, import)
  * Migrate to clean new Magento version (export from old, import to new)
  * Synchronize data between Magento installations
  * Recover whatever is still possible from broken installation
  * Delete or rename products or catalog extra data based on a criteria
  * Integrate with other applications using CSV, SOAP or XMLRPC interface

Preliminary performance tests on our dev hosting with real data:

  * Export ~80,000 products with ~1,500 inline categories: 122 seconds
  * Import ~80,000 products...
  * Export ~1,500 categories...
  * Import ~1,500 categories...
  * Export 97854 rows of extra product data: ~5.53 seconds
  * Import 97854 rows of extra product data (no changes found): ~94 seconds

Product Import features:
  * Columns
    * Assign header aliases
    * Custom multiselect values separator per column

  * Validation
    * Duplicate attributes in columns
    * Missing/Duplicate SKU
    * Missing product type and attribute set for new products or invalid for existing products
    * Missing required attribute values for new products or empty for existing products
    * Duplicate unique attribute values
    * Invalid attribute value type (int, decimal, datetime)

  * Values
    * Inserting/Updating/Deleting attributes only when needed (not using sql replace for performance)
    * Correct save of multiselect values depending on backend type (int, varchar)

  * Websites/Stores
    * If in single store mode, save only to defaults
    * Correct save of Global and Website scope attribute values

  * Maintenance
    * Reindex only updated products

  * Reporting
    * Export errors and warnings result as original spreadsheet with cells color coded

Import TODO:
  * Validation
    * Warning on attribute being not in product's attribute set