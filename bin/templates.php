<?php
/**
 * Constants
 *
 * @category  PHP
 * @author    Yuan Xie <shayx@nationalfibre.net>
 * @copyright 2013 Instaclick Inc.
 * @license   http://spdx.org/licenses/MIT MIT License
 * @link      https://github.com/instaclick/TranslationDictionary
 */

// XML
// The translation XML's body
$TEMPLATE_XML_BODY = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">
  <file source-language="" datatype="plaintext" original="file.ext">
    <body>
    </body>
  </file>
</xliff>
EOT;

// The translation XML's trans-unit
// Note the formating (including the four trailing spaces at the end) is intentional
$TEMPLATE_XML_UNIT = <<<EOT
<trans-unit id="" resname="">
      <source></source>
      <target></target>
    </trans-unit>
    
EOT;
