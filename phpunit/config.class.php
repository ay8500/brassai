<?php
/**
 * Created by PhpStorm.
 * User: Levi
 * Date: 07.12.2018
 * Time: 00:06
 */

namespace phpunit;

class config {

    /**
     * @var string Title of the php unit test page
     */
    public static $SiteTitle = "PHP Unit Webinterface for: \"A kolozsvári Brassai Sámuel líceum véndiakjai.\" ";

    /**
     * @var string start directory for *Test.php test files
     */
    public static $startDir = __DIR__.DIRECTORY_SEPARATOR.'..';

    /**
     * @var array exclude file list
     */
    public static $excludeFiles = array('images','..','.','.git');

}