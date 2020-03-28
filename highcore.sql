/*
 Navicat Premium Data Transfer

 Source Server         : Mysql
 Source Server Type    : MySQL
 Source Server Version : 80015
 Source Host           : localhost:3306
 Source Schema         : highcore

 Target Server Type    : MySQL
 Target Server Version : 80015
 File Encoding         : 65001

 Date: 29/03/2020 01:19:35
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for clans
-- ----------------------------
DROP TABLE IF EXISTS `clans`;
CREATE TABLE `clans`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `UNIQUE_NAMES`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of clans
-- ----------------------------
INSERT INTO `clans` VALUES (1, 'Natus Vincere', 'Lorem ipsum dolor.');
INSERT INTO `clans` VALUES (2, 'fnatic', 'Lorem ipsum dolor.');
INSERT INTO `clans` VALUES (3, 'Virtus.pro', 'Lorem ipsum dolor.');

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES (1, 'soldier');
INSERT INTO `roles` VALUES (2, 'co-leader');
INSERT INTO `roles` VALUES (3, 'clanleader');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `clans_id` int(11) NULL DEFAULT NULL,
  `roles_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `clans_id`(`clans_id`) USING BTREE,
  INDEX `roles_id`(`roles_id`) USING BTREE,
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`clans_id`) REFERENCES `clans` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`roles_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'f0rest', 1, 3);
INSERT INTO `users` VALUES (2, 'friberg', 1, 2);
INSERT INTO `users` VALUES (3, 'av3k', 1, 1);
INSERT INTO `users` VALUES (4, 'MaDFroG', 1, 1);
INSERT INTO `users` VALUES (5, 'Daigo', 2, 3);
INSERT INTO `users` VALUES (6, 'infi', 2, 2);
INSERT INTO `users` VALUES (7, 'Mango', 2, 1);
INSERT INTO `users` VALUES (8, 'Mew2King', 2, 1);
INSERT INTO `users` VALUES (9, 'Nairo', 3, 3);
INSERT INTO `users` VALUES (10, 'YellOwStaR', 3, 2);
INSERT INTO `users` VALUES (11, 'Sneaky', 3, 1);
INSERT INTO `users` VALUES (12, 'Rekkles', 3, 1);

SET FOREIGN_KEY_CHECKS = 1;
