<?php
/**
 * Csv2Xlf Standalone Command-line Tool
 *
 * This simple script will:
 *     1) Take a symfony2 .xlf file (preferrably the fallback language) and a .csv dictionary, and;
 *     2) Per configuration, populate the additional languages into corresponding .xlf files.
 *
 * PHP version 5 (>= 5.3.0)
 *
 * @category  PHP
 * @author    Yuan Xie <shayx@nationalfibre.net>
 * @copyright 2013 Instaclick Inc.
 * @license   http://spdx.org/licenses/MIT MIT License
 * @link      https://github.com/instaclick/Csv2Xlf
 */

error_reporting(E_ALL);

include_once("config.php");
include_once("include/constants.php");
include_once("include/functions.php");

if ($argc != 3 || $argv[1] == "h" || $argv[1] == "help" ) {
    echo "Usage:   php csv2xlf.php <source.xx.xlf> <translation.csv>\n";
    echo "Example: php csv2xlf.php messages.en.xlf language_template.csv\n";
    echo "Note:\n";
    echo "   1. Please update the config.php for configurations before running.\n";
    echo "   2. This script is for automating the bulk of the manual translation process, you are still responsible for manually ensuring a quality translation.\n";
    echo "\n";

    return;
}

$sourcePath         = $PATH_DATA . $argv[1];
$translationCsvPath = $PATH_DATA . $argv[2];

if ( ! is_file($sourcePath)) {
    echo "File " . $sourcePath . " was not found.\n";

    return;
}

if ( ! is_file($translationCsvPath)) {
    echo "File " . $translationCsvPath . " was not found.\n";

    return;
}

echo "Parsing " . $argv[1] . " ...\n";
$xmlObject          = simplexml_load_file($sourcePath);
$sourceLanguageCode = (string) $xmlObject->file['source-language'];
$sourceLanguageName = $TARGET_TRANSLATIONS[(string)$sourceLanguageCode];
echo "Parsing completed.\n\n";

echo "Parsing " . $argv[2] . " ...\n";
$csvArray = csv2array($translationCsvPath);
echo "Parsing completed.\n\n";

// Remove all files in the "/target" directory
array_map('unlink', glob($PATH_TARGET . '*'));

echo "Generating translation files ...\n";

// Foreach $TARGET_TRANSLATIONS, compose the translation xml file
foreach ($TARGET_TRANSLATIONS as $targetLanguageCode => $targetLanguageName) {
    // If the source language is the same target language, skip the xml generation
    if ($sourceLanguageName == $targetLanguageName) {
        continue;
    }

    // If the translation file does not include the target language, skip the xml generation
    if ( ! in_array($targetLanguageName, $csvArray['language'])) {
        echo " * The dictionary did not define " . $targetLanguageName . " translations.\n";
        echo "   " . $targetLanguageName . " translation file " . $targetPath . " was NOT generated.\n\n";
        continue;
    }

    echo " * Generating translations for " . $targetLanguageName . ":\n";

    $targetPath = getTargetFilePath($PATH_TARGET . $argv[1], $targetLanguageCode);

    $messageCounter                           = 0;
    $targetXmlObject                          = clone($xmlObject);
    $targetXmlObject->file['source-language'] = $targetLanguageCode;

    $transUnitList = $targetXmlObject->file->body->{'trans-unit'};

    foreach ($transUnitList as $transUnit) {
        $key         = (string) $transUnit->source;
        $translation = isset($csvArray['translation'][$key]) && isset($csvArray['translation'][$key][$targetLanguageName])
                     ? $csvArray['translation'][$key][$targetLanguageName]
                     : null;

        if ( ! isset($translation)) {
            echo '   > Warning: could not find a translation for "' . $key . "\".\n";
            $messageCounter++;
            $transUnit->target = '';
            continue;
        }

        $transUnit->target = $translation;
    }

    $targetContent = $XML_HEAD . "\n" . $targetXmlObject->asXML();

    file_put_contents($targetPath, $targetContent);

    if ($messageCounter > 0) {
        echo "   There were " . $messageCounter . " warnings occurred during the generation process.\n";
    }
    echo "   " . $targetLanguageName . " translation file " . $targetPath . " was generated.\n";
    echo "\n";
}

echo "Translation files were generated.\n";
