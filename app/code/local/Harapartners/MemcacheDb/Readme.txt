The core idea of the 'MemcacheDb'

Override Mage_Core_Model_Session_Abstract_Varien to allow memcache_db mode

Note: read-from/write-to are both considered sync
i.e. after reading from DB, it will not write after session close (even if there is nothing in the DB)


MemcacheDb data structure
...

//close executed upon object __destruct
//Garbage collection in memcache is handled by expiration

Garbage collection

7 days, inactive sessions in memcache can be overwriten, for garbage collection
Expired sessions may persist in memcache and may exist in the DB

session_start() => session open and read
seesion_write_close() (in the destructor of the session object) => session write and close (GC here)
rarely session destroy

Allow special query caching
key=top_layer_nav_boys, with all cached results
A script to build the true search result and cache it (array results)