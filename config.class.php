<?php
include_once Config::$lpfw.'logger.class.php';
include_once Config::$lpfw.'ltools.php';

use maierlabs\lpfw\Logger as Logger;

//set logger output to file
Logger::setLoggerType(  \maierlabs\lpfw\LoggerType::file);
Logger::setLoggerLevel( \maierlabs\lpfw\LoggerLevel::info.\maierlabs\lpfw\LoggerLevel::error.\maierlabs\lpfw\LoggerLevel::debug);

class Config {

    public static $SiteTitle = "A kolozsvári Brassai Sámuel líceum véndiakjai. ";

    public static $siteUrl = "https://brassai.blue-l.de";
    public static $siteMail ="brassai@blue-l.de";
    public static $facebookApplId="1606012466308740";
    public static $timeZoneOffsetMinutes=60;                // Server timezone eg: London=0, Berlin=60, Moscow=120
    public static $dateTimeFormat="<b>Y.m.d</b> H:i:s";
    public static $dateFormat="<b>Y.m.d</b>";

    public static $webAppVersion = "2020.05.20";  //Used to load the actual css und js files.

    public static $SupportedLang = array("hu"); //First language ist the default language.

    public static $lpfw = __DIR__. '/../lpfw/';

    public static $secret_key = 'iskola';
    public static $secret_iv = 'brassai';

    public static $backupTableList = "-accelerator,-article,-request";



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