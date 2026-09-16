-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: voyages
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_64C19C15E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (43,'Afrique'),(44,'Amériques'),(42,'Asie'),(41,'Europe'),(45,'Océanie');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_request`
--

DROP TABLE IF EXISTS `contact_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_request` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `destination_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A1B8AE1E816C6140` (`destination_id`),
  KEY `IDX_A1B8AE1EA76ED395` (`user_id`),
  CONSTRAINT `FK_A1B8AE1E816C6140` FOREIGN KEY (`destination_id`) REFERENCES `destination` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A1B8AE1EA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_request`
--

LOCK TABLES `contact_request` WRITE;
/*!40000 ALTER TABLE `contact_request` DISABLE KEYS */;
INSERT INTO `contact_request` VALUES (15,'interet','Julie Martin','julie.martin@example.com','0612345678','Je rêve de partir à Bora-Bora cet été, quelles sont vos offres ?','nouveau','2026-09-06 18:55:07',51,NULL),(16,'rendez-vous','Thomas Leroy','thomas.leroy@example.com','0698765432','Disponible en fin de semaine pour discuter d\'un voyage au Japon.','nouveau','2026-09-06 18:55:07',52,NULL),(17,'contact','Sarah Benali','sarah.benali@example.com',NULL,'Bonjour, proposez-vous des séjours sur mesure en famille ?','nouveau','2026-09-06 18:55:07',NULL,NULL);
/*!40000 ALTER TABLE `contact_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `destination`
--

DROP TABLE IF EXISTS `destination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `destination` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `type` varchar(30) NOT NULL,
  `description` longtext NOT NULL,
  `category_id` int NOT NULL,
  `image` longtext,
  PRIMARY KEY (`id`),
  KEY `IDX_3EC63EAA12469DE2` (`category_id`),
  CONSTRAINT `FK_3EC63EAA12469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `destination`
--

LOCK TABLES `destination` WRITE;
/*!40000 ALTER TABLE `destination` DISABLE KEYS */;
INSERT INTO `destination` VALUES (51,'Bora-Bora','Polynésie française','plage','Lagon turquoise et bungalows sur pilotis pour un séjour de rêve.',45,NULL),(52,'Kyoto','Japon','ville','Temples anciens, jardins zen et ruelles traditionnelles.',42,NULL),(53,'Chamonix','France','montagne','Au pied du Mont-Blanc, paradis des amateurs de montagne.',41,NULL),(54,'Costa Rica','Costa Rica','nature','Forêts tropicales, volcans et faune exceptionnelle.',44,NULL),(55,'Santorin','Grèce','plage','Maisons blanches et couchers de soleil sur la mer Égée.',41,NULL),(56,'Marrakech','Maroc','ville','Souks colorés, palais et saveurs épicées au cœur de la médina.',43,NULL);
/*!40000 ALTER TABLE `destination` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20260831120121','2026-09-11 13:40:53',242),('DoctrineMigrations\\Version20260902122331','2026-09-11 13:40:53',56),('DoctrineMigrations\\Version20260902124235','2026-09-11 13:40:53',97),('DoctrineMigrations\\Version20260906170808','2026-09-11 13:40:53',974),('DoctrineMigrations\\Version20260906175941','2026-09-11 13:40:54',183),('DoctrineMigrations\\Version20260906185346','2026-09-11 13:40:55',94),('DoctrineMigrations\\Version20260908130000','2026-09-11 13:40:55',122);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `active` tinyint NOT NULL DEFAULT '1',
  `temporary` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (38,'admin@escale.fr','[\"ROLE_ADMIN\"]','$2y$10$Vn9gn7ETH9Nz2QZRxRaCeOt6gGTNFRw4FZiw2QvTqzYsD0VBCNY32','Admin','2026-09-11 17:18:07',1,0),(39,'employe1@escale.fr','[\"ROLE_EMPLOYEE\"]','$2y$10$3I1IMilmH7lm358ONyYmA.CDOwSk0iWITGo8LWA1NENR9QXNbBYeq','Employe1','2026-09-11 17:18:07',1,0),(40,'employe2@escale.fr','[\"ROLE_EMPLOYEE\"]','$2y$10$nktR0hMbuCcwOoYV6tFjQ.fJ7qZRA4Ye03TTIGndujU9EusnvcuzK','Employe2','2026-09-11 17:18:07',0,0),(41,'membre1@escale.fr','[]','$2y$10$mrtohZLESNRj5g44No9lpOsPr7mgHTYnsApmMuROkRE33Qug1nI/q','Membre1','2026-09-11 17:18:07',1,0),(42,'membre2@escale.fr','[]','$2y$10$7LsoLBZ2./2/EMgxzNgKz.LcfiWsbWyDj0AawK3LciSyNizu61HoW','Membre2','2026-09-11 17:18:07',0,0);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_destination`
--

DROP TABLE IF EXISTS `user_destination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_destination` (
  `user_id` int NOT NULL,
  `destination_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`destination_id`),
  KEY `IDX_97DDF73FA76ED395` (`user_id`),
  KEY `IDX_97DDF73F816C6140` (`destination_id`),
  CONSTRAINT `FK_97DDF73F816C6140` FOREIGN KEY (`destination_id`) REFERENCES `destination` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_97DDF73FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_destination`
--

LOCK TABLES `user_destination` WRITE;
/*!40000 ALTER TABLE `user_destination` DISABLE KEYS */;
INSERT INTO `user_destination` VALUES (38,53),(41,51),(41,55),(41,56);
/*!40000 ALTER TABLE `user_destination` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-16 12:28:04
