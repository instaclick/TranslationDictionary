<?php
/**
 * Translation Dictionary Standalone Command-line Tool - Xlf2Csv
 *
 * This simple script will:
 *     1) Take a directory of .xlf translation files, and;
 *     2) Per config, compose a .csv dictionary.
 *
 * PHP version 5 (>= 5.3.0)
 *
 * @category  PHP
 * @author    Yuan Xie <shayx@nationalfibre.net>
 * @copyright 2013 Instaclick Inc.
 * @license   http://spdx.org/licenses/MIT MIT License
 * @link      https://github.com/instaclick/TranslationDictionary
 */

error_reporting(E_ALL);

include_once("config.php");
include_once("templates.php");
include_once("include/constants.php");
include_once("include/functions.php");

if ($argc != 2 || $argv[1] == "h" || $argv[1] == "help" ) {
    echo "Usage: php xlf2csv.php <dictionary.csv>\n";
    echo "Note: Please confirm/update the config.php before running.\n";
    echo "\n";

    return;
}

echo "Composing dictionary from \"" . $PATH_DATA . "\"\n";

// Collect a list of .xlf files that are 1) under data directory, and 2) under data directory's subdirectories
$xlfPathList1 = glob($PATH_DATA . '*.xlf');
$xlfPathList2 = glob($PATH_DATA . '*' . DIRECTORY_SEPARATOR . '*.xlf');
$xlfPathList  = array_merge($xlfPathList1, $xlfPathList2);

// Collect all translation entries in a directionary array
$directionary = xlf2dictionary($xlfPathList, $TARGET_TRANSLATIONS);

echo "Composing completed.\n\n";

$translationCsvPath = $PATH_TARGET . $argv[1];

// Remove the existing translation csv file
if (is_file($translationCsvPath)) {
    unlink($translationCsvPath);
}

echo "Generating translation dictionary \"" . $argv[1] . "\" ...\n";

$filePointer = fopen($translationCsvPath, 'w');

foreach ($directionary as $directionaryLine) {
    fputcsv($filePointer, $directionaryLine, CSV_COLUMN_DELIMITER);
}

fclose($filePointer);

echo "Translation dictionary csv was generated.\n";
