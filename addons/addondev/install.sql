CREATE TABLE IF NOT EXISTS `__PREFIX__addondev_gen` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NULL DEFAULT NULL COMMENT '模板名称' COLLATE 'utf8mb4_general_ci',
	`addon` VARCHAR(64) NOT NULL COMMENT '插件名' COLLATE 'utf8mb4_general_ci',
	`mtable` VARCHAR(32) NOT NULL COMMENT '模型表' COLLATE 'utf8mb4_general_ci',
	`controller` VARCHAR(32) NOT NULL COMMENT '模型控制器' COLLATE 'utf8mb4_general_ci',
	`model` VARCHAR(32) NULL DEFAULT NULL COMMENT '模型' COLLATE 'utf8mb4_general_ci',
	`fields` VARCHAR(255) NULL DEFAULT NULL COMMENT '模型可见字段' COLLATE 'utf8mb4_general_ci',
	`menu_switch` TINYINT(4) NULL DEFAULT '1' COMMENT '生成菜单:0=否,1=是',
	`delete_switch` TINYINT(4) NULL DEFAULT '0' COMMENT '删除模式:0=否,1=是',
	`import_switch` TINYINT(4) NULL DEFAULT '0' COMMENT '导入功能:0=否,1=是',
	`local_switch` TINYINT(1) NULL DEFAULT 1 COMMENT '模型目录:0=common,1=admin',
	`tree_switch` TINYINT(4) NULL DEFAULT '0' COMMENT '树视图:0=否,1=是',
    `tagcontroller` VARCHAR(255) NULL DEFAULT NULL COMMENT 'tag控制器' COLLATE 'utf8mb4_general_ci',
	`relation` TEXT(65535) NULL DEFAULT NULL COMMENT '关联表' COLLATE 'utf8mb4_general_ci',
	`relationmodel` TEXT(65535) NULL DEFAULT NULL COMMENT '关联模型' COLLATE 'utf8mb4_general_ci',
	`relationforeignkey` TEXT(65535) NULL DEFAULT NULL COMMENT '主表外键' COLLATE 'utf8mb4_general_ci',
	`relationprimarykey` TEXT(65535) NULL DEFAULT NULL COMMENT '关联表主键' COLLATE 'utf8mb4_general_ci',
	`relationcontroller` TEXT(65535) NULL DEFAULT NULL COMMENT '关联控制器' COLLATE 'utf8mb4_general_ci',
	`relationmode` TEXT(65535) NULL DEFAULT NULL COMMENT '关联模式' COLLATE 'utf8mb4_general_ci',
	`relationfields` TEXT(65535) NULL DEFAULT NULL COMMENT '关联表可见字段' COLLATE 'utf8mb4_general_ci',
	`selectpagefield` TEXT(65535) NULL DEFAULT NULL COMMENT '关联选项字段' COLLATE 'utf8mb4_general_ci',
	`headingfilterfield` TEXT(65535) NULL DEFAULT NULL COMMENT '顶部Tab页字段' COLLATE 'utf8mb4_general_ci',
	`ignorefields` VARCHAR(255) NULL DEFAULT NULL COMMENT '排除字段' COLLATE 'utf8mb4_general_ci',
	`setcheckboxsuffix` VARCHAR(255) NULL DEFAULT 'data,state,status' COMMENT '复选框后缀' COLLATE 'utf8mb4_general_ci',
	`enumradiosuffix` VARCHAR(255) NULL DEFAULT 'data,state,status' COMMENT '单选框后缀' COLLATE 'utf8mb4_general_ci',
	`imagefield` VARCHAR(255) NULL DEFAULT 'image,images,avatar,avatars' COMMENT '图片后缀' COLLATE 'utf8mb4_general_ci',
	`filefield` VARCHAR(255) NULL DEFAULT 'file,files' COMMENT '文件后缀' COLLATE 'utf8mb4_general_ci',
	`tagsuffix` VARCHAR(255) NULL DEFAULT 'tag,tags' COMMENT '标签后缀' COLLATE 'utf8mb4_general_ci',
	`intdatesuffix` VARCHAR(255) NULL DEFAULT 'time' COMMENT '日期后缀' COLLATE 'utf8mb4_general_ci',
	`switchsuffix` VARCHAR(255) NULL DEFAULT 'switch' COMMENT '开关后缀' COLLATE 'utf8mb4_general_ci',
	`editorsuffix` VARCHAR(255) NULL DEFAULT 'content' COMMENT '富文本编辑器' COLLATE 'utf8mb4_general_ci',
	`citysuffix` VARCHAR(255) NULL DEFAULT 'city' COMMENT '城市后缀' COLLATE 'utf8mb4_general_ci',
	`jsonsuffix` VARCHAR(255) NULL DEFAULT 'json' COMMENT 'JSON配置后缀' COLLATE 'utf8mb4_general_ci',
	`selectpagesuffix` VARCHAR(255) NULL DEFAULT '_id,_ids' COMMENT 'selectpage后缀' COLLATE 'utf8mb4_general_ci',
	`selectpagessuffix` VARCHAR(255) NULL DEFAULT '_ids' COMMENT 'selectpage多选后缀' COLLATE 'utf8mb4_general_ci',
	`sortfield` VARCHAR(255) NULL DEFAULT 'weigh' COMMENT '排序字段' COLLATE 'utf8mb4_general_ci',
	`editorclass` VARCHAR(255) NULL DEFAULT 'editor' COMMENT '编辑器Class' COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE
)
COMMENT='生成代码模板'
COLLATE='utf8mb4_general_ci'
ENGINE=innodb;

CREATE TABLE IF NOT EXISTS `__PREFIX__addondev_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gen_id` int(11) NOT NULL DEFAULT '0' COMMENT '生成ID',
  `filename` varchar(255) DEFAULT NULL COMMENT '文件',
  `filetype` enum('php','js','html','other') DEFAULT 'php' COMMENT '文件类型:php=php,js=js,html=html,other=其他',
  `code` text COMMENT '代码',
  `createtime` bigint(16) DEFAULT NULL COMMENT '保存时间',
  PRIMARY KEY (`id`),
  KEY `gen_id` (`gen_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='代码文件日志';

-- v1.1.2 --
ALTER TABLE `__PREFIX__addondev_gen` 
ADD COLUMN `local_switch` TINYINT(1) NULL DEFAULT 1 COMMENT '模型目录:0=common,1=admin' AFTER `import_switch`;