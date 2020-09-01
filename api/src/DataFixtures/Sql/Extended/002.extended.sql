-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Hôte : db
-- Généré le : mar. 01 sep. 2020 à 12:13
-- Version du serveur :  10.4.11-MariaDB-1:10.4.11+maria~bionic
-- Version de PHP : 7.4.1

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Déchargement des données de la table `notification`
--

INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES
(1, 1, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(2, 2, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(3, 3, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(4, 4, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(5, 4, 2, NULL, 1, NULL, NULL, NULL, 1, 1, 1),
(6, 4, 3, NULL, 1, NULL, NULL, NULL, 1, 1, 2),
(7, 5, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(8, 5, 2, NULL, 1, NULL, NULL, NULL, 1, 1, 1),
(9, 5, 3, NULL, 1, NULL, NULL, NULL, 1, 1, 2),
(10, 6, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(11, 6, 2, NULL, 1, NULL, NULL, NULL, 1, 1, 1),
(12, 6, 3, NULL, 1, NULL, NULL, NULL, 1, 1, 2),
(13, 7, 2, NULL, 1, NULL, NULL, NULL, 1, 1, 0),
(14, 7, 3, NULL, 1, NULL, NULL, NULL, 1, 1, 1),
(15, 8, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(16, 8, 2, NULL, 1, NULL, NULL, NULL, 1, 1, 1),
(17, 8, 3, NULL, 1, NULL, NULL, NULL, 1, 1, 2),
(18, 9, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(19, 9, 2, NULL, 1, NULL, NULL, NULL, 1, 1, 1),
(20, 9, 3, NULL, 1, NULL, NULL, NULL, 1, 1, 2),
(21, 10, 1, NULL, 1, NULL, NULL, NULL, NULL, 0, 0),
(22, 11, 2, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0),
(23, 12, 1, NULL, 0, NULL, NULL, NULL, 1, 0, 0),
(24, 12, 2, NULL, 0, NULL, NULL, NULL, 1, 0, 1),
(25, 12, 3, NULL, 0, NULL, NULL, NULL, 1, 1, 2),
(26, 12, 4, NULL, 0, NULL, NULL, NULL, 1, 1, 3),
(27, 4, 4, NULL, 1, NULL, NULL, NULL, 1, 1, 3),
(28, 5, 4, NULL, 1, NULL, NULL, NULL, 1, 1, 3),
(29, 6, 4, NULL, 1, NULL, NULL, NULL, 1, 1, 3),
(30, 7, 4, NULL, 1, NULL, NULL, NULL, 1, 1, 2),
(31, 8, 4, NULL, 1, NULL, NULL, NULL, 1, 1, 3),
(32, 9, 4, NULL, 1, NULL, NULL, NULL, 1, 1, 3),
(33, 13, 3, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(34, 14, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(35, 14, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 1),
(36, 14, 3, NULL, 1, NULL, NULL, NULL, 1, 0, 2),
(37, 14, 4, NULL, 1, NULL, NULL, NULL, 1, 0, 3),
(38, 15, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(39, 15, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 1),
(40, 15, 3, NULL, 1, NULL, NULL, NULL, 1, 0, 2),
(41, 15, 4, NULL, 1, NULL, NULL, NULL, 1, 0, 3),
(42, 16, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(43, 16, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 1),
(44, 16, 3, NULL, 1, NULL, NULL, NULL, 1, 0, 2),
(45, 16, 4, NULL, 1, NULL, NULL, NULL, 1, 0, 3),
(46, 17, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(47, 17, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 1),
(48, 17, 3, NULL, 1, NULL, NULL, NULL, 1, 0, 2),
(49, 17, 4, NULL, 1, NULL, NULL, NULL, 1, 0, 3),
(50, 18, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(52, 18, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 1),
(53, 18, 3, NULL, 1, NULL, NULL, NULL, 1, 0, 2),
(54, 18, 4, NULL, 1, NULL, NULL, NULL, 1, 0, 3),
(55, 19, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(56, 19, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 1),
(57, 19, 3, NULL, 1, NULL, NULL, NULL, 1, 0, 2),
(58, 19, 4, NULL, 1, NULL, NULL, NULL, 1, 0, 3),
(59, 20, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(60, 20, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 1),
(61, 20, 3, NULL, 1, NULL, NULL, NULL, 1, 0, 2),
(62, 20, 4, NULL, 1, NULL, NULL, NULL, 1, 0, 3),
(63, 21, 2, NULL, 1, NULL, NULL, NULL, 1, 1, 0),
(64, 22, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(65, 23, 3, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(83, 32, 2, NULL, 1, NULL, '2020-03-06 14:59:00', NULL, 1, 0, 0),
(84, 66, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(85, 66, 3, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(86, 67, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(87, 67, 3, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(88, 43, 3, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(89, 44, 1, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(90, 45, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(91, 68, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(92, 66, 4, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(93, 67, 4, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(94, 70, 2, NULL, 1, NULL, NULL, NULL, 1, 0, 0),
(95, 74, 2, NULL, 1, NULL, '2020-07-06 10:00:00', NULL, 1, 0, 0),
(96, 75, 2, NULL, 1, NULL, '2020-07-06 11:30:00', NULL, 1, 0, 0),
(97, 76, 2, NULL, 1, NULL, '2020-07-07 16:20:00', NULL, 1, 0, 0);

--
-- Déchargement des données de la table `notified`
--

INSERT INTO `notified` (`id`, `notification_id`, `user_id`, `status`, `sent_date`, `received_date`, `read_date`, `proposal_id`, `matching_id`, `ask_history_id`, `recipient_id`, `created_date`, `updated_date`, `community_id`) VALUES
(1, 1, 1, 1, '2020-09-01 08:23:05', NULL, NULL, NULL, NULL, NULL, NULL, '2020-09-01 08:23:05', NULL, NULL),
(2, 1, 2, 1, '2020-09-01 08:25:32', NULL, NULL, NULL, NULL, NULL, NULL, '2020-09-01 08:25:32', NULL, NULL),
(3, 1, 3, 1, '2020-09-01 08:28:46', NULL, NULL, NULL, NULL, NULL, NULL, '2020-09-01 08:28:46', NULL, NULL),
(4, 1, 4, 1, '2020-09-01 08:31:27', NULL, NULL, NULL, NULL, NULL, NULL, '2020-09-01 08:31:27', NULL, NULL),
(5, 1, 5, 1, '2020-09-01 08:34:17', NULL, NULL, NULL, NULL, NULL, NULL, '2020-09-01 08:34:17', NULL, NULL),
(6, 16, 1, 1, '2020-09-01 09:13:48', NULL, NULL, NULL, 1, NULL, NULL, '2020-09-01 09:13:48', NULL, NULL),
(7, 1, 6, 1, '2020-09-01 09:15:48', NULL, NULL, NULL, NULL, NULL, NULL, '2020-09-01 09:15:48', NULL, NULL),
(8, 16, 1, 1, '2020-09-01 09:17:50', NULL, NULL, NULL, 2, NULL, NULL, '2020-09-01 09:17:50', NULL, NULL),
(9, 16, 1, 1, '2020-09-01 09:21:48', NULL, NULL, NULL, 4, NULL, NULL, '2020-09-01 09:21:48', NULL, NULL),
(10, 63, 1, 1, '2020-09-01 09:43:47', NULL, NULL, NULL, NULL, NULL, NULL, '2020-09-01 09:43:47', NULL, NULL),
(11, 63, 2, 1, '2020-09-01 09:47:09', NULL, NULL, NULL, NULL, NULL, NULL, '2020-09-01 09:47:09', NULL, NULL),
(12, 63, 5, 1, '2020-09-01 09:52:57', NULL, NULL, NULL, NULL, NULL, NULL, '2020-09-01 09:52:57', NULL, NULL);

--
-- Déchargement des données de la table `proposal`
--

INSERT INTO `proposal` (`id`, `user_id`, `created_date`, `proposal_linked_id`, `type`, `comment`, `criteria_id`, `user_delegate_id`, `updated_date`, `private`, `paused`, `event_id`, `external`, `dynamic`, `active`, `finished`, `subject_id`, `exposed`, `external_id`, `seo`) VALUES
(1, 1, '2020-09-01 08:46:33', 2, 2, 'Prise en charge et/ou dépose possible proche des gares Sncf', 1, NULL, '2020-09-01 08:46:33', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(2, 1, '2020-09-01 08:46:33', 1, 3, 'Prise en charge et/ou dépose possible proche des gares Sncf', 2, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(3, 1, '2020-09-01 08:47:35', NULL, 1, 'Trip vers les Vosges', 3, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(4, 2, '2020-09-01 08:48:26', 5, 2, NULL, 4, NULL, '2020-09-01 08:48:27', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(5, 2, '2020-09-01 08:48:27', 4, 3, NULL, 5, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(6, 2, '2020-09-01 08:51:32', 7, 2, 'Arrivée proche gare sncf', 6, NULL, '2020-09-01 08:51:32', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(7, 2, '2020-09-01 08:51:32', 6, 3, 'Arrivée proche gare sncf', 7, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(8, 2, '2020-09-01 08:53:11', 9, 2, 'Salut!', 8, NULL, '2020-09-01 08:53:11', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(9, 2, '2020-09-01 08:53:11', 8, 3, 'Salut!', 9, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(10, 3, '2020-09-01 08:55:02', 11, 2, NULL, 10, NULL, '2020-09-01 08:55:03', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(11, 3, '2020-09-01 08:55:03', 10, 3, NULL, 11, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(12, 3, '2020-09-01 08:57:22', 13, 2, NULL, 12, NULL, '2020-09-01 08:57:22', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(13, 3, '2020-09-01 08:57:22', 12, 3, NULL, 13, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(14, 4, '2020-09-01 09:01:40', 15, 2, 'Les vélos sont acceptés!!!', 14, NULL, '2020-09-01 09:01:40', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(15, 4, '2020-09-01 09:01:40', 14, 3, 'Les vélos sont acceptés!!!', 15, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(16, 4, '2020-09-01 09:03:23', NULL, 1, 'Arrivée à la gare', 16, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(17, 5, '2020-09-01 09:08:02', 18, 2, NULL, 17, NULL, '2020-09-01 09:08:02', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(18, 5, '2020-09-01 09:08:02', 17, 3, NULL, 18, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(19, 5, '2020-09-01 09:13:47', NULL, 1, NULL, 19, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(20, 6, '2020-09-01 09:17:49', 21, 2, NULL, 21, NULL, '2020-09-01 09:17:50', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(21, 6, '2020-09-01 09:17:50', 20, 3, NULL, 23, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(22, 6, '2020-09-01 09:21:47', NULL, 1, NULL, 25, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL);

--
-- Déchargement des données de la table `redirect`
--

INSERT INTO `redirect` (`id`, `origin_uri`, `type`, `destination_id`, `created_date`, `updated_date`, `language`) VALUES
(1, '/communes/covoiturages/', 99, 0, '2020-07-09 00:00:00', NULL, 'FR');

--
-- Déchargement des données de la table `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `refresh_token`, `username`, `valid`) VALUES
(1, '8e3dc2d4194b787909193a2c01c621a41deb9da6b2bf29063bc5b53a21e276d70dd701d71dec3b021e6e1af75564d7eb33bbd9d825a0df759a6e9b07150bf8aa', 'front', '2020-10-01 08:16:30'),
(2, '6a35eb1e9ab27c34de217ac427f616ca2890d2817d259a76df60e2df2933d40fdb6d9bb0e5eeb056503772ba5030228fe9906ef4b91976538cb93d8f5a7ff26c', 'sylvain.test@yopmail.com', '2020-10-01 08:23:14'),
(3, '431d280ae90dec72f16cf569e22112fecf8f08cc903efe0d189c37c295acefd50192a93e316a3f06286ffa66885e7871808c0eaf681b9457a531c1a520eec4b3', 'max.test@yopmail.com', '2020-10-01 08:25:41'),
(4, 'fd45976bb18b48380aecf839ad7e4ee5b1b32734977a3f6de69b278815cb28cb0c1b430a1430652da77e0b3a5f0fe42433e4bac2687ccecc4210de10d49dc198', 'celine.test@yopmail.com', '2020-10-01 08:29:04'),
(5, 'a2a5788aacca7d27e328a652c8c514c674c45a3aaabd5ed756629fcb9743920e9c2ec54364019f8570a0c7737ec486a80320fc0987ccf9a4e496362eedb05fd7', 'olivier.test@yopmail.com', '2020-10-01 08:32:35'),
(6, 'b11a9668a76aaa474c6700466b1703005f8c89434e0f8f7fdc15bfb4e696097659c5b30114da63a177d78bde69937e28b8efc3f50239cbdf9d2d7e0c0673ea5d', 'remi.test@yopmail.com', '2020-10-01 08:35:29'),
(7, 'e014a29ddc3093454c6fdcb90ef1ef66cf5aa5eb27b09d0126e7c422e3793089dd0ba24c4c57b749311a4de428e2301330d7a240e05fa12680ea52bdefebb091', 'sylvain.test@yopmail.com', '2020-10-01 08:44:59'),
(8, 'abfba937fe74dcd04c49336db4339c94627ba8fcd68dea7217c4b37f3b50788e2785bdb3df452e0c7ff6aed916924ee0ee0cf8c0a91e301546dfca03cb8c88d3', 'max.test@yopmail.com', '2020-10-01 08:48:05'),
(9, 'bd903cf51b5a729abfb673d84fa36ba9dfb59f2ab02ba7fe4ada0c765b2e95d2ebc352e81acd0251de981df202153e12c069f012afe362b48f00e9d27aceb85e', 'celine.test@yopmail.com', '2020-10-01 08:53:49'),
(10, '9dac30238c8ebe5d679f77db68907d0c74232d7d3136098337be37ccde8fae5c88ba74d48e5fab1f2596de0ee1e74cc3a124a67d6acffecd32da7622664b89d8', 'olivier.test@yopmail.com', '2020-10-01 08:58:45'),
(11, '5475c8403b57e1f1733d9348cb705ff0b425b8207058781cf8019eb1d10e482f06c6d6816df85ea203af291faeeb1171594ad1c784ea2d39abc6717667433a92', 'remi.test@yopmail.com', '2020-10-01 09:07:04'),
(12, 'd267fad92d28c67d98d5722f4ad7d34d07948de448d2aeab12e91bc62dcfe04dcd5a0dbec0639be7126cc571b7449ea67d6380ce99df88fe1778ff3477691fee', 'marion.test@yopmail.com', '2020-10-01 09:16:13'),
(13, 'f6df7cc9ef2d1977e2a6575bc68cb571da84c1944d44abd61f235e51c4807a3620a70b99a0b2af616ba0c49ef401cc900d24b1d06f1b6cab7bae551858420b29', 'sylvain.test@yopmail.com', '2020-10-01 09:31:00'),
(14, '91029b43a9bb026aa79a82ebae7022453f5bfdcb85a5e2c8aaf2db636e67176e5d5db254f0d5084196714071f654ba260f756a9dc3d38eecf9c2ec858fcab1d9', 'max.test@yopmail.com', '2020-10-01 09:44:40'),
(15, '3881192d4e54f213ac0c6c87706de6baa24560dbbeedb24e24ade5ad1333e6feded3d96df0510ff4fe58bc2d59536bba23d00a6a0d7027224a0ae15a426ca5dd', 'remi.test@yopmail.com', '2020-10-01 09:49:17'),
(16, 'bae9c85c5e40fe86b207cfc50b0b0619ead8661cad20e15a51af37dcf5a78df92abc779b0f20f4cf020872e3a5488f1c3392730ead563bd9dbde1028a2353069', 'marion.test@yopmail.com', '2020-10-01 09:57:37'),
(17, 'bdfb187e59c82406e684ef8738512fe4624636b8275b75b11de7553b99fe2f3906e94ae0bf397c0d239bf4630d03d1dff792243a9c13c66e982876fcbe1d4d4e', 'olivier.test@yopmail.com', '2020-10-01 09:59:04');

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `given_name`, `family_name`, `email`, `password`, `gender`, `nationality`, `birth_date`, `telephone`, `status`, `any_route_as_passenger`, `multi_transport_mode`, `max_detour_duration`, `max_detour_distance`, `created_date`, `pwd_token`, `geo_token`, `language`, `pwd_token_date`, `updated_date`, `validated_date`, `email_token`, `facebook_id`, `smoke`, `music`, `music_favorites`, `chat`, `chat_favorites`, `news_subscription`, `phone_token`, `phone_validated_date`, `phone_display`, `pro_name`, `pro_email`, `user_delegate_id`, `unsubscribe_token`, `unsubscribe_date`, `solidary_user_id`, `last_activity_date`, `mobile`) VALUES
(1, 'Sylvain', 'Test', 'sylvain.test@yopmail.com', '$argon2id$v=19$m=65536,t=4,p=1$NZ5VEQTYOZBSEPrdQ30qgQ$85IKuIqPJ6+Z3KUogh5JsHtW/4wAODcQH1mMVeHDDzQ', 2, NULL, '1982-08-21', '0606060606', 1, 0, 0, NULL, NULL, '2020-09-01 08:23:05', NULL, '56275db013e69bb2cd184ab68b865b5b17e19859876eebb4acb473a513777493', 'fr_FR', NULL, '2020-09-01 09:44:33', '2020-10-01 08:23:14', NULL, NULL, 0, 1, NULL, 1, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, '874286dffd5548bdf4e38ea1ef853487bd122fdf1bca25006ee24f0fc1bf8a01', NULL, NULL, '2020-09-01 09:44:33', 0),
(2, 'Max', 'Test', 'max.test@yopmail.com', '$argon2id$v=19$m=65536,t=4,p=1$wAeu3HAihOk97JLbYF7eTQ$LjfI/ZQgEpw66iiBek3IJFLAo+L9BgKJvMWCWUxSk0s', 2, NULL, '1984-08-16', '0202020202', 1, 0, 0, NULL, NULL, '2020-09-01 08:25:32', NULL, '0d1c9f89b233e1020f1eb0204a753f48dae7e794406fa01023ecca30c74737b6', 'fr_FR', NULL, '2020-09-01 09:49:01', '2020-10-01 08:25:41', NULL, NULL, 0, 1, NULL, 1, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, '657cab91eefd9757fdca917d64a58fe3e07918986781bbee14af423dcfb1da23', NULL, NULL, '2020-09-01 09:49:01', 0),
(3, 'Céline', 'Test', 'celine.test@yopmail.com', '$argon2id$v=19$m=65536,t=4,p=1$GskhCLz/C2SmEqBZ+NF/xw$qXES/C6hpJmcbG6DkcNsF6mzmofAH+BrxUChORD/Gd0', 1, NULL, '1993-08-07', '0404040404', 1, 0, 0, NULL, NULL, '2020-09-01 08:28:46', NULL, 'cd57dddbfaab1439c71aece2deb78889c3fd3e80ecdf16f41e938eb5a9029999', 'fr_FR', NULL, '2020-09-01 08:57:44', '2020-10-01 08:29:04', NULL, NULL, 0, 1, NULL, 1, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, 'dfb7a6253e80666bbb1082a25da4250d4fedc5354b00063c0cb68f84d39398a4', NULL, NULL, '2020-09-01 08:57:44', 0),
(4, 'Olivier', 'Test', 'olivier.test@yopmail.com', '$argon2id$v=19$m=65536,t=4,p=1$LbX+UIXo/fa5aPepYrXvZA$kHQ37x/BWlrWEGVf7+ip3+Sbqd4HEXn71+Xv8BZDX+Y', 2, NULL, '1984-06-11', '0101010101', 1, 0, 0, NULL, NULL, '2020-09-01 08:31:27', NULL, '5128b58c23e6b3a04167f8120d0c5303416300fc2dafb5c1c6857a55c5f169f1', 'fr_FR', NULL, '2020-09-01 10:11:03', '2020-10-01 08:32:35', NULL, NULL, 0, 1, NULL, 1, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, '419136a9c0fcf58f614ba880533668acad0204b509acce877322b83c2f628afe', NULL, NULL, '2020-09-01 10:11:03', 0),
(5, 'Rémi', 'Test', 'remi.test@yopmail.com', '$argon2id$v=19$m=65536,t=4,p=1$6+B0rydsN1T3DDPjNdXB3g$gPKLXhVMvW6QCRCXv4LKlWAZK3T3ZR0Typ1IyW5JEVY', 2, NULL, '1980-05-12', '0303030303', 1, 0, 0, NULL, NULL, '2020-09-01 08:34:17', NULL, '784d721432eb481f63ad3982871dd2e27ae77f5d219ad02dc67229528fd53213', 'fr_FR', NULL, '2020-09-01 09:57:01', '2020-10-01 08:35:29', NULL, NULL, 0, 1, NULL, 1, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, 'd0b3c6c5c9832b66f48f02e3bd8909ecc4d37bd7b5e8ce85c9f8d9b6e0b79a02', NULL, NULL, '2020-09-01 09:57:01', 0),
(6, 'Marion', 'Test', 'marion.test@yopmail.com', '$argon2id$v=19$m=65536,t=4,p=1$7ETM39SU6X5Nqp1p0rk+mw$Fr61/QlxRg5wAJLhbGHSxXMqBfth801+g3nySHu5QIw', 1, NULL, '1998-11-05', '0808080808', 1, 0, 0, NULL, NULL, '2020-09-01 09:15:48', NULL, '8824fe486b0942e55039195b6619bb9754dc2a74721d36e4c1c8cb502ea7ba96', 'fr_FR', NULL, '2020-09-01 09:58:45', '2020-10-01 09:16:13', NULL, NULL, 0, 1, NULL, 1, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, '040eee4e0711e537a580b1f4d35dc246bbc2fc6c343433a689691e7b1c8213b3', NULL, NULL, '2020-09-01 09:58:45', 0);

--
-- Déchargement des données de la table `user_auth_assignment`
--

INSERT INTO `user_auth_assignment` (`id`, `user_id`, `auth_item_id`, `territory_id`) VALUES
(1, 1, 1, NULL),
(2, 2, 3, NULL),
(3, 3, 3, NULL),
(4, 4, 3, NULL),
(5, 5, 3, NULL),
(6, 6, 3, NULL),
(7, 5, 8, NULL),
(8, 6, 8, NULL),
(9, 4, 8, NULL);

--
-- Déchargement des données de la table `user_notification`
--

INSERT INTO `user_notification` (`id`, `notification_id`, `user_id`, `active`, `created_date`, `updated_date`) VALUES
(1, 5, 1, 1, '2020-09-01 08:23:05', NULL),
(2, 6, 1, 0, '2020-09-01 08:23:05', NULL),
(3, 8, 1, 1, '2020-09-01 08:23:05', NULL),
(4, 9, 1, 0, '2020-09-01 08:23:05', NULL),
(5, 11, 1, 1, '2020-09-01 08:23:05', NULL),
(6, 12, 1, 0, '2020-09-01 08:23:05', NULL),
(7, 13, 1, 1, '2020-09-01 08:23:05', NULL),
(8, 14, 1, 0, '2020-09-01 08:23:05', NULL),
(9, 16, 1, 1, '2020-09-01 08:23:05', NULL),
(10, 17, 1, 0, '2020-09-01 08:23:05', NULL),
(11, 19, 1, 1, '2020-09-01 08:23:05', NULL),
(12, 20, 1, 0, '2020-09-01 08:23:05', NULL),
(13, 25, 1, 0, '2020-09-01 08:23:05', NULL),
(14, 26, 1, 0, '2020-09-01 08:23:05', NULL),
(15, 27, 1, 0, '2020-09-01 08:23:05', NULL),
(16, 28, 1, 0, '2020-09-01 08:23:05', NULL),
(17, 29, 1, 0, '2020-09-01 08:23:05', NULL),
(18, 30, 1, 0, '2020-09-01 08:23:05', NULL),
(19, 31, 1, 0, '2020-09-01 08:23:05', NULL),
(20, 32, 1, 0, '2020-09-01 08:23:05', NULL),
(21, 63, 1, 1, '2020-09-01 08:23:05', NULL),
(22, 5, 2, 1, '2020-09-01 08:25:32', NULL),
(23, 6, 2, 0, '2020-09-01 08:25:32', NULL),
(24, 8, 2, 1, '2020-09-01 08:25:32', NULL),
(25, 9, 2, 0, '2020-09-01 08:25:32', NULL),
(26, 11, 2, 1, '2020-09-01 08:25:32', NULL),
(27, 12, 2, 0, '2020-09-01 08:25:32', NULL),
(28, 13, 2, 1, '2020-09-01 08:25:32', NULL),
(29, 14, 2, 0, '2020-09-01 08:25:32', NULL),
(30, 16, 2, 1, '2020-09-01 08:25:32', NULL),
(31, 17, 2, 0, '2020-09-01 08:25:32', NULL),
(32, 19, 2, 1, '2020-09-01 08:25:32', NULL),
(33, 20, 2, 0, '2020-09-01 08:25:32', NULL),
(34, 25, 2, 0, '2020-09-01 08:25:32', NULL),
(35, 26, 2, 0, '2020-09-01 08:25:32', NULL),
(36, 27, 2, 0, '2020-09-01 08:25:32', NULL),
(37, 28, 2, 0, '2020-09-01 08:25:32', NULL),
(38, 29, 2, 0, '2020-09-01 08:25:32', NULL),
(39, 30, 2, 0, '2020-09-01 08:25:32', NULL),
(40, 31, 2, 0, '2020-09-01 08:25:32', NULL),
(41, 32, 2, 0, '2020-09-01 08:25:32', NULL),
(42, 63, 2, 1, '2020-09-01 08:25:32', NULL),
(43, 5, 3, 1, '2020-09-01 08:28:46', NULL),
(44, 6, 3, 0, '2020-09-01 08:28:46', NULL),
(45, 8, 3, 1, '2020-09-01 08:28:46', NULL),
(46, 9, 3, 0, '2020-09-01 08:28:46', NULL),
(47, 11, 3, 1, '2020-09-01 08:28:46', NULL),
(48, 12, 3, 0, '2020-09-01 08:28:46', NULL),
(49, 13, 3, 1, '2020-09-01 08:28:46', NULL),
(50, 14, 3, 0, '2020-09-01 08:28:46', NULL),
(51, 16, 3, 1, '2020-09-01 08:28:46', NULL),
(52, 17, 3, 0, '2020-09-01 08:28:46', NULL),
(53, 19, 3, 1, '2020-09-01 08:28:46', NULL),
(54, 20, 3, 0, '2020-09-01 08:28:46', NULL),
(55, 25, 3, 0, '2020-09-01 08:28:46', NULL),
(56, 26, 3, 0, '2020-09-01 08:28:46', NULL),
(57, 27, 3, 0, '2020-09-01 08:28:46', NULL),
(58, 28, 3, 0, '2020-09-01 08:28:46', NULL),
(59, 29, 3, 0, '2020-09-01 08:28:46', NULL),
(60, 30, 3, 0, '2020-09-01 08:28:46', NULL),
(61, 31, 3, 0, '2020-09-01 08:28:46', NULL),
(62, 32, 3, 0, '2020-09-01 08:28:46', NULL),
(63, 63, 3, 1, '2020-09-01 08:28:46', NULL),
(64, 5, 4, 1, '2020-09-01 08:31:27', NULL),
(65, 6, 4, 0, '2020-09-01 08:31:27', NULL),
(66, 8, 4, 1, '2020-09-01 08:31:27', NULL),
(67, 9, 4, 0, '2020-09-01 08:31:27', NULL),
(68, 11, 4, 1, '2020-09-01 08:31:27', NULL),
(69, 12, 4, 0, '2020-09-01 08:31:27', NULL),
(70, 13, 4, 1, '2020-09-01 08:31:27', NULL),
(71, 14, 4, 0, '2020-09-01 08:31:27', NULL),
(72, 16, 4, 1, '2020-09-01 08:31:27', NULL),
(73, 17, 4, 0, '2020-09-01 08:31:27', NULL),
(74, 19, 4, 1, '2020-09-01 08:31:27', NULL),
(75, 20, 4, 0, '2020-09-01 08:31:27', NULL),
(76, 25, 4, 0, '2020-09-01 08:31:27', NULL),
(77, 26, 4, 0, '2020-09-01 08:31:27', NULL),
(78, 27, 4, 0, '2020-09-01 08:31:27', NULL),
(79, 28, 4, 0, '2020-09-01 08:31:27', NULL),
(80, 29, 4, 0, '2020-09-01 08:31:27', NULL),
(81, 30, 4, 0, '2020-09-01 08:31:27', NULL),
(82, 31, 4, 0, '2020-09-01 08:31:27', NULL),
(83, 32, 4, 0, '2020-09-01 08:31:27', NULL),
(84, 63, 4, 1, '2020-09-01 08:31:27', NULL),
(85, 5, 5, 1, '2020-09-01 08:34:17', NULL),
(86, 6, 5, 0, '2020-09-01 08:34:17', NULL),
(87, 8, 5, 1, '2020-09-01 08:34:17', NULL),
(88, 9, 5, 0, '2020-09-01 08:34:17', NULL),
(89, 11, 5, 1, '2020-09-01 08:34:17', NULL),
(90, 12, 5, 0, '2020-09-01 08:34:17', NULL),
(91, 13, 5, 1, '2020-09-01 08:34:17', NULL),
(92, 14, 5, 0, '2020-09-01 08:34:17', NULL),
(93, 16, 5, 1, '2020-09-01 08:34:17', NULL),
(94, 17, 5, 0, '2020-09-01 08:34:17', NULL),
(95, 19, 5, 1, '2020-09-01 08:34:17', NULL),
(96, 20, 5, 0, '2020-09-01 08:34:17', NULL),
(97, 25, 5, 0, '2020-09-01 08:34:17', NULL),
(98, 26, 5, 0, '2020-09-01 08:34:17', NULL),
(99, 27, 5, 0, '2020-09-01 08:34:17', NULL),
(100, 28, 5, 0, '2020-09-01 08:34:17', NULL),
(101, 29, 5, 0, '2020-09-01 08:34:17', NULL),
(102, 30, 5, 0, '2020-09-01 08:34:17', NULL),
(103, 31, 5, 0, '2020-09-01 08:34:17', NULL),
(104, 32, 5, 0, '2020-09-01 08:34:17', NULL),
(105, 63, 5, 1, '2020-09-01 08:34:17', NULL),
(106, 5, 6, 1, '2020-09-01 09:15:48', NULL),
(107, 6, 6, 0, '2020-09-01 09:15:48', NULL),
(108, 8, 6, 1, '2020-09-01 09:15:48', NULL),
(109, 9, 6, 0, '2020-09-01 09:15:48', NULL),
(110, 11, 6, 1, '2020-09-01 09:15:48', NULL),
(111, 12, 6, 0, '2020-09-01 09:15:48', NULL),
(112, 13, 6, 1, '2020-09-01 09:15:48', NULL),
(113, 14, 6, 0, '2020-09-01 09:15:48', NULL),
(114, 16, 6, 1, '2020-09-01 09:15:48', NULL),
(115, 17, 6, 0, '2020-09-01 09:15:48', NULL),
(116, 19, 6, 1, '2020-09-01 09:15:48', NULL),
(117, 20, 6, 0, '2020-09-01 09:15:48', NULL),
(118, 25, 6, 0, '2020-09-01 09:15:48', NULL),
(119, 26, 6, 0, '2020-09-01 09:15:48', NULL),
(120, 27, 6, 0, '2020-09-01 09:15:48', NULL),
(121, 28, 6, 0, '2020-09-01 09:15:48', NULL),
(122, 29, 6, 0, '2020-09-01 09:15:48', NULL),
(123, 30, 6, 0, '2020-09-01 09:15:48', NULL),
(124, 31, 6, 0, '2020-09-01 09:15:48', NULL),
(125, 32, 6, 0, '2020-09-01 09:15:48', NULL),
(126, 63, 6, 1, '2020-09-01 09:15:48', NULL);

--
-- Déchargement des données de la table `waypoint`
--

INSERT INTO `waypoint` (`id`, `proposal_id`, `address_id`, `matching_id`, `position`, `destination`, `ask_id`, `created_date`, `updated_date`, `duration`, `role`, `reached`, `floating`) VALUES
(1, 1, 6, NULL, 0, 0, NULL, '2020-09-01 08:46:33', NULL, NULL, NULL, NULL, NULL),
(2, 1, 7, NULL, 1, 1, NULL, '2020-09-01 08:46:33', NULL, NULL, NULL, NULL, NULL),
(3, 2, 8, NULL, 0, 0, NULL, '2020-09-01 08:46:33', NULL, NULL, NULL, NULL, NULL),
(4, 2, 9, NULL, 1, 1, NULL, '2020-09-01 08:46:33', NULL, NULL, NULL, NULL, NULL),
(5, 3, 10, NULL, 0, 0, NULL, '2020-09-01 08:47:35', NULL, NULL, NULL, NULL, NULL),
(6, 3, 11, NULL, 1, 1, NULL, '2020-09-01 08:47:35', NULL, NULL, NULL, NULL, NULL),
(7, 4, 12, NULL, 0, 0, NULL, '2020-09-01 08:48:26', NULL, NULL, NULL, NULL, NULL),
(8, 4, 13, NULL, 1, 1, NULL, '2020-09-01 08:48:26', NULL, NULL, NULL, NULL, NULL),
(9, 5, 14, NULL, 0, 0, NULL, '2020-09-01 08:48:27', NULL, NULL, NULL, NULL, NULL),
(10, 5, 15, NULL, 1, 1, NULL, '2020-09-01 08:48:27', NULL, NULL, NULL, NULL, NULL),
(11, 6, 16, NULL, 0, 0, NULL, '2020-09-01 08:51:32', NULL, NULL, NULL, NULL, NULL),
(12, 6, 17, NULL, 1, 1, NULL, '2020-09-01 08:51:32', NULL, NULL, NULL, NULL, NULL),
(13, 7, 18, NULL, 0, 0, NULL, '2020-09-01 08:51:32', NULL, NULL, NULL, NULL, NULL),
(14, 7, 19, NULL, 1, 1, NULL, '2020-09-01 08:51:32', NULL, NULL, NULL, NULL, NULL),
(15, 8, 20, NULL, 0, 0, NULL, '2020-09-01 08:53:11', NULL, NULL, NULL, NULL, NULL),
(16, 8, 21, NULL, 1, 1, NULL, '2020-09-01 08:53:11', NULL, NULL, NULL, NULL, NULL),
(17, 9, 22, NULL, 0, 0, NULL, '2020-09-01 08:53:11', NULL, NULL, NULL, NULL, NULL),
(18, 9, 23, NULL, 1, 1, NULL, '2020-09-01 08:53:11', NULL, NULL, NULL, NULL, NULL),
(19, 10, 24, NULL, 0, 0, NULL, '2020-09-01 08:55:02', NULL, NULL, NULL, NULL, NULL),
(20, 10, 25, NULL, 1, 1, NULL, '2020-09-01 08:55:02', NULL, NULL, NULL, NULL, NULL),
(21, 11, 26, NULL, 0, 0, NULL, '2020-09-01 08:55:03', NULL, NULL, NULL, NULL, NULL),
(22, 11, 27, NULL, 1, 1, NULL, '2020-09-01 08:55:03', NULL, NULL, NULL, NULL, NULL),
(23, 12, 28, NULL, 0, 0, NULL, '2020-09-01 08:57:22', NULL, NULL, NULL, NULL, NULL),
(24, 12, 29, NULL, 1, 1, NULL, '2020-09-01 08:57:22', NULL, NULL, NULL, NULL, NULL),
(25, 13, 30, NULL, 0, 0, NULL, '2020-09-01 08:57:22', NULL, NULL, NULL, NULL, NULL),
(26, 13, 31, NULL, 1, 1, NULL, '2020-09-01 08:57:22', NULL, NULL, NULL, NULL, NULL),
(27, 14, 32, NULL, 0, 0, NULL, '2020-09-01 09:01:40', NULL, NULL, NULL, NULL, NULL),
(28, 14, 33, NULL, 1, 1, NULL, '2020-09-01 09:01:40', NULL, NULL, NULL, NULL, NULL),
(29, 15, 34, NULL, 0, 0, NULL, '2020-09-01 09:01:40', NULL, NULL, NULL, NULL, NULL),
(30, 15, 35, NULL, 1, 1, NULL, '2020-09-01 09:01:40', NULL, NULL, NULL, NULL, NULL),
(31, 16, 36, NULL, 0, 0, NULL, '2020-09-01 09:03:23', NULL, NULL, NULL, NULL, NULL),
(32, 16, 37, NULL, 1, 1, NULL, '2020-09-01 09:03:23', NULL, NULL, NULL, NULL, NULL),
(33, 17, 38, NULL, 0, 0, NULL, '2020-09-01 09:08:02', NULL, NULL, NULL, NULL, NULL),
(34, 17, 39, NULL, 1, 1, NULL, '2020-09-01 09:08:02', NULL, NULL, NULL, NULL, NULL),
(35, 18, 40, NULL, 0, 0, NULL, '2020-09-01 09:08:02', NULL, NULL, NULL, NULL, NULL),
(36, 18, 41, NULL, 1, 1, NULL, '2020-09-01 09:08:02', NULL, NULL, NULL, NULL, NULL),
(37, 19, 42, NULL, 0, 0, NULL, '2020-09-01 09:13:47', NULL, NULL, NULL, NULL, NULL),
(38, 19, 43, NULL, 1, 1, NULL, '2020-09-01 09:13:47', NULL, NULL, NULL, NULL, NULL),
(39, NULL, 44, 1, 0, 0, NULL, '2020-09-01 09:13:48', NULL, 0, 1, NULL, NULL),
(40, NULL, 45, 1, 1, 0, NULL, '2020-09-01 09:13:48', NULL, 3269, 2, NULL, NULL),
(41, NULL, 46, 1, 2, 0, NULL, '2020-09-01 09:13:48', NULL, 5002, 2, NULL, NULL),
(42, NULL, 47, 1, 3, 1, NULL, '2020-09-01 09:13:48', NULL, 5002, 1, NULL, NULL),
(43, 20, 49, NULL, 0, 0, NULL, '2020-09-01 09:17:49', NULL, NULL, NULL, NULL, NULL),
(44, 20, 50, NULL, 1, 1, NULL, '2020-09-01 09:17:49', NULL, NULL, NULL, NULL, NULL),
(45, NULL, 51, 2, 0, 0, NULL, '2020-09-01 09:17:50', NULL, 0, 1, NULL, NULL),
(46, NULL, 52, 2, 1, 0, NULL, '2020-09-01 09:17:50', NULL, 0, 2, NULL, NULL),
(47, NULL, 53, 2, 2, 0, NULL, '2020-09-01 09:17:50', NULL, 2345, 2, NULL, NULL),
(48, NULL, 54, 2, 3, 1, NULL, '2020-09-01 09:17:50', NULL, 2345, 1, NULL, NULL),
(49, 21, 55, NULL, 0, 0, NULL, '2020-09-01 09:17:50', NULL, NULL, NULL, NULL, NULL),
(50, 21, 56, NULL, 1, 1, NULL, '2020-09-01 09:17:50', NULL, NULL, NULL, NULL, NULL),
(51, NULL, 57, 3, 0, 0, NULL, '2020-09-01 09:17:51', NULL, 0, 1, NULL, NULL),
(52, NULL, 58, 3, 1, 0, NULL, '2020-09-01 09:17:51', NULL, 0, 2, NULL, NULL),
(53, NULL, 59, 3, 2, 0, NULL, '2020-09-01 09:17:51', NULL, 2352, 2, NULL, NULL),
(54, NULL, 60, 3, 3, 1, NULL, '2020-09-01 09:17:51', NULL, 2352, 1, NULL, NULL),
(55, 22, 61, NULL, 0, 0, NULL, '2020-09-01 09:21:47', NULL, NULL, NULL, NULL, NULL),
(56, 22, 62, NULL, 1, 1, NULL, '2020-09-01 09:21:47', NULL, NULL, NULL, NULL, NULL),
(57, NULL, 63, 4, 0, 0, NULL, '2020-09-01 09:21:48', NULL, 0, 1, NULL, NULL),
(58, NULL, 64, 4, 1, 0, NULL, '2020-09-01 09:21:48', NULL, 0, 2, NULL, NULL),
(59, NULL, 65, 4, 2, 0, NULL, '2020-09-01 09:21:48', NULL, 2345, 2, NULL, NULL),
(60, NULL, 66, 4, 3, 1, NULL, '2020-09-01 09:21:48', NULL, 2345, 1, NULL, NULL);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
