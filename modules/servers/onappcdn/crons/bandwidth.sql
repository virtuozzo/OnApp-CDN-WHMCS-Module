--
-- Table structure for table `tblonappcdn_bandwidth`
--

CREATE TABLE IF NOT EXISTS `tblonappcdn_bandwidth` (
  `hosting_id` int(11) NOT NULL,
  `created_at` date NOT NULL,
  `cached` double NOT NULL,
  `non_cached` double NOT NULL,
  `aflexi_resource_id` int(11) NOT NULL,
  `cdn_hostname` varchar(100) NOT NULL,
  `resource_id` int(11) NOT NULL,
  UNIQUE KEY `created_at` (`created_at`,`aflexi_resource_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;