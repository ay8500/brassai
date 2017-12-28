CREATE DATABASE  IF NOT EXISTS `db652851844` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `db652851844`;
-- MySQL dump 10.13  Distrib 5.6.13, for Win32 (x86)
--
-- Host: localhost    Database: db652851844
-- ------------------------------------------------------
-- Server version	5.6.20

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `class`
--

DROP TABLE IF EXISTS `class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schoolID` int(11) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `graduationYear` int(11) DEFAULT NULL,
  `text` varchar(145) DEFAULT NULL,
  `headTeacherID` int(11) DEFAULT NULL,
  `changeDate` datetime DEFAULT NULL,
  `changeIP` varchar(45) DEFAULT NULL,
  `changeUserID` int(11) DEFAULT NULL,
  `changeForID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8 COMMENT='School class';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interpret`
--

DROP TABLE IF EXISTS `interpret`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interpret` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(245) DEFAULT NULL,
  `link` varchar(245) DEFAULT NULL,
  `changeDate` datetime DEFAULT NULL,
  `changeIP` varchar(45) DEFAULT NULL,
  `changeUserID` int(11) DEFAULT NULL,
  `changeForID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3293 DEFAULT CHARSET=utf8 COMMENT='Song interprets';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(145) DEFAULT NULL,
  `text` text,
  `comment` text,
  `privacy` varchar(45) DEFAULT NULL,
  `changeIP` varchar(45) DEFAULT NULL,
  `changeDate` datetime DEFAULT NULL,
  `changeUserID` int(11) DEFAULT NULL,
  `changeForID` int(11) DEFAULT NULL,
  `isDeleted` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='User messages';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `person`
--

DROP TABLE IF EXISTS `person`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `person` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classID` int(11) NOT NULL,
  `isTeacher` int(11) NOT NULL DEFAULT '0',
  `firstname` varchar(245) NOT NULL,
  `lastname` varchar(245) DEFAULT NULL,
  `picture` varchar(245) DEFAULT NULL,
  `geolat` varchar(45) DEFAULT NULL,
  `geolng` varchar(45) DEFAULT NULL,
  `user` varchar(145) NOT NULL,
  `passw` varchar(255) DEFAULT NULL,
  `role` varchar(145) DEFAULT NULL,
  `birthname` varchar(145) DEFAULT NULL,
  `partner` varchar(145) DEFAULT NULL,
  `address` varchar(245) DEFAULT NULL,
  `zipcode` varchar(45) DEFAULT NULL,
  `place` varchar(145) DEFAULT NULL,
  `country` varchar(145) DEFAULT NULL,
  `phone` varchar(145) DEFAULT NULL,
  `mobil` varchar(145) DEFAULT NULL,
  `email` varchar(245) DEFAULT NULL,
  `homepage` varchar(255) DEFAULT NULL,
  `skype` varchar(145) DEFAULT NULL,
  `education` varchar(245) DEFAULT NULL,
  `employer` varchar(255) DEFAULT NULL,
  `function` varchar(245) DEFAULT NULL,
  `children` varchar(245) DEFAULT NULL,
  `facebook` varchar(245) DEFAULT NULL,
  `facebookid` varchar(45) DEFAULT NULL,
  `twitter` varchar(245) DEFAULT NULL,
  `cv` text,
  `story` text,
  `aboutMe` text,
  `changeIP` varchar(45) DEFAULT NULL,
  `changeDate` datetime DEFAULT NULL,
  `changeUserID` int(11) DEFAULT NULL,
  `changeForID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=588 DEFAULT CHARSET=utf8 COMMENT='Classmates und teachers';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `picture`
--

DROP TABLE IF EXISTS `picture`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `picture` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `personID` int(11) DEFAULT NULL,
  `schoolID` int(11) DEFAULT NULL,
  `classID` int(11) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `isVisibleForAll` int(11) DEFAULT NULL,
  `title` varchar(145) DEFAULT NULL,
  `comment` varchar(8196) DEFAULT NULL,
  `isDeleted` int(11) DEFAULT NULL,
  `uploadDate` datetime DEFAULT NULL,
  `changeDate` datetime DEFAULT NULL,
  `changeIP` varchar(45) DEFAULT NULL,
  `changeUserID` int(11) DEFAULT NULL,
  `changeForID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school`
--

DROP TABLE IF EXISTS `school`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school` (
  `id` int(11) NOT NULL,
  `name` varchar(145) DEFAULT NULL,
  `nameGiver` text,
  `history` text,
  `phone` varchar(45) DEFAULT NULL,
  `mail` varchar(145) DEFAULT NULL,
  `homepage` varchar(245) DEFAULT NULL,
  `addressCity` varchar(145) DEFAULT NULL,
  `addressCountry` varchar(145) DEFAULT NULL,
  `addressZipCode` varchar(45) DEFAULT NULL,
  `addressStreet` varchar(45) DEFAULT NULL,
  `changeDate` datetime DEFAULT NULL,
  `changeIP` varchar(45) DEFAULT NULL,
  `changeUserID` int(11) DEFAULT NULL,
  `changeForID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='School';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `song`
--

DROP TABLE IF EXISTS `song`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `song` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interpretID` int(11) DEFAULT NULL,
  `name` varchar(245) DEFAULT NULL,
  `video` varchar(245) DEFAULT NULL,
  `link` varchar(245) DEFAULT NULL,
  `changeDate` datetime DEFAULT NULL,
  `changeIP` varchar(45) DEFAULT NULL,
  `changeUserID` int(11) DEFAULT NULL,
  `changeForID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2847 DEFAULT CHARSET=utf8 COMMENT='Songs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `songvote`
--

DROP TABLE IF EXISTS `songvote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `songvote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `personID` int(11) DEFAULT NULL,
  `songID` int(11) DEFAULT NULL,
  `changeDate` datetime DEFAULT NULL,
  `changeIP` varchar(45) DEFAULT NULL,
  `changeUserID` int(11) DEFAULT NULL,
  `changeForID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7161 DEFAULT CHARSET=utf8 COMMENT='Song Votes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vote`
--

DROP TABLE IF EXISTS `vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classID` int(11) DEFAULT NULL,
  `personID` int(11) DEFAULT NULL,
  `meetAfterYear` int(11) DEFAULT NULL,
  `isSchool` int(11) DEFAULT NULL,
  `isCemetery` int(11) DEFAULT NULL,
  `isDinner` int(11) DEFAULT NULL,
  `isExcursion` int(11) DEFAULT NULL,
  `place` varchar(255) DEFAULT NULL,
  `eventDay` varchar(255) DEFAULT NULL,
  `changeDate` datetime DEFAULT NULL,
  `changeIP` varchar(45) DEFAULT NULL,
  `changeUserID` int(11) DEFAULT NULL,
  `changeForID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8 COMMENT='Voting	';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

DROP TABLE IF EXISTS `request`;

CREATE TABLE `request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) DEFAULT NULL,
  `typeID` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8;

CREATE TABLE `personhistory` (
  `historyID` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `classID` int(11) NOT NULL,
  `isTeacher` int(11) NOT NULL DEFAULT '0',
  `firstname` varchar(245) NOT NULL,
  `lastname` varchar(245) DEFAULT NULL,
  `picture` varchar(245) DEFAULT NULL,
  `geolat` varchar(45) DEFAULT NULL,
  `geolng` varchar(45) DEFAULT NULL,
  `user` varchar(145) NOT NULL,
  `passw` varchar(255) DEFAULT NULL,
  `role` varchar(145) DEFAULT NULL,
  `birthname` varchar(145) DEFAULT NULL,
  `partner` varchar(145) DEFAULT NULL,
  `address` varchar(245) DEFAULT NULL,
  `zipcode` varchar(45) DEFAULT NULL,
  `place` varchar(145) DEFAULT NULL,
  `country` varchar(145) DEFAULT NULL,
  `phone` varchar(145) DEFAULT NULL,
  `mobil` varchar(145) DEFAULT NULL,
  `email` varchar(245) DEFAULT NULL,
  `homepage` varchar(255) DEFAULT NULL,
  `skype` varchar(145) DEFAULT NULL,
  `education` varchar(245) DEFAULT NULL,
  `employer` varchar(255) DEFAULT NULL,
  `function` varchar(245) DEFAULT NULL,
  `children` varchar(245) DEFAULT NULL,
  `facebook` varchar(245) DEFAULT NULL,
  `facebookid` varchar(45) DEFAULT NULL,
  `twitter` varchar(245) DEFAULT NULL,
  `cv` text,
  `story` text,
  `aboutMe` text,
  `changeIP` varchar(45) DEFAULT NULL,
  `changeDate` datetime DEFAULT NULL,
  `changeUserID` int(11) DEFAULT NULL,
  `changeForID` int(11) DEFAULT NULL,
  `historyType` varchar(45) DEFAULT NULL,
  `historyDate` datetime DEFAULT NULL,
  `historyUserID` int(11) DEFAULT NULL
  PRIMARY KEY (`historyID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='History of Classmates und teachers' AUTO_INCREMENT=1 ;


