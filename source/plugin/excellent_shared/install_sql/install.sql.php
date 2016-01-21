<?php
	$predb="gcdiscuzforum_";
	$install_sql=<<<EOP
-- 新建分类名称表
DROP TABLE IF EXISTS `{$predb}forum_gc_type_thread`;
CREATE TABLE IF NOT EXISTS `{$predb}forum_gc_type_thread` (
  `type_id` mediumint(8) unsigned PRIMARY KEY AUTO_INCREMENT,
  `type_name` char(15) NOT NULL,
  `type_level` tinyint(4) NOT NULL
) ENGINE=INNODB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
-- InnoDB存储引擎建造分类表，只需要写关于删除帖子的触发器
-- 新建帖子分类表
-- 帖子可以同时属于多重分类，即该表是多对多的
-- 可以对level为30以上的分类做多重帖子分类，而30以下做树形分类
DROP TABLE IF EXISTS `{$predb}forum_gc_excellent_thread`;
CREATE TABLE IF NOT EXISTS `{$predb}forum_gc_excellent_thread` (
  `gcid` mediumint(8) unsigned PRIMARY KEY AUTO_INCREMENT,
  `tid` mediumint(8) unsigned NOT NULL,
  `type_id` mediumint(8) unsigned NOT NULL,
  `rate` text DEFAULT NULL,
FOREIGN KEY (`type_id` )
    REFERENCES `{$predb}forum_gc_type_thread` (`type_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=INNODB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
-- 新建触发器，当帖子删除之前，触发器删除与帖子相连关系
DROP TABLE IF EXISTS `type_beforedelete_on_thread`;
DROP TRIGGER IF EXISTS `type_beforedelete_on_thread`;
DELIMITER //
CREATE TRIGGER `type_beforedelete_on_thread` 
BEFORE DELETE ON `{$predb}forum_thread`
FOR EACH ROW
BEGIN 
	DELETE FROM `{$predb}forum_gc_excellent_thread` WHERE `tid`=OLD.tid;
END//
DELIMITER ;
-- 新建分类关系表
CREATE TABLE IF NOT EXISTS `{$predb}forum_gc_type_relation` (
  `father_type_id` mediumint(8) unsigned NOT NULL,
  `child_type_id` mediumint(8) unsigned NOT NULL UNIQUE,

FOREIGN KEY (`father_type_id` )
    REFERENCES `{$predb}forum_gc_type_thread` (`type_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
FOREIGN KEY (`child_type_id` )
    REFERENCES `{$predb}forum_gc_type_thread` (`type_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE

) ENGINE=INNODB DEFAULT CHARSET=utf8;	
-- 插入root节点(此为树形分类的起始节点，必须预先插入)
INSERT INTO `{$predb}forum_gc_type_thread`(`type_name`,`type_level`) VALUES ('root',0)
EOP;

echo $install_sql;
?>