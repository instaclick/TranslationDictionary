<?php
/**
 * Translation Dictionary Standalone Command-line Tool - Csv2Xlf
 *
 * This simple script will:
 *     1) Take a .csv dictionary, and;
 *     2) Per config and template, populate the dictionary into .xlf translation files.
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
    echo "Usage: php csv2xlf.php <dictionary.csv>\n";
    echo "Note: Please confirm/update the config.php and templates.php before running.\n";
    echo "\n";

    return;
}

$translationCsvName = $argv[1];
$translationCsvPath = $PATH_DATA . $translationCsvName;

if ( ! is_file($translationCsvPath)) {
    echo "File " . $translationCsvPath . " was not found.\n";

    return;
}

echo "Parsing translation dictionary " . $translationCsvName . " ...\n";
$csvArray = csv2array($translationCsvPath);
echo "Parsing completed.\n\n";

// Remove all files and directories in the target directory
array_map('unlink', glob($PATH_TARGET . '*' . DIRECTORY_SEPARATOR . '*'));
array_map('rmdir', glob($PATH_TARGET . '*'));

echo "Generating translation files ...\n";

// For each $TARGET_TRANSLATIONS, compose the translation xml file
foreach ($TARGET_TRANSLATIONS as $targetLanguageCode => $targetLanguageName) {
    // If the translation file does not include the target language, skip the xml generation
    if ( ! in_array($targetLanguageName, $csvArray['language'])) {
        echo " * The dictionary did not define " . $targetLanguageName . " translations.\n";
        echo "   " . $targetLanguageName . " translation file(s) was NOT generated.\n\n";
        continue;
    }

    echo " * Generating translations for " . $targetLanguageName . ":\n";

    $messageCounter       = 0;
    $targetXmlObjectArray = array();

    // Per translation entries, build up the DOMDocument objects for the target xlf files
    foreach ($csvArray['translation'] as $transUnitSource => $translationDefinition) {
        $transUnitTarget = isset($translationDefinition[$targetLanguageName])
                     ? $translationDefinition[$targetLanguageName]
                     : null;

        if ( ! isset($transUnitTarget)) {
            $messageCounter++;
            echo '   > Warning: could not find a translation for "' . $transUnitSource . "\".";

            if ( ! GENERATE_EMPTY_TRANSLATION_ENTRIES) {
                echo "\n";
                continue;
            }

            echo " An empty entry was generated anyway.\n";
        }

        $bundleKey = reset(explode($KEY_DELIMITER, $transUnitSource));

        if ( ! isset($targetXmlObjectArray[$bundleKey])) {
            $targetXmlObjectArray[$bundleKey][$targetLanguageCode] = DOMDocument::loadXML($TEMPLATE_XML_BODY);
            $fileNode                                              = $targetXmlObjectArray[$bundleKey][$targetLanguageCode]->getElementsByTagName('file')->item(0);

            $fileNode->setAttribute('source-language', $targetLanguageCode);
        }

        // Create the new trans-unit
        $transUnitFragment = $targetXmlObjectArray[$bundleKey][$targetLanguageCode]->createDocumentFragment();
        $transUnitFragment->appendXML($TEMPLATE_XML_UNIT);
        $transUnit = $targetXmlObjectArray[$bundleKey][$targetLanguageCode]->getElementsByTagName('file')->item(0)->getElementsByTagName('body')->item(0)->appendChild($transUnitFragment);

        // Set the trans-unit by directionary
        $transUnit->setAttribute('id', md5($transUnitSource));
        $transUnit->setAttribute('resname', $transUnitSource);
        $transUnit->getElementsByTagName('source')->item(0)->appendChild(new DOMText($transUnitSource));
        $transUnit->getElementsByTagName('target')->item(0)->appendChild(new DOMText($transUnitTarget));
    }

    // Per each DOMDocument object, output the target xlf files
    foreach ($targetXmlObjectArray as $bundleKey => $translationList) {
        $targetBundlePath = $PATH_TARGET . $bundleKey . DIRECTORY_SEPARATOR;

        if ( ! is_dir($targetBundlePath)) {
            mkdir($targetBundlePath, 0777);
        }

        foreach ($translationList as $targetLanguageCode => $translationDOMDocument) {
            $filePath = $targetBundlePath . getTargetFileName($targetLanguageCode);

            file_put_contents($filePath, $translationDOMDocument->saveXML());
        }
    }

    echo "   ";
    if ($messageCounter > 0) {
        echo "There were " . $messageCounter . " warnings occurred during the generation process. ";
    }
    echo $targetLanguageName . " translation was generated.\n\n";
}

echo "Translation files were generated.\n";
