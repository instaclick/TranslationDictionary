Csv2Xlf Standalone Command-line Tool
====================

This tool is developed in sync with [symfony's repository](https://github.com/symfony/symfony).

## Description
The script takes a symfony2 `.xlf` file (preferrably the fallback language) and a `.csv` dictionary, and per configuration, populate the additional languages into corresponding `.xlf` files.

## To Execute
1. Place the `.xlf` and `.csv` files in the `data/` directory;
2. Check and update the `bin/config.php` file to adjust script behaviour;
3. Run `csv2xlf.php` script on command-line (run without script arguments will prompt help);
4. Receive result xlf files in the `target/` directory.

## Prerequisites
1. All translations must be in one master csv file;
2. Translations are key-based.

## Peferences (`config.php`)

1. Csv column is delimited by semicolons ";";
2. Csv row is lined by line-end symbol "\n";
3. The key column is headered "Key";
4. Will trim (remove leading and trailing whitespaces) translations in xlf;
5. Will compress (consolidate all neighboring whitespaces into one) translations in xlf;
6. Will NOT strip tags of translations in xlf.

## Behaviour
1. Duplicated keys will be hinted, then tolerated and utilized. 
3. If a key-language definition combo has another conflicting definition, the script will hint and use the first one.
3. If a key-language definition combo is not found, the script will hint and generate an empty translation entry (which must be manually filled in or removed from in the result xlf file).

## Additional Notes
- There are test data files in the `doc/sample/data/` directory for you to play around with;
- The `doc/target/` directory contains `good/` and `bad/` subdirectories which are sample output files.

## Todo

- Tests
- Expand into TranslationDictionary tool
