<?php

/**
 * @package Lingo
 * @subpackage Database
 *
 * @copyright (c) 2014, Jensen Technologies S.L. All rights reserved
 */
defined('JPATH_LINGO') or die;

use PHPSQL\Creator;
use PHPSQL\Parser;

/**
 * Database driver class extends from Joomla Platform Database Driver class
 *
 * @package Lingo
 * @subpackage Database
 * @since 1.0
 */
class LingoDatabaseParser
{

    public static function getCurrentShadowTableName($sql)
    {
        $parser = new Parser();
        $sqlElements = $parser->parse($sql);
        if (!empty($sqlElements['FROM'])) {
            $fromStatements = $sqlElements['FROM'];

            // Initialise variables
            $found = false;
            $fromTable = null;
            $index = -1;

            // Go through all the from statements (it includes both FROM and JOIN statements)
            for ($i = 0; $i < count($fromStatements) && !$found; $i++) {
                if ($fromStatements[$i]['join_type'] === 'JOIN' && $fromStatements[$i]['ref_type'] === false) {
                    $fromTable = $fromStatements[$i];
                    $found = true;
                    $index = $i;
                }
            }

            // If a from statement was found
            if ($fromTable !== null) {

                // Get language code, if the current language is different than the default language
                $languageCode = self::getDbLanguageTag();

                // If it's not the same, let's append it to the table name
                if ($languageCode !== '') {

                    $fromTable['table'] = self::generateShadowTableName($fromTable['table'],
                                    $languageCode);
                    $sqlElements['FROM'][$index] = $fromTable;
                }

                // Put together the query again
                $creator = new Creator($sqlElements);
                $sql = $creator->created;
            }
        }

        return $sql;
    }

    /**
     * Get language tag to add at the end of the table name
     * @return string
     */
    private static function getDbLanguageTag()
    {
        $currentLanguage = JFactory::getLanguage();
        $currentLanguageTag = $currentLanguage->getTag();
        $defaultLanguageTag = $currentLanguage->getDefault();

        $languageTag = '';

        // If it is not the default language, let's get the language tag
        if ($currentLanguageTag !== $defaultLanguageTag) {

            // Clean language tag
            $languageTag = self::cleanLanguageTag($currentLanguageTag);
        }

        return $languageTag;
    }

    /**
     * Clean language tag
     * @param string $languageTag
     * @return string language tag cleaned
     */
    private static function cleanLanguageTag($languageTag)
    {
        return strtolower(str_replace(array('-'), array(''), $languageTag));
    }

    /**
     * Get table name without Joomla prefixes
     * @param string $tableName
     * @return string clean table name
     */
    private static function cleanTableName($tableName)
    {
        return str_replace(array('#__', JFactory::getConfig()->get('dbprefix')),
                '', $tableName);
    }

    /**
     * Generate shadow table name
     * @param string $tableName Table name
     * @param string $languageTag clean language tag
     * @return string shadow table name.
     */
    private static function generateShadowTableName($tableName, $languageTag)
    {
        return '#__lingo_sh_' . $languageTag . '_' . self::cleanTableName($tableName);
    }

}
