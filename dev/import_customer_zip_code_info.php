<?php

if ($argc !== 2) {
    die("Usage: php import_customer_zip_code_info.php file.csv\n");
}

require_once __DIR__ . '/../app/Mage.php';
Mage::app();

$start = time();

$source = fopen($argv[1], 'r');

$i = 0;
$header = array();
while ($row = fgetcsv($source)) {
    if (0 == $i++) {
        $header = $row;
        continue;
    }

    $entry = Mage::getModel('totsycustomer/zipCodeInfo');
    $entry->setData(array_combine($header, $row))
        ->save();
}

fclose($source);

$end = time();

$seconds = $end - $start;
$duration = "$seconds seconds";
if ($seconds > 60) {
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    $duration = "${minutes}m ${seconds}s";
}

echo "Imported $i zip codes. Time: $duration, Memory: ", getPeakMemory('MB'), PHP_EOL;

function getPeakMemory($targetUnit = 'kB') {
    $units = array('b', 'kB', 'MB');
    $currentBytes = memory_get_peak_usage();
    $currentUnit  = 'b';

    for ($i = 0; $i < count($units); $i++) {
        if ($units[$i] == $targetUnit) {
            break;
        }

        $currentBytes /= 1024;
        $currentUnit = $units[$i+1];
    }

    return number_format($currentBytes, 1) . ' ' . $currentUnit;
}
