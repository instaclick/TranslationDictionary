# Translation Dictionary Standalone Command-line Tool

This tool is developed in sync with [symfony's repository](https://github.com/symfony/symfony).

## Csv2Xlf
The script takes a .csv dictionary, and per config and template, populates the dictionary into .xlf translation files.

##### To Execute
1. Place the `.csv` file in `data/` directory;
2. Check and update the `bin/config.php` file to adjust script behaviour;
3. Check and update the `bin/templates.php` file to adjust xlf files' formating;
4. Run `csv2xlf.php` script on command-line (run without script arguments will prompt help);
5. Receive result xlf files in the `target/` directory.

##### Prerequisites
1. All translations must be in one master csv file;
2. Translations are key-based.

##### Peferences (`config.php`)
1. Csv column is delimited by semicolons ";";
2. Csv row is lined by line-end symbol "\n";
3. The key column is headered "Key";
4. Will NOT generate empty-target translation entries in xlf;
5. Will trim (remove leading and trailing whitespaces) translations in xlf;
6. Will compress (consolidate all neighboring whitespaces into one) translations in xlf;
7. Will NOT strip tags of translations in xlf.

##### Behaviour
1. Duplicated keys will be hinted, then tolerated and utilized;
2. If a key-language definition combo has another conflicting definition, the script will hint and use the first one;
3. If a key-language definition combo is not found, the script will hint and generate an empty translation entry (which must be manually filled in or removed from in the result xlf file).

## Xlf2Csv
The script takes a directory of .xlf translation files, and per config, compose a .csv dictionary.

##### To Execute
1. Place the `.xlf` files in `data/` directory or it's immediate subdirectories;
2. Check and update the `bin/config.php` file to adjust script behaviour;
3. Run `xlf2csv.php` script on command-line (run without script arguments will prompt help);
5. Receive result dictionary csv file in the `target/` directory.

##### Prerequisites
1. All xlf files must be valid;
2. Translations are key-based.

##### Peferences (`config.php`)
1. Csv column is delimited by semicolons ";";
2. The key column is headered "Key".

##### Behaviour
1. If a key-language definition combo is not found, an empty column for that language will still be generated in the key row;
2. Translation entries with an empty key or empty translation will be hinted and ignored;
3. Duplicated keys will be hinted, then tolerated and utilized;
4. If a key-language definition combo has another conflicting definition, the script will hint and use the first one.

## Additional Notes
- There are test data files in the `doc/sample/` directory for you to play around with;
- The sample directory contains `good/` and `bad/` data directories which are sample output files.

## Todo
- Tests
