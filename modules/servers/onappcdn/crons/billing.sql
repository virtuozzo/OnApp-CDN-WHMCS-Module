--
-- Table structure for table `tblonappcdn_billing`
--

CREATE TABLE IF NOT EXISTS `tblonappcdn_billing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cost` varchar(255) NOT NULL,
  `edge_group_id` int(11) NOT NULL,
  `edge_group_label` varchar(255) NOT NULL,
  `stat_time` datetime NOT NULL,
  `traffic` varchar(255) NOT NULL,
  `cdn_resource_id` int(11) NOT NULL,
  `hosting_id` int(11) NOT NULL,
  `currency_rate` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_cdn_stat_time_for_edge_group` (`edge_group_id`,`stat_time`,`cdn_resource_id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=98 ;
