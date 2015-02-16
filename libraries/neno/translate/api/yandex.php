<?php
/**
 * @package     Neno
 * @subpackage  TranslateApi
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_NENO') or die;
jimport('joomla.application.component.helper');

/**
 * Class NenoTranslateApiYandex
 *
 * @since  1.0
 */
class NenoTranslateApiYandex extends NenoTranslateApi
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * Translate text using yandex api
     *
     * @param   string $apiKey  the key provided by user
     * @param   string $text    text to translate
     * @param   string $source  source language
     * @param   string $target  target language default french
     *
     * @return string
     */
    public function translate($text,$source="en-US",$target="fr-Fr")
    {
        // get the key configured by user
        $this->apiKey = JComponentHelper::getParams('com_neno')->get('yandexApiKey');

        // convert from JISO to ISO codes
        $target = $this->convertFromJisoToIso($target);

        // language parameter for url
        $source = $this->convertFromJisoToIso($source);
        $lang = $source."-".$target;


        if($this->apiKey == "")
        {
            // Use default key if not provided
            $this->apiKey = 'trnsl.1.1.20150213T133918Z.49d67bfc65b3ee2a.b4ccfa0eaee0addb2adcaf91c8a38d55764e50c0';
        }

        // For POST requests, the maximum size of the text being passed is 10000 characters.
        $textString = str_split($text, 10000);
        $textStrings='';
        foreach($textString as $str)
        {
            $textStrings .= '&text=' . rawurlencode($str);
        }

        $url    = 'https://translate.yandex.net/api/v1.5/tr.json/translate?key=' . $this->apiKey . '&lang=' . $lang . $textStrings;

        // Invoke the GET request.
        $response = $this->get($url);

        $text = null;

        // Log it if server response is not OK.
        if ($response->code != 200)
        {
            NenoLog::log('Yandex api failed with response: ' . $response->code, 1);
        }
        else
        {
            $reponseBody=json_decode($response->body);
            $text = $reponseBody->text[0];
        }

        return $text;

    }

    /**
     * Method to make supplied language codes equivalent to yandex api codes
     *
     * @param   string $jiso Joomla ISO language code
     *
     * @return string
     */
    public function convertFromJisoToIso($jiso)
    {
        // split the language code parts using hypen
        $jisoParts = (explode("-",$jiso));
        $iso2Tag = strtolower($jisoParts[0]);

        switch($iso2Tag)
        {
            case "nb":
                $iso2 = "no";
                break;

            default:
                $iso2 = $iso2Tag;
                break;
        }

        return $iso2;
    }

}

