<?php

namespace maierlabs\lpfw;

include_once "loggerType.class.php";
include_once  "loggerLevel.class.php";


class Logger
{
    private static $loggerLevel=LoggerLevel::info;
    private static $loggerType=LoggerType::file;

    //LoggerLevel aus der Session
    public static function setLoggerLevel($loggerLevel)
    {
        self::$loggerLevel = $loggerLevel;
    }

    //LoggerType aus der Session
    public static function setLoggerType($loggerType)
    {
        self::$loggerType = $loggerType;
    }

    /**
     * log text
     * @param string $text
     * @param string $level
     */
    public static function _($text, $level = LoggerLevel::info)
    {
        if (strrpos(self::$loggerLevel, $level) > -1) {
            if (self::$loggerType == LoggerType::html) {
                echo('<span style="color:black;background-color:white;">' . $text . "</span><br/>");
            }
            if (self::$loggerType == LoggerType::file) {
                Logger::logToFile($text, $level);
            }
        }
    }

    //Text Loggen wenn condition falsch, dann als Fehler
    //Condition wird unverändert zurück gegeben
    public static function loggerConditioned($condition, $text, $level = LoggerLevel::debug)
    {
        if (strrpos($_SESSION['loggerLevel'], $level) > -1) {
            if ($_SESSION['loggerType'] == LoggerType::html) {
                echo('<span style="color:black;background-color:white;">' . $text . "</span><br/>");
            }
            if ($_SESSION['loggerType'] == LoggerType::file) {
                if ($condition)
                    Logger::logToFile($text, $level);
                else
                    Logger::logToFile($text, LoggerLevel::error);
            }
        }
        return $condition;
    }

    //Array loggen
    public static function loggerArray($arr, $level = LoggerLevel::info)
    {
        if (strrpos($_SESSION['loggerLevel'], $level) > -1) {
            if ($_SESSION['loggerType'] == LoggerType::html) {
                echo("<table>");
                foreach ($arr as $key => $value) {
                    echo("<tr>");
                    echo("<td>" . $key . "</td><td>" . $value . "</td>");
                    echo("</tr>");
                }
                echo("</table>");
            }
            if ($_SESSION['loggerType'] == LoggerType::file) {
                Logger::logToFile("Array: " . var_export($arr, true), $level);
            }
        }
    }

    //Tabelle logen
    public static function loggerTable($table, $level = LoggerLevel::info)
    {
        if (strrpos($_SESSION['loggerLevel'], $level) > -1) {
            if ($_SESSION['loggerType'] == LoggerType::html) {
                if (count($table) > 0) {
                    echo("<table>");
                    echo("<tr>");
                    foreach ($table[0] as $key => $value) {
                        echo("<td>" . $key . "</td>");
                    }
                    echo("</tr>");
                    foreach ($table as $arr) {
                        echo("<tr>");
                        foreach ($arr as $key => $value) {
                            echo("<td>" . $value . "</td>");
                        }
                        echo("</tr>");
                    }
                    echo("</table>");
                }
            }
            if ($_SESSION['loggerType'] == LoggerType::file) {
                if (count($table) > 0) {
                    foreach ($table as $qkey => $arr) {
                        $line = "Line:" . $qkey . ";";
                        foreach ($arr as $key => $value) {
                            $line .= $key . ":" . $value . ",";
                        }
                        Logger::logToFile($line, $level);
                    }
                }
            }
        }
    }

    public static function logToFile($logText, $level)
    {
        $file = './log';
        $text = date('Y-m-d H:i:s') . "\t";
        $text .= $level . "\t";
        $text .= $_SERVER["REMOTE_ADDR"] . "\t";
        $text .= $_SERVER["SCRIPT_NAME"] . "\t";
        $text .= $_SERVER["REQUEST_URI"] . "\t";
        if (isset($_SESSION['USER']))
            $text .= $_SESSION['USER'] . "\t";
        $text .= $logText . "\t";
        $text .= "\r\n";
        file_put_contents($file, $text, FILE_APPEND | LOCK_UN);
    }
}


