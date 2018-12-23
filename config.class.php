<?php
include_once 'tools/logger.class.php';
include_once 'tools/ltools.php';

use maierlabs\lpfw\Logger as Logger;

//set logger output to file
Logger::setLoggerType(  \maierlabs\lpfw\LoggerType::file);
Config::init();

class Config {

    public static $SiteTitle = "A kolozsvári Brassai Sámuel líceum véndiakjai. ";

    public static $siteUrl = "https://brassai.blue-l.de";
    public static $siteMail ="brassai@blue-l.de";
    public static $timeZoneOffsetMinutes=60;
    public static $dateTimeFormat="<b>Y.m.d</b> H:i:s";
    public static $dateFormat="<b>Y.m.d</b>";

    public static $webAppVersion = "20181223a";  //Used to load the actual css und js files.

    private static $SupportedLang = array("hu"); //First language ist the default language.

    private static $textResource;

    /**
     * initialise
     * @return void
     */
    public static function init(){
        // Set languge include file
        if (!isset($_SESSION['LANG'])) $_SESSION['LANG'] = self::$SupportedLang[0];
        // Change language
        if (null!=getGetParam("language"))
        {
            $_SESSION['LANG'] = getGetParam("language");
        }

        $LangFile = "Lang_" . $_SESSION['LANG'] . ".php";
        if (file_exists($LangFile))
            self::$textResource=include $LangFile;
        else
            self::$textResource=include "Lang_" . self::$SupportedLang[0] . ".php";
    }


    /**
     * Get internationalized  text resource
     * @param string $index ressource index
     * @return string
     */
    public static function _text($index)
    {
        if (isset(self::$textResource[$index]))
            return self::$textResource[$index];
        else {
            return "#" . $index . "#";
        }
    }

    /**
     * Get Database propertys host, database, user, password
     * @return object
     */
    public static function getDatabasePropertys()
    {
        $ret = new stdClass();
        if (!isLocalhost()) {
            $ret->host='db652851844.db.1and1.com';
            $ret->database='db652851844';
            $ret->user='dbo652851844';
            $ret->password='levi1967';
        } else {
            $ret->host='localhost';
            $ret->database='db652851844';
            $ret->user='root';
            $ret->password='root';
        }

        return $ret;
    }
}
?>