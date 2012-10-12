<?php
/**
 * PHP Version 5.3
 *
 * @category  Totsy
 * @package   Totsy_Sailthru
 * @author    Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright 2012 Totsy LLC Copyright (c) 
 */
class Totsy_Sailthru_Helper_Cache
{

    // set cache time for 3 days 
    // 3*24*60*60
    const TTL = 259200; 

    private $_full_path = null;
    private $_originHttpHost = 'totsy.com';
    private $_isHttpHostChanged = false;
    private $_cache_dir_path = '/var/www/current/var/tmp/';
    private $_cache_file = null;
    private $_cache_file_ext = '.json';
    private $_cache_skip_check = false;
    private $_cache_store = null;
    private $_cache_disabled = false;

    public function disableCache(){
        if ($this->_cache_disabled == false){
            $this->_cache_disabled = true;
        }
    }

    public function enableCache(){
        if ($this->_cache_disabled == true){
            $this->_cache_disabled = false;
        }
    }

    public function isCacheDisabled(){
        return $this->_cache_disabled;
    }

    /**
    * This method does all diry caching job.
    * On succes returs json encoded string or booelan false!
    *
    * @param string $full_path full path to caching directory
    *
    * @return   string/boolean  
    */
    public function runner($full_path = null)
    {
        
        if (!empty($full_path)) {
            $this->_full_path = $full_path;
        }

        if (empty($this->_full_path)) {
            throw new Exception('Unknown cache location.');
        }
        
        if (!empty($_SERVER['HTTP_HOST'])) {
            $this->_originHttpHost = $_SERVER['HTTP_HOST'];
        }

        // handle cli params first
        $this->_doParams();
        // 
        if($this->isCacheDisabled()){
            return false;
        }

        // make sure all cache related params are taking its place
        //$this->_handleGetParams();
        // how we have domain and get params, so generate a cache filename
        $this->_generateCacheFileName();
        // touch cache file
        $this->_touchNewCacheFile();
        // decide wheather to use cached data or not
        $cached = $this->cacheDecision();
        // rewrite doamin name, if needed
        $this->_setRightHttpHost($cached);

        return $cached;
    }

    /**
    * Return store code. This walue chages for mamasource otherwise is NULL
    *
    * @return string
    */
    public function getStoreCode() 
    {
        return $this->_cache_store;
    }

    /**
    * Save processed data into cache file
    *
    * @param string &$data json encoded sting
    *
    * @return void
    */
    public function rememberCache(&$data)
    {
        
        // rewrite doamin name, if needed
        $this->_setRightHttpHost($data);
        
        // write all data into cache file
        $fh = fopen($this->_getFullCacheFileName(), 'w');
        fwrite($fh, $data);
        fclose($fh);

        // remove unused cache file
        if (file_exists($this->_getFullCacheFileName().'.new')) {
            unlink($this->_getFullCacheFileName().'.new');
        }

    }

    /**
    * Makes a decision weather get data from cached file or not
    *
    * @return string/boolean 
    */
    public function cacheDecision()
    {

        if (file_exists($this->_getFullCacheFileName()) 
            && $this->_cache_skip_check===false
        ) {

            $time = time() - filectime($this->_getFullCacheFileName());
            
            if ($time<self::$TTL 
                || file_exists($this->_getFullCacheFileName().'.new')
            ) {
                return file_get_contents($this->_getFullCacheFileName());
            }

        }

        return false;
    }

    /**
    * make sure to get all necessary data even from get params
    *
    * @return void
    */
    private function _handleGetParams()
    {
    
        if (!empty($_GET['domain'])) {
            switch ($_GET['domain']){
                case 'mamasource.totsy.com':
                    $this->_setHttHost('mamasource.totsy.com');
                    $this->_cache_store = 'mamasource';
                break;

                case 'totsy.com':
                case 'www.totsy.com':    
                default:
                    $this->_setHttHost('www.totsy.com');        
                break;
            }
        }
    }

    /**
    * Prepare new cache file for current request
    *
    * @return void
    */
    private function _touchNewCacheFile()
    {
    
        if ($this->_cache_skip_check===true) {
            file_put_contents(
                $this->_getFullCacheFileName().'.new',
                'addin some cache'
            );
        }

    }

    /**
    * Generate a new cache file name
    *
    * @return void
    */
    private function _generateCacheFileName()
    {
        $this->_cache_file = md5(
            $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']
        ).$this->_cache_file_ext;
    }

    /**
    * Dispatch cli params
    *
    * @return void
    */
    private function _doParams()
    {
        if (php_sapi_name()=='cli') {
            $this->_cache_skip_check = true;

            $index=array_search('--get', $argv);
            if ( $index!==false && $argc>$index+1) {
                 $_SERVER['REQUEST_URI'] = $argv[$index+1];
            }

            $index=array_search('--domain', $argv);
            if ( $index!==false && $argc>$index+1) {
                $this->_setHttHost($argv[$index+1]);
            }
        } else if (!empty($_GET['domain'])){
            $this->_setHttHost($_GET['domain']);

        }
    }

    /**
    * Repalces SERVER HTTP_HOST for current session only
    *
    * @param string $httHost new HTTP_HOST value
    *
    * @return void
    */
    private function _setHttHost($httHost) 
    {
        $_SERVER['HTTP_HOST'] = $httHost;
        $this->_isHttpHostChanged = true;
    }

    /**
    * Build full path to cache file
    *
    * @return string 
    */
    private function _getFullCacheFileName() 
    {
        return  $file = $this->_full_path.
                $this->_cache_dir_path.
                $this->_cache_file;
    }

    /**
    * In Case we need to override current HTTP_HOST (domain) value,
    * we doing it here. 
    *
    * @param string &$json referenced string to json encoded string  
    *
    * @return void
    */
    public function _setRightHttpHost(&$json)
    {

        if ( $this->_isHttpHostChanged==true ) {
            $json = str_replace(
                $this->_originHttpHost, 
                $_SERVER['HTTP_HOST'], 
                $json
            );
        }

    }
}

?>