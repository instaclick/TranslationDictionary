Csv2Xlf Standalone Command-line Tool
====================

This tool is developed in sync with [symfony's repository](https://github.com/symfony/symfony).

## Description:
1. The script takes a symfony2 `.xlf` file (preferrably the fallback language) and a `.csv` dictionary, and;
2. Per configuration, populate the additional languages into corresponding `.xlf` files.

## To Execute:
1. Place the `.xlf` and `.csv` files in the `data/` directory;
2. Check and update the `bin/config.php` file to adjust script behavior;
3. Run `csv2xlf.php` script (run without script arguments will prompt help);
4. Receive result xlf files in the `target/` directory.

## Additional Notes:
- There are test data files in the `doc/sample/data/` directory for you to play around with;
- The `doc/target/` directory contains `good/` and `bad/` subdirectories which are sample output files.

## Todo

- Tests
- Expand into TranslationDictionary tool