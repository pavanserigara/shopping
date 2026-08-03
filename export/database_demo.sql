-- LocalShopOS Production Database Dump with Demo Data
-- Generated: 2026-08-03 14:34:06
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `ad_views`;
CREATE TABLE `ad_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `viewed_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ad_id` (`ad_id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `viewed_at` (`viewed_at`),
  CONSTRAINT `ad_views_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ad_views_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('1', '1', '1', '2026-08-03 18:37:35');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('2', '1', '1', '2026-08-03 18:42:24');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('3', '1', '1', '2026-08-03 18:42:56');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('4', '1', '1', '2026-08-03 18:43:09');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('5', '1', '1', '2026-08-03 18:43:13');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('6', '1', '1', '2026-08-03 18:44:14');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('7', '1', '1', '2026-08-03 18:44:35');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('8', '1', '1', '2026-08-03 18:44:44');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('9', '1', '1', '2026-08-03 18:44:53');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('10', '1', '1', '2026-08-03 18:45:02');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('11', '1', '1', '2026-08-03 18:45:12');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('12', '1', '1', '2026-08-03 18:46:19');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('13', '1', '1', '2026-08-03 18:46:21');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('14', '1', '1', '2026-08-03 18:46:23');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('15', '1', '1', '2026-08-03 18:46:28');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('16', '1', '1', '2026-08-03 18:46:37');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('17', '1', '1', '2026-08-03 18:47:23');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('18', '1', '1', '2026-08-03 18:47:23');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('19', '1', '1', '2026-08-03 18:47:31');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('20', '1', '1', '2026-08-03 18:47:35');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('21', '1', '1', '2026-08-03 18:47:40');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('22', '1', '1', '2026-08-03 18:47:49');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('23', '1', '1', '2026-08-03 18:47:58');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('24', '1', '1', '2026-08-03 18:48:46');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('25', '1', '1', '2026-08-03 18:48:46');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('26', '1', '1', '2026-08-03 18:48:47');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('27', '1', '1', '2026-08-03 18:49:01');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('28', '1', '1', '2026-08-03 18:49:10');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('29', '1', '1', '2026-08-03 18:49:19');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('30', '1', '1', '2026-08-03 18:49:28');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('31', '1', '1', '2026-08-03 18:49:37');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('32', '1', '1', '2026-08-03 19:06:06');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('33', '1', '1', '2026-08-03 19:06:15');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('34', '1', '1', '2026-08-03 19:06:24');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('35', '1', '1', '2026-08-03 19:06:33');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('36', '2', '1', '2026-08-03 19:06:45');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('37', '1', '1', '2026-08-03 19:06:49');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('38', '2', '1', '2026-08-03 19:06:59');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('39', '1', '1', '2026-08-03 19:07:03');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('40', '2', '1', '2026-08-03 19:07:12');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('41', '1', '1', '2026-08-03 19:07:17');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('42', '2', '1', '2026-08-03 19:07:26');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('43', '1', '1', '2026-08-03 19:07:30');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('44', '2', '1', '2026-08-03 19:07:39');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('45', '2', '1', '2026-08-03 19:07:50');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('46', '1', '1', '2026-08-03 19:07:55');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('47', '2', '1', '2026-08-03 19:08:03');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('48', '1', '1', '2026-08-03 19:08:08');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('49', '2', '1', '2026-08-03 19:09:09');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('50', '1', '1', '2026-08-03 19:09:14');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('51', '2', '1', '2026-08-03 19:12:11');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('52', '3', '1', '2026-08-03 19:12:13');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('53', '2', '1', '2026-08-03 19:12:18');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('54', '1', '1', '2026-08-03 19:12:22');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('55', '2', '1', '2026-08-03 19:12:31');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('56', '1', '1', '2026-08-03 19:12:36');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('57', '2', '1', '2026-08-03 19:12:45');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('58', '1', '1', '2026-08-03 19:12:49');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('59', '3', '1', '2026-08-03 19:12:57');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('60', '2', '1', '2026-08-03 19:13:01');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('61', '3', '1', '2026-08-03 19:13:04');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('62', '2', '1', '2026-08-03 19:13:08');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('63', '1', '1', '2026-08-03 19:13:13');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('64', '2', '1', '2026-08-03 19:17:20');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('65', '1', '1', '2026-08-03 19:17:24');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('66', '10', '5', '2026-08-03 19:17:44');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('67', '10', '5', '2026-08-03 19:17:58');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('68', '10', '5', '2026-08-03 19:18:11');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('69', '10', '5', '2026-08-03 19:18:25');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('70', '10', '5', '2026-08-03 19:18:38');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('71', '10', '5', '2026-08-03 19:18:52');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('72', '10', '5', '2026-08-03 19:19:05');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('73', '10', '5', '2026-08-03 19:19:19');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('74', '10', '5', '2026-08-03 19:19:33');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('75', '10', '5', '2026-08-03 19:19:51');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('76', '10', '5', '2026-08-03 19:20:00');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('77', '10', '5', '2026-08-03 19:20:13');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('78', '3', '1', '2026-08-03 19:20:54');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('79', '2', '1', '2026-08-03 19:21:03');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('80', '1', '1', '2026-08-03 19:21:08');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('81', '2', '1', '2026-08-03 19:21:22');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('82', '1', '1', '2026-08-03 19:21:26');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('83', '2', '1', '2026-08-03 19:21:40');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('84', '1', '1', '2026-08-03 19:21:44');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('85', '2', '1', '2026-08-03 19:21:58');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('86', '1', '1', '2026-08-03 19:32:30');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('87', '2', '1', '2026-08-03 19:32:45');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('88', '1', '1', '2026-08-03 19:32:48');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('89', '2', '1', '2026-08-03 19:33:03');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('90', '1', '1', '2026-08-03 19:33:06');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('91', '2', '1', '2026-08-03 19:33:20');
INSERT INTO `ad_views` (`id`, `ad_id`, `tenant_id`, `viewed_at`) VALUES ('92', '1', '1', '2026-08-03 19:33:24');

DROP TABLE IF EXISTS `admin_action_log`;
CREATE TABLE `admin_action_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actor_admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_tenant_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `actor_admin_id` (`actor_admin_id`),
  KEY `target_tenant_id` (`target_tenant_id`),
  CONSTRAINT `admin_action_log_ibfk_1` FOREIGN KEY (`actor_admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admin_action_log_ibfk_2` FOREIGN KEY (`target_tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_action_log` (`id`, `actor_admin_id`, `action`, `target_tenant_id`, `created_at`) VALUES ('1', '1', 'reset_password_user_2', '1', '2026-08-03 18:52:15');
INSERT INTO `admin_action_log` (`id`, `actor_admin_id`, `action`, `target_tenant_id`, `created_at`) VALUES ('2', '1', 'reset_password_user_2', '1', '2026-08-03 18:52:18');
INSERT INTO `admin_action_log` (`id`, `actor_admin_id`, `action`, `target_tenant_id`, `created_at`) VALUES ('3', '1', 'reset_password_user_2', '1', '2026-08-03 19:01:28');
INSERT INTO `admin_action_log` (`id`, `actor_admin_id`, `action`, `target_tenant_id`, `created_at`) VALUES ('4', '1', 'reset_password_user_2', '1', '2026-08-03 19:01:29');
INSERT INTO `admin_action_log` (`id`, `actor_admin_id`, `action`, `target_tenant_id`, `created_at`) VALUES ('5', '1', 'reset_password_user_2', '1', '2026-08-03 19:02:17');

DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL,
  `role` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `tenant_id` (`tenant_id`),
  KEY `email_2` (`email`),
  CONSTRAINT `admin_users_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_users` (`id`, `tenant_id`, `role`, `email`, `password_hash`, `created_at`, `is_active`) VALUES ('1', NULL, 'super_admin', 'admin@localshopos.com', '$2y$10$MQhmuTpmsi86adXmu8yzZOHIyIWsR1AJafKet8lyWoxWPWhU56Qci', '2026-08-03 18:35:13', '1');
INSERT INTO `admin_users` (`id`, `tenant_id`, `role`, `email`, `password_hash`, `created_at`, `is_active`) VALUES ('2', '1', 'tenant_admin', 'ramesh@kirana.com', '$2y$10$CFFT6.HrDoZTCxdPShfQBuUyMOKHc4B2bA8yLDBSX90HuVZ4Zi0ze', '2026-08-03 18:35:34', '1');
INSERT INTO `admin_users` (`id`, `tenant_id`, `role`, `email`, `password_hash`, `created_at`, `is_active`) VALUES ('4', '2', 'tenant_admin', 'contact@freshfarm.com', '$2y$10$CFFT6.HrDoZTCxdPShfQBuUyMOKHc4B2bA8yLDBSX90HuVZ4Zi0ze', '2026-08-03 19:15:40', '1');
INSERT INTO `admin_users` (`id`, `tenant_id`, `role`, `email`, `password_hash`, `created_at`, `is_active`) VALUES ('5', '3', 'tenant_admin', 'info@guptabakery.com', '$2y$10$CFFT6.HrDoZTCxdPShfQBuUyMOKHc4B2bA8yLDBSX90HuVZ4Zi0ze', '2026-08-03 19:15:57', '1');
INSERT INTO `admin_users` (`id`, `tenant_id`, `role`, `email`, `password_hash`, `created_at`, `is_active`) VALUES ('6', '4', 'tenant_admin', 'sales@apexelectronics.com', '$2y$10$CFFT6.HrDoZTCxdPShfQBuUyMOKHc4B2bA8yLDBSX90HuVZ4Zi0ze', '2026-08-03 19:15:58', '1');
INSERT INTO `admin_users` (`id`, `tenant_id`, `role`, `email`, `password_hash`, `created_at`, `is_active`) VALUES ('7', '5', 'tenant_admin', 'support@voguetrends.in', '$2y$10$CFFT6.HrDoZTCxdPShfQBuUyMOKHc4B2bA8yLDBSX90HuVZ4Zi0ze', '2026-08-03 19:15:58', '1');
INSERT INTO `admin_users` (`id`, `tenant_id`, `role`, `email`, `password_hash`, `created_at`, `is_active`) VALUES ('8', '6', 'tenant_admin', 'care@greenleafmeds.com', '$2y$10$CFFT6.HrDoZTCxdPShfQBuUyMOKHc4B2bA8yLDBSX90HuVZ4Zi0ze', '2026-08-03 19:15:58', '1');
INSERT INTO `admin_users` (`id`, `tenant_id`, `role`, `email`, `password_hash`, `created_at`, `is_active`) VALUES ('9', '7', 'tenant_admin', 'sales@royalfootwear.in', '$2y$10$CFFT6.HrDoZTCxdPShfQBuUyMOKHc4B2bA8yLDBSX90HuVZ4Zi0ze', '2026-08-03 19:15:59', '1');
INSERT INTO `admin_users` (`id`, `tenant_id`, `role`, `email`, `password_hash`, `created_at`, `is_active`) VALUES ('10', '8', 'tenant_admin', 'hello@spicegarden.com', '$2y$10$CFFT6.HrDoZTCxdPShfQBuUyMOKHc4B2bA8yLDBSX90HuVZ4Zi0ze', '2026-08-03 19:15:59', '1');
INSERT INTO `admin_users` (`id`, `tenant_id`, `role`, `email`, `password_hash`, `created_at`, `is_active`) VALUES ('11', '9', 'tenant_admin', 'sales@urbannest.in', '$2y$10$CFFT6.HrDoZTCxdPShfQBuUyMOKHc4B2bA8yLDBSX90HuVZ4Zi0ze', '2026-08-03 19:15:59', '1');
INSERT INTO `admin_users` (`id`, `tenant_id`, `role`, `email`, `password_hash`, `created_at`, `is_active`) VALUES ('12', '10', 'tenant_admin', 'hello@petjoycare.com', '$2y$10$CFFT6.HrDoZTCxdPShfQBuUyMOKHc4B2bA8yLDBSX90HuVZ4Zi0ze', '2026-08-03 19:15:59', '1');

DROP TABLE IF EXISTS `ads`;
CREATE TABLE `ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` enum('banner','mid_page') NOT NULL DEFAULT 'banner',
  `image_url` varchar(550) NOT NULL,
  `link_url` varchar(550) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `tenant_id_2` (`tenant_id`,`type`,`is_active`),
  CONSTRAINT `ads_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('1', '1', 'Festival Special Sale', 'banner', '/uploads/1/ads/ad_1785762451_6a7092935fbf6.png', 'http://example.com/sale', '1', NULL, NULL, '2026-08-03 18:37:31');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('2', '1', 'Test demo', 'banner', '/uploads/1/ads/ad_1785764197_6a7099651d2d7.jpg', '', '1', NULL, NULL, '2026-08-03 19:06:37');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('3', '1', 'Demo bottom', 'mid_page', '/uploads/1/ads/ad_1785764257_6a7099a15b49b.jpg', '', '1', NULL, NULL, '2026-08-03 19:07:37');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('4', '2', '100% Certified Organic Harvest', 'banner', 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:40');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('5', '2', 'Fresh Green Leafy Veggies Special', 'mid_page', 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:40');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('6', '3', 'Freshly Baked Daily Delights', 'banner', 'https://images.unsplash.com/photo-1517433670267-08bbd4be890f?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:57');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('7', '3', 'Desi Ghee Sweets Festival Offer', 'mid_page', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:57');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('8', '4', 'Upgrade Your Tech Accessories Today', 'banner', 'https://images.unsplash.com/photo-1498049860654-af1a5c57abf3?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:58');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('9', '4', 'Flat 20% Off on TWS Earbuds & Speakers', 'mid_page', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:58');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('10', '5', 'New Festive Fashion Collection 2026', 'banner', 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:58');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('11', '5', 'Buy 2 Get 1 Free on Cotton Tees', 'mid_page', 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:58');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('12', '6', 'Essential Health & Immunity Essentials', 'banner', 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:59');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('13', '6', 'Flat 15% Off Health Supplements', 'mid_page', 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:59');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('14', '7', 'Step Up In Style — Premium Footwear', 'banner', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:59');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('15', '7', 'Flat ₹300 Cashback on Sneakers', 'mid_page', 'https://images.unsplash.com/photo-1552346154-21d32810aba3?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:59');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('16', '8', 'Pure Organic Spices Straight From Farms', 'banner', 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:59');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('17', '8', 'Special Combo Offer on Kitchen Spices', 'mid_page', 'https://images.unsplash.com/photo-1509358271058-acd02cc93898?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:59');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('18', '9', 'Transform Your Living Space With Modern Decor', 'banner', 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:59');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('19', '9', 'Flat 25% Off on Nordic Lighting Range', 'mid_page', 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:59');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('20', '10', 'Everything Your Happy Pets Deserve', 'banner', 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:59');
INSERT INTO `ads` (`id`, `tenant_id`, `title`, `type`, `image_url`, `link_url`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('21', '10', 'Flat 20% Off Premium Pet Foods', 'mid_page', 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=1000&auto=format&fit=crop&q=80', NULL, '1', NULL, NULL, '2026-08-03 19:15:59');

DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percent','flat') NOT NULL DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_id` (`tenant_id`,`code`),
  CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `coupons` (`id`, `tenant_id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`, `is_active`, `created_at`) VALUES ('1', '1', 'WELCOME10', 'percent', '10.00', '200.00', NULL, '1', '2026-08-03 18:35:13');
INSERT INTO `coupons` (`id`, `tenant_id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`, `is_active`, `created_at`) VALUES ('2', '1', 'SAVE50', 'flat', '50.00', '500.00', NULL, '1', '2026-08-03 18:35:13');
INSERT INTO `coupons` (`id`, `tenant_id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`, `is_active`, `created_at`) VALUES ('3', '1', 'WELCOME50', 'flat', '50.00', '200.00', NULL, '1', '2026-08-03 18:38:07');
INSERT INTO `coupons` (`id`, `tenant_id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`, `is_active`, `created_at`) VALUES ('4', '2', 'WELCOME10', 'percent', '10.00', '200.00', NULL, '1', '2026-08-03 19:15:40');
INSERT INTO `coupons` (`id`, `tenant_id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`, `is_active`, `created_at`) VALUES ('5', '3', 'WELCOME10', 'percent', '10.00', '200.00', NULL, '1', '2026-08-03 19:15:57');
INSERT INTO `coupons` (`id`, `tenant_id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`, `is_active`, `created_at`) VALUES ('6', '4', 'WELCOME10', 'percent', '10.00', '200.00', NULL, '1', '2026-08-03 19:15:58');
INSERT INTO `coupons` (`id`, `tenant_id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`, `is_active`, `created_at`) VALUES ('7', '5', 'WELCOME10', 'percent', '10.00', '200.00', NULL, '1', '2026-08-03 19:15:58');
INSERT INTO `coupons` (`id`, `tenant_id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`, `is_active`, `created_at`) VALUES ('8', '6', 'WELCOME10', 'percent', '10.00', '200.00', NULL, '1', '2026-08-03 19:15:59');
INSERT INTO `coupons` (`id`, `tenant_id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`, `is_active`, `created_at`) VALUES ('9', '7', 'WELCOME10', 'percent', '10.00', '200.00', NULL, '1', '2026-08-03 19:15:59');
INSERT INTO `coupons` (`id`, `tenant_id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`, `is_active`, `created_at`) VALUES ('10', '8', 'WELCOME10', 'percent', '10.00', '200.00', NULL, '1', '2026-08-03 19:15:59');
INSERT INTO `coupons` (`id`, `tenant_id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`, `is_active`, `created_at`) VALUES ('11', '9', 'WELCOME10', 'percent', '10.00', '200.00', NULL, '1', '2026-08-03 19:15:59');
INSERT INTO `coupons` (`id`, `tenant_id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`, `is_active`, `created_at`) VALUES ('12', '10', 'WELCOME10', 'percent', '10.00', '200.00', NULL, '1', '2026-08-03 19:16:00');

DROP TABLE IF EXISTS `global_ad_views`;
CREATE TABLE `global_ad_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `global_ad_id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `viewed_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `global_ad_id` (`global_ad_id`),
  KEY `viewed_at` (`viewed_at`),
  CONSTRAINT `global_ad_views_ibfk_1` FOREIGN KEY (`global_ad_id`) REFERENCES `global_ads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('1', '1', '1', '2026-08-03 18:44:10');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('2', '1', '2', '2026-08-03 18:44:17');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('3', '1', '3', '2026-08-03 18:44:22');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('4', '1', '1', '2026-08-03 18:44:31');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('5', '1', '1', '2026-08-03 18:44:40');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('6', '1', '1', '2026-08-03 18:44:49');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('7', '1', '1', '2026-08-03 18:44:58');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('8', '1', '1', '2026-08-03 18:45:07');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('9', '1', '1', '2026-08-03 18:45:16');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('10', '1', '1', '2026-08-03 18:46:20');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('11', '1', '1', '2026-08-03 18:46:22');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('12', '1', '1', '2026-08-03 18:46:25');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('13', '1', '1', '2026-08-03 18:46:30');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('14', '1', '1', '2026-08-03 18:46:35');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('15', '1', '1', '2026-08-03 18:46:42');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('16', '1', '1', '2026-08-03 18:47:23');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('17', '1', '1', '2026-08-03 18:47:23');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('18', '1', '1', '2026-08-03 18:47:28');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('19', '1', '1', '2026-08-03 18:47:47');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('20', '1', '1', '2026-08-03 18:47:54');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('21', '1', '1', '2026-08-03 18:48:46');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('22', '1', '1', '2026-08-03 18:48:47');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('23', '1', '1', '2026-08-03 18:48:55');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('24', '1', '1', '2026-08-03 18:48:58');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('25', '1', '1', '2026-08-03 18:49:06');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('26', '1', '1', '2026-08-03 18:49:15');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('27', '1', '1', '2026-08-03 18:49:24');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('28', '1', '1', '2026-08-03 18:49:33');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('29', '1', '1', '2026-08-03 19:06:01');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('30', '1', '1', '2026-08-03 19:06:11');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('31', '1', '1', '2026-08-03 19:06:20');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('32', '1', '1', '2026-08-03 19:06:29');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('33', '1', '1', '2026-08-03 19:06:38');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('34', '1', '1', '2026-08-03 19:06:40');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('35', '1', '1', '2026-08-03 19:06:54');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('36', '1', '1', '2026-08-03 19:07:08');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('37', '1', '1', '2026-08-03 19:07:21');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('38', '1', '1', '2026-08-03 19:07:35');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('39', '1', '1', '2026-08-03 19:07:42');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('40', '1', '1', '2026-08-03 19:07:45');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('41', '1', '1', '2026-08-03 19:07:59');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('42', '1', '1', '2026-08-03 19:08:12');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('43', '1', '1', '2026-08-03 19:09:04');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('44', '1', '1', '2026-08-03 19:09:18');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('45', '1', '1', '2026-08-03 19:12:13');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('46', '1', '1', '2026-08-03 19:12:27');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('47', '1', '1', '2026-08-03 19:12:40');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('48', '1', '1', '2026-08-03 19:12:54');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('49', '1', '1', '2026-08-03 19:12:57');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('50', '1', '1', '2026-08-03 19:13:03');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('51', '1', '1', '2026-08-03 19:17:18');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('52', '2', '5', '2026-08-03 19:17:35');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('53', '3', '5', '2026-08-03 19:17:35');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('54', '1', '5', '2026-08-03 19:17:40');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('55', '2', '5', '2026-08-03 19:17:49');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('56', '3', '5', '2026-08-03 19:17:51');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('57', '1', '5', '2026-08-03 19:17:53');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('58', '3', '5', '2026-08-03 19:17:57');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('59', '3', '5', '2026-08-03 19:17:58');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('60', '3', '5', '2026-08-03 19:17:58');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('61', '3', '5', '2026-08-03 19:17:59');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('62', '3', '5', '2026-08-03 19:17:59');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('63', '3', '5', '2026-08-03 19:17:59');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('64', '3', '5', '2026-08-03 19:17:59');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('65', '3', '5', '2026-08-03 19:18:00');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('66', '3', '5', '2026-08-03 19:18:00');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('67', '2', '5', '2026-08-03 19:18:02');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('68', '1', '5', '2026-08-03 19:18:07');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('69', '2', '5', '2026-08-03 19:18:16');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('70', '1', '5', '2026-08-03 19:18:20');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('71', '2', '5', '2026-08-03 19:18:29');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('72', '1', '5', '2026-08-03 19:18:34');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('73', '3', '5', '2026-08-03 19:18:36');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('74', '2', '5', '2026-08-03 19:18:43');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('75', '1', '5', '2026-08-03 19:18:47');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('76', '2', '5', '2026-08-03 19:18:56');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('77', '1', '5', '2026-08-03 19:19:01');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('78', '2', '5', '2026-08-03 19:19:10');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('79', '1', '5', '2026-08-03 19:19:14');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('80', '2', '5', '2026-08-03 19:19:24');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('81', '1', '5', '2026-08-03 19:19:28');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('82', '2', '5', '2026-08-03 19:19:37');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('83', '1', '5', '2026-08-03 19:19:51');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('84', '2', '5', '2026-08-03 19:19:52');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('85', '1', '5', '2026-08-03 19:19:55');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('86', '2', '5', '2026-08-03 19:20:04');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('87', '1', '5', '2026-08-03 19:20:09');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('88', '2', '5', '2026-08-03 19:20:48');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('89', '1', '5', '2026-08-03 19:20:49');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('90', '2', '1', '2026-08-03 19:20:54');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('91', '3', '1', '2026-08-03 19:20:54');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('92', '1', '1', '2026-08-03 19:20:59');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('93', '2', '1', '2026-08-03 19:21:12');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('94', '1', '1', '2026-08-03 19:21:17');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('95', '2', '1', '2026-08-03 19:21:31');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('96', '1', '1', '2026-08-03 19:21:35');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('97', '2', '1', '2026-08-03 19:21:49');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('98', '1', '1', '2026-08-03 19:21:53');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('99', '2', '1', '2026-08-03 19:32:35');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('100', '1', '1', '2026-08-03 19:32:39');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('101', '2', '1', '2026-08-03 19:32:53');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('102', '1', '1', '2026-08-03 19:32:57');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('103', '2', '1', '2026-08-03 19:33:11');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('104', '1', '1', '2026-08-03 19:33:15');
INSERT INTO `global_ad_views` (`id`, `global_ad_id`, `tenant_id`, `viewed_at`) VALUES ('105', '2', '1', '2026-08-03 19:33:29');

DROP TABLE IF EXISTS `global_ads`;
CREATE TABLE `global_ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `image_url` varchar(550) NOT NULL,
  `link_url` varchar(550) DEFAULT NULL,
  `placement` enum('banner','mid_page') NOT NULL DEFAULT 'banner',
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `placement` (`placement`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `global_ads` (`id`, `title`, `image_url`, `link_url`, `placement`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('1', 'demo', '/uploads/global_ads/global_ad_1785762846_6a70941ecbe5f.png', '', 'banner', '1', NULL, NULL, '2026-08-03 18:44:06');
INSERT INTO `global_ads` (`id`, `title`, `image_url`, `link_url`, `placement`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('2', 'Platform Super Sale 2026', 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1000&auto=format&fit=crop&q=80', '/shops.php', 'banner', '1', NULL, NULL, '2026-08-03 19:16:00');
INSERT INTO `global_ads` (`id`, `title`, `image_url`, `link_url`, `placement`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES ('3', 'LocalShopOS Instant Cashback Deal', 'https://images.unsplash.com/photo-1556742049-0a670f4a4591?w=1000&auto=format&fit=crop&q=80', '/shops.php', 'mid_page', '1', NULL, NULL, '2026-08-03 19:16:00');

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_order` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('1', '1', '1', '2', '245.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('2', '2', '3', '1', '28.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('3', '3', '6', '1', '180.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('4', '3', '7', '1', '60.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('5', '4', '20', '1', '520.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('6', '4', '21', '1', '650.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('7', '5', '25', '1', '899.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('8', '5', '26', '1', '1499.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('9', '6', '30', '1', '499.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('10', '6', '31', '1', '1299.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('11', '7', '34', '1', '380.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('12', '7', '35', '1', '450.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('13', '8', '38', '1', '1899.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('14', '8', '39', '1', '1299.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('15', '9', '42', '1', '320.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('16', '9', '43', '1', '160.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('17', '10', '46', '1', '799.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('18', '10', '47', '1', '1299.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('19', '11', '50', '1', '750.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES ('20', '11', '51', '1', '420.00');

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `customer_contact` varchar(100) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `coupon_code` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'new',
  `accepted_at` datetime DEFAULT NULL,
  `preparing_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `delivery_type` varchar(20) DEFAULT 'delivery',
  `delivery_address` text DEFAULT NULL,
  `delivery_contact` varchar(50) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT 'cod',
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `tenant_id_2` (`tenant_id`,`status`),
  KEY `tenant_id_3` (`tenant_id`,`created_at`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`id`, `tenant_id`, `customer_contact`, `total`, `discount_amount`, `coupon_code`, `status`, `accepted_at`, `preparing_at`, `completed_at`, `delivery_type`, `delivery_address`, `delivery_contact`, `payment_mode`, `delivery_fee`, `created_at`) VALUES ('1', '1', '+919876543210', '490.00', '0.00', NULL, 'accepted', '2026-08-03 18:37:19', NULL, NULL, 'delivery', '123 Main Street, Bangalore', '+919876543210', 'cod', '0.00', '2026-08-03 18:37:08');
INSERT INTO `orders` (`id`, `tenant_id`, `customer_contact`, `total`, `discount_amount`, `coupon_code`, `status`, `accepted_at`, `preparing_at`, `completed_at`, `delivery_type`, `delivery_address`, `delivery_contact`, `payment_mode`, `delivery_fee`, `created_at`) VALUES ('2', '1', '7676446647', '28.00', '0.00', NULL, 'completed', '2026-08-03 19:03:42', '2026-08-03 19:03:46', '2026-08-03 19:04:03', 'pickup', '', '7676446647', 'upi', '0.00', '2026-08-03 18:46:38');
INSERT INTO `orders` (`id`, `tenant_id`, `customer_contact`, `total`, `discount_amount`, `coupon_code`, `status`, `accepted_at`, `preparing_at`, `completed_at`, `delivery_type`, `delivery_address`, `delivery_contact`, `payment_mode`, `delivery_fee`, `created_at`) VALUES ('3', '2', '919876543210', '240.00', '0.00', NULL, 'completed', NULL, NULL, NULL, 'delivery', NULL, NULL, 'cod', '0.00', '2026-08-03 19:15:57');
INSERT INTO `orders` (`id`, `tenant_id`, `customer_contact`, `total`, `discount_amount`, `coupon_code`, `status`, `accepted_at`, `preparing_at`, `completed_at`, `delivery_type`, `delivery_address`, `delivery_contact`, `payment_mode`, `delivery_fee`, `created_at`) VALUES ('4', '3', '919876543210', '1170.00', '0.00', NULL, 'completed', NULL, NULL, NULL, 'delivery', NULL, NULL, 'cod', '0.00', '2026-08-03 19:15:57');
INSERT INTO `orders` (`id`, `tenant_id`, `customer_contact`, `total`, `discount_amount`, `coupon_code`, `status`, `accepted_at`, `preparing_at`, `completed_at`, `delivery_type`, `delivery_address`, `delivery_contact`, `payment_mode`, `delivery_fee`, `created_at`) VALUES ('5', '4', '919876543210', '2398.00', '0.00', NULL, 'completed', NULL, NULL, NULL, 'delivery', NULL, NULL, 'cod', '0.00', '2026-08-03 19:15:58');
INSERT INTO `orders` (`id`, `tenant_id`, `customer_contact`, `total`, `discount_amount`, `coupon_code`, `status`, `accepted_at`, `preparing_at`, `completed_at`, `delivery_type`, `delivery_address`, `delivery_contact`, `payment_mode`, `delivery_fee`, `created_at`) VALUES ('6', '5', '919876543210', '1798.00', '0.00', NULL, 'completed', NULL, NULL, NULL, 'delivery', NULL, NULL, 'cod', '0.00', '2026-08-03 19:15:58');
INSERT INTO `orders` (`id`, `tenant_id`, `customer_contact`, `total`, `discount_amount`, `coupon_code`, `status`, `accepted_at`, `preparing_at`, `completed_at`, `delivery_type`, `delivery_address`, `delivery_contact`, `payment_mode`, `delivery_fee`, `created_at`) VALUES ('7', '6', '919876543210', '830.00', '0.00', NULL, 'completed', NULL, NULL, NULL, 'delivery', NULL, NULL, 'cod', '0.00', '2026-08-03 19:15:59');
INSERT INTO `orders` (`id`, `tenant_id`, `customer_contact`, `total`, `discount_amount`, `coupon_code`, `status`, `accepted_at`, `preparing_at`, `completed_at`, `delivery_type`, `delivery_address`, `delivery_contact`, `payment_mode`, `delivery_fee`, `created_at`) VALUES ('8', '7', '919876543210', '3198.00', '0.00', NULL, 'completed', NULL, NULL, NULL, 'delivery', NULL, NULL, 'cod', '0.00', '2026-08-03 19:15:59');
INSERT INTO `orders` (`id`, `tenant_id`, `customer_contact`, `total`, `discount_amount`, `coupon_code`, `status`, `accepted_at`, `preparing_at`, `completed_at`, `delivery_type`, `delivery_address`, `delivery_contact`, `payment_mode`, `delivery_fee`, `created_at`) VALUES ('9', '8', '919876543210', '480.00', '0.00', NULL, 'completed', NULL, NULL, NULL, 'delivery', NULL, NULL, 'cod', '0.00', '2026-08-03 19:15:59');
INSERT INTO `orders` (`id`, `tenant_id`, `customer_contact`, `total`, `discount_amount`, `coupon_code`, `status`, `accepted_at`, `preparing_at`, `completed_at`, `delivery_type`, `delivery_address`, `delivery_contact`, `payment_mode`, `delivery_fee`, `created_at`) VALUES ('10', '9', '919876543210', '2098.00', '0.00', NULL, 'completed', NULL, NULL, NULL, 'delivery', NULL, NULL, 'cod', '0.00', '2026-08-03 19:15:59');
INSERT INTO `orders` (`id`, `tenant_id`, `customer_contact`, `total`, `discount_amount`, `coupon_code`, `status`, `accepted_at`, `preparing_at`, `completed_at`, `delivery_type`, `delivery_address`, `delivery_contact`, `payment_mode`, `delivery_fee`, `created_at`) VALUES ('11', '10', '919876543210', '1170.00', '0.00', NULL, 'completed', NULL, NULL, NULL, 'delivery', NULL, NULL, 'cod', '0.00', '2026-08-03 19:16:00');

DROP TABLE IF EXISTS `payment_log`;
CREATE TABLE `payment_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `notes` varchar(550) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `payment_log_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_log_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_log` (`id`, `tenant_id`, `plan_id`, `amount`, `payment_date`, `notes`, `created_at`) VALUES ('1', '1', '2', '499.00', '2026-08-03', 'Monthly subscription renewal via GPay', '2026-08-03 18:35:13');
INSERT INTO `payment_log` (`id`, `tenant_id`, `plan_id`, `amount`, `payment_date`, `notes`, `created_at`) VALUES ('2', '3', '3', '999.00', '2026-08-03', 'Annual subscription upfront payment', '2026-08-03 18:35:13');

DROP TABLE IF EXISTS `plan_features`;
CREATE TABLE `plan_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `feature_key` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  KEY `feature_key` (`feature_key`),
  CONSTRAINT `plan_features_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('3', '2', 'product_management');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('4', '2', 'product_image_gallery');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('5', '2', 'order_management');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('6', '2', 'sales_reports');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('7', '2', 'shop_ads');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('15', '1', 'product_management');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('16', '1', 'order_management');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('17', '1', 'coupons');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('18', '3', 'product_management');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('19', '3', 'product_image_gallery');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('20', '3', 'order_management');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('21', '3', 'sales_reports');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('22', '3', 'shop_ads');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('23', '3', 'ad_analytics');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('24', '3', 'shop_logo_upload');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('25', '3', 'shop_directory_listing');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('26', '3', 'qr_code_generator');
INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`) VALUES ('27', '3', 'coupons');

DROP TABLE IF EXISTS `plans`;
CREATE TABLE `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `billing_period` varchar(50) DEFAULT 'monthly',
  `product_limit` int(11) DEFAULT 30,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `plans` (`id`, `name`, `price`, `billing_period`, `product_limit`, `is_default`, `created_at`) VALUES ('1', 'Free Starter (Updated)', '0.00', 'monthly', '50', '0', '2026-08-03 18:35:12');
INSERT INTO `plans` (`id`, `name`, `price`, `billing_period`, `product_limit`, `is_default`, `created_at`) VALUES ('2', 'Pro Merchant', '499.00', 'monthly', '100', '0', '2026-08-03 18:35:12');
INSERT INTO `plans` (`id`, `name`, `price`, `billing_period`, `product_limit`, `is_default`, `created_at`) VALUES ('3', 'Gold VIP Store', '999.00', 'monthly', '500', '0', '2026-08-03 18:35:12');

DROP TABLE IF EXISTS `platform_settings`;
CREATE TABLE `platform_settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `site_name` varchar(255) DEFAULT 'LocalShopOS',
  `support_contact_number` varchar(50) DEFAULT '+917676446647',
  `whatsapp_contact` varchar(50) DEFAULT '917676446647',
  `site_logo_url` varchar(550) DEFAULT '/assets/logo.png',
  `primary_color` varchar(20) DEFAULT '#f5b400',
  `accent_color` varchar(20) DEFAULT '#f5b400',
  `default_trial_days` int(11) DEFAULT 15,
  `default_product_limit` int(11) DEFAULT 30,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `platform_settings` (`id`, `site_name`, `support_contact_number`, `whatsapp_contact`, `site_logo_url`, `primary_color`, `accent_color`, `default_trial_days`, `default_product_limit`, `updated_at`) VALUES ('1', 'LocalShopOS', '+917676446647', '917676446647', '/assets/logo.png', '#f5b400', '#f5b400', '15', '30', '2026-08-03 18:57:19');

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(550) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_count` int(11) DEFAULT 0,
  `photo_url` varchar(550) DEFAULT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'General',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `tenant_id_2` (`tenant_id`,`is_active`),
  KEY `tenant_id_3` (`tenant_id`,`category`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('1', '1', 'Fortune Chakki Fresh Atta 5kg', '245.00', '25', 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=400', 'Groceries', '1', '2026-08-03 18:35:13');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('2', '1', 'Amul Taaza Toned Milk 1L', '54.00', '40', 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=400', 'Dairy & Milk', '1', '2026-08-03 18:35:13');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('3', '1', 'Tata Salt Vacuum Evaporated 1kg', '28.00', '15', 'https://images.unsplash.com/photo-1518110168401-f2844efde3fc?w=400', 'Groceries', '1', '2026-08-03 18:35:13');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('4', '1', 'Surf Excel Easy Wash Powder 1kg', '140.00', '2', 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=400', 'Household Essentials', '1', '2026-08-03 18:35:13');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('5', '1', 'Maggi 2-Minute Masala Noodles 280g', '48.00', '0', 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=400', 'Snacks & Bakery', '1', '2026-08-03 18:35:13');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('6', '2', 'Shimla Red Apples 1kg', '180.00', '12', 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=400', 'Fruits & Vegetables', '1', '2026-08-03 18:35:13');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('7', '2', 'Organic Robusta Bananas 1 Dozen', '60.00', '18', 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=400', 'Fruits & Vegetables', '1', '2026-08-03 18:35:13');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('8', '2', 'Fresh Tomatoes 1kg', '35.00', '3', 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=400', 'Fruits & Vegetables', '1', '2026-08-03 18:35:13');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('10', '1', 'Fortune Sunlite Refined Sunflower Oil 1L', '145.00', '50', 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=500&auto=format&fit=crop&q=80', 'Oils & Ghee', '1', '2026-08-03 19:15:40');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('11', '1', 'Aashirvaad Shuddh Chakki Atta 5kg', '260.00', '40', 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=500&auto=format&fit=crop&q=80', 'Atta & Flours', '1', '2026-08-03 19:15:40');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('12', '1', 'India Gate Basmati Rice Feast Rozzana 1kg', '110.00', '35', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500&auto=format&fit=crop&q=80', 'Rice & Grains', '1', '2026-08-03 19:15:40');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('13', '1', 'Toor Dal Premium Cleaned 1kg', '165.00', '30', 'https://images.unsplash.com/photo-1515543237350-b3eea1ec8082?w=500&auto=format&fit=crop&q=80', 'Pulses & Dals', '1', '2026-08-03 19:15:40');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('14', '1', 'Amul Butter Pasteurized 500g', '275.00', '25', 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?w=500&auto=format&fit=crop&q=80', 'Dairy Products', '1', '2026-08-03 19:15:40');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('15', '2', 'Fresh Shimla Red Apples (1kg)', '180.00', '45', 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=500&auto=format&fit=crop&q=80', 'Fresh Fruits', '1', '2026-08-03 19:15:40');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('16', '2', 'Organic Robusta Bananas (1 Dozen)', '60.00', '60', 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=500&auto=format&fit=crop&q=80', 'Fresh Fruits', '1', '2026-08-03 19:15:40');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('17', '2', 'Farm Fresh Tomatoes (1kg)', '40.00', '80', 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=500&auto=format&fit=crop&q=80', 'Fresh Vegetables', '1', '2026-08-03 19:15:40');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('18', '2', 'Organic Spinach / Palak Bunch', '25.00', '30', 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=500&auto=format&fit=crop&q=80', 'Leafy Greens', '1', '2026-08-03 19:15:40');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('19', '2', 'Sweet Alphonso Mangoes (1kg)', '450.00', '20', 'https://images.unsplash.com/photo-1553279768-865429fa0078?w=500&auto=format&fit=crop&q=80', 'Seasonal Fruits', '1', '2026-08-03 19:15:40');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('20', '3', 'Pure Desi Ghee Kaju Katli (500g)', '520.00', '25', 'https://images.unsplash.com/photo-1599785209707-a456fc1337bb?w=500&auto=format&fit=crop&q=80', 'Traditional Sweets', '1', '2026-08-03 19:15:57');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('21', '3', 'Fresh Belgian Chocolate Cake 1kg', '650.00', '15', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=500&auto=format&fit=crop&q=80', 'Cakes & Pastries', '1', '2026-08-03 19:15:57');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('22', '3', 'Crispy Butter Khari Biscuit 250g', '90.00', '40', 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=500&auto=format&fit=crop&q=80', 'Bakery Snacks', '1', '2026-08-03 19:15:57');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('23', '3', 'Hot Gulab Jamun Box (12 Pcs)', '240.00', '30', 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=500&auto=format&fit=crop&q=80', 'Traditional Sweets', '1', '2026-08-03 19:15:57');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('24', '3', 'Artisanal Garlic Bread Loaf', '85.00', '20', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500&auto=format&fit=crop&q=80', 'Fresh Breads', '1', '2026-08-03 19:15:57');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('25', '4', 'Wireless Bluetooth Earbuds TWS i12', '899.00', '30', 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=500&auto=format&fit=crop&q=80', 'Audio Accessories', '1', '2026-08-03 19:15:58');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('26', '4', 'Fast Charging Power Bank 20000mAh', '1499.00', '20', 'https://images.unsplash.com/photo-1609592424074-88482436f54c?w=500&auto=format&fit=crop&q=80', 'Power & Charging', '1', '2026-08-03 19:15:58');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('27', '4', 'Smart Fitness Band Watch Active HD', '1999.00', '15', 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=500&auto=format&fit=crop&q=80', 'Wearables', '1', '2026-08-03 19:15:58');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('28', '4', 'Heavy Duty Braided Type-C Cable 1.5m', '299.00', '100', 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=500&auto=format&fit=crop&q=80', 'Cables & Adaptors', '1', '2026-08-03 19:15:58');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('29', '4', 'Portable RGB Bluetooth Speaker 10W', '1250.00', '25', 'https://images.unsplash.com/photo-1545454675-3531b543be5d?w=500&auto=format&fit=crop&q=80', 'Audio Accessories', '1', '2026-08-03 19:15:58');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('30', '5', 'Pure Cotton Oversized Printed T-Shirt', '499.00', '35', 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500&auto=format&fit=crop&q=80', 'Men Casual Wear', '1', '2026-08-03 19:15:58');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('31', '5', 'Floral Printed Anarkali Kurti Set', '1299.00', '20', 'https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?w=500&auto=format&fit=crop&q=80', 'Women Ethnic Wear', '1', '2026-08-03 19:15:58');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('32', '5', 'Classic Slim Fit Denim Jeans Blue', '1499.00', '25', 'https://images.unsplash.com/photo-1542272604-780c96856592?w=500&auto=format&fit=crop&q=80', 'Bottom Wear', '1', '2026-08-03 19:15:58');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('33', '5', 'Casual Linen Button-Down Shirt', '899.00', '30', 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&auto=format&fit=crop&q=80', 'Men Casual Wear', '1', '2026-08-03 19:15:58');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('34', '6', 'Chyawanprash Special Immune Booster 1kg', '380.00', '40', 'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?w=500&auto=format&fit=crop&q=80', 'Ayurveda & Health', '1', '2026-08-03 19:15:58');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('35', '6', 'Multivitamin Daily Minerals Tablets 60s', '450.00', '50', 'https://images.unsplash.com/photo-1550572017-edd951aa8f72?w=500&auto=format&fit=crop&q=80', 'Supplements', '1', '2026-08-03 19:15:58');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('36', '6', 'Non-Contact Infrared Forehead Thermometer', '850.00', '15', 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=500&auto=format&fit=crop&q=80', 'Medical Devices', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('37', '6', 'N95 Respirator Masks Pack of 5', '150.00', '100', 'https://images.unsplash.com/photo-1584634731339-252c581abfc5?w=500&auto=format&fit=crop&q=80', 'First Aid & Masks', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('38', '7', 'Men Leather Official Oxford Shoes', '1899.00', '20', 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?w=500&auto=format&fit=crop&q=80', 'Men Footwear', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('39', '7', 'Breathable Lightweight Running Sneakers', '1299.00', '35', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&auto=format&fit=crop&q=80', 'Sports Shoes', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('40', '7', 'Women Elegant Block Heel Sandals', '999.00', '25', 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=500&auto=format&fit=crop&q=80', 'Women Footwear', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('41', '7', 'Genuine Leather Men Wallet Dark Brown', '499.00', '40', 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=500&auto=format&fit=crop&q=80', 'Accessories', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('42', '8', 'Organic Whole Green Cardamom / Elaichi 100g', '320.00', '30', 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=500&auto=format&fit=crop&q=80', 'Whole Spices', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('43', '8', 'Kashmiri Red Chilli Powder 250g', '160.00', '50', 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=500&auto=format&fit=crop&q=80', 'Ground Spices', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('44', '8', 'Pure Organic Turmeric / Haldi Powder 500g', '140.00', '45', 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=500&auto=format&fit=crop&q=80', 'Ground Spices', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('45', '8', 'Assam Black Orthodox Loose Tea 500g', '280.00', '35', 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=500&auto=format&fit=crop&q=80', 'Teas & Coffee', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('46', '9', 'Handcrafted Ceramic Flower Vase Set', '799.00', '20', 'https://images.unsplash.com/photo-1612196808214-b7e239e5f6b7?w=500&auto=format&fit=crop&q=80', 'Decor Artifacts', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('47', '9', 'Warm LED Bedside Table Lamp Nordic', '1299.00', '15', 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500&auto=format&fit=crop&q=80', 'Lighting', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('48', '9', '100% Cotton King Size Bedsheet With Pillow Covers', '999.00', '30', 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=500&auto=format&fit=crop&q=80', 'Bedding & Linens', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('49', '9', 'Aromatic Scented Candle Lavender 200g', '349.00', '50', 'https://images.unsplash.com/photo-1603006905003-be475563bc59?w=500&auto=format&fit=crop&q=80', 'Home Fragrance', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('50', '10', 'Pedigree Adult Dog Food Chicken & Vegetables 3kg', '750.00', '25', 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=500&auto=format&fit=crop&q=80', 'Dog Food', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('51', '10', 'Whiskas Ocean Fish Dry Cat Food 1.2kg', '420.00', '30', 'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=500&auto=format&fit=crop&q=80', 'Cat Food', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('52', '10', 'Durable Rubber Dog Chew Toy Ball', '199.00', '50', 'https://images.unsplash.com/photo-1535294435445-d7249524ef2e?w=500&auto=format&fit=crop&q=80', 'Pet Toys', '1', '2026-08-03 19:15:59');
INSERT INTO `products` (`id`, `tenant_id`, `name`, `price`, `stock_count`, `photo_url`, `category`, `is_active`, `created_at`) VALUES ('53', '10', 'Padded Adjustable Dog Harness & Leash Set', '599.00', '20', 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=500&auto=format&fit=crop&q=80', 'Pet Accessories', '1', '2026-08-03 19:15:59');

DROP TABLE IF EXISTS `tenants`;
CREATE TABLE `tenants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(255) NOT NULL,
  `subdomain` varchar(100) NOT NULL,
  `whatsapp_number` varchar(20) NOT NULL,
  `category` varchar(100) DEFAULT 'General Store',
  `logo_url` varchar(550) DEFAULT NULL,
  `product_limit` int(11) DEFAULT 30,
  `plan_status` varchar(20) DEFAULT 'trial',
  `trial_ends_at` datetime DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `is_open` tinyint(1) DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `delivery_enabled` tinyint(1) DEFAULT 1,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `min_delivery_order` decimal(10,2) DEFAULT 0.00,
  `delivery_area_note` varchar(255) DEFAULT NULL,
  `order_thank_you_msg` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `subdomain` (`subdomain`),
  KEY `subdomain_2` (`subdomain`),
  KEY `plan_status` (`plan_status`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `tenants_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tenants` (`id`, `shop_name`, `subdomain`, `whatsapp_number`, `category`, `logo_url`, `product_limit`, `plan_status`, `trial_ends_at`, `plan_id`, `is_open`, `last_login_at`, `delivery_enabled`, `delivery_fee`, `min_delivery_order`, `delivery_area_note`, `order_thank_you_msg`, `created_at`) VALUES ('1', 'Laxmi General Store', 'laxmi-kirana', '917676446647', 'Grocery & Staples', 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=200&auto=format&fit=crop&q=80', '50', 'active', '2026-08-17 18:35:13', '1', '1', '2026-08-03 18:35:13', '1', '25.00', '150.00', 'Delivering within 5km radius of Koramangala', 'Thank you for shopping with Laxmi Kirana!', '2026-08-03 18:35:13');
INSERT INTO `tenants` (`id`, `shop_name`, `subdomain`, `whatsapp_number`, `category`, `logo_url`, `product_limit`, `plan_status`, `trial_ends_at`, `plan_id`, `is_open`, `last_login_at`, `delivery_enabled`, `delivery_fee`, `min_delivery_order`, `delivery_area_note`, `order_thank_you_msg`, `created_at`) VALUES ('2', 'Fresh Farm Organics', 'fresh-fruits', '917676446647', 'Organic Produce', 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=200&auto=format&fit=crop&q=80', '30', 'active', '2026-08-10 18:35:13', '1', '1', '2026-08-03 18:35:13', '1', '0.00', '0.00', NULL, NULL, '2026-08-03 18:35:13');
INSERT INTO `tenants` (`id`, `shop_name`, `subdomain`, `whatsapp_number`, `category`, `logo_url`, `product_limit`, `plan_status`, `trial_ends_at`, `plan_id`, `is_open`, `last_login_at`, `delivery_enabled`, `delivery_fee`, `min_delivery_order`, `delivery_area_note`, `order_thank_you_msg`, `created_at`) VALUES ('3', 'Gupta Bakery & Sweets', 'gupta-bakery', '917676446647', 'Sweets & Snacks', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=200&auto=format&fit=crop&q=80', '500', 'active', NULL, '3', '1', '2026-08-03 18:35:13', '1', '0.00', '0.00', NULL, NULL, '2026-08-03 18:35:13');
INSERT INTO `tenants` (`id`, `shop_name`, `subdomain`, `whatsapp_number`, `category`, `logo_url`, `product_limit`, `plan_status`, `trial_ends_at`, `plan_id`, `is_open`, `last_login_at`, `delivery_enabled`, `delivery_fee`, `min_delivery_order`, `delivery_area_note`, `order_thank_you_msg`, `created_at`) VALUES ('4', 'Apex Electronics & Mobile', 'apex-electronics', '917676446647', 'Electronics & Mobile', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=200&auto=format&fit=crop&q=80', '50', 'active', NULL, NULL, '1', NULL, '1', '0.00', '500.00', 'Standard local delivery within 5km radius.', 'Thank you for shopping with us! We are preparing your order.', '2026-08-03 19:15:58');
INSERT INTO `tenants` (`id`, `shop_name`, `subdomain`, `whatsapp_number`, `category`, `logo_url`, `product_limit`, `plan_status`, `trial_ends_at`, `plan_id`, `is_open`, `last_login_at`, `delivery_enabled`, `delivery_fee`, `min_delivery_order`, `delivery_area_note`, `order_thank_you_msg`, `created_at`) VALUES ('5', 'Vogue Trends Apparel', 'vogue-trends', '917676446647', 'Fashion & Clothing', 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=200&auto=format&fit=crop&q=80', '50', 'active', NULL, NULL, '1', NULL, '1', '50.00', '499.00', 'Standard local delivery within 5km radius.', 'Thank you for shopping with us! We are preparing your order.', '2026-08-03 19:15:58');
INSERT INTO `tenants` (`id`, `shop_name`, `subdomain`, `whatsapp_number`, `category`, `logo_url`, `product_limit`, `plan_status`, `trial_ends_at`, `plan_id`, `is_open`, `last_login_at`, `delivery_enabled`, `delivery_fee`, `min_delivery_order`, `delivery_area_note`, `order_thank_you_msg`, `created_at`) VALUES ('6', 'Green Leaf Wellness Pharmacy', 'greenleaf-meds', '917676446647', 'Healthcare & Pharmacy', 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=200&auto=format&fit=crop&q=80', '50', 'active', NULL, NULL, '1', NULL, '1', '20.00', '100.00', 'Standard local delivery within 5km radius.', 'Thank you for shopping with us! We are preparing your order.', '2026-08-03 19:15:58');
INSERT INTO `tenants` (`id`, `shop_name`, `subdomain`, `whatsapp_number`, `category`, `logo_url`, `product_limit`, `plan_status`, `trial_ends_at`, `plan_id`, `is_open`, `last_login_at`, `delivery_enabled`, `delivery_fee`, `min_delivery_order`, `delivery_area_note`, `order_thank_you_msg`, `created_at`) VALUES ('7', 'Royal Footwear & Accessories', 'royal-footwear', '917676446647', 'Footwear & Bags', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=200&auto=format&fit=crop&q=80', '50', 'active', NULL, NULL, '1', NULL, '1', '45.00', '399.00', 'Standard local delivery within 5km radius.', 'Thank you for shopping with us! We are preparing your order.', '2026-08-03 19:15:59');
INSERT INTO `tenants` (`id`, `shop_name`, `subdomain`, `whatsapp_number`, `category`, `logo_url`, `product_limit`, `plan_status`, `trial_ends_at`, `plan_id`, `is_open`, `last_login_at`, `delivery_enabled`, `delivery_fee`, `min_delivery_order`, `delivery_area_note`, `order_thank_you_msg`, `created_at`) VALUES ('8', 'Spice Garden Organic Spices', 'spice-garden', '917676446647', 'Spices & Condiments', 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=200&auto=format&fit=crop&q=80', '50', 'active', NULL, NULL, '1', NULL, '1', '30.00', '250.00', 'Standard local delivery within 5km radius.', 'Thank you for shopping with us! We are preparing your order.', '2026-08-03 19:15:59');
INSERT INTO `tenants` (`id`, `shop_name`, `subdomain`, `whatsapp_number`, `category`, `logo_url`, `product_limit`, `plan_status`, `trial_ends_at`, `plan_id`, `is_open`, `last_login_at`, `delivery_enabled`, `delivery_fee`, `min_delivery_order`, `delivery_area_note`, `order_thank_you_msg`, `created_at`) VALUES ('9', 'Urban Nest Home Decor', 'urban-nest', '917676446647', 'Home & Living', 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=200&auto=format&fit=crop&q=80', '50', 'active', NULL, NULL, '1', NULL, '1', '75.00', '600.00', 'Standard local delivery within 5km radius.', 'Thank you for shopping with us! We are preparing your order.', '2026-08-03 19:15:59');
INSERT INTO `tenants` (`id`, `shop_name`, `subdomain`, `whatsapp_number`, `category`, `logo_url`, `product_limit`, `plan_status`, `trial_ends_at`, `plan_id`, `is_open`, `last_login_at`, `delivery_enabled`, `delivery_fee`, `min_delivery_order`, `delivery_area_note`, `order_thank_you_msg`, `created_at`) VALUES ('10', 'Pet Joy Care Shop', 'pet-joy-care', '917676446647', 'Pet Supplies', 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=200&auto=format&fit=crop&q=80', '50', 'active', NULL, NULL, '1', NULL, '1', '35.00', '300.00', 'Standard local delivery within 5km radius.', 'Thank you for shopping with us! We are preparing your order.', '2026-08-03 19:15:59');

SET FOREIGN_KEY_CHECKS=1;
