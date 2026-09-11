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
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (43,'Afrique'),(44,'Amériques'),(42,'Asie'),(41,'Europe'),(45,'Océanie');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `destination`
--

LOCK TABLES `destination` WRITE;
/*!40000 ALTER TABLE `destination` DISABLE KEYS */;
INSERT INTO `destination` VALUES (51,'Bora-Bora','Polynésie française','plage','Lagon turquoise et bungalows sur pilotis pour un séjour de rêve.',45,NULL),(52,'Kyoto','Japon','ville','Temples anciens, jardins zen et ruelles traditionnelles.',42,NULL),(53,'Chamonix','France','montagne','Au pied du Mont-Blanc, paradis des amateurs de montagne.',41,NULL),(54,'Costa Rica','Costa Rica','nature','Forêts tropicales, volcans et faune exceptionnelle.',44,NULL),(55,'Santorin','Grèce','plage','Maisons blanches et couchers de soleil sur la mer Égée.',41,NULL),(56,'Marrakech','Maroc','ville','Souks colorés, palais et saveurs épicées au cœur de la médina.',43,NULL);
/*!40000 ALTER TABLE `destination` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (23,'admin@escale.fr','[\"ROLE_ADMIN\"]','$2y$13$44lfKmhxVHjGRsu2VI4Yg.m3R38ilDkuYHr.rFuJhsElCzqQjrRG.','Admin','2026-09-06 18:55:06',1,0),(25,'membre@escale.fr','[]','$2y$13$3pHUt5PkUaKdh0pQE2102uEaTpYhAv/bvstkjwaLeErzqyl.h6qk.','Voyageur','2026-09-06 18:55:07',1,0),(26,'employe2@escale.fr','[\"ROLE_EMPLOYEE\"]','$2y$13$575OpLEpJhTuv9o3PYBEqeUDJSnYCSDQUK9hranQ0ViLnB2BbO/jy','Employe2','2026-09-07 14:59:23',1,0),(35,'azerty@azerty.com','[]','$2y$13$3vs9haIPlYb5dG146Hma7Or9PuWF5MOIW97UAyEjKVZo/CyogzLe6','Azerty','2026-09-09 17:15:54',1,0),(36,'qsdfg@qsdfg.com','[\"ROLE_EMPLOYEE\"]','$2y$13$waIgDTkHJ9bC4r65nA4nxuvhqLk/x1OHhPUU29Ac/EX026GL3Rp1C','Qsdfg','2026-09-09 17:40:22',1,0);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `contact_request`
--

LOCK TABLES `contact_request` WRITE;
/*!40000 ALTER TABLE `contact_request` DISABLE KEYS */;
INSERT INTO `contact_request` VALUES (15,'interet','Julie Martin','julie.martin@example.com','0612345678','Je rêve de partir à Bora-Bora cet été, quelles sont vos offres ?','nouveau','2026-09-06 18:55:07',51,25),(16,'rendez-vous','Thomas Leroy','thomas.leroy@example.com','0698765432','Disponible en fin de semaine pour discuter d\'un voyage au Japon.','nouveau','2026-09-06 18:55:07',52,NULL),(17,'contact','Sarah Benali','sarah.benali@example.com',NULL,'Bonjour, proposez-vous des séjours sur mesure en famille ?','nouveau','2026-09-06 18:55:07',NULL,NULL);
/*!40000 ALTER TABLE `contact_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_destination`
--

LOCK TABLES `user_destination` WRITE;
/*!40000 ALTER TABLE `user_destination` DISABLE KEYS */;
INSERT INTO `user_destination` VALUES (23,53),(26,51),(26,53),(26,54),(26,56);
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

-- Dump completed on 2026-09-11 14:57:06
