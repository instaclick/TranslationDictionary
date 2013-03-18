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
 * @link      https://github.com/instaclick/Csv2Xlf
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
 * Get target file path.
 *
 * @param $orginalFilePath    The original file's path
 * @param $targetLanguageCode The target language code
 *
 * @return string
 */
function getTargetFilePath($orginalFilePath, $targetLanguageCode)
{
    $sourceArray                         = explode('.', $orginalFilePath);
    $sourceLanguageCodeKey               = count($sourceArray) - 2;
    $sourceArray[$sourceLanguageCodeKey] = $targetLanguageCode;

    return implode('.', $sourceArray);
}
