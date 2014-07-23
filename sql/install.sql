CREATE TABLE `resources_rooms_order` (
  `resource_id` varchar(32) NOT NULL,
  `priority` int(5) NOT NULL,
  `checked` int(1) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  PRIMARY KEY (`resource_id`,`user_id`)
) 