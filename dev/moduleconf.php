<?php
/**
 * @category    Totsy
 * @package     dev
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

function usage() {
    echo "Usage:\nmoduleconf <modulename> <classtype> <classname>\n\n";
    echo "<modulename> is the name of the new module. Supplying this argument "
        . "alone will simply create an empty module, and register it in the "
        . "module directory (in `app/etc/modules/Totsy_All.xml`).", PHP_EOL;
    echo "<classtype> can be either `controller`, `block`, `helper`, or `model` "
        . "(case insensitive).", PHP_EOL;
    echo "<classname> is the name of the new class.", PHP_EOL;
    exit(1);
}

// determine the Magento installation root
$mageRoot = realpath(__DIR__ . '/../');

// determine an author tag by inspecting Git user information
$username = exec('git config user.name');
$useremail = exec('git config user.email');
$author = "$username <$useremail>";

// initialize a module only, when no other arguments are provided
if ($argc == 2) {
    init($argv[1]);
    exit(0);
}

if ($argc != 4) {
    usage();
}

// route this request based on the class type
switch (strtolower($argv[2])) {
    case 'controller':
        controller($argv[1], $argv[3]);
        break;
    case 'block':
    case 'helper':
    case 'model':
        standard($argv[1], $argv[2], $argv[3]);
        break;
    default:
        usage();
}

/**
 * Initialize a Magento module by creating a directory in the Totsy namespace
 * for the new module, creating a module configuration file, and finally
 * updating the global module directory configuration.
 *
 * @param string $moduleName The name of the module.
 * @return bool
 */
function init($moduleName) {
    global $mageRoot;

    // create the directory for this module in the local code pool
    $modulePath = $mageRoot . "/app/code/local/Totsy/$moduleName";
    if (!is_dir($modulePath)) {
        mkdir($modulePath);
        echo "+ $modulePath", PHP_EOL;
        mkdir($modulePath . '/etc');
        echo "+ {$modulePath}/etc", PHP_EOL;
    } else {
        return true;
    }

    // update the module configuration to add this module
    $xmlAppConf = simplexml_load_file($mageRoot . '/app/etc/modules/Totsy_All.xml');
    $node = $xmlAppConf->xpath('//modules');
    $moduleNode = $node[0];
    $moduleNode = $moduleNode->addChild("Totsy_$moduleName");
    $moduleNode->addChild('active', 'true');
    $moduleNode->addChild('codePool', 'local');

    // create a new module configuration file
    $xmlModuleConf = new SimpleXmlElement('<config></config>');
    $xmlModuleConf->addChild('modules')
        ->addChild("Totsy_$moduleName")
        ->addChild('version', '0.1.0');

    file_put_contents($modulePath . '/etc/config.xml', xmlpp($xmlModuleConf->asXML()));
    echo "+ {$modulePath}/etc/config.xml", PHP_EOL;
    file_put_contents($mageRoot . '/app/etc/modules/Totsy_All.xml', xmlpp($xmlAppConf->asXML()));
    echo "* {$mageRoot}/app/etc/modules/Totsy_All.xml", PHP_EOL;

    return true;
}

/**
 * Handle a new controller request.
 * Create a new action controller in the module's 'controllers' directory.
 *
 * @param string $moduleName The name of the module.
 * @param string $className The name of the new controller class.
 *
 * @return void
 */
function controller($moduleName, $className) {
    global $mageRoot, $author;

    init($moduleName);

    $controllersPath = $mageRoot . "/app/code/local/Totsy/$moduleName/controllers";
    if (!is_dir($controllersPath)) {
        mkdir($controllersPath);
        echo "+ $controllersPath", PHP_EOL;
    }

    $controllerFileContents = <<<EOH
<?php
/**
 * @category    Totsy
 * @package     Totsy_$moduleName
 * @author      $author
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_{$moduleName}_{$className}Controller
    extends Mage_Core_Controller_Front_Action
{
}

EOH;
    file_put_contents($controllersPath . "/$className.php", $controllerFileContents);
    echo "+ {$controllersPath}/$className.php", PHP_EOL;
}

/**
 * Handle a new standard class request.
 * Create a new class in the appropriate module subdirectory.
 * A standard class is currently either a Block, Helper or Model class.
 *
 * @param string $moduleName The name of the module.
 * @param string $classType  The type of class (either 'block', 'helper', or 'model')
 * @param string $className  The name of the new controller class.
 *
 * @return void
 */
function standard($moduleName, $classType, $className) {
    global $mageRoot, $author;

    init($moduleName);

    $classType = ucwords($classType);
    $basePath = $mageRoot . "/app/code/local/Totsy/$moduleName/$classType";
    if (!is_dir($basePath)) {
        mkdir($basePath);
        echo "+ $basePath", PHP_EOL;
    }

    $classPath = str_replace('_', '/', $className);
    if (file_exists($basePath . "/$classPath.php")) {
        echo "- skip ", $basePath . "/$classPath.php", " (already exists)", PHP_EOL;
    }

    $classDir  = $basePath . '/' . dirname($classPath);
    if (!is_dir($classDir)) {
        mkdir($classDir, 0777, true);
        echo "+ $classDir", PHP_EOL;
    }

    $fileContents = <<<EOH
<?php
/**
 * @category    Totsy
 * @package     Totsy_{$moduleName}_{$classType}
 * @author      $author
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_{$moduleName}_{$classType}_{$className}
EOH;

    if ($namespace = is_override($moduleName, $classType, $className)) {
        // add a rewrite to the module configuration
        $pluralClass = strtolower($classType) . 's';
        $lowerModName = strtolower($moduleName);
        $lowerClassName = strtolower($className);

        // special case when the parent namespace being overriden is Enterprise
        if ('enterprise' == strtolower($namespace)) {
            $lowerModName = 'enterprise_' . $lowerModName;
        }

        $modconf = simplexml_load_file($mageRoot . "/app/code/local/Totsy/$moduleName/etc/config.xml");

        if (!$modconf->global) {
            $modconf->addChild('global');
        }
        if (!$modconf->global->$pluralClass) {
            $modconf->global->addChild($pluralClass);
        }
        if (!$modconf->global->$pluralClass->$lowerModName) {
            $modconf->global->$pluralClass->addChild($lowerModName);
        }
        if (!$modconf->global->$pluralClass->$lowerModName->rewrite) {
            $modconf->global->$pluralClass->$lowerModName->addChild('rewrite');
        }
        if (!$modconf->global->$pluralClass->$lowerModName->rewrite->$lowerClassName) {
            $modconf->global->$pluralClass->$lowerModName->rewrite->addChild($lowerClassName, "Totsy_{$moduleName}_{$classType}_{$className}");
            echo "Added a $classType rewrite for class $className to $mageRoot/app/code/local/Totsy/$moduleName/etc/config.xml", PHP_EOL;
        }

        file_put_contents($mageRoot . "/app/code/local/Totsy/$moduleName/etc/config.xml", xmlpp($modconf->asXML()));
        echo "* {$mageRoot}/app/code/local/Totsy/$moduleName/etc/config.xml", PHP_EOL;

        // add the 'extends' clause to the class definition
        $fileContents .= "\n    extends {$namespace}_{$moduleName}_{$classType}_{$className}";
    }

    $fileContents .= "\n{\n}\n";

    if (!file_exists($basePath . "/$classPath.php")) {
        file_put_contents($basePath . "/$classPath.php", $fileContents);
        echo "+ {$basePath}/$classPath.php", PHP_EOL;
    }
}

/**
 * Determine if a class overrides an existing core class.
 *
 * @param string $moduleName The name of the module.
 * @param string $classType  The type of class (either 'helper' or 'data')
 * @param string $className  The name of the new controller class.
 *
 * @return string The namespace which the class is overriding.
 */
function is_override($moduleName, $classType, $className) {
    global $mageRoot;

    $classDir = str_replace('_', '/', $className);
    foreach (glob($mageRoot . "/app/code/core/*", GLOB_ONLYDIR) as $namespace) {
        $originalFile = "$namespace/$moduleName/$classType/$classDir.php";
        if (file_exists($originalFile)) {
            return substr($namespace, strrpos($namespace, '/')+1);
        }
    }

    return false;
}

/**
 * Prettifies an XML string into a human-readable and indented work of art
 *
 * @param string $xml The XML as a string
 * @param boolean $html_output True if the output should be escaped (for use in HTML)
 */
function xmlpp($xml, $html_output=false) {
    $xml_obj = new SimpleXMLElement($xml);
    $level = 4;
    $indent = 0; // current indentation level
    $pretty = array();

    // get an array containing each XML element
    $xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));

    // shift off opening XML tag if present
    if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {
      $pretty[] = array_shift($xml);
    }

    foreach ($xml as $el) {
      if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {
          // opening tag, increase indent
          $pretty[] = str_repeat(' ', $indent) . $el;
          $indent += $level;
      } else {
        if (preg_match('/^<\/.+>$/', $el)) {
          $indent -= $level;  // closing tag, decrease indent
        }
        if ($indent < 0) {
          $indent += $level;
        }
        $pretty[] = str_repeat(' ', $indent) . $el;
      }
    }
    $xml = implode("\n", $pretty);
    return ($html_output) ? htmlentities($xml) : $xml;
}
