<?php

class Unirgy_SimpleUp_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_ftpPassword;
    protected $_ftpDirMode = 0775;
    protected $_ftpFileMode = 0664;

    public function __construct()
    {
        Mage::getConfig()->loadModulesConfiguration('usimpleup.xml', Mage::getConfig());
        $this->_ftpPassword = Mage::app()->getRequest()->getPost('ftp_password');
    }

    public function download($uri)
    {
        $dlDir = Mage::getConfig()->getVarDir('usimpleup/download');
        Mage::getConfig()->createDirIfNotExists($dlDir);

        $filePath = $dlDir.'/'.basename($uri);
        $fd = fopen($filePath, 'wb');

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL =>  $uri,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FILE => $fd,
        ));
        if (curl_exec($ch)===false) {
            $error = $this->__('Error while downloading file: %s', curl_error($ch));
            curl_close($ch);
            fclose($fd);
            Mage::throwException($error);
        }
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE)!=200) {
            $error = $this->__('File not found or error while downloading: %s', $uri);
            curl_close($ch);
            fclose($fd);
            Mage::throwException($error);
        }
        curl_close($ch);
        fclose($fd);

        return $filePath;
    }

    public function install($uri, $filePath)
    {
        $tempDir = Mage::getConfig()->getVarDir('usimpleup/unpacked').'/'.basename($filePath);
        Mage::getConfig()->createDirIfNotExists($tempDir);

        $this->unarchive($filePath, $tempDir);
        $this->registerModulesFromDir($uri, $tempDir);

        $useFtp = Mage::getStoreConfig('usimpleup/ftp/active');
        if ($useFtp) {
            $errors = $this->ftpUpload($tempDir);
            if ($errors) {
                $logDir = Mage::getConfig()->getVarDir('usimpleup/log').'/'.basename($filePath);
                Mage::getConfig()->createDirIfNotExists($logDir);

                $fd = fopen($logDir.'/errors.log', 'a+');
                foreach ($errors as $error) {
                    fwrite($fd, date('Y-m-d H:i:s').' '.$error."\n");
                }
                fclose($fd);
                Mage::throwException($this->__('Errors during FTP upload, see this log file: %s', 'usimpleup/log/'.basename($filePath).'/errors.log'));
            }
        } else {
            $this->unarchive($filePath, Mage::getBaseDir());
        }

        return $this;
    }

    public function unarchive($filePath, $target)
    {
        switch (strtolower(pathinfo($filePath, PATHINFO_EXTENSION))) {
            case 'zip':
                $this->unzip($filePath, $target);
                break;
            default:
                Mage::throwException($this->__('Unknown archive format'));
        }
    }

    public function unzip($filePath, $target)
    {
        if (!extension_loaded('zip')) {
            Mage::throwException($this->__('Zip PHP extension is not installed'));
        }
        $zip = new ZipArchive();
        if (!$zip->open($filePath)) {
            Mage::throwException($this->__('Invalid or corrupted zip file'));
        }
        if (!$zip->extractTo($target)) {
            $zip->close();
            Mage::throwException($this->__('Errors during unpacking zip file. Please check destination write permissions: %s', $target));
        }
        $zip->close();
    }

    public function ftpUpload($from)
    {
        if (!extension_loaded('ftp')) {
            Mage::throwException($this->__('FTP PHP extension is not installed'));
        }
        $conf = Mage::getStoreConfig('usimpleup/ftp');
        if (!($conn = ftp_connect($conf['host'], $conf['port']))) {
            Mage::throwException($this->__('Could not connect to FTP host'));
        }
        $password = $this->_ftpPassword ? $this->_ftpPassword : Mage::helper('core')->decrypt($conf['password']);
        if (!@ftp_login($conn, $conf['user'], $password)) {
            ftp_close($conn);
            Mage::throwException($this->__('Could not login to FTP host'));
        }
        if (!@ftp_chdir($conn, $conf['path'])) {
            ftp_close($conn);
            Mage::throwException($this->__('Could not navigate to FTP Magento base path'));
        }

        $errors = $this->ftpUploadDir($conn, $from.'/');

        ftp_close($conn);

        return $errors;
    }

    public function ftpUploadDir($conn, $source, $ftpPath='')
    {
        $errors = array();
        $dir = opendir($source);
        while ($file = readdir($dir)) {
            if ($file=='.' || $file=="..") {
                continue;
            }
            if (!is_dir($source.$file)) {
                if (@ftp_put($conn, $file, $source.$file, FTP_BINARY)) {
                    // all is good
                    #ftp_chmod($conn, $this->_ftpFileMode, $file);
                } else {
                    $errors[] = ftp_pwd($conn).'/'.$file;
                }
                continue;
            }
            if (@ftp_chdir($conn, $file)) {
                // all is good
            } elseif (@ftp_mkdir($conn, $file)) {
                ftp_chmod($conn, $this->_ftpDirMode, $file);
                ftp_chdir($conn, $file);
            } else {
                $errors[] = ftp_pwd($conn).'/'.$file.'/';
                continue;
            }
            $errors += $this->ftpUploadDir($conn, $source.$file.'/', $ftpPath.$file.'/');
            ftp_chdir($conn, '..');
        }
        return $errors;
    }

    public function registerModulesFromDir($uri, $dir)
    {
        $configFiles = glob($dir.'/app/code/*/*/*/etc/config.xml');
        if (!$configFiles) {
            Mage::throwException('Could not find module configuration files');
        }
        foreach ($configFiles as $file) {
            $config = new Varien_Simplexml_Config($file);
            if (!$config->getNode('modules')) {
                continue;
            }
            foreach ($config->getNode('modules')->children() as $modName=>$modConf) {
                if (!isset($modConf->usimpleup) || !isset($modConf->usimpleup['remote'])) {
                    continue;
                }
                $module = Mage::getModel('usimpleup/module')->load($modName, 'module_name')
                    ->setModuleName($modName)
                    ->setDownloadUri($uri)
                    ->setLastDownloaded(now())
                    ->setLastVersion((string)$modConf->version)
                    ->save();
            }
        }
    }

    public function checkUpdates()
    {
        set_time_limit(0);
        $dbModules = Mage::getModel('usimpleup/module')->getCollection();
        $uriMods = array();
        foreach ($dbModules as $mod) {
            $modName = $mod->getModuleName();
            if (!$modName) {
                continue;
            }
            $usimpleup = Mage::getConfig()->getNode("modules/{$modName}/usimpleup");
            if (!$usimpleup || !isset($usimpleup['remote'])) {
                continue;
            }
            $uriMods[(string)$usimpleup['remote'].$mod->getLicenseKey()][$modName] = $mod;
        }

        foreach ($uriMods as $uri=>$mods) {
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL =>  $uri,
                CURLOPT_RETURNTRANSFER => true,
            ));
            $response = curl_exec($ch);
            curl_close($ch);
            if ($response===false) {
                Mage::throwException($this->__('Error while downloading file: %s', curl_error($ch)));
            }
            /*
            $response = @file_get_contents($uri);
            if (!$response) {
                Mage::throwException($this->__('Invalid meta uri resource: %s', $uri));
            }
            */
            //$xml = new Varien_Simplexml_Element($response);
            try {
                $result = Zend_Json::decode($response);
            } catch (Exception $e) {
                if ($e->getMessage()=='Decoding failed: Syntax error') {
                    $result = array();
                } else {
                    throw $e;
                }
            }
            foreach ((array)$result as $modName=>$node) {
                if (!$modName || empty($mods[$modName]) || !isset($node['version']['latest'])) {
                    continue;
                }
                $mods[$modName]->setLastChecked(now())->setRemoteVersion((string)$node['version']['latest'])->save();
            }
        }
    }

    public function cleanCache()
    {
        Mage::app()->cleanCache();
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
            apc_clear_cache('user');
        }
        return $this;
    }

    public function installModules($uris)
    {
        set_time_limit(0);
        foreach ($uris as $uri) {
            if (empty($uri)) {
                continue;
            }
            $filePath = $this->download($uri);
            $this->install($uri, $filePath);
        }
        $this->cleanCache();
    }

    public function upgradeModules($modules)
    {
        set_time_limit(0);
        $modules = Mage::getModel('usimpleup/module')->getCollection()
            ->addFieldToFilter('module_id', array('in'=>$modules));
        foreach ($modules as $mod) {
            $uri = $mod->getDownloadUri();
            $filePath = $this->download($uri);
            $this->install($uri, $filePath);
        }
        $this->cleanCache();
    }
}