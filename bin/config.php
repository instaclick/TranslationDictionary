<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

// The csv's line end symbol
define ('CSV_LINE_END_SYMBOL', "\n");
// The csv's column delimiter
define ('CSV_COLUMN_DELIMITER', ';');
// The csv's header column which indicates the column stores all keys.
define ('CSV_KEY_COLUMN_NAME', 'Key');

// Trim translations during the execution
define ('DO_TRIM', true);
// "Compress" all neighboring whitespaces to one during the execution
define ('DO_COMPRESS', true);
// Strip tags from the translations during the execution
define ('DO_STRIP_TAGS', false);

// This defines what language you want to translate to
$TARGET_TRANSLATIONS = array (
    'en' => 'English',
    'fr' => 'French',
    'de' => 'German',
    'pt' => 'Portuguese',
);
