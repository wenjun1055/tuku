-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- 主机: w.rdc.sae.sina.com.cn:3307
-- 生成日期: 2013 年 03 月 08 日 10:42
-- 服务器版本: 5.5.23
-- PHP 版本: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `app_catsoul`
--

-- --------------------------------------------------------

--
-- 表的结构 `album`
--

CREATE TABLE IF NOT EXISTS `album` (
  `id` smallint(8) NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) NOT NULL,
  `album_name` varchar(32) CHARACTER SET utf8 NOT NULL,
  `album_desc` varchar(255) CHARACTER SET utf8 NOT NULL,
  `parent_id` smallint(8) NOT NULL DEFAULT '0',
  `create_time` varchar(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_album_user1` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- 表的结构 `disk`
--

CREATE TABLE IF NOT EXISTS `disk` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) NOT NULL,
  `appkey` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `appsecret` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `account_name` varchar(60) NOT NULL,
  `account_password` varchar(60) NOT NULL,
  `account_volume` varchar(16) CHARACTER SET utf8 DEFAULT NULL COMMENT '账户实际的总共容量',
  `user_volume` varchar(16) CHARACTER SET utf8 NOT NULL DEFAULT '0' COMMENT '开放给用户的总共容量',
  `used_volume` varchar(16) CHARACTER SET utf8 NOT NULL DEFAULT '0' COMMENT '用户已使用容量',
  PRIMARY KEY (`id`),
  KEY `fk_disk_user` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=59 ;

-- --------------------------------------------------------

--
-- 表的结构 `picture`
--

CREATE TABLE IF NOT EXISTS `picture` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `disk_id` mediumint(8) NOT NULL,
  `fid` varchar(16) CHARACTER SET utf8 NOT NULL,
  `upload_time` varchar(16) CHARACTER SET utf8 NOT NULL,
  `desc_name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `album_id` smallint(8) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_files_disk1` (`disk_id`),
  KEY `fk_picture_album1` (`album_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=102 ;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `email` varchar(60) CHARACTER SET utf8 NOT NULL,
  `username` varchar(60) CHARACTER SET utf8 DEFAULT NULL COMMENT '用户资料表',
  `password` varchar(32) CHARACTER SET utf8 NOT NULL,
  `reg_time` int(11) NOT NULL,
  `last_login` int(11) DEFAULT NULL,
  `last_ip` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `visit_count` smallint(5) NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT '0' COMMENT '0为普通用户，10为管理员',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=59 ;
