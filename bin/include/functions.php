<?php
/**
 * Functions
 *
 * PHP version 5 (>= 5.3.0)
 *
 * @category  PHP
 * @author    Yuan Xie <shayx@nationalfibre.net>
 * @copyright 2013 Instaclick Inc.
 * @license   http://spdx.org/licenses/MIT MIT License
 * @link      https://github.com/instaclick/TranslationDictionary
 */

/**
 * Parse the csv file into an array presentation.
 *
 * @param $filePath The .csv file's relative path to the main script
 *
 * @return array
 */
function csv2array($filePath)
{
    $fileContentArray = str_getcsv(file_get_contents($filePath), CSV_LINE_END_SYMBOL);

    $languageArray    = str_getcsv($fileContentArray[0], CSV_COLUMN_DELIMITER);
    unset($fileContentArray[0]);

    $translationArray = content2array($fileContentArray, $languageArray);
    unset($languageArray[0]);

    $csvArray         = array(
        'language'    => $languageArray,
        'translation' => $translationArray,
    );

    return $csvArray;
}

/**
 * Parse the csv file's content into an array presentation.
 *
 * @param $contentArray  The .csv file's content in array representation
 * @param $languageArray The .csv file's language mapping in array representation
 *
 * @return array
 */
function content2array($contentArray, $languageArray)
{
    $translationArray     = array ();
    $languageMappingArray = array_flip($languageArray);
    $columnKeyId          = $languageMappingArray[CSV_KEY_COLUMN_NAME];

    foreach ($contentArray as $csvRow) {
        $rowArray = str_getcsv($csvRow, CSV_COLUMN_DELIMITER);
        $rowKey   = $rowArray[$columnKeyId];

        if (empty($rowKey)) {
            continue;
        }

        unset($rowArray[$columnKeyId]);

        $rowArray = processRow($rowArray, $languageArray);

        if (isset($translationArray[$rowKey])) {
            echo " * " . $rowKey . " -- duplicated keys entries were found.";
            $rowArray = mergeRow($translationArray[$rowKey], $rowArray);
            echo "\n";
        }

        $translationArray[$rowKey] = $rowArray;
    }

    return massageTranslation($translationArray);
}

/**
 * Process a row.
 *
 * @param $rowArray      A csv record in array representation
 * @param $languageArray The .csv file's language mapping in array representation
 *
 * @return array
 */
function processRow($rowArray, $languageArray)
{
    $processRow = array();

    foreach ($rowArray as $id => $column) {
        $processRow[$languageArray[$id]] = $column;
    }

    return $processRow;
}

/**
 * Merge two rows.
 *
 * @param $rowArray1 A csv record in array representation
 * @param $rowArray2 A csv record in array representation
 *
 * @return array
 */
function mergeRow($rowArray1, $rowArray2)
{
    $mergedRow = array();

    foreach ($rowArray1 as $id => $column) {
        if (empty($column)) {
            $mergedRow[$id] = $rowArray2[$id];
            continue;
        }

        if ( ! empty($rowArray2[$id]) && (strtolower($rowArray1[$id]) !== strtolower($rowArray2[$id]))) {
            echo "\n   > Warning: conflicting translations were found: \"" . $rowArray1[$id] . "\" vs \"" . $rowArray2[$id] . "\". \"" . $rowArray1[$id] . "\" was used.";
        }

        $mergedRow[$id] = $rowArray1[$id];
    }

    return $mergedRow;
}

/**
 * Perform clean up actions (e.g. set empty, trim, strip tags) on all elements of an translation array (as configured).
 *
 * @param $csvArray A csv in array representation
 *
 * @return array
 */
function massageTranslation($csvArray)
{
    $messagedCsv = array ();

    foreach ($csvArray as $id => $value) {
        $messagedCsv[$id] = massageRow($value);
    }

    return $messagedCsv;
}

/**
 * Perform clean up actions (e.g. set empty, trim, strip tags) on all elements of an array (as configured).
 *
 * @param $rowArray A csv record in array representation
 *
 * @return array
 */
function massageRow($rowArray)
{
    foreach ($rowArray as $id => $value) {
        if (DO_TRIM) {
            $rowArray[$id] = trim($rowArray[$id]);
        }

        if (DO_COMPRESS) {
            $rowArray[$id] = preg_replace('/\s\s+/', ' ', $rowArray[$id]);
        }

        if (DO_STRIP_TAGS) {
            $rowArray[$id] = strip_tags($rowArray[$id]);
        }

        if (empty($value) && $value !== null) {
            unset($rowArray[$id]);
        }
    }

    return $rowArray;
}

/**
 * Get target file name.
 *
 * @param $languageCode Language code
 *
 * @return string
 */
function getTargetFileName($languageCode)
{
    return 'messages.' . $languageCode . '.xlf';
}

/**
 * Parse a list of xlf files into an array presentation of dictionary.
 *
 * @param array $xlfPathList  An array of .xlf files that contain translation definitions
 * @param array $languageList An array of target translation languages
 *
 * @return array
 */
function xlf2dictionary($xlfPathList, $languageList)
{
    $translationKeyList = array_merge(
        array('key' => CSV_KEY_COLUMN_NAME),
        $languageList
    );

    $dictionary = array(
        'key' => $translationKeyList,
    );

    foreach ($xlfPathList as $xlfPath) {
        $xlfDocument = new DOMDocument();
        $xlfDocument->load($xlfPath);

        $sourceLanguage  = $xlfDocument->getElementsByTagName('file')->item(0)->attributes->getNamedItem('source-language')->nodeValue;
        $transUnitList   = $xlfDocument->getElementsByTagName('trans-unit');
        $dictionary      = mergeTransUnitList($dictionary, $languageList, $sourceLanguage, $transUnitList);
    }

    return $dictionary;
}

/**
 * Merge trans unit list into the dictionary.
 *
 * @param array  $dictionary     An array of .xlf file path that contain translation definitions
 * @param array  $languageList   An array of target translation languages
 * @param string $sourceLanguage Source language
 * @param array  $transUnitList  An list of trans-units
 *
 * @return array
 */
function mergeTransUnitList($dictionary, $languageList, $sourceLanguage, $transUnitList)
{
    $emptyTargetTranslations = array();

    foreach ($languageList as $languageId => $languageContent) {
        $emptyTargetTranslations[$languageId] = '';
    }

    foreach ($transUnitList as $transUnit) {
        $translation = $transUnit->childNodes;
        $source      = $translation->item(1)->nodeValue;
        $target      = $translation->item(3)->nodeValue;

        // Prompt empty source
        if ($source == '') {
            echo " * An entry with an empty key were found. Entry ignored.\n";

            continue;
        }

        // Prompt empty target
        if ($target == '') {
            echo " * " . $source . " -- an entry with an empty translation were found. Entry ignored.\n";

            continue;
        }

        // Create translation entry (if it doesn't exist already)
        if ( ! isset($dictionary[$source][$source])) {
            $translationEntryList = array_merge(
                array($source => $source),
                $emptyTargetTranslations
            );

            $dictionary[$source] = $translationEntryList;
        }

        // Fill the language translation (if it doesn't exist already)
        if ($dictionary[$source][$sourceLanguage] == '') {
            $dictionary[$source][$sourceLanguage] = $target;

            continue;
        }

        // Warn duplicated definitions
        echo " * " . $source . " -- duplicated keys entries were found.";

        if ($dictionary[$source][$sourceLanguage] != $target) {
            echo "\n   > Warning: conflicting translations were found: \"" . $dictionary[$source][$sourceLanguage] . "\" vs \"" . $target . "\". \"" . $dictionary[$source][$sourceLanguage] . "\" was used.";
        }

        echo "\n";
    }

    return $dictionary;
}
