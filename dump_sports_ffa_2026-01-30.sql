/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sports_ffa
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0+deb12u2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accommodations`
--

DROP TABLE IF EXISTS `accommodations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `accommodations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accommodations`
--

LOCK TABLES `accommodations` WRITE;
/*!40000 ALTER TABLE `accommodations` DISABLE KEYS */;
INSERT INTO `accommodations` VALUES
(1,'Chambre twin équipage'),
(2,'Chambre double'),
(3,'Chambre twin partagée'),
(4,'Chambre individuelle'),
(5,'Repas de midi'),
(6,'Inscription seule');
/*!40000 ALTER TABLE `accommodations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aircrafts`
--

DROP TABLE IF EXISTS `aircrafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `aircrafts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `callsign` varchar(8) NOT NULL,
  `speed` varchar(255) NOT NULL,
  `flyingclub` varchar(30) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `oaci` varchar(8) DEFAULT NULL,
  `brand` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_59AF8E00A76ED395` (`user_id`),
  CONSTRAINT `FK_59AF8E00A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aircrafts`
--

LOCK TABLES `aircrafts` WRITE;
/*!40000 ALTER TABLE `aircrafts` DISABLE KEYS */;
INSERT INTO `aircrafts` VALUES
(1,'F-BRTL','90','Privé','DR340','LFOV','ROBIN',11),
(2,'F-GBVH','80','Chateauneuf sur cher','DR400','LFFU','Robin',21),
(3,'F-BRTL','80','privé','DR340','LFOV','Robin',5),
(4,'F-GBVH','80','UNAC Châteauneuf sur Cher','DR400','LFFU','Robin',32),
(5,'F-BRTL','75','Privé','DR340','LFOV','ROBIN',11),
(6,'F-HXLZ','75','Brocard','Elixir','LFOX','Elixir Aircraft',26),
(7,'F-GSRJ','75','AC Vichy','DR400','LFLV','ROBIN',53),
(8,'F-GDIK','95','DACA','152','lfcs','cessna',2),
(9,'F-HTEM','75','A/C DES FINANCES','DR400','LFOX','ROBIN',56),
(10,'F-BXIX','75','ACHS','172','LFPT','Cessna',36),
(11,'F-BXEO','75','JEAN PIQUE NOT','DR400','LFRC','ROBIN',61),
(12,'F-BOCR','75','Aéroclub de LAON','DR220','LFAF','Delmontez-Robin',65),
(13,'F-GUXI','75','Aéroclub de VERSAILLES','DR420','LFPZ','ROBIN',66),
(14,'F-BSJV','80','PRIVÉ','Dr360','LFPL','Robin',69),
(15,'F-BOFF','75','des Cheminots','DR221','LFOX','Robin',72),
(16,'F-PDPE','75','ACM','MCR4S','LFOV','Dyn\'Aéro',50),
(17,'F-GUXI','75','Versailles','Dauphin 2+2','LFPZ','DR400',75),
(18,'F-GUXI','75','ACV','DR 400','LFPZ','Robin',76),
(19,'F-HDLI','75','Montélimar Porte de Provence','F150','LFLQ','Cessna',60),
(20,'F-PGMC','75','Privé',NULL,NULL,'MCR 4S',83),
(21,'F-GAHM','75','Aéroclub de l\'Aisne','DR400','LFOW','Robin',84),
(22,'F-GYRL','75','Aeroclub de l’ENAC','DR400','LFCL','Robin',33),
(23,'F-GNJF','75','Angers marcé','DA20','LFJR','Diamond',87),
(24,'F-GNJF','75','Angers marcé','DA20','LFJR','Diamond',15);
/*!40000 ALTER TABLE `aircrafts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `competition_accommodation`
--

DROP TABLE IF EXISTS `competition_accommodation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `competition_accommodation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `competition_id` int(11) DEFAULT NULL,
  `accommodation_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DAED96B27B39D312` (`competition_id`),
  KEY `IDX_DAED96B28F3692CD` (`accommodation_id`),
  CONSTRAINT `FK_DAED96B27B39D312` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`),
  CONSTRAINT `FK_DAED96B28F3692CD` FOREIGN KEY (`accommodation_id`) REFERENCES `accommodations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `competition_accommodation`
--

LOCK TABLES `competition_accommodation` WRITE;
/*!40000 ALTER TABLE `competition_accommodation` DISABLE KEYS */;
INSERT INTO `competition_accommodation` VALUES
(2,14,4,12000.00),
(3,14,3,7500.00),
(6,11,2,20000.00),
(7,11,4,20000.00),
(8,11,1,20000.00),
(9,12,2,20000.00),
(10,12,4,30000.00),
(12,12,1,20000.00),
(13,13,2,20000.00),
(14,13,4,12000.00),
(15,13,3,10000.00);
/*!40000 ALTER TABLE `competition_accommodation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `competitions`
--

DROP TABLE IF EXISTS `competitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `competitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typecompetition_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `end_date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `start_registration` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `end_registration` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `location` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `selectable` tinyint(1) DEFAULT NULL,
  `information` longtext DEFAULT NULL,
  `payment_info` longtext DEFAULT NULL,
  `role` longtext DEFAULT NULL COMMENT '(DC2Type:simple_array)',
  `programme_pdf` varchar(255) DEFAULT NULL,
  `elite_max` int(11) DEFAULT NULL,
  `honor_max` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A7DD463DA4842409` (`typecompetition_id`),
  CONSTRAINT `FK_A7DD463DA4842409` FOREIGN KEY (`typecompetition_id`) REFERENCES `type_competition` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `competitions`
--

LOCK TABLES `competitions` WRITE;
/*!40000 ALTER TABLE `competitions` DISABLE KEYS */;
INSERT INTO `competitions` VALUES
(1,1,'CIRRA NO Blois','2025-04-25 00:00:00','2025-04-26 00:00:00','2025-03-01 00:00:00','2025-04-01 00:00:00','Blois - Le Breuil LFOQ','2025-06-01 23:59:59',0,NULL,NULL,NULL,NULL,NULL,NULL),
(2,2,'CIRPP NO Blois','2025-04-26 00:00:00','2025-04-27 00:00:00','2025-03-01 00:00:00','2025-04-01 00:00:00','Blois - Le Bleuil LFOQ','2025-06-01 23:59:59',0,NULL,NULL,NULL,NULL,NULL,NULL),
(3,2,'CIRPP SE Valréas','2025-04-11 00:00:00','2025-04-12 00:00:00','2025-03-01 00:00:00','2025-04-01 00:00:00','Valréas LFNV','2025-06-01 23:59:59',0,NULL,NULL,NULL,NULL,NULL,NULL),
(4,1,'CIRRA SE Valréas','2025-04-12 00:00:00','2025-04-13 00:00:00','2025-03-01 00:00:00','2025-04-01 00:00:00','Valréas LFNV','2025-06-01 23:59:59',0,NULL,NULL,NULL,NULL,NULL,NULL),
(5,2,'CIRPP NE Montbéliard','2025-05-09 00:00:00','2025-05-10 00:00:00','2025-03-01 00:00:00','2025-05-01 00:00:00','Monbéliard LFSM','2025-06-01 23:59:59',0,NULL,NULL,NULL,NULL,NULL,NULL),
(6,1,'CIRRA NE Montbéliard','2025-05-10 00:00:00','2025-05-11 00:00:00','2025-03-01 00:00:00','2025-05-01 00:00:00','Monbéliard LFSM','2025-06-01 23:59:59',0,NULL,NULL,NULL,NULL,NULL,NULL),
(7,2,'CIRPP SO Couhé-Vérac','2025-05-23 00:00:00','2025-05-24 00:00:00','2025-03-01 00:00:00','2025-05-01 00:00:00','Couhé-Vérac LFDV','2025-06-01 23:59:59',0,NULL,NULL,NULL,NULL,NULL,NULL),
(8,1,'CIRRA SO Couhé-Vérac','2025-05-24 00:00:00','2025-05-25 00:00:00','2025-03-01 00:00:00','2025-05-01 00:00:00','Couhé-Vérac LFDV','2025-06-01 23:59:59',0,NULL,NULL,NULL,NULL,NULL,NULL),
(9,2,'CIRPP IDF/HDF Compiégne','2025-06-13 00:00:00','2025-06-14 00:00:00','2025-03-01 00:00:00','2025-06-01 00:00:00','Compiégne Margny LFAD','2025-06-01 23:59:59',0,NULL,NULL,NULL,NULL,NULL,NULL),
(10,1,'CIRRA IDF/HDF Compiégne','2025-06-14 00:00:00','2025-06-15 00:00:00','2025-03-01 00:00:00','2025-06-01 00:00:00','Compiégne Margny LFAD','2025-06-01 23:59:59',0,NULL,NULL,NULL,NULL,NULL,NULL),
(11,1,'Championnat de France de Rallye - Vesoul','2025-06-27 00:00:00','2025-06-29 00:00:00','2025-06-01 00:00:00','2025-06-29 00:00:00','Vesoul - LFQW','2025-06-01 23:59:59',0,'<strong><u>Contacts et renseignements</u></strong>\r\n\r\n<strong>Directeur de compétition</strong> : Philippe Muller </br>\r\nTéléphone : 06 62 08 14 80  </br>\r\nEmail : docpmuller@gmail.com</br>\r\n<strong>Routeur </strong>: Jean-Claude LOCHET</br>\r\nTéléphone :   06 77 21 93 40</br>\r\nEmail : breizhaeronautik@gmail.com</br>\r\n<strong><u>Important</u> :</strong></br>\r\nVous devrez présenter les papiers de l’avion, votre licence de pilote, votre licence FFA et votre certificat médical de classe 1, ou 2. </br>\r\nPour les mineurs, remplir l\'autorisation des parents et l\'auto-questionnaire de santé en annexe du règlement. </br>\r\n<strong><u>Règlement de la compétition</u> :</strong></br>\r\nLe règlement de la compétition, ainsi que des conseils utiles pour les nouveaux participants, peuvent être consultés sur le site Internet de la FFA  http://www.ff-aero.fr </br>\r\n<strong><u>Evénements</u> :</strong></br>\r\nSi lors d\'un championnat vous avez été témoin ou victime de tout événement ou incident utile ou important à nous signaler, merci d\'utiliser l\'adresse spécifique suivante : competitionrex@ff-aero.fr</br>','⚠️ <strong>Votre inscription sera validée après réception de votre règlement.</strong></br></br>\r\nL\'accueil et la prise en charge par la FFA, organisatrice des championnats de France s’effectue le vendredi 27 juin à partir de 10h00</br></br>\r\n\r\nL’inscription comprend l’hôtellerie et la restauration du vendredi 27 juin à midi au dimanche 29 juin à midi inclus. L\'hébergement des championnats se réparti sur deux hôtels Brit Hotel </br></br>\r\n\r\nPaiement par virement sur le compte  bancaire de la <strong>Fédération Française Aéronautique.</strong></br>\r\nIndiquer impérativement l\'objet :<strong> CDF RA 2025 + Votre Nom</strong></br>\r\n<li>IBAN : FR76 3000 4002 3200 0200 0288 185</li>\r\n<li>BIC : BNPAFRPPXXX</li>\r\n<li>Objet : CDF RA 2025 + Votre Nom</li>',NULL,NULL,NULL,NULL),
(12,3,'Championnat de France ANR - Vichy','2025-09-05 00:00:00','2025-09-07 00:00:00','2025-07-12 00:00:00','2025-08-29 00:00:00','Vichy - LFLV','2025-06-01 23:59:59',0,'<strong><u>Contacts et renseignements</u></strong></br>\r\n\r\n⚠️ <strong>Cette année il est prévu une navigation le Vendredi après-midi.</strong></br>\r\n\r\nCarte utilisée pour la compétitions IGN D42-43 au 1:150 000 éme</br>\r\n\r\n<strong>Directeur de compétition</strong> : Robert RIUTORT</br>\r\nTéléphone : 06 85 82 78 63  </br>\r\nEmail : robert.riutort@orange.fr</br>\r\n<strong>Routeur </strong>: Joël TREMBLET</br>\r\nTéléphone :   06 62 27 45 12</br>\r\nEmail : joel.tremblet@gmail.com</br>\r\n<strong><u>Important</u> :</strong></br>\r\nVous devrez présenter les papiers de l’avion, votre licence de pilote, votre licence FFA et votre certificat médical de classe 1, ou 2. </br>\r\nPour les mineurs, remplir l\'autorisation des parents et l\'auto-questionnaire de santé en annexe du règlement. </br>\r\n<strong><u>Règlement de la compétition</u> :</strong></br>\r\nLe règlement de la compétition,  peut être consulté sur le site </br>\r\n<strong><u>Evénements</u> :</strong></br>\r\nSi lors d\'un championnat vous avez été témoin ou victime de tout événement ou incident utile ou important à nous signaler, merci d\'utiliser l\'adresse spécifique suivante : competitionrex@ff-aero.fr</br>','⚠️ <strong>Votre inscription sera validée après réception de votre règlement.</strong></br></br>\r\nL\'accueil et la prise en charge par la FFA, organisatrice des championnats de France s’effectue le vendredi 05 septembre à partir de 10 h</br></br>\r\n\r\nL’inscription comprend l’hôtellerie et la restauration du vendredi 05 septembre à 12 h au dimanche 07 septembre à midi inclus.  Pour les chambres individuelles, le prix affiché correspond à 2 chambres pour un équipage.\r\n L\'hébergement du championnat sera à :          Centre International de Séjour  -  Parc Omnisport de Vichy    -   Route du Pont de l\'Europe   -   03700  -  Bellerive sur Allier</br></br>\r\n\r\nPaiement par virement sur le compte  bancaire de la <strong>Fédération Française Aéronautique.</strong></br>\r\nIndiquer impérativement l\'objet :<strong> CDF ANR 2025 + Votre Nom</strong></br>\r\n<li>IBAN : FR76 3000 4002 3200 0200 0288 185</li>\r\n<li>BIC : BNPAFRPPXXX</li>\r\n<li>Objet : CDF ANR 2025 + Votre Nom</li>',NULL,'687211da9b72c.pdf',11,14),
(13,2,'CdF Pilotage de Précision - Rochefort','2025-09-19 00:00:00','2025-09-21 00:00:00','2025-08-01 00:00:00','2025-08-31 00:00:00','Rochefort - LFDN','2025-06-01 23:59:59',0,'<strong><u>Contacts et renseignements</u></strong>\r\n\r\n<strong>Directeur de compétition</strong> : Philippe MULLER </br>\r\nTéléphone : 06 62 08 14 80  </br>\r\nEmail : docpmuller@gmail.com</br>\r\n<strong>Routeur </strong>: Bertrand DE GREEF</br>\r\nTéléphone :   06 86 00 25 17</br>\r\nEmail : bertrand.degreef@gmail.com</br>\r\n<strong><u>Important</u> :</strong></br>\r\nVous devrez présenter les papiers de l’avion, votre licence de pilote, votre licence FFA et votre certificat médical de classe 1, ou 2. </br>\r\nPour les mineurs, remplir l\'autorisation des parents et l\'auto-questionnaire de santé en annexe du règlement. </br>\r\n<strong><u>Règlement de la compétition</u> :</strong></br>\r\nLe règlement de la compétition peut être consulté sur le site </br>\r\n<strong><u>Evénements</u> :</strong></br>\r\nSi lors d\'un championnat vous avez été témoin ou victime de tout événement ou incident utile ou important à nous signaler, merci d\'utiliser l\'adresse spécifique suivante : competitionrex@ff-aero.fr</br>','⚠️ <strong>Votre inscription sera validée après réception de votre règlement.</strong></br></br>\r\nL\'accueil et la prise en charge par la FFA, organisatrice des championnats de France s’effectue le vendredi 19 septembre à partir de 10h00</br></br>\r\n\r\nL’inscription comprend l’hôtellerie et la restauration du vendredi 19 septembre à midi au dimanche 21 septembre à midi inclus. L\'hébergement des championnats sera à l\'hotel</br></br>\r\n\r\nPaiement par virement sur le compte  bancaire de la <strong>Fédération Française Aéronautique.</strong></br>\r\nIndiquer impérativement l\'objet :<strong> CDF PP 2025 + Votre Nom</strong></br>\r\n<li>IBAN : FR76 3000 4002 3200 0200 0288 185</li>\r\n<li>BIC : BNPAFRPPXXX</li>\r\n<li>Objet : CDF PP 2025 + Votre Nom</li>',NULL,'68c041a1065d6.pdf',NULL,NULL),
(14,2,'Test','2025-10-01 00:00:00','2025-10-05 00:00:00','2025-06-07 00:00:00','2025-09-30 00:00:00','Cherbourg','2025-06-06 18:48:27',0,'Bla bla bla !','Envoyer votre règlement par virement sur la banque CCF\nIBAN : FR76 4556 7890 1234 2345 78',NULL,NULL,NULL,NULL),
(15,3,'CdF ANR - Châteaudun','2024-09-20 00:00:00','2024-09-22 00:00:00','2024-08-01 00:00:00','2024-08-31 00:00:00','Châteaudun','2025-07-12 10:52:26',0,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `competitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `competitions_users`
--

DROP TABLE IF EXISTS `competitions_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `competitions_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `competition_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_comp_user` (`competition_id`,`user_id`),
  KEY `IDX_F9E5DA4E7B39D312` (`competition_id`),
  KEY `IDX_F9E5DA4EA76ED395` (`user_id`),
  CONSTRAINT `FK_F9E5DA4E7B39D312` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`),
  CONSTRAINT `FK_F9E5DA4EA76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `competitions_users`
--

LOCK TABLES `competitions_users` WRITE;
/*!40000 ALTER TABLE `competitions_users` DISABLE KEYS */;
INSERT INTO `competitions_users` VALUES
(16,14,1,'Directeur de compétition'),
(17,14,2,'Routeur'),
(18,14,3,'Informatique'),
(19,12,42,'Directeur de compétition'),
(20,12,1,'Routeur'),
(22,12,93,'Informatique'),
(23,13,12,'Directeur de compétition'),
(24,13,9,'Routeur'),
(25,13,3,'Informatique'),
(26,13,2,'Informatique'),
(28,14,120,'Autre');
/*!40000 ALTER TABLE `competitions_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `crew_competition_accommodation`
--

DROP TABLE IF EXISTS `crew_competition_accommodation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `crew_competition_accommodation` (
  `competition_accommodation_id` int(11) NOT NULL,
  `crews_id` int(11) NOT NULL,
  PRIMARY KEY (`competition_accommodation_id`,`crews_id`),
  KEY `IDX_43238728593B5CCD` (`competition_accommodation_id`),
  KEY `IDX_43238728B3F00855` (`crews_id`),
  CONSTRAINT `FK_43238728593B5CCD` FOREIGN KEY (`competition_accommodation_id`) REFERENCES `competition_accommodation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_43238728B3F00855` FOREIGN KEY (`crews_id`) REFERENCES `crews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `crew_competition_accommodation`
--

LOCK TABLES `crew_competition_accommodation` WRITE;
/*!40000 ALTER TABLE `crew_competition_accommodation` DISABLE KEYS */;
INSERT INTO `crew_competition_accommodation` VALUES
(9,19),
(9,30),
(9,34),
(9,42),
(9,44),
(10,26),
(10,33),
(10,43),
(10,46),
(12,20),
(12,21),
(12,22),
(12,23),
(12,24),
(12,25),
(12,27),
(12,29),
(12,31),
(12,41),
(13,49),
(13,71),
(13,74),
(13,76),
(14,50),
(14,53),
(14,57),
(14,63),
(14,64),
(14,65),
(14,67),
(14,68),
(14,69),
(14,70),
(14,72),
(14,73),
(15,51),
(15,52),
(15,54),
(15,56),
(15,58),
(15,59),
(15,60),
(15,61),
(15,62),
(15,66);
/*!40000 ALTER TABLE `crew_competition_accommodation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `crews`
--

DROP TABLE IF EXISTS `crews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `crews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `competition_id` int(11) DEFAULT NULL,
  `pilot_id` int(11) DEFAULT NULL,
  `navigator_id` int(11) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `callsign` varchar(8) DEFAULT NULL,
  `aircraft_speed` varchar(255) DEFAULT NULL,
  `aircraft_type` varchar(20) DEFAULT NULL,
  `aircraft_flyingclub` varchar(30) DEFAULT NULL,
  `aircraft_sharing` tinyint(1) DEFAULT NULL,
  `pilot_shared` varchar(30) DEFAULT NULL,
  `registered_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `registeredby_id` int(11) NOT NULL,
  `validation_payment` tinyint(1) DEFAULT NULL,
  `aircraft_oaci` varchar(8) DEFAULT NULL,
  `aircraft_brand` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3EE854EB7B39D312` (`competition_id`),
  KEY `IDX_3EE854EBCE55439B` (`pilot_id`),
  KEY `IDX_3EE854EB473C72C` (`navigator_id`),
  KEY `IDX_3EE854EBF1C1B900` (`registeredby_id`),
  CONSTRAINT `FK_3EE854EB473C72C` FOREIGN KEY (`navigator_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_3EE854EB7B39D312` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`),
  CONSTRAINT `FK_3EE854EBCE55439B` FOREIGN KEY (`pilot_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_3EE854EBF1C1B900` FOREIGN KEY (`registeredby_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `crews`
--

LOCK TABLES `crews` WRITE;
/*!40000 ALTER TABLE `crews` DISABLE KEYS */;
INSERT INTO `crews` VALUES
(16,15,32,21,'Elite','F-GBVH',NULL,NULL,NULL,0,NULL,'2025-07-12 12:20:01',1,0,NULL,NULL),
(17,15,26,24,'Elite','F-HFLY',NULL,NULL,NULL,0,NULL,'2025-07-12 12:28:30',1,NULL,NULL,NULL),
(19,12,11,39,'Elite','F-BRTL','75','DR340','Privé',1,'Alain Dambre','2025-07-15 10:18:26',11,0,'LFOV','ROBIN'),
(20,12,32,21,'Elite','F-GBVH','75','DR400','UNAC Châteauneuf sur Cher',0,NULL,'2025-07-18 11:42:11',32,0,'LFFU','Robin'),
(21,12,26,24,'Elite','F-HXLY','75','Elixir','Brocard',0,NULL,'2025-07-18 14:29:40',26,0,'LFOX','Elixir Aircraft'),
(22,12,53,55,'Honneur','F-GSRJ','75','DR400','AC Vichy',0,NULL,'2025-07-31 12:13:52',53,0,'LFLV','ROBIN'),
(23,12,56,57,'Elite','F-HTEM','75','DR400','A/C DES FINANCES',0,NULL,'2025-08-01 08:07:12',56,0,'LFOX','ROBIN'),
(24,12,36,59,'Elite','F-BXIX','75','172','ACHS',0,NULL,'2025-08-04 20:35:03',36,0,'LFPT','Cessna'),
(25,12,61,62,'Elite','F-BXEO','75','DR400','JEAN PIQUE NOT',0,NULL,'2025-08-13 16:21:27',61,0,'LFRC','ROBIN'),
(26,12,65,68,'Honneur','F-BOCR','75','DR220','Aéroclub de LAON',0,NULL,'2025-08-15 07:19:24',65,1,'LFAF','Delmontez-Robin'),
(27,12,66,67,'Elite','F-GUXI','75','DR420','Aéroclub de VERSAILLES',1,'Eric PAGERON','2025-08-15 21:05:48',66,0,'LFPZ','ROBIN'),
(29,12,63,5,'Elite','F-BXRS','75','DR340','privé',1,'MARREAU  Claude','2025-08-16 12:02:30',5,0,'LFOV','Robin'),
(30,12,69,71,'Honneur','F-BSJV','75','Dr360','PRIVÉ',0,NULL,'2025-08-17 14:17:27',69,1,'LFPL','Robin'),
(31,12,70,72,'Honneur','F-BOFF','75','DR221','des Cheminots',0,NULL,'2025-08-17 15:13:45',72,0,'LFOX','Robin'),
(33,12,73,64,'Honneur','F-GPAP','75','DR400','CLAP73',0,NULL,'2025-08-18 19:35:13',64,0,'LFLE','Robin'),
(34,12,74,50,'Elite','F-PDPE','75','MCR4S','ACM',0,NULL,'2025-08-20 09:58:39',50,1,'LFOV','Dyn\'Aéro'),
(41,12,75,76,'Honneur','F-GUXI','75','Dauphin 2+2','Versailles',1,'Du Ranquet Benoît','2025-08-21 13:39:39',75,0,'LFPZ','DR400'),
(42,12,60,82,'Elite','F-HDLI','75','F150','Montélimar Porte de Provence',0,NULL,'2025-08-21 16:31:27',60,0,'LFLQ','Cessna'),
(43,12,23,83,'Honneur','F-PGMC','75',NULL,'Privé',0,NULL,'2025-08-23 07:17:06',83,0,NULL,'MCR 4S'),
(44,12,85,84,'Honneur','F-GAHM','75','DR400','Aéroclub de l\'Aisne',0,NULL,'2025-08-23 08:23:45',84,0,'LFOW','Robin'),
(46,12,33,86,'Honneur','F-GYRL','75','DR400','Aeroclub de l’ENAC',0,NULL,'2025-08-24 12:57:21',33,0,'LFCL','Robin'),
(49,13,78,NULL,'Elite','F-GDRS','70','C152',NULL,1,'Le Camu','2025-09-09 07:31:54',1,0,NULL,'Cessna'),
(50,13,23,NULL,'Honneur','F-PGMC','80','MCR 4S',NULL,0,NULL,'2025-09-09 07:33:43',1,0,NULL,'Dyn\'Aéro'),
(51,13,26,NULL,'Honneur','F-HXLY','75','Elixir',NULL,0,NULL,'2025-09-09 07:34:55',1,0,NULL,'Elixir Aircraft'),
(52,13,24,NULL,'Elite','F-HXLY','75','Elixir',NULL,0,NULL,'2025-09-09 07:35:46',1,0,NULL,'Elixir Aircraft'),
(53,13,28,NULL,'Elite','F-GVHS','70','DA20',NULL,0,NULL,'2025-09-09 07:37:04',1,0,NULL,'Diamond'),
(54,13,38,NULL,'Honneur','F-GIYA','70','C152',NULL,0,NULL,'2025-09-09 07:38:36',1,0,NULL,'Cessna'),
(55,13,19,NULL,'Elite','F-GIYA','70','C152',NULL,0,NULL,'2025-09-09 07:39:39',1,0,NULL,'Cessna'),
(56,13,60,NULL,'Elite','F-GNAP','70','F150',NULL,0,NULL,'2025-09-09 07:40:44',1,0,NULL,'Cessna'),
(57,13,61,NULL,'Elite','F-GBIE','80','DR400',NULL,0,NULL,'2025-09-09 07:41:53',1,0,NULL,'Robin'),
(58,13,31,NULL,'Elite','F-GDIJ','70','C152',NULL,0,NULL,'2025-09-09 07:42:45',1,0,NULL,'Cessna'),
(59,13,79,NULL,'Elite','F-GDIJ','70','C152',NULL,0,NULL,'2025-09-09 07:43:29',1,0,NULL,'Cessna'),
(60,13,32,NULL,'Elite','F-GBVH','80','DR420',NULL,0,NULL,'2025-09-09 07:45:00',1,0,NULL,'Robin'),
(61,13,34,NULL,'Elite','F-GOSN','70','C152',NULL,0,NULL,'2025-09-09 07:46:07',1,0,NULL,'Cessna'),
(62,13,8,NULL,'Honneur','F-BSJQ','80','DR300',NULL,0,NULL,'2025-09-09 07:46:50',1,0,NULL,'Robin'),
(63,13,30,NULL,'Elite','F-HLMZ','70','P2008',NULL,0,NULL,'2025-09-09 07:49:00',1,0,NULL,'Tecnam'),
(64,13,49,NULL,'Elite','F-HLMZ','70','P2008',NULL,0,NULL,'2025-09-09 07:49:31',1,0,NULL,'Tecnam'),
(65,13,96,NULL,'Honneur','F-HRMM','80','DR420',NULL,0,NULL,'2025-09-09 15:38:38',1,0,NULL,'Robin'),
(66,13,97,NULL,'Elite','F-GIKA','80','DR460',NULL,0,NULL,'2025-09-09 17:27:32',1,0,NULL,'Robin'),
(67,13,99,NULL,'Honneur','F-GBIE','80','DR400',NULL,0,NULL,'2025-09-11 06:07:50',1,0,NULL,'Robin'),
(68,13,98,NULL,'Honneur','F-BSJQ','80','DR300',NULL,0,NULL,'2025-09-11 06:09:03',1,0,NULL,'Robin'),
(69,13,100,NULL,'Honneur','F-GSKX','80','DR420',NULL,0,NULL,'2025-09-11 06:10:06',1,0,NULL,'Robin'),
(70,13,101,NULL,'Honneur','F-HMVG','70','APM30',NULL,0,NULL,'2025-09-13 20:20:17',1,0,NULL,'APM Aéro'),
(71,13,104,NULL,'Elite','F-GNAP','70','F150',NULL,0,NULL,'2025-09-15 17:15:21',1,0,NULL,'Cessna'),
(72,13,105,NULL,'Elite','F-PMMR','70','DR1050',NULL,0,NULL,'2025-09-15 18:56:08',1,0,NULL,'Jodel'),
(73,13,103,NULL,'Honneur','F-GJZG','80','DR400',NULL,0,NULL,'2025-09-16 16:33:53',1,0,NULL,'Robin'),
(74,13,106,NULL,'Honneur','F-HRMM','80','DR420',NULL,0,NULL,'2025-09-16 16:35:31',1,0,NULL,'Robin'),
(76,13,107,NULL,'Elite','F-HFCL','80','DA20',NULL,0,NULL,'2025-09-16 22:03:08',1,0,NULL,'Diamond');
/*!40000 ALTER TABLE `crews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES
('DoctrineMigrations\\Version20250428163446',NULL,NULL),
('DoctrineMigrations\\Version20250428164556',NULL,NULL),
('DoctrineMigrations\\Version20250428173645',NULL,NULL),
('DoctrineMigrations\\Version20250428180127',NULL,NULL),
('DoctrineMigrations\\Version20250429185205',NULL,NULL),
('DoctrineMigrations\\Version20250430053442',NULL,NULL),
('DoctrineMigrations\\Version20250430061221',NULL,NULL),
('DoctrineMigrations\\Version20250503045447',NULL,NULL),
('DoctrineMigrations\\Version20250503180604',NULL,NULL),
('DoctrineMigrations\\Version20250508071142',NULL,NULL),
('DoctrineMigrations\\Version20250508071752',NULL,NULL),
('DoctrineMigrations\\Version20250508074437',NULL,NULL),
('DoctrineMigrations\\Version20250508084346',NULL,NULL),
('DoctrineMigrations\\Version20250508181144',NULL,NULL),
('DoctrineMigrations\\Version20250508181318',NULL,NULL),
('DoctrineMigrations\\Version20250508181530',NULL,NULL),
('DoctrineMigrations\\Version20250509071358',NULL,NULL),
('DoctrineMigrations\\Version20250509124652',NULL,NULL),
('DoctrineMigrations\\Version20250509162024',NULL,NULL),
('DoctrineMigrations\\Version20250510055201',NULL,NULL),
('DoctrineMigrations\\Version20250510134231',NULL,NULL),
('DoctrineMigrations\\Version20250510134511',NULL,NULL),
('DoctrineMigrations\\Version20250510145629',NULL,NULL),
('DoctrineMigrations\\Version20250510145855',NULL,NULL),
('DoctrineMigrations\\Version20250514083320',NULL,NULL),
('DoctrineMigrations\\Version20250514131834',NULL,NULL),
('DoctrineMigrations\\Version20250514144401',NULL,NULL),
('DoctrineMigrations\\Version20250514145242',NULL,NULL),
('DoctrineMigrations\\Version20250514145414',NULL,NULL),
('DoctrineMigrations\\Version20250514160852',NULL,NULL),
('DoctrineMigrations\\Version20250514161104',NULL,NULL),
('DoctrineMigrations\\Version20250514174612',NULL,NULL),
('DoctrineMigrations\\Version20250514180552',NULL,NULL),
('DoctrineMigrations\\Version20250514183214',NULL,NULL),
('DoctrineMigrations\\Version20250521080853',NULL,NULL),
('DoctrineMigrations\\Version20250521154452',NULL,NULL),
('DoctrineMigrations\\Version20250522045452',NULL,NULL),
('DoctrineMigrations\\Version20250522045913',NULL,NULL),
('DoctrineMigrations\\Version20250522053603',NULL,NULL),
('DoctrineMigrations\\Version20250522062020',NULL,NULL),
('DoctrineMigrations\\Version20250522073838',NULL,NULL),
('DoctrineMigrations\\Version20250522161802',NULL,NULL),
('DoctrineMigrations\\Version20250522170148',NULL,NULL),
('DoctrineMigrations\\Version20250522171545',NULL,NULL),
('DoctrineMigrations\\Version20250523045843',NULL,NULL),
('DoctrineMigrations\\Version20250526145539',NULL,NULL),
('DoctrineMigrations\\Version20250528062923',NULL,NULL),
('DoctrineMigrations\\Version20250530064002',NULL,NULL),
('DoctrineMigrations\\Version20250530064617',NULL,NULL),
('DoctrineMigrations\\Version20250530080524',NULL,NULL),
('DoctrineMigrations\\Version20250530160651',NULL,NULL),
('DoctrineMigrations\\Version20250601150343',NULL,NULL),
('DoctrineMigrations\\Version20250604071352',NULL,NULL),
('DoctrineMigrations\\Version20250604172247',NULL,NULL),
('DoctrineMigrations\\Version20250604172359',NULL,NULL),
('DoctrineMigrations\\Version20250604172708',NULL,NULL),
('DoctrineMigrations\\Version20250606063959','2025-06-06 06:40:11',12),
('DoctrineMigrations\\Version20250607090224','2025-06-15 12:32:04',6),
('DoctrineMigrations\\Version20250607091201','2025-06-11 16:29:36',12),
('DoctrineMigrations\\Version20250607091312','2025-06-11 16:29:36',18),
('DoctrineMigrations\\Version20250609155346',NULL,NULL),
('DoctrineMigrations\\Version20250610155828',NULL,NULL),
('DoctrineMigrations\\Version20250610160303',NULL,NULL),
('DoctrineMigrations\\Version20250613075945','2025-06-14 14:53:56',9),
('DoctrineMigrations\\Version20250615121634',NULL,NULL),
('DoctrineMigrations\\Version20250615125331',NULL,NULL),
('DoctrineMigrations\\Version20250615142806','2025-06-15 14:42:05',13),
('DoctrineMigrations\\Version20250615144352','2025-06-15 14:47:02',14),
('DoctrineMigrations\\Version20250620062422','2025-06-24 06:45:34',7),
('DoctrineMigrations\\Version20250620082031','2025-06-24 06:45:34',28),
('DoctrineMigrations\\Version20250620105616','2025-06-24 06:45:35',7),
('DoctrineMigrations\\Version20250620125006','2025-06-24 06:45:35',5),
('DoctrineMigrations\\Version20250620141047','2025-06-24 06:45:35',3),
('DoctrineMigrations\\Version20250620141730','2025-06-24 06:45:35',3),
('DoctrineMigrations\\Version20250620144704','2025-06-24 06:45:35',5),
('DoctrineMigrations\\Version20250621090431','2025-06-24 06:45:35',6),
('DoctrineMigrations\\Version20250623123220','2025-06-24 06:45:35',9),
('DoctrineMigrations\\Version20250624063238','2025-06-24 06:45:35',10),
('DoctrineMigrations\\Version20250624070652','2025-06-26 18:56:30',20),
('DoctrineMigrations\\Version20250627083447','2025-06-29 15:38:54',11),
('DoctrineMigrations\\Version20250627085526','2025-06-29 15:38:54',16),
('DoctrineMigrations\\Version20250627105239','2025-06-29 15:38:54',9),
('DoctrineMigrations\\Version20250629053650','2025-06-29 15:38:54',4),
('DoctrineMigrations\\Version20250630085349','2025-06-30 08:57:23',7),
('DoctrineMigrations\\Version20250701070706','2025-07-08 05:12:53',27),
('DoctrineMigrations\\Version20250701071708','2025-07-08 05:12:53',20),
('DoctrineMigrations\\Version20250707151316','2025-07-08 05:12:53',3),
('DoctrineMigrations\\Version20250707160720','2025-07-08 05:12:53',7),
('DoctrineMigrations\\Version20250710182842','2025-07-10 19:04:38',9),
('DoctrineMigrations\\Version20250712053409','2025-07-12 08:25:40',6),
('DoctrineMigrations\\Version20250715161023','2025-07-16 13:21:50',10),
('DoctrineMigrations\\Version20250715162927','2025-07-16 13:21:50',5),
('DoctrineMigrations\\Version20250715164755','2025-07-16 13:21:50',19),
('DoctrineMigrations\\Version20250716132929','2025-08-10 09:32:45',11),
('DoctrineMigrations\\Version20250717150338','2025-08-10 09:32:45',4),
('DoctrineMigrations\\Version20250717160629','2025-08-10 09:32:45',4),
('DoctrineMigrations\\Version20250810092925','2025-08-10 09:32:45',4),
('DoctrineMigrations\\Version20250810112740','2025-08-10 11:30:22',14),
('DoctrineMigrations\\Version20250818131120','2025-08-19 11:27:11',17),
('DoctrineMigrations\\Version20250916174920','2025-09-17 11:37:31',12),
('DoctrineMigrations\\Version20260118111001','2026-01-18 11:19:18',29);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messenger_messages`
--

LOCK TABLES `messenger_messages` WRITE;
/*!40000 ALTER TABLE `messenger_messages` DISABLE KEYS */;
INSERT INTO `messenger_messages` VALUES
(10,'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:6:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}s:51:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\ErrorDetailsStamp\\\";a:1:{i:0;O:51:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\ErrorDetailsStamp\\\":4:{s:67:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\ErrorDetailsStamp\\0exceptionClass\\\";s:57:\\\"Symfony\\\\Component\\\\Mime\\\\Exception\\\\InvalidArgumentException\\\";s:66:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\ErrorDetailsStamp\\0exceptionCode\\\";i:0;s:69:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\ErrorDetailsStamp\\0exceptionMessage\\\";s:83:\\\"file_get_contents(/tmp/phpYaD12r): Failed to open stream: No such file or directory\\\";s:69:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\ErrorDetailsStamp\\0flattenException\\\";O:57:\\\"Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\\":12:{s:66:\\\"\\0Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\0message\\\";s:83:\\\"file_get_contents(/tmp/phpYaD12r): Failed to open stream: No such file or directory\\\";s:63:\\\"\\0Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\0code\\\";i:0;s:67:\\\"\\0Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\0previous\\\";N;s:64:\\\"\\0Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\0trace\\\";a:31:{i:0;a:8:{s:9:\\\"namespace\\\";s:0:\\\"\\\";s:11:\\\"short_class\\\";s:0:\\\"\\\";s:5:\\\"class\\\";s:0:\\\"\\\";s:4:\\\"type\\\";s:0:\\\"\\\";s:8:\\\"function\\\";s:0:\\\"\\\";s:4:\\\"file\\\";s:61:\\\"/var/www/sports.ff-aero/vendor/symfony/mime/Part/TextPart.php\\\";s:4:\\\"line\\\";i:127;s:4:\\\"args\\\";a:0:{}}i:1;a:8:{s:9:\\\"namespace\\\";s:27:\\\"Symfony\\\\Component\\\\Mime\\\\Part\\\";s:11:\\\"short_class\\\";s:8:\\\"TextPart\\\";s:5:\\\"class\\\";s:36:\\\"Symfony\\\\Component\\\\Mime\\\\Part\\\\TextPart\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:7:\\\"getBody\\\";s:4:\\\"file\\\";s:61:\\\"/var/www/sports.ff-aero/vendor/symfony/mime/Part/TextPart.php\\\";s:4:\\\"line\\\";i:146;s:4:\\\"args\\\";a:0:{}}i:2;a:8:{s:9:\\\"namespace\\\";s:27:\\\"Symfony\\\\Component\\\\Mime\\\\Part\\\";s:11:\\\"short_class\\\";s:8:\\\"TextPart\\\";s:5:\\\"class\\\";s:36:\\\"Symfony\\\\Component\\\\Mime\\\\Part\\\\TextPart\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:12:\\\"bodyToString\\\";s:4:\\\"file\\\";s:83:\\\"/var/www/sports.ff-aero/vendor/symfony/brevo-mailer/Transport/BrevoApiTransport.php\\\";s:4:\\\"line\\\";i:129;s:4:\\\"args\\\";a:0:{}}i:3;a:8:{s:9:\\\"namespace\\\";s:47:\\\"Symfony\\\\Component\\\\Mailer\\\\Bridge\\\\Brevo\\\\Transport\\\";s:11:\\\"short_class\\\";s:17:\\\"BrevoApiTransport\\\";s:5:\\\"class\\\";s:65:\\\"Symfony\\\\Component\\\\Mailer\\\\Bridge\\\\Brevo\\\\Transport\\\\BrevoApiTransport\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:18:\\\"prepareAttachments\\\";s:4:\\\"file\\\";s:83:\\\"/var/www/sports.ff-aero/vendor/symfony/brevo-mailer/Transport/BrevoApiTransport.php\\\";s:4:\\\"line\\\";i:96;s:4:\\\"args\\\";a:0:{}}i:4;a:8:{s:9:\\\"namespace\\\";s:47:\\\"Symfony\\\\Component\\\\Mailer\\\\Bridge\\\\Brevo\\\\Transport\\\";s:11:\\\"short_class\\\";s:17:\\\"BrevoApiTransport\\\";s:5:\\\"class\\\";s:65:\\\"Symfony\\\\Component\\\\Mailer\\\\Bridge\\\\Brevo\\\\Transport\\\\BrevoApiTransport\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:10:\\\"getPayload\\\";s:4:\\\"file\\\";s:83:\\\"/var/www/sports.ff-aero/vendor/symfony/brevo-mailer/Transport/BrevoApiTransport.php\\\";s:4:\\\"line\\\";i:52;s:4:\\\"args\\\";a:0:{}}i:5;a:8:{s:9:\\\"namespace\\\";s:47:\\\"Symfony\\\\Component\\\\Mailer\\\\Bridge\\\\Brevo\\\\Transport\\\";s:11:\\\"short_class\\\";s:17:\\\"BrevoApiTransport\\\";s:5:\\\"class\\\";s:65:\\\"Symfony\\\\Component\\\\Mailer\\\\Bridge\\\\Brevo\\\\Transport\\\\BrevoApiTransport\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:9:\\\"doSendApi\\\";s:4:\\\"file\\\";s:80:\\\"/var/www/sports.ff-aero/vendor/symfony/mailer/Transport/AbstractApiTransport.php\\\";s:4:\\\"line\\\";i:37;s:4:\\\"args\\\";a:0:{}}i:6;a:8:{s:9:\\\"namespace\\\";s:34:\\\"Symfony\\\\Component\\\\Mailer\\\\Transport\\\";s:11:\\\"short_class\\\";s:20:\\\"AbstractApiTransport\\\";s:5:\\\"class\\\";s:55:\\\"Symfony\\\\Component\\\\Mailer\\\\Transport\\\\AbstractApiTransport\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:10:\\\"doSendHttp\\\";s:4:\\\"file\\\";s:81:\\\"/var/www/sports.ff-aero/vendor/symfony/mailer/Transport/AbstractHttpTransport.php\\\";s:4:\\\"line\\\";i:70;s:4:\\\"args\\\";a:0:{}}i:7;a:8:{s:9:\\\"namespace\\\";s:34:\\\"Symfony\\\\Component\\\\Mailer\\\\Transport\\\";s:11:\\\"short_class\\\";s:21:\\\"AbstractHttpTransport\\\";s:5:\\\"class\\\";s:56:\\\"Symfony\\\\Component\\\\Mailer\\\\Transport\\\\AbstractHttpTransport\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:6:\\\"doSend\\\";s:4:\\\"file\\\";s:77:\\\"/var/www/sports.ff-aero/vendor/symfony/mailer/Transport/AbstractTransport.php\\\";s:4:\\\"line\\\";i:90;s:4:\\\"args\\\";a:0:{}}i:8;a:8:{s:9:\\\"namespace\\\";s:34:\\\"Symfony\\\\Component\\\\Mailer\\\\Transport\\\";s:11:\\\"short_class\\\";s:17:\\\"AbstractTransport\\\";s:5:\\\"class\\\";s:52:\\\"Symfony\\\\Component\\\\Mailer\\\\Transport\\\\AbstractTransport\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:4:\\\"send\\\";s:4:\\\"file\\\";s:70:\\\"/var/www/sports.ff-aero/vendor/symfony/mailer/Transport/Transports.php\\\";s:4:\\\"line\\\";i:51;s:4:\\\"args\\\";a:0:{}}i:9;a:8:{s:9:\\\"namespace\\\";s:34:\\\"Symfony\\\\Component\\\\Mailer\\\\Transport\\\";s:11:\\\"short_class\\\";s:10:\\\"Transports\\\";s:5:\\\"class\\\";s:45:\\\"Symfony\\\\Component\\\\Mailer\\\\Transport\\\\Transports\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:4:\\\"send\\\";s:4:\\\"file\\\";s:74:\\\"/var/www/sports.ff-aero/vendor/symfony/mailer/Messenger/MessageHandler.php\\\";s:4:\\\"line\\\";i:31;s:4:\\\"args\\\";a:0:{}}i:10;a:8:{s:9:\\\"namespace\\\";s:34:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\";s:11:\\\"short_class\\\";s:14:\\\"MessageHandler\\\";s:5:\\\"class\\\";s:49:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\MessageHandler\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:8:\\\"__invoke\\\";s:4:\\\"file\\\";s:87:\\\"/var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/HandleMessageMiddleware.php\\\";s:4:\\\"line\\\";i:152;s:4:\\\"args\\\";a:0:{}}i:11;a:8:{s:9:\\\"namespace\\\";s:38:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\";s:11:\\\"short_class\\\";s:23:\\\"HandleMessageMiddleware\\\";s:5:\\\"class\\\";s:62:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\HandleMessageMiddleware\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:11:\\\"callHandler\\\";s:4:\\\"file\\\";s:87:\\\"/var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/HandleMessageMiddleware.php\\\";s:4:\\\"line\\\";i:91;s:4:\\\"args\\\";a:0:{}}i:12;a:8:{s:9:\\\"namespace\\\";s:38:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\";s:11:\\\"short_class\\\";s:23:\\\"HandleMessageMiddleware\\\";s:5:\\\"class\\\";s:62:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\HandleMessageMiddleware\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:6:\\\"handle\\\";s:4:\\\"file\\\";s:85:\\\"/var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/SendMessageMiddleware.php\\\";s:4:\\\"line\\\";i:71;s:4:\\\"args\\\";a:0:{}}i:13;a:8:{s:9:\\\"namespace\\\";s:38:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\";s:11:\\\"short_class\\\";s:21:\\\"SendMessageMiddleware\\\";s:5:\\\"class\\\";s:60:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\SendMessageMiddleware\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:6:\\\"handle\\\";s:4:\\\"file\\\";s:97:\\\"/var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/FailedMessageProcessingMiddleware.php\\\";s:4:\\\"line\\\";i:34;s:4:\\\"args\\\";a:0:{}}i:14;a:8:{s:9:\\\"namespace\\\";s:38:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\";s:11:\\\"short_class\\\";s:33:\\\"FailedMessageProcessingMiddleware\\\";s:5:\\\"class\\\";s:72:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\FailedMessageProcessingMiddleware\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:6:\\\"handle\\\";s:4:\\\"file\\\";s:97:\\\"/var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/DispatchAfterCurrentBusMiddleware.php\\\";s:4:\\\"line\\\";i:68;s:4:\\\"args\\\";a:0:{}}i:15;a:8:{s:9:\\\"namespace\\\";s:38:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\";s:11:\\\"short_class\\\";s:33:\\\"DispatchAfterCurrentBusMiddleware\\\";s:5:\\\"class\\\";s:72:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\DispatchAfterCurrentBusMiddleware\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:6:\\\"handle\\\";s:4:\\\"file\\\";s:98:\\\"/var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/RejectRedeliveredMessageMiddleware.php\\\";s:4:\\\"line\\\";i:41;s:4:\\\"args\\\";a:0:{}}i:16;a:8:{s:9:\\\"namespace\\\";s:38:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\";s:11:\\\"short_class\\\";s:34:\\\"RejectRedeliveredMessageMiddleware\\\";s:5:\\\"class\\\";s:73:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\RejectRedeliveredMessageMiddleware\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:6:\\\"handle\\\";s:4:\\\"file\\\";s:89:\\\"/var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/AddBusNameStampMiddleware.php\\\";s:4:\\\"line\\\";i:37;s:4:\\\"args\\\";a:0:{}}i:17;a:8:{s:9:\\\"namespace\\\";s:38:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\";s:11:\\\"short_class\\\";s:25:\\\"AddBusNameStampMiddleware\\\";s:5:\\\"class\\\";s:64:\\\"Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\AddBusNameStampMiddleware\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:6:\\\"handle\\\";s:4:\\\"file\\\";s:63:\\\"/var/www/sports.ff-aero/vendor/symfony/messenger/MessageBus.php\\\";s:4:\\\"line\\\";i:70;s:4:\\\"args\\\";a:0:{}}i:18;a:8:{s:9:\\\"namespace\\\";s:27:\\\"Symfony\\\\Component\\\\Messenger\\\";s:11:\\\"short_class\\\";s:10:\\\"MessageBus\\\";s:5:\\\"class\\\";s:38:\\\"Symfony\\\\Component\\\\Messenger\\\\MessageBus\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:8:\\\"dispatch\\\";s:4:\\\"file\\\";s:71:\\\"/var/www/sports.ff-aero/vendor/symfony/messenger/RoutableMessageBus.php\\\";s:4:\\\"line\\\";i:54;s:4:\\\"args\\\";a:0:{}}i:19;a:8:{s:9:\\\"namespace\\\";s:27:\\\"Symfony\\\\Component\\\\Messenger\\\";s:11:\\\"short_class\\\";s:18:\\\"RoutableMessageBus\\\";s:5:\\\"class\\\";s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\RoutableMessageBus\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:8:\\\"dispatch\\\";s:4:\\\"file\\\";s:59:\\\"/var/www/sports.ff-aero/vendor/symfony/messenger/Worker.php\\\";s:4:\\\"line\\\";i:159;s:4:\\\"args\\\";a:0:{}}i:20;a:8:{s:9:\\\"namespace\\\";s:27:\\\"Symfony\\\\Component\\\\Messenger\\\";s:11:\\\"short_class\\\";s:6:\\\"Worker\\\";s:5:\\\"class\\\";s:34:\\\"Symfony\\\\Component\\\\Messenger\\\\Worker\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:13:\\\"handleMessage\\\";s:4:\\\"file\\\";s:59:\\\"/var/www/sports.ff-aero/vendor/symfony/messenger/Worker.php\\\";s:4:\\\"line\\\";i:108;s:4:\\\"args\\\";a:0:{}}i:21;a:8:{s:9:\\\"namespace\\\";s:27:\\\"Symfony\\\\Component\\\\Messenger\\\";s:11:\\\"short_class\\\";s:6:\\\"Worker\\\";s:5:\\\"class\\\";s:34:\\\"Symfony\\\\Component\\\\Messenger\\\\Worker\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:3:\\\"run\\\";s:4:\\\"file\\\";s:83:\\\"/var/www/sports.ff-aero/vendor/symfony/messenger/Command/ConsumeMessagesCommand.php\\\";s:4:\\\"line\\\";i:244;s:4:\\\"args\\\";a:0:{}}i:22;a:8:{s:9:\\\"namespace\\\";s:35:\\\"Symfony\\\\Component\\\\Messenger\\\\Command\\\";s:11:\\\"short_class\\\";s:22:\\\"ConsumeMessagesCommand\\\";s:5:\\\"class\\\";s:58:\\\"Symfony\\\\Component\\\\Messenger\\\\Command\\\\ConsumeMessagesCommand\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:7:\\\"execute\\\";s:4:\\\"file\\\";s:66:\\\"/var/www/sports.ff-aero/vendor/symfony/console/Command/Command.php\\\";s:4:\\\"line\\\";i:326;s:4:\\\"args\\\";a:0:{}}i:23;a:8:{s:9:\\\"namespace\\\";s:33:\\\"Symfony\\\\Component\\\\Console\\\\Command\\\";s:11:\\\"short_class\\\";s:7:\\\"Command\\\";s:5:\\\"class\\\";s:41:\\\"Symfony\\\\Component\\\\Console\\\\Command\\\\Command\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:3:\\\"run\\\";s:4:\\\"file\\\";s:62:\\\"/var/www/sports.ff-aero/vendor/symfony/console/Application.php\\\";s:4:\\\"line\\\";i:1096;s:4:\\\"args\\\";a:0:{}}i:24;a:8:{s:9:\\\"namespace\\\";s:25:\\\"Symfony\\\\Component\\\\Console\\\";s:11:\\\"short_class\\\";s:11:\\\"Application\\\";s:5:\\\"class\\\";s:37:\\\"Symfony\\\\Component\\\\Console\\\\Application\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:12:\\\"doRunCommand\\\";s:4:\\\"file\\\";s:79:\\\"/var/www/sports.ff-aero/vendor/symfony/framework-bundle/Console/Application.php\\\";s:4:\\\"line\\\";i:126;s:4:\\\"args\\\";a:0:{}}i:25;a:8:{s:9:\\\"namespace\\\";s:38:\\\"Symfony\\\\Bundle\\\\FrameworkBundle\\\\Console\\\";s:11:\\\"short_class\\\";s:11:\\\"Application\\\";s:5:\\\"class\\\";s:50:\\\"Symfony\\\\Bundle\\\\FrameworkBundle\\\\Console\\\\Application\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:12:\\\"doRunCommand\\\";s:4:\\\"file\\\";s:62:\\\"/var/www/sports.ff-aero/vendor/symfony/console/Application.php\\\";s:4:\\\"line\\\";i:324;s:4:\\\"args\\\";a:0:{}}i:26;a:8:{s:9:\\\"namespace\\\";s:25:\\\"Symfony\\\\Component\\\\Console\\\";s:11:\\\"short_class\\\";s:11:\\\"Application\\\";s:5:\\\"class\\\";s:37:\\\"Symfony\\\\Component\\\\Console\\\\Application\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:5:\\\"doRun\\\";s:4:\\\"file\\\";s:79:\\\"/var/www/sports.ff-aero/vendor/symfony/framework-bundle/Console/Application.php\\\";s:4:\\\"line\\\";i:80;s:4:\\\"args\\\";a:0:{}}i:27;a:8:{s:9:\\\"namespace\\\";s:38:\\\"Symfony\\\\Bundle\\\\FrameworkBundle\\\\Console\\\";s:11:\\\"short_class\\\";s:11:\\\"Application\\\";s:5:\\\"class\\\";s:50:\\\"Symfony\\\\Bundle\\\\FrameworkBundle\\\\Console\\\\Application\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:5:\\\"doRun\\\";s:4:\\\"file\\\";s:62:\\\"/var/www/sports.ff-aero/vendor/symfony/console/Application.php\\\";s:4:\\\"line\\\";i:175;s:4:\\\"args\\\";a:0:{}}i:28;a:8:{s:9:\\\"namespace\\\";s:25:\\\"Symfony\\\\Component\\\\Console\\\";s:11:\\\"short_class\\\";s:11:\\\"Application\\\";s:5:\\\"class\\\";s:37:\\\"Symfony\\\\Component\\\\Console\\\\Application\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:3:\\\"run\\\";s:4:\\\"file\\\";s:90:\\\"/var/www/sports.ff-aero/vendor/symfony/runtime/Runner/Symfony/ConsoleApplicationRunner.php\\\";s:4:\\\"line\\\";i:49;s:4:\\\"args\\\";a:0:{}}i:29;a:8:{s:9:\\\"namespace\\\";s:40:\\\"Symfony\\\\Component\\\\Runtime\\\\Runner\\\\Symfony\\\";s:11:\\\"short_class\\\";s:24:\\\"ConsoleApplicationRunner\\\";s:5:\\\"class\\\";s:65:\\\"Symfony\\\\Component\\\\Runtime\\\\Runner\\\\Symfony\\\\ConsoleApplicationRunner\\\";s:4:\\\"type\\\";s:2:\\\"->\\\";s:8:\\\"function\\\";s:3:\\\"run\\\";s:4:\\\"file\\\";s:51:\\\"/var/www/sports.ff-aero/vendor/autoload_runtime.php\\\";s:4:\\\"line\\\";i:29;s:4:\\\"args\\\";a:0:{}}i:30;a:8:{s:9:\\\"namespace\\\";s:0:\\\"\\\";s:11:\\\"short_class\\\";s:0:\\\"\\\";s:5:\\\"class\\\";s:0:\\\"\\\";s:4:\\\"type\\\";s:0:\\\"\\\";s:8:\\\"function\\\";s:12:\\\"require_once\\\";s:4:\\\"file\\\";s:35:\\\"/var/www/sports.ff-aero/bin/console\\\";s:4:\\\"line\\\";i:15;s:4:\\\"args\\\";a:1:{i:0;a:2:{i:0;s:6:\\\"string\\\";i:1;s:51:\\\"/var/www/sports.ff-aero/vendor/autoload_runtime.php\\\";}}}}s:72:\\\"\\0Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\0traceAsString\\\";s:4463:\\\"#0 /var/www/sports.ff-aero/vendor/symfony/mime/Part/TextPart.php(146): Symfony\\\\Component\\\\Mime\\\\Part\\\\TextPart->getBody()\n#1 /var/www/sports.ff-aero/vendor/symfony/brevo-mailer/Transport/BrevoApiTransport.php(129): Symfony\\\\Component\\\\Mime\\\\Part\\\\TextPart->bodyToString()\n#2 /var/www/sports.ff-aero/vendor/symfony/brevo-mailer/Transport/BrevoApiTransport.php(96): Symfony\\\\Component\\\\Mailer\\\\Bridge\\\\Brevo\\\\Transport\\\\BrevoApiTransport->prepareAttachments()\n#3 /var/www/sports.ff-aero/vendor/symfony/brevo-mailer/Transport/BrevoApiTransport.php(52): Symfony\\\\Component\\\\Mailer\\\\Bridge\\\\Brevo\\\\Transport\\\\BrevoApiTransport->getPayload()\n#4 /var/www/sports.ff-aero/vendor/symfony/mailer/Transport/AbstractApiTransport.php(37): Symfony\\\\Component\\\\Mailer\\\\Bridge\\\\Brevo\\\\Transport\\\\BrevoApiTransport->doSendApi()\n#5 /var/www/sports.ff-aero/vendor/symfony/mailer/Transport/AbstractHttpTransport.php(70): Symfony\\\\Component\\\\Mailer\\\\Transport\\\\AbstractApiTransport->doSendHttp()\n#6 /var/www/sports.ff-aero/vendor/symfony/mailer/Transport/AbstractTransport.php(90): Symfony\\\\Component\\\\Mailer\\\\Transport\\\\AbstractHttpTransport->doSend()\n#7 /var/www/sports.ff-aero/vendor/symfony/mailer/Transport/Transports.php(51): Symfony\\\\Component\\\\Mailer\\\\Transport\\\\AbstractTransport->send()\n#8 /var/www/sports.ff-aero/vendor/symfony/mailer/Messenger/MessageHandler.php(31): Symfony\\\\Component\\\\Mailer\\\\Transport\\\\Transports->send()\n#9 /var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/HandleMessageMiddleware.php(152): Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\MessageHandler->__invoke()\n#10 /var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/HandleMessageMiddleware.php(91): Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\HandleMessageMiddleware->callHandler()\n#11 /var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/SendMessageMiddleware.php(71): Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\HandleMessageMiddleware->handle()\n#12 /var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/FailedMessageProcessingMiddleware.php(34): Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\SendMessageMiddleware->handle()\n#13 /var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/DispatchAfterCurrentBusMiddleware.php(68): Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\FailedMessageProcessingMiddleware->handle()\n#14 /var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/RejectRedeliveredMessageMiddleware.php(41): Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\DispatchAfterCurrentBusMiddleware->handle()\n#15 /var/www/sports.ff-aero/vendor/symfony/messenger/Middleware/AddBusNameStampMiddleware.php(37): Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\RejectRedeliveredMessageMiddleware->handle()\n#16 /var/www/sports.ff-aero/vendor/symfony/messenger/MessageBus.php(70): Symfony\\\\Component\\\\Messenger\\\\Middleware\\\\AddBusNameStampMiddleware->handle()\n#17 /var/www/sports.ff-aero/vendor/symfony/messenger/RoutableMessageBus.php(54): Symfony\\\\Component\\\\Messenger\\\\MessageBus->dispatch()\n#18 /var/www/sports.ff-aero/vendor/symfony/messenger/Worker.php(159): Symfony\\\\Component\\\\Messenger\\\\RoutableMessageBus->dispatch()\n#19 /var/www/sports.ff-aero/vendor/symfony/messenger/Worker.php(108): Symfony\\\\Component\\\\Messenger\\\\Worker->handleMessage()\n#20 /var/www/sports.ff-aero/vendor/symfony/messenger/Command/ConsumeMessagesCommand.php(244): Symfony\\\\Component\\\\Messenger\\\\Worker->run()\n#21 /var/www/sports.ff-aero/vendor/symfony/console/Command/Command.php(326): Symfony\\\\Component\\\\Messenger\\\\Command\\\\ConsumeMessagesCommand->execute()\n#22 /var/www/sports.ff-aero/vendor/symfony/console/Application.php(1096): Symfony\\\\Component\\\\Console\\\\Command\\\\Command->run()\n#23 /var/www/sports.ff-aero/vendor/symfony/framework-bundle/Console/Application.php(126): Symfony\\\\Component\\\\Console\\\\Application->doRunCommand()\n#24 /var/www/sports.ff-aero/vendor/symfony/console/Application.php(324): Symfony\\\\Bundle\\\\FrameworkBundle\\\\Console\\\\Application->doRunCommand()\n#25 /var/www/sports.ff-aero/vendor/symfony/framework-bundle/Console/Application.php(80): Symfony\\\\Component\\\\Console\\\\Application->doRun()\n#26 /var/www/sports.ff-aero/vendor/symfony/console/Application.php(175): Symfony\\\\Bundle\\\\FrameworkBundle\\\\Console\\\\Application->doRun()\n#27 /var/www/sports.ff-aero/vendor/symfony/runtime/Runner/Symfony/ConsoleApplicationRunner.php(49): Symfony\\\\Component\\\\Console\\\\Application->run()\n#28 /var/www/sports.ff-aero/vendor/autoload_runtime.php(29): Symfony\\\\Component\\\\Runtime\\\\Runner\\\\Symfony\\\\ConsoleApplicationRunner->run()\n#29 /var/www/sports.ff-aero/bin/console(15): require_once(\\\'...\\\')\n#30 {main}\\\";s:64:\\\"\\0Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\0class\\\";s:57:\\\"Symfony\\\\Component\\\\Mime\\\\Exception\\\\InvalidArgumentException\\\";s:69:\\\"\\0Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\0statusCode\\\";i:500;s:69:\\\"\\0Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\0statusText\\\";s:21:\\\"Internal Server Error\\\";s:66:\\\"\\0Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\0headers\\\";a:0:{}s:63:\\\"\\0Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\0file\\\";s:61:\\\"/var/www/sports.ff-aero/vendor/symfony/mime/Part/TextPart.php\\\";s:63:\\\"\\0Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\0line\\\";i:127;s:67:\\\"\\0Symfony\\\\Component\\\\ErrorHandler\\\\Exception\\\\FlattenException\\0asString\\\";N;}}}s:44:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\DelayStamp\\\";a:4:{i:0;O:44:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\DelayStamp\\\":1:{s:51:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\DelayStamp\\0delay\\\";i:1000;}i:1;O:44:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\DelayStamp\\\":1:{s:51:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\DelayStamp\\0delay\\\";i:2000;}i:2;O:44:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\DelayStamp\\\":1:{s:51:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\DelayStamp\\0delay\\\";i:4000;}i:3;O:44:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\DelayStamp\\\":1:{s:51:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\DelayStamp\\0delay\\\";i:0;}}s:49:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\\";a:4:{i:0;O:49:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\\":2:{s:61:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\0retryCount\\\";i:1;s:64:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\0redeliveredAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-08-26 09:12:35.081860\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}i:1;O:49:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\\":2:{s:61:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\0retryCount\\\";i:2;s:64:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\0redeliveredAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-08-26 09:12:36.090611\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}i:2;O:49:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\\":2:{s:61:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\0retryCount\\\";i:3;s:64:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\0redeliveredAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-08-26 09:12:38.096915\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}i:3;O:49:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\\":2:{s:61:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\0retryCount\\\";i:0;s:64:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\RedeliveryStamp\\0redeliveredAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-08-26 09:12:42.103766\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}}s:57:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\TransportMessageIdStamp\\\";a:1:{i:0;O:57:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\TransportMessageIdStamp\\\":1:{s:61:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\TransportMessageIdStamp\\0id\\\";i:9;}}s:61:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\SentToFailureTransportStamp\\\";a:1:{i:0;O:61:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\SentToFailureTransportStamp\\\":1:{s:83:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\SentToFailureTransportStamp\\0originalReceiverName\\\";s:5:\\\"async\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;N;i:1;N;i:2;s:93:\\\"<p>Bonjour Joël,<br />\r\n<br />\r\nVoici les informations importantes pour la compétition.</p>\\\";i:3;s:5:\\\"utf-8\\\";i:4;a:1:{i:0;O:36:\\\"Symfony\\\\Component\\\\Mime\\\\Part\\\\DataPart\\\":4:{s:11:\\\"\\0*\\0_headers\\\";O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:0:{}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}s:10:\\\"\\0*\\0_parent\\\";a:6:{s:4:\\\"body\\\";O:32:\\\"Symfony\\\\Component\\\\Mime\\\\Part\\\\File\\\":2:{s:38:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Part\\\\File\\0path\\\";s:14:\\\"/tmp/phpYaD12r\\\";s:42:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Part\\\\File\\0filename\\\";N;}s:7:\\\"charset\\\";N;s:7:\\\"subtype\\\";s:12:\\\"octet-stream\\\";s:11:\\\"disposition\\\";s:10:\\\"attachment\\\";s:4:\\\"name\\\";s:15:\\\"Vichy ANR-M.jpg\\\";s:8:\\\"encoding\\\";s:6:\\\"base64\\\";}s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Part\\\\DataPart\\0filename\\\";s:15:\\\"Vichy ANR-M.jpg\\\";s:47:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Part\\\\DataPart\\0mediaType\\\";s:11:\\\"application\\\";}}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:4:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:19:\\\"jtremblet@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:23:\\\"joel.tremblet@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:4:\\\"Test\\\";}}s:8:\\\"reply-to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:8:\\\"Reply-To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:23:\\\"joel.tremblet@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}','[]','failed','2025-08-26 09:12:42','2025-08-26 09:12:42',NULL);
/*!40000 ALTER TABLE `messenger_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `results`
--

DROP TABLE IF EXISTS `results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ranking` int(11) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `flyingclub` varchar(128) DEFAULT NULL,
  `committee` varchar(50) DEFAULT NULL,
  `navigation` int(11) NOT NULL,
  `observation` int(11) NOT NULL,
  `landing` int(11) NOT NULL,
  `flight_planning` int(11) NOT NULL,
  `category` varchar(15) DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `literal_crew` varchar(255) NOT NULL,
  `competition_id` int(11) DEFAULT NULL,
  `crew_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9FA3E4147B39D312` (`competition_id`),
  KEY `IDX_9FA3E4145FE259F6` (`crew_id`),
  CONSTRAINT `FK_9FA3E4145FE259F6` FOREIGN KEY (`crew_id`) REFERENCES `crews` (`id`),
  CONSTRAINT `FK_9FA3E4147B39D312` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=372 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `results`
--

LOCK TABLES `results` WRITE;
/*!40000 ALTER TABLE `results` DISABLE KEYS */;
INSERT INTO `results` VALUES
(88,1,'M','Aéroclub de la Haute Saône','2',112,120,0,0,'Elite',NULL,'LEBRETON Nicolas',3,NULL),
(89,2,'M','CASI','3',168,240,0,0,'Elite',NULL,'DAMBRE Alain',3,NULL),
(90,3,'M','A/C d\'Annecy','1',330,105,0,0,'Elite',NULL,'TROUCHE Jean-Baptiste',3,NULL),
(91,4,'M','A/C de Montélimar','1',322,150,0,0,'Elite',NULL,'HERAUDEAU Claude',3,NULL),
(92,5,'M','ACAT Toulouse','11',328,270,0,0,'Elite',NULL,'DUPREZ Jean',3,NULL),
(93,6,'M','Mayenne Air Loisir','13',406,255,0,0,'Elite',NULL,'MARREAUD Claude',3,NULL),
(94,7,'M','A/C Banque de France','8',512,195,0,0,'Elite',NULL,'LIENART Patrick',3,NULL),
(95,8,'F','A/C du Var','12',558,195,0,0,'Elite',NULL,'GRAFF Claire',3,NULL),
(96,9,'M','A/C du Pays de Montbeliard','2',806,180,0,0,'Elite',NULL,'MOUTAMANI Mehdi',3,NULL),
(97,10,'M','A/C du Haut-Rhin','6',1110,150,0,0,'Elite',NULL,'FUCHS Alexis',3,NULL),
(98,1,'F','ACAM','12',246,165,0,0,'Honneur',NULL,'SCHRAMM Floriane',3,NULL),
(99,2,'F','ACAT Toulouse','11',244,195,0,0,'Honneur',NULL,'MEDYNA Oksana',3,NULL),
(100,3,'M','A/C du VAR','12',384,75,0,0,'Honneur',NULL,'JIREAU Jérôme',3,NULL),
(101,4,'M','A/C du pays de Montbeliard','2',682,210,0,0,'Honneur',NULL,'JEANBLANC Gilles',3,NULL),
(102,5,'M','A/C du VAR','12',752,255,0,0,'Honneur',NULL,'JACQUET Charles',3,NULL),
(103,6,'M','A/C d\'Annonay et de la vallée du Rhône','1',1352,315,0,0,'Honneur',NULL,'SAPONJIC Adrien',3,NULL),
(104,7,'M','A/C d\'Annonay et de la vallée du Rhône','1',1898,330,0,0,'Honneur',NULL,'OUDOT Laurent',3,NULL),
(112,1,'','Royan','10',1240,895,0,0,'Honneur',NULL,'Lugan Françoise /  COSSARDEAUX Delphine',1,NULL),
(113,2,'','AC Angers Marcé','13',877,1335,0,0,'Honneur',NULL,'BETTIG Bruno /  RENAUDIN  Hervé',1,NULL),
(114,3,'','Aéroclub de Tours','4',1031,1355,0,0,'Honneur',NULL,'MAGNIEN Benoît /  FRIEDMANN Marine',1,NULL),
(115,4,'','AC HISPANO SUIZA / AC BLOIS VENDOME','8',1359,1135,0,0,'Honneur',NULL,'SONNET Nicolas /  BLOCH Emmanuel',1,NULL),
(116,5,'','Clément ADER (LFBR) / Aéroclub de l\'ENAC (LFCL)','11',941,1575,0,0,'Honneur',NULL,'VALENCIA LAURENT /  TAMARO MARIE-ANNE',1,NULL),
(117,6,'','Aéroclub de Blois-Vendôme','4',888,1745,0,0,'Honneur',NULL,'GAGLIANO Gabriel /  KORMANN Thibaullt',1,NULL),
(118,7,'','ACBV','4',1291,1355,0,0,'Honneur',NULL,'de Bellefon Audrey /  PIERRON Sebastien',1,NULL),
(119,8,'',NULL,'4',999,1735,0,0,'Honneur',NULL,'Fressencourt  Thierry /  AUPY Pascal',1,NULL),
(120,1,'','HISPANO SUIZA / Ac du var','8',69,240,0,0,'Elite',NULL,'RIVIERE  Olivier /  JIREAU Jérôme',1,NULL),
(121,2,'','Aéroclub de Compiègne  / Poitiers','7',180,445,0,0,'Elite',NULL,'Muller Hugo /  MOUTERDE Hervé',1,NULL),
(122,3,'','AGCL','10',415,595,0,0,'Elite',NULL,'LORPHELIN Pierre Yves /  BALLEREAU Anne Laure',1,NULL),
(123,4,'','Brocard Etampes','8',385,970,0,0,'Elite',NULL,'Colonge François /  RIQUIER Patrice',1,NULL),
(124,5,'','UNAC Châteauneuf sur Cher','4',655,865,0,0,'Elite',NULL,'GUILLERAULT Martin /  CAZAUX Geneviève',1,NULL),
(125,6,'','AC POITOU / Tours Aéro Club','10',502,1050,0,0,'Elite',NULL,'BRULAT Johannes /  PROUET Antoine',1,NULL),
(126,7,'','Aéroclub Banque de France / Aéroclub du Gatinais','8',701,1195,0,0,'Elite',NULL,'MIRIGAY Solange /  BOSSARD Philippe',1,NULL),
(127,8,'','Aéroclub de Versailles','8',867,1420,0,0,'Elite',NULL,'du RANQUET Benoît /  FAIVRE Arnaud',1,NULL),
(128,9,'','AILES DU MAINE / CASI','13',1160,1540,0,0,'Elite',NULL,'GOUPIL Anne Charlotte /  DAMBRE Alain',1,NULL),
(129,1,'','Hispano Suiza','8',202,350,0,0,'Honneur',NULL,'SONNET Nicolas',2,NULL),
(130,2,'','TOURS AEROCLUB','4',390,225,0,0,'Honneur',NULL,'PROUET Antoine',2,NULL),
(131,3,'','Brocard Etampes','8',376,400,0,0,'Honneur',NULL,'Riquier Patrice',2,NULL),
(132,4,'','AEROCLUB PARIS.AERO','8',948,350,0,0,'Honneur',NULL,'FRESSENCOURT THIERRY',2,NULL),
(133,5,'','A?roclub du Pays Rochefortais','10',958,350,0,0,'Honneur',NULL,'Cuiengnet Geoffrey',2,NULL),
(134,6,'F','AC Blois Vendôme','4',1406,585,0,0,'Honneur',NULL,'DE BELLEFON Audrey',2,NULL),
(135,7,'','Aéroclub Vol Moteur de Bourges','4',1450,555,0,0,'Honneur',NULL,'CROISIC Gerald',2,NULL),
(136,1,'','A?roclub de la Haute Saone','2',184,140,0,0,'Elite',NULL,'LEBRETON Nicolas',2,NULL),
(137,2,'','UNAC Ch?teauneuf sur Cher','4',164,215,0,0,'Elite',NULL,'GUILLERAULT Martin',2,NULL),
(138,3,'','HISPANO SUIZA','8',278,135,0,0,'Elite',NULL,'RIVIERE Olivier',2,NULL),
(139,4,'','CASI','3',114,335,0,0,'Elite',NULL,'Dambre Alain',2,NULL),
(140,5,'','Jean Piquenot (Cherbourg)','9',274,305,0,0,'Elite',NULL,'HERAUDEAU FRANCOIS',2,NULL),
(141,6,'','Brocard Etampes','8',218,375,0,0,'Elite',NULL,'Colonge François',2,NULL),
(142,7,'','Mayenne Air Loisir','13',436,320,0,0,'Elite',NULL,'Marreaud Claude',2,NULL),
(143,8,'','AC Var','12',606,305,0,0,'Elite',NULL,'JIREAU JEROME',2,NULL),
(144,9,'','AC du Gatinais','4',576,380,0,0,'Elite',NULL,'BOSSARD Philippe',2,NULL),
(145,10,'','AC POITOU','10',628,385,0,0,'Elite',NULL,'BRULAT Johannes',2,NULL),
(146,11,'','A?roclub Banque de France','8',862,370,0,0,'Elite',NULL,'MIRIGAY Solange',2,NULL),
(162,1,'M','UNAC Châteauneuf sur Cher','4',124,225,0,0,'Elite',NULL,'GUILLERAULT Martin',7,NULL),
(163,2,'M','Mayenne Air Loisir','13',218,285,0,0,'Elite',NULL,'MARREAUD Claude',7,NULL),
(164,3,'M','SEPAVIA','10',272,280,0,0,'Elite',NULL,'LEGER Fabien',7,NULL),
(165,4,'M','CASI','3',258,315,0,0,'Elite',NULL,'DAMBRE Alain',7,NULL),
(166,5,'M','ACAT','11',270,360,0,0,'Elite',NULL,'DUPREZ Jean',7,NULL),
(167,6,'M','Banque de france','8',268,385,0,0,'Elite',NULL,'LIENART Patrick',7,NULL),
(168,7,'M','AC du Poitou','10',398,315,0,0,'Elite',NULL,'BRULAT Johannes',7,NULL),
(169,8,'F','Aéroclub Banque de France','8',532,225,0,0,'Elite',NULL,'MIRIGAY Solange',7,NULL),
(170,9,'M','Jean Piquenot','9',490,365,0,0,'Elite',NULL,'HERAUDEAU François',7,NULL),
(171,10,'M','AC du Gatinais','4',888,460,0,0,'Elite',NULL,'BOSSARD Philippe',7,NULL),
(172,11,'M','Brocard Etampes','8',1238,525,0,0,'Elite',NULL,'COLONGE François',7,NULL),
(173,12,'M','UAT Uzein','10',1536,455,0,0,'Elite',NULL,'ARETTE Alain',7,NULL),
(174,13,'M','Dassault Aéro-Club Aquitaine','10',2152,780,0,0,'Elite',NULL,'SAIGHI Sylvain',7,NULL),
(175,1,'M','Brocard Etampes','8',268,230,0,0,'Honneur',NULL,'RIQUIER Patrice',7,NULL),
(176,2,'M','Tours Aéro-Club','4',638,165,0,0,'Honneur',NULL,'PROUET Antoine',7,NULL),
(177,3,'M','AVMB Aéroclub Vol moteur de Bourges','4',594,280,0,0,'Honneur',NULL,'FRESSENCOURT Thierry',7,NULL),
(178,4,'M','Aéro-Club des Grands Lacs','10',644,255,0,0,'Honneur',NULL,'ROCHARD Pascal',7,NULL),
(179,5,'F','ACBV','4',736,550,0,0,'Honneur',NULL,'DE BELLEFON Audrey',7,NULL),
(180,6,'M','ACBV','4',1842,590,0,0,'Honneur',NULL,'DEVENNE Boris',7,NULL),
(181,7,'M','Aéro-Club de Bordeaux','10',2680,425,0,0,'Honneur',NULL,'MARCHAND Pascal',7,NULL),
(182,1,'M','Aéroclub de Compiègne  / Poitiers','7',42,15,0,0,'Elite',NULL,'MULLER Hugo /  MOUTERDE Hervé',8,NULL),
(183,2,'','UNAC Châteauneuf sur Cher','4',51,90,0,0,'Elite',NULL,'GUILLERAULT Martin /  CAZAUX Geneviève',8,NULL),
(184,3,'M','AC Poitou / Tours Aéro-Club','10',75,135,0,0,'Elite',NULL,'BRULAT Johannes /  PROUET Antoine',8,NULL),
(185,4,'','ACAT / Aéro-Club des IPSA','11',123,150,0,0,'Elite',NULL,'DUPREZ Jean /  GILLIERS Nathalie',8,NULL),
(186,5,'M','Banque de France / CASI','8',159,120,0,0,'Elite',NULL,'LIENART Patrick /  DAMBRE Alain',8,NULL),
(187,6,'M','SEPAVIA / CASI','10',57,235,0,0,'Elite',NULL,'LEGER Fabien /  LOCHET Jean-Claude',8,NULL),
(188,7,'','Mayenne Air Loisir','13',165,135,0,0,'Elite',NULL,'MARREAUD Catherine /  MARREAUD Claude',8,NULL),
(189,8,'M','Brocard Etampes','8',151,300,0,0,'Elite',NULL,'COLONGES François /  RIQUIER Patrice',8,NULL),
(190,9,'','Aéroclub Banque de France / Aéroclub du Gatinais','8',410,110,0,0,'Elite',NULL,'MIRIGAY Solange /  BOSSARD Philippe',8,NULL),
(191,1,'M','AC Brocard','8',69,170,0,0,'Honneur',NULL,'GOURSAUD Cyrille /  GOMES Romain',8,NULL),
(192,2,'M',NULL,'4',204,110,0,0,'Honneur',NULL,'FRESSENCOURT  Thierry /  AUPY Pascal',8,NULL),
(193,3,'M','Aubigny sur Nère  / Bertin','4',335,260,0,0,'Honneur',NULL,'BERTUCCHI Luc /  LEGENS Julyan',8,NULL),
(194,4,'F','Royan','10',502,275,0,0,'Honneur',NULL,'LUGAN Françoise /  COSSARDEAUX Delphine',8,NULL),
(195,5,'','UAT Uzein','10',169,765,0,0,'Honneur',NULL,'ARETTE  Alain /  CLAIRFOND Christel',8,NULL),
(196,6,'','Clément ADER / ENAC','11',424,555,0,0,'Honneur',NULL,'VALENCIA Laurent /  TAMARO Marie-Anne',8,NULL),
(197,7,'','ACBV','4',747,440,0,0,'Honneur',NULL,'DEVENNE Boris /  DE BELLEFON Audrey',8,NULL),
(250,1,'','HISPANO SUIZA / Ac du var','8',54,180,0,0,'Elite',NULL,'RIVIERE  Olivier /  JIREAU Jérôme',6,NULL),
(251,2,'','Bedarieux Grand Orb / Robert Thiery','11',180,165,0,0,'Elite',NULL,'SAUCE Paul /  FUSTER Mariano',6,NULL),
(252,3,'','AC Haut Rhin / AC Annecy','6',230,300,0,0,'Elite',NULL,'FUCHS Alexis /  TROUCHE Jean-Baptiste',6,NULL),
(253,4,'','Polygone 67','6',283,400,0,0,'Elite',NULL,'SCHRAMM Adèle /  LE CAMUS  Stéphane',6,NULL),
(254,5,'','Aéroclub du Pays de Montbéliard','2',286,530,0,0,'Elite',NULL,'MOUTAMANI Mehdi /  JEANBLANC Gilles',6,NULL),
(255,6,'','Aéroclub Banque de France / Aéroclub du Gatinais','8',396,700,0,0,'Elite',NULL,'MIRIGAY Solange /  BOSSARD Philippe',6,NULL),
(256,7,'','Brocard Etampes','8',192,985,0,0,'Elite',NULL,'COLONGE François /  RIQUIER Patrice',6,NULL),
(257,8,'','ACAT TOULOUSE','11',415,880,0,0,'Elite',NULL,'MEDYNA Oksana /  BROCHARD Guillaume',6,NULL),
(258,9,'','Aéroclub d\'AUBIGNY','4',394,1280,0,0,'Elite',NULL,'BONTEMPS Nicolas /  HEIDERIJK Geert',6,NULL),
(259,10,'','Aéroclub de Versailles','8',622,1095,0,0,'Elite',NULL,'DU RANQUET Benoît /  FAIVRE Arnaud',6,NULL),
(260,1,'M','Hispano SUIZA','8',466,900,0,0,'Honneur',NULL,'SONNET Nicolas /  CHABAUD Jean-Philippe',6,NULL),
(261,2,'','AC Brocard','8',667,925,0,0,'Honneur',NULL,'GOURSAUD Cyrille /  GOMES Romain',6,NULL),
(262,3,'','Aéroclub du Pays de Montbéliard','2',507,1345,0,0,'Honneur',NULL,'PROGIN Alexia /  BERNARD Thierry',6,NULL),
(263,4,'',NULL,'2',1750,1200,0,0,'Honneur',NULL,'LETISSIER Thomas /  GUMY Valérie',6,NULL),
(264,1,'','Aéroclub de la Haute Saone','2',92,30,0,0,'Elite',NULL,'LEBRETON Nicolas',5,NULL),
(265,2,'','Polygone 67','6',144,75,0,0,'Elite',NULL,'Schramm Adèle',5,NULL),
(266,3,'','Aéroclub du Haut-Rhin','6',246,180,0,0,'Elite',NULL,'FUCHS Alexis',5,NULL),
(267,4,'','AC Annecy','1',352,90,0,0,'Elite',NULL,'TROUCHE Jean-Baptiste',5,NULL),
(268,5,'','Aéroclub du Pays de Montbéliard','2',256,220,0,0,'Elite',NULL,'MOUTAMANI Mehdi',5,NULL),
(269,6,'','AC Var','12',448,75,0,0,'Elite',NULL,'Jireau JEROME',5,NULL),
(270,7,'','AC de Montélimar Porte de Provence','1',218,320,0,0,'Elite',NULL,'HERAUDEAU Claude',5,NULL),
(271,8,'','HISPANO SUIZA','8',492,90,0,0,'Elite',NULL,'RIVIERE Olivier',5,NULL),
(272,9,'M','ACAT TOULOUSE','11',328,460,0,0,'Elite',NULL,'BROCHARD Guillaume',5,NULL),
(273,10,'','Aéroclub Banque de France','8',668,385,0,0,'Elite',NULL,'MIRIGAY Solange',5,NULL),
(274,11,'','Brocard Etampes','8',758,465,0,0,'Elite',NULL,'Colonge François',5,NULL),
(275,12,'','Banque de france','8',926,370,0,0,'Elite',NULL,'Lienart Patrick',5,NULL),
(276,1,'','ACAT TOULOUSE','11',166,260,0,0,'Honneur',NULL,'Medyna Oksana',5,NULL),
(277,2,'','Brocard Etampes','8',538,345,0,0,'Honneur',NULL,'Riquier Patrice',5,NULL),
(278,3,'','AEROCLUB DU PAYS DE MONTBELLIARD','2',2712,690,0,0,'Honneur',NULL,'LETISSIER THOMAS',5,NULL),
(307,1,'M','Aéroclub d\' Annecy Haute Savoie','1',192,45,0,0,'Elite',NULL,'TROUCHE Jean-Baptiste',9,NULL),
(308,2,'M','Aéroclub du Pays de Montbéliard','2',362,185,0,0,'Elite',NULL,'MOUTAMANI Mehdi',9,NULL),
(309,3,'M','Hispano Suiza','8',588,155,0,0,'Elite',NULL,'RIVIERE Olivier',9,NULL),
(310,4,'M','Brocard Etampes','8',440,345,0,0,'Elite',NULL,'COLONGE François',9,NULL),
(311,1,'M','Brocard Etampes','8',220,155,0,0,'Honneur',NULL,'RIQUIER Patrice',9,NULL),
(312,2,'M','Hispano Suiza','8',198,225,0,0,'Honneur',NULL,'SONNET Nicolas',9,NULL),
(313,3,'M','Aéroclub de Champagne','6',320,345,0,0,'Honneur',NULL,'COULON Félix',9,NULL),
(314,4,'M','AVMB','4',444,240,0,0,'Honneur',NULL,'FRESSANCOURT Thierry',9,NULL),
(315,5,'M','Air Club Blois Vendôme','4',942,415,0,0,'Honneur',NULL,'DE BELLEFON Audrey',9,NULL),
(316,1,'','AC Haut Rhin / AC Annecy','6',75,210,0,0,'Elite',NULL,'FUCHS Alexis /  TROUCHE Jean-Baptiste',4,NULL),
(317,2,'','Aéroclub du Pays de Montb?liard','2',105,315,0,0,'Elite',NULL,'MOUTAMANI Mehdi /  JEANBLANC Gilles',4,NULL),
(318,3,'','ACAT TOULOUSE','11',150,340,0,0,'Elite',NULL,'Medyna Oksana /  BROCHARD Guillaume',4,NULL),
(319,4,'','AC Var','12',386,260,0,0,'Elite',NULL,'JACQUET CHARLES /  JIREAU JEROME',4,NULL),
(320,5,'','ACAT / Aeroclub des IPSA','11',220,480,0,0,'Elite',NULL,'Duprez Jean /  GILLIERS Nathalie',4,NULL),
(321,6,'','Mayenne Air Loisir','13',247,460,0,0,'Elite',NULL,'MARREAUD Catherine /  MARREAUD Claude',4,NULL),
(322,7,'','Banque de France / CASI','8',538,600,0,0,'Elite',NULL,'Lienart Patrick /  DAMBRE Alain',4,NULL),
(323,1,'','Aéroclub de l\'ENAC','11',574,390,0,0,'Honneur',NULL,'LENG Yuchen /  ZAZO Raul',4,NULL),
(324,2,'','ACAM / VUDENHAUT','12',675,430,0,0,'Honneur',NULL,'SCHRAMM Floriane /  TELLE-RABOT LAURA',4,NULL),
(325,3,'','A?roclub d\'Annonay et de la Vall?e du Rh?ne','1',800,580,0,0,'Honneur',NULL,'OUDOT LAURENT /  SAPONJIC ADRIEN',4,NULL),
(349,1,'M','AC Hispano Suiza / Les ailes nancéennes','8',297,355,0,0,'Honneur',NULL,'SONNET Nicolas /  CHABAUD Jean-Philippe',10,NULL),
(350,2,'M','AC Brocard','8',309,750,0,0,'Honneur',NULL,'GOURSAUD Cyrille /  GOMES Romain',10,NULL),
(351,3,'M','Aeroclub Vol moteur de Bourges / UNAC Union aéronautique de Chateauneuf sur Cher','4',439,890,0,0,'Honneur',NULL,'FRESSENCOURT Thierry /  AUPY Pascal',10,NULL),
(352,4,'M','Aubigny sur Nere / Bertin','4',753,705,0,0,'Honneur',NULL,'BERTUCCHI Luc /  LEGENS Julyan',10,NULL),
(353,5,'F','Royan','10',865,1040,0,0,'Honneur',NULL,'LUGAN Françoise /  COSSARDEAUX Delphine',10,NULL),
(354,6,'M','AC Compiègne','7',881,1110,0,0,'Honneur',NULL,'DE BELLEFON Audrey /  REIGNIER Franck',10,NULL),
(355,7,'M','AC Angers Marcé / Aéroclub Angers Marcé','13',1129,950,0,0,'Honneur',NULL,'BETTIG Bruno /  GOURMAUD Jean-François',10,NULL),
(356,8,'','Aéroclub de Tours','4',974,1340,0,0,'Honneur',NULL,'MAGNIEN Benoît /  FRIEDMANN Marine',10,NULL),
(357,9,'','Aeroclub de Champagne / Aéroclub de Champagne','6',976,1360,0,0,'Honneur',NULL,'COULON Félix /  LANGELLIER Bérengère',10,NULL),
(365,1,'','HISPANO SUIZA / AC Annecy','8',27,90,20,0,'Elite',NULL,'RIVIERE Olivier /  TROUCHE Jean-Baptiste',10,NULL),
(366,2,'M','Aéroclub de Compiègne / Poitiers','7',162,210,30,0,'Elite',NULL,'MULLER Hugo /  MOUTERDE Hervé',10,NULL),
(367,3,'','Aéroclub du Pays de Montbéliard','2',160,480,80,0,'Elite',NULL,'MOUTAMANI Mehdi /  JEANBLANC Gilles',10,NULL),
(368,4,'M','Brocard Etampes','8',87,610,40,0,'Elite',NULL,'COLONGE François /  RIQUIER Patrice',10,NULL),
(369,5,'M','Aéro-club de Champagne','6',226,465,60,0,'Elite',NULL,'LOZANO Charles /  CHAUVE Charles',10,NULL),
(370,6,'M','Aéroclub de Versailles','8',159,615,60,0,'Elite',NULL,'DU RANQUET Benoît /  FAIVRE Arnaud',10,NULL),
(371,7,'M','Aéroclub d\'Aubigny / Aéroclub d\'AUBIGNY','4',344,890,200,0,'Elite',NULL,'CHERRIER Denis /  HEIDERIJK Geert',10,NULL);
/*!40000 ALTER TABLE `results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_results`
--

DROP TABLE IF EXISTS `test_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `test_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `crew_id` int(11) DEFAULT NULL,
  `navigation` int(11) DEFAULT NULL,
  `observation` int(11) DEFAULT NULL,
  `landing` int(11) DEFAULT NULL,
  `flight_planning` int(11) DEFAULT NULL,
  `category` varchar(15) DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `literal_crew` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `dns` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_43E230DC1E5D0459` (`test_id`),
  KEY `IDX_43E230DC5FE259F6` (`crew_id`),
  CONSTRAINT `FK_43E230DC1E5D0459` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`),
  CONSTRAINT `FK_43E230DC5FE259F6` FOREIGN KEY (`crew_id`) REFERENCES `crews` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=808 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_results`
--

LOCK TABLES `test_results` WRITE;
/*!40000 ALTER TABLE `test_results` DISABLE KEYS */;
INSERT INTO `test_results` VALUES
(33,1,NULL,27,90,20,NULL,'Elite',NULL,'RIVIERE Olivier/TROUCHE Jean-Baptiste',0,0),
(34,1,NULL,544,790,0,NULL,'Honneur',NULL,'MAGNIEN Benoît/FRIEDMANN Marine',0,0),
(35,1,NULL,344,890,200,NULL,'Elite',NULL,'CHERRIER Denis/HEIDERIJK Geert',0,0),
(36,1,NULL,446,530,0,NULL,'Honneur',NULL,'LUGAN Françoise/COSSARDEAUX Delphine',0,0),
(37,1,NULL,226,465,60,NULL,'Elite',NULL,'LOZANO Charles/CHAUVE Charles',0,0),
(38,1,NULL,162,210,30,NULL,'Elite',NULL,'MULLER Hugo/MOUTERDE Hervé',0,0),
(39,1,NULL,168,490,0,NULL,'Honneur',NULL,'GOURSAUD Cyrille/GOMES Romain',0,0),
(40,1,NULL,180,165,0,NULL,'Honneur',NULL,'SONNET Nicolas/CHABAUD Jean-Philippe',0,0),
(41,1,NULL,87,610,40,NULL,'Elite',NULL,'COLONGE François/RIQUIER Patrice',0,0),
(42,1,NULL,159,615,60,NULL,'Elite',NULL,'DU RANQUET Benoît/FAIVRE Arnaud',0,0),
(43,1,NULL,510,465,0,NULL,'Honneur',NULL,'BERTUCCHI Luc/LEGENS Julyan',0,0),
(44,1,NULL,904,470,0,NULL,'Honneur',NULL,'FRESSENCOURT Thierry/AUPY Pascal',0,0),
(45,1,NULL,793,860,0,NULL,'Honneur',NULL,'COULON Félix/LANGELLIER Bérengère',0,0),
(46,1,NULL,559,480,0,NULL,'Honneur',NULL,'BETTIG Bruno/GOURMAUD Jean-François',0,0),
(48,1,NULL,438,560,0,NULL,'Honneur',NULL,'DE BELLEFON Audrey/REIGNIER Franck',0,0),
(49,2,NULL,15,60,20,NULL,'Elite',NULL,'RIVIERE Olivier/TROUCHE Jean-Baptiste',0,0),
(50,2,NULL,430,550,0,NULL,'Honneur',NULL,'MAGNIEN Benoît/FRIEDMANN Marine',0,0),
(51,2,NULL,214,920,40,NULL,'Elite',NULL,'CHERRIER Denis/HEIDERIJK Geert',0,0),
(52,2,NULL,419,510,0,NULL,'Honneur',NULL,'LUGAN Françoise/COSSARDEAUX Delphine',0,0),
(53,2,NULL,465,910,30,NULL,'Elite',NULL,'LOZANO Charles/CHAUVE Charles',0,0),
(54,2,NULL,93,280,30,NULL,'Elite',NULL,'MULLER Hugo/MOUTERDE Hervé',0,0),
(55,2,NULL,141,260,0,NULL,'Honneur',NULL,'GOURSAUD Cyrille/GOMES Romain',0,0),
(56,2,NULL,117,190,0,NULL,'Honneur',NULL,'SONNET Nicolas/CHABAUD Jean-Philippe',0,0),
(57,2,NULL,21,410,60,NULL,'Elite',NULL,'COLONGE François/RIQUIER Patrice',0,0),
(58,2,NULL,657,890,80,NULL,'Elite',NULL,'DU RANQUET Benoît/FAIVRE Arnaud',0,0),
(59,2,NULL,243,240,0,NULL,'Honneur',NULL,'BERTUCCHI Luc/LEGENS Julyan',0,0),
(60,2,NULL,225,420,0,NULL,'Honneur',NULL,'FRESSENCOURT Thierry/AUPY Pascal',0,0),
(61,2,NULL,183,500,0,NULL,'Honneur',NULL,'COULON Félix/LANGELLIER Bérengère',0,0),
(62,2,NULL,570,470,0,NULL,'Honneur',NULL,'BETTIG Bruno/GOURMAUD Jean-François',0,0),
(63,2,NULL,118,330,20,NULL,'Elite',NULL,'MOUTAMANI Mehdi/JEANBLANC Gilles',0,0),
(64,2,NULL,443,550,0,NULL,'Honneur',NULL,'DE BELLEFON Audrey/REIGNIER Franck',0,0),
(65,3,NULL,120,90,NULL,0,'Honneur',NULL,'RIQUIER Patrice',0,0),
(66,3,NULL,132,95,NULL,0,'Elite',NULL,'RIVIERE Olivier',0,0),
(67,3,NULL,166,125,NULL,0,'Elite',NULL,'MOUTAMANI Mehdi',0,0),
(68,3,NULL,334,325,NULL,0,'Honneur',NULL,'DE BELLEFON Audrey',0,0),
(69,3,NULL,222,195,NULL,0,'Honneur',NULL,'FRESSANCOURT Thierry',0,0),
(70,3,NULL,192,285,NULL,0,'Honneur',NULL,'COULON Félix',0,0),
(71,3,NULL,200,210,NULL,0,'Elite',NULL,'COLONGE François',0,0),
(72,3,NULL,58,165,NULL,0,'Honneur',NULL,'SONNET Nicolas',0,0),
(73,3,NULL,116,45,NULL,0,'Elite',NULL,'TROUCHE Jean-Baptiste',0,0),
(74,4,NULL,100,65,NULL,0,'Honneur',NULL,'RIQUIER Patrice',0,0),
(75,4,NULL,456,60,NULL,0,'Elite',NULL,'RIVIERE Olivier',0,0),
(76,4,NULL,196,60,NULL,0,'Elite',NULL,'MOUTAMANI Mehdi',0,0),
(77,4,NULL,608,90,NULL,0,'Honneur',NULL,'DE BELLEFON Audrey',0,0),
(78,4,NULL,222,45,NULL,0,'Honneur',NULL,'FRESSANCOURT Thierry',0,0),
(79,4,NULL,128,60,NULL,0,'Honneur',NULL,'COULON Félix',0,0),
(80,4,NULL,240,135,NULL,0,'Elite',NULL,'COLONGE François',0,0),
(81,4,NULL,140,60,NULL,0,'Honneur',NULL,'SONNET Nicolas',0,0),
(82,4,NULL,76,0,NULL,0,'Elite',NULL,'TROUCHE Jean-Baptiste',0,0),
(83,5,NULL,410,110,NULL,NULL,'Elite',NULL,'MIRIGAY Solange/BOSSARD Philippe',0,0),
(84,5,NULL,424,555,NULL,NULL,'Honneur',NULL,'VALENCIA Laurent/TAMARO Marie-Anne',0,0),
(85,5,NULL,123,150,NULL,NULL,'Elite',NULL,'DUPREZ Jean/GILLIERS Nathalie',0,0),
(86,5,NULL,151,300,NULL,NULL,'Elite',NULL,'COLONGES François/RIQUIER Patrice',0,0),
(87,5,NULL,69,170,NULL,NULL,'Honneur',NULL,'GOURSAUD Cyrille/GOMES Romain',0,0),
(88,5,NULL,159,120,NULL,NULL,'Elite',NULL,'LIENART Patrick/DAMBRE Alain',0,0),
(89,5,NULL,75,135,NULL,NULL,'Elite',NULL,'BRULAT Johannes/PROUET Antoine',0,0),
(90,5,NULL,51,90,NULL,NULL,'Elite',NULL,'GUILLERAULT Martin/CAZAUX Geneviève',0,0),
(91,5,NULL,502,275,NULL,NULL,'Honneur',NULL,'LUGAN Françoise/COSSARDEAUX Delphine',0,0),
(92,5,NULL,335,260,NULL,NULL,'Honneur',NULL,'BERTUCCHI Luc /LEGENS Julyan',0,0),
(93,5,NULL,204,110,NULL,NULL,'Honneur',NULL,'FRESSENCOURT  Thierry /AUPY Pascal',0,0),
(94,5,NULL,165,135,NULL,NULL,'Elite',NULL,'MARREAUD Catherine/MARREAUD Claude',0,0),
(95,5,NULL,42,15,NULL,NULL,'Elite',NULL,'MULLER Hugo/MOUTERDE Hervé ',0,0),
(96,5,NULL,169,765,NULL,NULL,'Honneur',NULL,'ARETTE  Alain /CLAIRFOND Christel',0,0),
(97,5,NULL,57,235,NULL,NULL,'Elite',NULL,'LEGER Fabien/LOCHET Jean-Claude',0,0),
(98,5,NULL,647,440,NULL,NULL,'Honneur',NULL,'DEVENNE Boris/DE BELLEFON Audrey',0,0),
(99,10,NULL,242,120,NULL,0,'Elite',NULL,'MIRIGAY Solange',0,0),
(100,10,NULL,132,180,NULL,0,'Elite',NULL,'DUPREZ Jean',0,0),
(101,10,NULL,128,125,NULL,0,'Honneur',NULL,'RIQUIER Patrice',0,0),
(102,10,NULL,282,165,NULL,0,'Elite',NULL,'COLONGE François',0,0),
(103,10,NULL,166,180,NULL,0,'Elite',NULL,'DAMBRE Alain',0,0),
(104,10,NULL,186,180,NULL,0,'Elite',NULL,'LIENART Patrick',0,0),
(105,10,NULL,224,120,NULL,0,'Elite',NULL,'BRULAT Johannes',0,0),
(106,10,NULL,268,80,NULL,0,'Honneur',NULL,'PROUET Antoine',0,0),
(107,10,NULL,72,105,NULL,0,'Elite',NULL,'GUILLERAULT Martin',0,0),
(108,10,NULL,222,185,NULL,0,'Honneur',NULL,'FRESSENCOURT Thierry ',0,0),
(109,10,NULL,126,135,NULL,0,'Elite',NULL,'MARREAUD Claude',0,0),
(110,10,NULL,592,265,NULL,0,'Elite',NULL,'BOSSARD Philippe',0,0),
(111,10,NULL,456,165,NULL,0,'Elite',NULL,'ARETTE Alain',0,0),
(112,10,NULL,198,140,NULL,0,'Elite',NULL,'LEGER Fabien',0,0),
(113,10,NULL,1380,425,NULL,0,'Honneur',NULL,'MARCHAND Pascal',0,0),
(114,10,NULL,996,355,NULL,0,'Elite',NULL,'SAIGHI Sylvain',0,0),
(115,10,NULL,392,275,NULL,0,'Honneur',NULL,'DE BELLEFON Audrey',0,0),
(116,10,NULL,916,310,NULL,0,'Honneur',NULL,'DEVENNE Boris',0,0),
(117,10,NULL,350,185,NULL,0,'Elite',NULL,'HERAUDEAU François',0,0),
(118,10,NULL,440,150,NULL,0,'Honneur',NULL,'ROCHARD Pascal',0,0),
(119,11,NULL,290,105,NULL,0,'Elite',NULL,'MIRIGAY Solange',0,0),
(120,11,NULL,138,180,NULL,0,'Elite',NULL,'DUPREZ Jean',0,0),
(121,11,NULL,140,105,NULL,0,'Honneur',NULL,'RIQUIER Patrice',0,0),
(122,11,NULL,956,360,NULL,0,'Elite',NULL,'COLONGE François',0,0),
(123,11,NULL,92,135,NULL,0,'Elite',NULL,'DAMBRE Alain',0,0),
(124,11,NULL,82,205,NULL,0,'Elite',NULL,'LIENART Patrick',0,0),
(125,11,NULL,174,195,NULL,0,'Elite',NULL,'BRULAT Johannes',0,0),
(126,11,NULL,370,85,NULL,0,'Honneur',NULL,'PROUET Antoine',0,0),
(127,11,NULL,52,120,NULL,0,'Elite',NULL,'GUILLERAULT Martin',0,0),
(128,11,NULL,372,95,NULL,0,'Honneur',NULL,'FRESSENCOURT Thierry ',0,0),
(129,11,NULL,92,150,NULL,0,'Elite',NULL,'MARREAUD Claude',0,0),
(130,11,NULL,296,195,NULL,0,'Elite',NULL,'BOSSARD Philippe',0,0),
(131,11,NULL,1080,290,NULL,0,'Elite',NULL,'ARETTE Alain',0,0),
(132,11,NULL,74,140,NULL,0,'Elite',NULL,'LEGER Fabien',0,0),
(133,11,NULL,1300,0,NULL,0,'Honneur',NULL,'MARCHAND Pascal',0,0),
(134,11,NULL,1156,425,NULL,0,'Elite',NULL,'SAIGHI Sylvain',0,0),
(135,11,NULL,344,275,NULL,0,'Honneur',NULL,'DE BELLEFON Audrey',0,0),
(136,11,NULL,926,280,NULL,0,'Honneur',NULL,'DEVENNE Boris',0,0),
(137,11,NULL,140,180,NULL,0,'Elite',NULL,'HERAUDEAU François',0,0),
(138,11,NULL,204,105,NULL,0,'Honneur',NULL,'ROCHARD Pascal',0,0),
(139,12,NULL,301,650,NULL,NULL,'Elite',NULL,'BONTEMPS Nicolas/HEIDERIJK Geert',0,0),
(140,12,NULL,327,330,NULL,NULL,'Elite',NULL,'MIRIGAY Solange/BOSSARD Philippe',0,0),
(141,12,NULL,123,420,NULL,NULL,'Elite',NULL,'COLONGE François/RIQUIER Patrice',0,0),
(142,12,NULL,226,310,NULL,NULL,'Elite',NULL,'MOUTAMANI Mehdi/JEANBLANC Gilles',0,0),
(143,12,NULL,421,585,NULL,NULL,'Honneur',NULL,'GOURSAUD Cyrille/GOMES Romain',0,0),
(144,12,NULL,48,150,NULL,NULL,'Elite',NULL,'RIVIERE  Olivier /JIREAU Jérôme ',0,0),
(145,12,NULL,15,90,NULL,NULL,'Elite',NULL,'FUCHS Alexis/TROUCHE Jean-Baptiste',0,0),
(146,12,NULL,232,340,NULL,NULL,'Elite',NULL,'SCHRAMM Adèle /LE CAMUS  Stéphane ',0,0),
(147,12,NULL,541,660,NULL,NULL,'Elite',NULL,'DU RANQUET Benoît/FAIVRE Arnaud',0,0),
(148,12,NULL,214,525,NULL,NULL,'Elite',NULL,'MEDYNA Oksana/BROCHARD Guillaume',0,0),
(149,12,NULL,900,500,NULL,NULL,'Honneur',NULL,'LETISSIER Thomas/GUMY Valérie',0,0),
(150,12,NULL,246,750,NULL,NULL,'Honneur',NULL,'PROGIN Alexia/BERNARD Thierry',0,0),
(151,12,NULL,156,60,NULL,NULL,'Elite',NULL,'SAUCE Paul/FUSTER Mariano',0,0),
(152,12,NULL,268,500,NULL,NULL,'Honneur',NULL,'SONNET Nicolas/CHABAUD Jean-Philippe',0,0),
(153,13,NULL,93,630,NULL,NULL,'Elite',NULL,'BONTEMPS Nicolas/HEIDERIJK Geert',0,0),
(154,13,NULL,69,370,NULL,NULL,'Elite',NULL,'MIRIGAY Solange/BOSSARD Philippe',0,0),
(155,13,NULL,69,565,NULL,NULL,'Elite',NULL,'COLONGE François/RIQUIER Patrice',0,0),
(156,13,NULL,60,220,NULL,NULL,'Elite',NULL,'MOUTAMANI Mehdi/JEANBLANC Gilles',0,0),
(157,13,NULL,246,340,NULL,NULL,'Honneur',NULL,'GOURSAUD Cyrille/GOMES Romain',0,0),
(158,13,NULL,6,30,NULL,NULL,'Elite',NULL,'RIVIERE  Olivier /JIREAU Jérôme ',0,0),
(159,13,NULL,215,210,NULL,NULL,'Elite',NULL,'FUCHS Alexis/TROUCHE Jean-Baptiste',0,0),
(160,13,NULL,51,60,NULL,NULL,'Elite',NULL,'SCHRAMM Adèle /LE CAMUS  Stéphane ',0,0),
(161,13,NULL,81,435,NULL,NULL,'Elite',NULL,'DU RANQUET Benoît/FAIVRE Arnaud',0,0),
(162,13,NULL,201,355,NULL,NULL,'Elite',NULL,'MEDYNA Oksana/BROCHARD Guillaume',0,0),
(163,13,NULL,850,700,NULL,NULL,'Honneur',NULL,'LETISSIER Thomas/GUMY Valérie',0,0),
(164,13,NULL,261,595,NULL,NULL,'Honneur',NULL,'PROGIN Alexia/BERNARD Thierry',0,0),
(165,13,NULL,24,105,NULL,NULL,'Elite',NULL,'SAUCE Paul/FUSTER Mariano',0,0),
(166,13,NULL,198,400,NULL,NULL,'Honneur',NULL,'SONNET Nicolas/CHABAUD Jean-Philippe',0,0),
(167,14,NULL,56,0,NULL,0,'Elite',NULL,'LEBRETON Nicolas',0,0),
(168,14,NULL,314,190,NULL,0,'Elite',NULL,'MIRIGAY Solange',0,0),
(169,14,NULL,74,155,NULL,0,'Honneur',NULL,'Medyna Oksana',0,0),
(170,14,NULL,298,115,NULL,0,'Honneur',NULL,'Riquier Patrice',0,0),
(171,14,NULL,596,190,NULL,0,'Elite',NULL,'Colonge François',0,0),
(172,14,NULL,138,110,NULL,0,'Elite',NULL,'MOUTAMANI Mehdi',0,0),
(173,14,NULL,122,170,NULL,0,'Elite',NULL,'HERAUDEAU Claude',0,0),
(174,14,NULL,294,205,NULL,0,'Elite',NULL,'Lienart Patrick',0,0),
(175,14,NULL,206,45,NULL,0,'Elite',NULL,'RIVIERE  Olivier',0,0),
(176,14,NULL,244,30,NULL,0,'Elite',NULL,'TROUCHE Jean-Baptiste',0,0),
(177,14,NULL,194,110,NULL,0,'Elite',NULL,'FUCHS Alexis',0,0),
(178,14,NULL,84,45,NULL,0,'Elite',NULL,'Schramm Adèle ',0,0),
(179,14,NULL,1500,280,NULL,0,'Honneur',NULL,'LETISSIER THOMAS',0,0),
(180,14,NULL,310,15,NULL,0,'Elite',NULL,'Jireau JEROME',0,0),
(181,14,NULL,218,225,NULL,0,'Elite',NULL,'BROCHARD Guillaume',0,0),
(182,15,NULL,36,30,NULL,0,'Elite',NULL,'LEBRETON Nicolas',0,0),
(183,15,NULL,354,195,NULL,0,'Elite',NULL,'MIRIGAY Solange',0,0),
(184,15,NULL,92,105,NULL,0,'Honneur',NULL,'Medyna Oksana',0,0),
(185,15,NULL,240,230,NULL,0,'Honneur',NULL,'Riquier Patrice',0,0),
(186,15,NULL,162,275,NULL,0,'Elite',NULL,'Colonge François',0,0),
(187,15,NULL,118,110,NULL,0,'Elite',NULL,'MOUTAMANI Mehdi',0,0),
(188,15,NULL,96,150,NULL,0,'Elite',NULL,'HERAUDEAU Claude',0,0),
(189,15,NULL,632,165,NULL,0,'Elite',NULL,'Lienart Patrick',0,0),
(190,15,NULL,286,45,NULL,0,'Elite',NULL,'RIVIERE  Olivier',0,0),
(191,15,NULL,108,60,NULL,0,'Elite',NULL,'TROUCHE Jean-Baptiste',0,0),
(192,15,NULL,52,70,NULL,0,'Elite',NULL,'FUCHS Alexis',0,0),
(193,15,NULL,60,30,NULL,0,'Elite',NULL,'Schramm Adèle ',0,0),
(194,15,NULL,1212,410,NULL,0,'Honneur',NULL,'LETISSIER THOMAS',0,0),
(195,15,NULL,138,60,NULL,0,'Elite',NULL,'Jireau JEROME',0,0),
(196,15,NULL,110,235,NULL,0,'Elite',NULL,'BROCHARD Guillaume',0,0),
(197,16,NULL,226,215,NULL,0,'Honneur',NULL,'FRESSENCOURT THIERRY',0,0),
(198,16,NULL,136,80,NULL,0,'Elite',NULL,'LEBRETON Nicolas',0,0),
(199,16,NULL,68,200,NULL,0,'Elite',NULL,'Dambre Alain',0,0),
(200,16,NULL,480,235,NULL,0,'Elite',NULL,'MIRIGAY Solange',0,0),
(201,16,NULL,98,120,NULL,0,'Honneur',NULL,'Riquier Patrice',0,0),
(202,16,NULL,116,195,NULL,0,'Elite',NULL,'Colonge François',0,0),
(203,16,NULL,1080,375,NULL,0,'Honneur',NULL,'DE BELLEFON Audrey',0,0),
(204,16,NULL,150,135,NULL,0,'Elite',NULL,'JIREAU JEROME',0,0),
(205,16,NULL,110,75,NULL,0,'Elite',NULL,'RIVIERE  Olivier',0,0),
(206,16,NULL,540,215,NULL,0,'Elite',NULL,'BRULAT Johannes',0,0),
(207,16,NULL,290,135,NULL,0,'Honneur',NULL,'PROUET Antoine',0,0),
(208,16,NULL,124,140,NULL,0,'Elite',NULL,'GUILLERAULT Martin',0,0),
(209,16,NULL,708,200,NULL,0,'Honneur',NULL,'Cuiengnet  Geoffrey ',0,0),
(210,16,NULL,518,210,NULL,0,'Honneur',NULL,'CROISIC Gerald',0,0),
(211,16,NULL,86,185,NULL,0,'Elite',NULL,'HERAUDEAU FRANCOIS',0,0),
(212,16,NULL,150,195,NULL,0,'Honneur',NULL,'SONNET Nicolas',0,0),
(213,16,NULL,194,215,NULL,0,'Elite',NULL,'Marreaud Claude',0,0),
(214,16,NULL,284,230,NULL,0,'Elite',NULL,'BOSSARD Philippe',0,0),
(215,17,NULL,722,135,NULL,0,'Honneur',NULL,'FRESSENCOURT THIERRY',0,0),
(216,17,NULL,48,60,NULL,0,'Elite',NULL,'LEBRETON Nicolas',0,0),
(217,17,NULL,46,135,NULL,0,'Elite',NULL,'Dambre Alain',0,0),
(218,17,NULL,382,135,NULL,0,'Elite',NULL,'MIRIGAY Solange',0,0),
(219,17,NULL,278,280,NULL,0,'Honneur',NULL,'Riquier Patrice',0,0),
(220,17,NULL,102,180,NULL,0,'Elite',NULL,'Colonge François',0,0),
(221,17,NULL,326,210,NULL,0,'Honneur',NULL,'DE BELLEFON Audrey',0,0),
(222,17,NULL,456,170,NULL,0,'Elite',NULL,'JIREAU JEROME',0,0),
(223,17,NULL,168,60,NULL,0,'Elite',NULL,'RIVIERE  Olivier',0,0),
(224,17,NULL,88,170,NULL,0,'Elite',NULL,'BRULAT Johannes',0,0),
(225,17,NULL,100,90,NULL,0,'Honneur',NULL,'PROUET Antoine',0,0),
(226,17,NULL,40,75,NULL,0,'Elite',NULL,'GUILLERAULT Martin',0,0),
(227,17,NULL,250,150,NULL,0,'Honneur',NULL,'Cuiengnet  Geoffrey ',0,0),
(228,17,NULL,932,345,NULL,0,'Honneur',NULL,'CROISIC Gerald',0,0),
(229,17,NULL,188,120,NULL,0,'Elite',NULL,'HERAUDEAU FRANCOIS',0,0),
(230,17,NULL,52,155,NULL,0,'Honneur',NULL,'SONNET Nicolas',0,0),
(231,17,NULL,242,105,NULL,0,'Elite',NULL,'Marreaud Claude',0,0),
(232,17,NULL,292,150,NULL,0,'Elite',NULL,'BOSSARD Philippe',0,0),
(233,18,NULL,296,450,NULL,NULL,'Elite',NULL,'MIRIGAY Solange/BOSSARD Philippe',0,0),
(234,18,NULL,451,905,NULL,NULL,'Honneur',NULL,'VALENCIA LAURENT/TAMARO MARIE-ANNE',0,0),
(235,18,NULL,160,480,NULL,NULL,'Elite',NULL,'Colonge François/RIQUIER Patrice',0,0),
(236,18,NULL,33,60,NULL,NULL,'Elite',NULL,'RIVIERE  Olivier /JIREAU Jérôme ',0,0),
(237,18,NULL,160,420,NULL,NULL,'Elite',NULL,'BRULAT Johannes/PROUET Antoine',0,0),
(238,18,NULL,378,390,NULL,NULL,'Elite',NULL,'GUILLERAULT Martin/CAZAUX Geneviève',0,0),
(239,18,NULL,655,435,NULL,NULL,'Honneur',NULL,'Lugan Françoise/COSSARDEAUX Delphine',0,0),
(240,18,NULL,153,540,NULL,NULL,'Elite',NULL,'du RANQUET Benoît/FAIVRE Arnaud',0,0),
(241,18,NULL,609,710,NULL,NULL,'Honneur',NULL,'de Bellefon Audrey/PIERRON Sebastien',0,0),
(242,18,NULL,473,730,NULL,NULL,'Honneur',NULL,'Fressencourt  Thierry /AUPY Pascal',0,0),
(243,18,NULL,280,740,NULL,NULL,'Honneur',NULL,'MAGNIEN Benoît/FRIEDMANN Marine',0,0),
(244,18,NULL,456,1050,NULL,NULL,'Honneur',NULL,'GAGLIANO Gabriel/KORMANN Thibaullt',0,0),
(245,18,NULL,419,640,NULL,NULL,'Elite',NULL,'GOUPIL Anne Charlotte/DAMBRE Alain',0,0),
(246,18,NULL,624,850,NULL,NULL,'Honneur',NULL,'BETTIG Bruno/RENAUDIN  Hervé',0,0),
(247,18,NULL,99,210,NULL,NULL,'Elite',NULL,'Muller Hugo/MOUTERDE Hervé',0,0),
(248,18,NULL,800,570,NULL,NULL,'Honneur',NULL,'SONNET Nicolas/BLOCH Emmanuel',0,0),
(249,18,NULL,183,370,NULL,NULL,'Elite',NULL,'LORPHELIN Pierre Yves/BALLEREAU Anne Laure',0,0),
(250,19,NULL,405,745,NULL,NULL,'Elite',NULL,'MIRIGAY Solange/BOSSARD Philippe',0,0),
(251,19,NULL,490,670,NULL,NULL,'Honneur',NULL,'VALENCIA LAURENT/TAMARO MARIE-ANNE',0,0),
(252,19,NULL,225,490,NULL,NULL,'Elite',NULL,'Colonge François/RIQUIER Patrice',0,0),
(253,19,NULL,36,180,NULL,NULL,'Elite',NULL,'RIVIERE  Olivier /JIREAU Jérôme ',0,0),
(254,19,NULL,342,630,NULL,NULL,'Elite',NULL,'BRULAT Johannes/PROUET Antoine',0,0),
(255,19,NULL,277,475,NULL,NULL,'Elite',NULL,'GUILLERAULT Martin/CAZAUX Geneviève',0,0),
(256,19,NULL,585,460,NULL,NULL,'Honneur',NULL,'Lugan Françoise/COSSARDEAUX Delphine',0,0),
(257,19,NULL,714,880,NULL,NULL,'Elite',NULL,'du RANQUET Benoît/FAIVRE Arnaud',0,0),
(258,19,NULL,682,645,NULL,NULL,'Honneur',NULL,'de Bellefon Audrey/PIERRON Sebastien',0,0),
(259,19,NULL,526,1005,NULL,NULL,'Honneur',NULL,'Fressencourt  Thierry /AUPY Pascal',0,0),
(260,19,NULL,751,615,NULL,NULL,'Honneur',NULL,'MAGNIEN Benoît/FRIEDMANN Marine',0,0),
(261,19,NULL,432,695,NULL,NULL,'Honneur',NULL,'GAGLIANO Gabriel/KORMANN Thibaullt',0,0),
(262,19,NULL,741,900,NULL,NULL,'Elite',NULL,'GOUPIL Anne Charlotte/DAMBRE Alain',0,0),
(263,19,NULL,253,485,NULL,NULL,'Honneur',NULL,'BETTIG Bruno/RENAUDIN  Hervé',0,0),
(264,19,NULL,81,235,NULL,NULL,'Elite',NULL,'Muller Hugo/MOUTERDE Hervé',0,0),
(265,19,NULL,559,565,NULL,NULL,'Honneur',NULL,'SONNET Nicolas/BLOCH Emmanuel',0,0),
(266,19,NULL,232,225,NULL,NULL,'Elite',NULL,'LORPHELIN Pierre Yves/BALLEREAU Anne Laure',0,0),
(267,20,NULL,574,390,NULL,NULL,'Honneur',NULL,'LENG Yuchen/ZAZO Raul',0,0),
(268,20,NULL,150,340,NULL,NULL,'Elite',NULL,'Medyna Oksana/BROCHARD Guillaume',0,0),
(269,20,NULL,220,480,NULL,NULL,'Elite',NULL,'Duprez Jean/GILLIERS Nathalie',0,0),
(270,20,NULL,247,460,NULL,NULL,'Elite',NULL,'MARREAUD Catherine/MARREAUD Claude',0,0),
(271,20,NULL,386,260,NULL,NULL,'Elite',NULL,'JACQUET CHARLES/JIREAU JEROME',0,0),
(272,20,NULL,105,315,NULL,NULL,'Elite',NULL,'MOUTAMANI Mehdi/JEANBLANC Gilles',0,0),
(273,20,NULL,538,600,NULL,NULL,'Elite',NULL,'Lienart Patrick/DAMBRE Alain',0,0),
(274,20,NULL,675,430,NULL,NULL,'Honneur',NULL,'SCHRAMM Floriane/TELLE-RABOT LAURA',0,0),
(275,20,NULL,75,210,NULL,NULL,'Elite',NULL,'FUCHS Alexis/TROUCHE Jean-Baptiste',0,0),
(276,20,NULL,800,580,NULL,NULL,'Honneur',NULL,'OUDOT LAURENT/SAPONJIC ADRIEN',0,0),
(277,22,NULL,26,90,NULL,0,'Elite',NULL,'LEBRETON Nicolas',0,0),
(278,22,NULL,90,75,NULL,0,'Honneur',NULL,'SCHRAMM Floriane',0,0),
(279,22,NULL,164,90,NULL,0,'Honneur',NULL,'MEDYNA Oksana',0,0),
(280,22,NULL,238,135,NULL,0,'Elite',NULL,'DUPREZ Jean',0,0),
(281,22,NULL,122,120,NULL,0,'Elite',NULL,'GRAFF Claire',0,0),
(282,22,NULL,268,150,NULL,0,'Elite',NULL,'MARREAUD Claude',0,0),
(283,22,NULL,78,120,NULL,0,'Elite',NULL,'DAMBRE Alain',0,0),
(284,22,NULL,164,45,NULL,0,'Honneur',NULL,'JIREAU Jérôme',0,0),
(285,22,NULL,688,105,NULL,0,'Elite',NULL,'MOUTAMANI Mehdi',0,0),
(286,22,NULL,110,60,NULL,0,'Elite',NULL,'HERAUDEAU Claude',0,0),
(287,22,NULL,212,120,NULL,0,'Elite',NULL,'LIENART Patrick',0,0),
(288,22,NULL,256,135,NULL,0,'Honneur',NULL,'JEANBLANC Gilles',0,0),
(289,22,NULL,692,165,NULL,0,'Honneur',NULL,'SAPONJIC Adrien',0,0),
(290,22,NULL,1234,180,NULL,0,'Honneur',NULL,'OUDOT Laurent',0,0),
(291,22,NULL,164,90,NULL,0,'Elite',NULL,'TROUCHE Jean-Baptiste',0,0),
(292,22,NULL,404,90,NULL,0,'Elite',NULL,'FUCHS Alexis',0,0),
(293,22,NULL,270,120,NULL,0,'Honneur',NULL,'JACQUET Charles',0,0),
(294,23,NULL,86,30,NULL,0,'Elite',NULL,'LEBRETON Nicolas',0,0),
(295,23,NULL,156,90,NULL,0,'Honneur',NULL,'SCHRAMM Floriane',0,0),
(296,23,NULL,80,105,NULL,0,'Honneur',NULL,'MEDYNA Oksana',0,0),
(297,23,NULL,90,135,NULL,0,'Elite',NULL,'DUPREZ Jean',0,0),
(298,23,NULL,436,75,NULL,0,'Elite',NULL,'GRAFF Claire',0,0),
(299,23,NULL,138,105,NULL,0,'Elite',NULL,'MARREAUD Claude',0,0),
(300,23,NULL,90,120,NULL,0,'Elite',NULL,'DAMBRE Alain',0,0),
(301,23,NULL,220,30,NULL,0,'Honneur',NULL,'JIREAU Jérôme',0,0),
(302,23,NULL,118,75,NULL,0,'Elite',NULL,'MOUTAMANI Mehdi',0,0),
(303,23,NULL,212,90,NULL,0,'Elite',NULL,'HERAUDEAU Claude',0,0),
(304,23,NULL,300,75,NULL,0,'Elite',NULL,'LIENART Patrick',0,0),
(305,23,NULL,426,75,NULL,0,'Honneur',NULL,'JEANBLANC Gilles',0,0),
(306,23,NULL,660,150,NULL,0,'Honneur',NULL,'SAPONJIC Adrien',0,0),
(307,23,NULL,664,150,NULL,0,'Honneur',NULL,'OUDOT Laurent',0,0),
(308,23,NULL,166,15,NULL,0,'Elite',NULL,'TROUCHE Jean-Baptiste',0,0),
(309,23,NULL,706,60,NULL,0,'Elite',NULL,'FUCHS Alexis',0,0),
(310,23,NULL,482,135,NULL,0,'Honneur',NULL,'JACQUET Charles',0,0),
(351,9,NULL,18,15,20,NULL,'Elite',NULL,'RIVIERE Olivier/JIREAU Jerome',0,0),
(352,9,NULL,111,300,0,NULL,'Elite',NULL,'COLONGE François/RIQUIER Patrice',0,0),
(353,9,NULL,205,400,40,NULL,'Elite',NULL,'MEDYNA Oksana/BROCHARD Guillaume',0,0),
(354,9,NULL,171,135,10,NULL,'Elite',NULL,'GUILLERAULT Martin/CAZAUX Geneviève',0,0),
(355,9,NULL,171,210,60,NULL,'Elite',NULL,'MIRIGAY Solange/BOSSARD Philippe',0,0),
(356,9,NULL,54,15,30,NULL,'Elite',NULL,'MULLER Hugo/MOUTERDE Hervé',0,0),
(357,9,NULL,695,620,20,NULL,'Elite',NULL,'BRULAT Johannes/PROUET Antoine',0,0),
(358,9,NULL,204,320,40,NULL,'Elite',NULL,'MOUTAMANI Mehdi/JEANBLANC Gilles',0,0),
(359,9,NULL,237,275,40,NULL,'Elite',NULL,'RIUTORD Robert/HEIDERIJK Geert',0,0),
(360,9,NULL,388,510,70,NULL,'Elite',NULL,'DU RANQUET Benoît/FAIVRE Arnaud',0,0),
(361,9,NULL,120,50,80,NULL,'Elite',NULL,'TROUCHE Jean-Baptiste/FUSTER Mariano',0,0),
(362,9,NULL,887,650,120,NULL,'Honneur',NULL,'LUGAN Françoise/COSSARDEAUX Delphine',0,0),
(363,9,NULL,332,455,20,NULL,'Honneur',NULL,'BERTUCCHI Luc/LEGENS Julyan',0,0),
(364,9,NULL,409,685,200,NULL,'Honneur',NULL,'BETTIG Bruno/RENAUDIN Hervé',0,0),
(365,9,NULL,481,800,100,NULL,'Honneur',NULL,'VALENCIA Laurent/TAMARO Marie-Anne',0,0),
(366,9,NULL,1002,625,10,NULL,'Honneur',NULL,'GOURSAUD Cyrille/GOMES Romain',0,0),
(367,9,NULL,114,150,30,NULL,'Honneur',NULL,'SONNET Nicolas/CHABAUD Jean-Philippe',0,0),
(368,9,NULL,343,320,20,NULL,'Honneur',NULL,'FRESSENCOURT Thierry/AUPY Pascal',0,0),
(369,9,NULL,324,275,80,NULL,'Elite',NULL,'LIENARTPatrick/DAMBRE Alain',0,0),
(370,9,NULL,597,420,200,NULL,'Honneur',NULL,'GUMY Didier/GUMY Valérie',0,0),
(371,7,NULL,18,185,20,NULL,'Elite',NULL,'RIVIERE Olivier/JIREAU Jerome',0,0),
(372,7,NULL,293,665,30,NULL,'Elite',NULL,'COLONGE François/RIQUIER Patrice',0,0),
(373,7,NULL,514,570,100,NULL,'Elite',NULL,'MEDYNA Oksana/BROCHARD Guillaume',0,0),
(374,7,NULL,313,330,20,NULL,'Elite',NULL,'GUILLERAULT Martin/CAZAUX Geneviève',0,0),
(375,7,NULL,256,490,40,NULL,'Elite',NULL,'MIRIGAY Solange/BOSSARD Philippe',0,0),
(376,7,NULL,54,120,80,NULL,'Elite',NULL,'MULLER Hugo/MOUTERDE Hervé',0,0),
(377,7,NULL,321,455,10,NULL,'Elite',NULL,'BRULAT Johannes/PROUET Antoine',0,0),
(378,7,NULL,235,505,200,NULL,'Elite',NULL,'MOUTAMANI Mehdi/JEANBLANC Gilles',0,0),
(379,7,NULL,322,740,40,NULL,'Elite',NULL,'RIUTORD Robert/HEIDERIJK Geert',0,0),
(380,7,NULL,651,1110,200,NULL,'Elite',NULL,'DU RANQUET Benoît/FAIVRE Arnaud',0,0),
(381,7,NULL,69,225,40,NULL,'Elite',NULL,'TROUCHE Jean-Baptiste/FUSTER Mariano',0,0),
(382,7,NULL,1001,800,200,NULL,'Honneur',NULL,'LUGAN Françoise/COSSARDEAUX Delphine',0,0),
(383,7,NULL,471,420,40,NULL,'Honneur',NULL,'BERTUCCHI Luc/LEGENS Julyan',0,0),
(384,7,NULL,360,545,120,NULL,'Honneur',NULL,'BETTIG Bruno/RENAUDIN Hervé',0,0),
(385,7,NULL,713,965,200,NULL,'Honneur',NULL,'VALENCIA Laurent/TAMARO Marie-Anne',0,0),
(386,7,NULL,859,605,60,NULL,'Honneur',NULL,'GOURSAUD Cyrille/GOMES Romain',0,0),
(387,7,NULL,192,650,40,NULL,'Honneur',NULL,'SONNET Nicolas/CHABAUD Jean-Philippe',0,0),
(388,7,NULL,365,810,120,NULL,'Honneur',NULL,'FRESSENCOURT Thierry/AUPY Pascal',0,0),
(389,7,NULL,245,405,10,NULL,'Elite',NULL,'LIENARTPatrick/DAMBRE Alain',0,0),
(390,7,NULL,634,595,30,NULL,'Honneur',NULL,'GUMY Didier/GUMY Valérie',0,0),
(391,8,NULL,9,135,30,NULL,'Elite',NULL,'RIVIERE Olivier/JIREAU Jerome',0,0),
(392,8,NULL,87,380,330,NULL,'Elite',NULL,'COLONGE François/RIQUIER Patrice',0,0),
(393,8,NULL,295,375,250,NULL,'Elite',NULL,'MEDYNA Oksana/BROCHARD Guillaume',0,0),
(394,8,NULL,111,240,130,NULL,'Elite',NULL,'GUILLERAULT Martin/CAZAUX Geneviève',0,0),
(395,8,NULL,210,360,290,NULL,'Elite',NULL,'MIRIGAY Solange/BOSSARD Philippe',0,0),
(396,8,NULL,30,150,60,NULL,'Elite',NULL,'MULLER Hugo/MOUTERDE Hervé',0,0),
(397,8,NULL,441,500,210,NULL,'Elite',NULL,'BRULAT Johannes/PROUET Antoine',0,0),
(398,8,NULL,168,365,140,NULL,'Elite',NULL,'MOUTAMANI Mehdi/JEANBLANC Gilles',0,0),
(399,8,NULL,359,810,50,NULL,'Elite',NULL,'RIUTORD Robert/HEIDERIJK Geert',0,0),
(400,8,NULL,450,820,80,NULL,'Elite',NULL,'DU RANQUET Benoît/FAIVRE Arnaud',0,0),
(401,8,NULL,42,90,60,NULL,'Elite',NULL,'TROUCHE Jean-Baptiste/FUSTER Mariano',0,0),
(402,8,NULL,818,820,400,NULL,'Honneur',NULL,'LUGAN Françoise/COSSARDEAUX Delphine',0,0),
(403,8,NULL,389,490,400,NULL,'Honneur',NULL,'BERTUCCHI Luc/LEGENS Julyan',0,0),
(404,8,NULL,402,580,240,NULL,'Honneur',NULL,'BETTIG Bruno/RENAUDIN Hervé',0,0),
(405,8,NULL,511,470,260,NULL,'Honneur',NULL,'VALENCIA Laurent/TAMARO Marie-Anne',0,0),
(406,8,NULL,1506,1370,10,NULL,'Honneur',NULL,'GOURSAUD Cyrille/GOMES Romain',0,0),
(407,8,NULL,576,530,280,NULL,'Honneur',NULL,'SONNET Nicolas/CHABAUD Jean-Philippe',0,0),
(408,8,NULL,600,645,160,NULL,'Honneur',NULL,'FRESSENCOURT Thierry/AUPY Pascal',0,0),
(409,8,NULL,105,325,150,NULL,'Elite',NULL,'LIENARTPatrick/DAMBRE Alain',0,0),
(410,8,NULL,820,745,400,NULL,'Honneur',NULL,'GUMY Didier/GUMY Valérie',0,0),
(411,32,16,12,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(412,32,17,21,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(421,33,16,42,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(422,33,17,33,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(423,34,16,81,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(424,34,17,608,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(425,24,20,15,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(426,24,26,216,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(427,24,31,818,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(432,24,30,282,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(434,24,23,180,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(436,24,24,480,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(437,24,22,2613,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(438,24,21,444,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(439,24,44,904,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(441,24,25,15,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(444,24,33,640,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(446,24,29,12,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(447,24,41,3223,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(448,24,19,1039,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(451,24,43,977,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(452,24,27,279,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(456,25,21,159,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(457,25,25,63,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(458,25,19,84,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(459,25,29,153,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(460,25,41,255,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(461,25,26,1052,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(462,25,20,87,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(463,25,31,1616,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(464,25,27,981,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(465,25,43,534,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(466,25,34,276,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(467,25,30,705,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(468,25,42,774,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(469,25,46,353,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(470,24,42,15,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(471,24,46,459,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(472,24,34,192,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(473,25,23,69,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(474,25,24,141,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(475,25,22,1324,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(476,25,44,120,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(477,25,33,1010,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(479,26,43,21,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(480,26,34,300,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(481,26,42,459,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(483,26,30,518,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(484,26,46,462,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(485,26,23,3,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(486,26,24,243,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(488,26,29,293,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(490,26,25,81,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(491,26,19,9,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(493,26,31,21,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(495,26,41,543,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(496,26,33,456,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(497,26,22,683,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(498,26,21,327,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(500,26,20,135,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(501,26,26,683,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(503,27,26,96,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(504,27,27,1265,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(505,26,44,683,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(506,26,27,291,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(507,27,31,1137,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(508,27,43,0,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(509,27,34,548,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(510,27,30,1034,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(511,27,42,303,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(512,27,46,672,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(513,27,24,63,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(514,27,21,744,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(515,27,23,84,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(519,27,22,1570,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(520,27,33,1085,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(522,27,19,1634,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(523,27,41,1391,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(524,27,20,9,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(525,27,29,468,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(526,27,25,1301,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(527,27,44,4702,NULL,0,NULL,'Honneur',NULL,NULL,1,1),
(528,31,27,1594,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(531,31,19,1234,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(533,31,21,3162,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(534,31,31,1412,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(535,31,22,4570,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(537,31,33,2788,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(538,31,25,1891,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(539,31,30,2466,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(541,31,26,1671,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(542,31,34,2588,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(543,31,24,741,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(544,31,29,1116,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(545,31,46,878,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(547,31,23,459,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(548,31,41,768,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(549,31,20,291,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(550,31,43,1441,NULL,0,NULL,'Honneur',NULL,NULL,1,0),
(552,31,42,1775,NULL,0,NULL,'Elite',NULL,NULL,1,0),
(553,31,44,4570,NULL,0,NULL,'Honneur',NULL,NULL,1,1),
(646,30,63,NULL,NULL,328,NULL,'Elite',NULL,NULL,1,0),
(647,30,64,NULL,NULL,635,NULL,'Elite',NULL,NULL,1,0),
(648,30,65,NULL,NULL,190,NULL,'Honneur',NULL,NULL,1,0),
(649,30,66,NULL,NULL,137,NULL,'Elite',NULL,NULL,1,0),
(650,30,67,NULL,NULL,140,NULL,'Honneur',NULL,NULL,1,0),
(651,30,68,NULL,NULL,55,NULL,'Honneur',NULL,NULL,1,0),
(652,30,69,NULL,NULL,330,NULL,'Honneur',NULL,NULL,1,0),
(653,30,70,NULL,NULL,140,NULL,'Honneur',NULL,NULL,1,0),
(654,30,71,NULL,NULL,216,NULL,'Elite',NULL,NULL,1,0),
(655,30,72,NULL,NULL,530,NULL,'Elite',NULL,NULL,1,0),
(656,30,73,NULL,NULL,420,NULL,'Honneur',NULL,NULL,1,0),
(657,30,74,NULL,NULL,70,NULL,'Honneur',NULL,NULL,1,0),
(658,30,76,NULL,NULL,409,NULL,'Elite',NULL,NULL,1,0),
(659,30,49,NULL,NULL,101,NULL,'Elite',NULL,NULL,1,0),
(660,30,50,NULL,NULL,30,NULL,'Honneur',NULL,NULL,1,0),
(661,30,51,NULL,NULL,115,NULL,'Honneur',NULL,NULL,1,0),
(662,30,52,NULL,NULL,355,NULL,'Elite',NULL,NULL,1,0),
(663,30,53,NULL,NULL,216,NULL,'Elite',NULL,NULL,1,0),
(664,30,54,NULL,NULL,425,NULL,'Honneur',NULL,NULL,1,0),
(665,30,55,NULL,NULL,425,NULL,'Elite',NULL,NULL,1,0),
(666,30,56,NULL,NULL,207,NULL,'Elite',NULL,NULL,1,0),
(667,30,57,NULL,NULL,90,NULL,'Elite',NULL,NULL,1,0),
(668,30,58,NULL,NULL,334,NULL,'Elite',NULL,NULL,1,0),
(669,30,59,NULL,NULL,235,NULL,'Elite',NULL,NULL,1,0),
(670,30,60,NULL,NULL,64,NULL,'Elite',NULL,NULL,1,0),
(671,30,61,NULL,NULL,441,NULL,'Elite',NULL,NULL,1,0),
(672,30,62,NULL,NULL,145,NULL,'Honneur',NULL,NULL,1,0),
(700,28,63,706,220,NULL,2,'Elite',NULL,NULL,1,0),
(701,28,64,487,310,NULL,200,'Elite',NULL,NULL,1,0),
(702,28,65,759,370,NULL,0,'Honneur',NULL,NULL,1,0),
(703,28,66,300,280,NULL,39,'Elite',NULL,NULL,1,0),
(704,28,67,347,130,NULL,0,'Honneur',NULL,NULL,1,0),
(705,28,68,531,450,NULL,0,'Honneur',NULL,NULL,1,0),
(706,28,69,1500,470,NULL,0,'Honneur',NULL,NULL,1,0),
(707,28,70,544,310,NULL,0,'Honneur',NULL,NULL,1,0),
(708,28,71,270,130,NULL,0,'Elite',NULL,NULL,1,0),
(709,28,72,312,160,NULL,27,'Elite',NULL,NULL,1,0),
(710,28,73,623,270,NULL,0,'Honneur',NULL,NULL,1,0),
(711,28,74,1021,350,NULL,0,'Honneur',NULL,NULL,1,0),
(712,28,76,547,260,NULL,116,'Elite',NULL,NULL,1,0),
(713,28,49,264,150,NULL,0,'Elite',NULL,NULL,1,0),
(714,28,50,634,250,NULL,0,'Honneur',NULL,NULL,1,0),
(715,28,51,294,210,NULL,0,'Honneur',NULL,NULL,1,0),
(716,28,52,775,240,NULL,10,'Elite',NULL,NULL,1,0),
(717,28,53,387,40,NULL,200,'Elite',NULL,NULL,1,0),
(718,28,54,222,110,NULL,0,'Honneur',NULL,NULL,1,0),
(719,28,55,893,250,NULL,62,'Elite',NULL,NULL,1,0),
(720,28,56,150,220,NULL,0,'Elite',NULL,NULL,1,0),
(721,28,57,664,330,NULL,0,'Elite',NULL,NULL,1,0),
(722,28,58,1015,200,NULL,2,'Elite',NULL,NULL,1,0),
(723,28,59,243,80,NULL,1,'Elite',NULL,NULL,1,0),
(724,28,60,135,90,NULL,0,'Elite',NULL,NULL,1,0),
(725,28,61,1207,310,NULL,12,'Elite',NULL,NULL,1,0),
(726,28,62,706,270,NULL,0,'Honneur',NULL,NULL,1,0),
(781,29,63,501,140,NULL,4,'Elite',NULL,NULL,1,0),
(782,29,64,378,470,NULL,200,'Elite',NULL,NULL,1,0),
(783,29,65,1263,270,NULL,0,'Honneur',NULL,NULL,1,0),
(784,29,66,430,290,NULL,0,'Elite',NULL,NULL,1,0),
(785,29,67,531,100,NULL,0,'Honneur',NULL,NULL,1,0),
(786,29,68,1308,430,NULL,0,'Honneur',NULL,NULL,1,0),
(787,29,69,857,470,NULL,0,'Honneur',NULL,NULL,1,0),
(788,29,70,477,380,NULL,0,'Honneur',NULL,NULL,1,0),
(789,29,71,234,80,NULL,0,'Elite',NULL,NULL,1,0),
(790,29,72,138,140,NULL,19,'Elite',NULL,NULL,1,0),
(791,29,73,329,230,NULL,0,'Honneur',NULL,NULL,1,0),
(792,29,74,291,290,NULL,0,'Honneur',NULL,NULL,1,0),
(793,29,76,456,360,NULL,43,'Elite',NULL,NULL,1,0),
(794,29,49,240,80,NULL,2,'Elite',NULL,NULL,1,0),
(795,29,50,348,180,NULL,0,'Honneur',NULL,NULL,1,0),
(796,29,51,102,140,NULL,0,'Honneur',NULL,NULL,1,0),
(797,29,52,435,380,NULL,59,'Elite',NULL,NULL,1,0),
(798,29,53,90,60,NULL,200,'Elite',NULL,NULL,1,0),
(799,29,54,166,60,NULL,0,'Honneur',NULL,NULL,1,0),
(800,29,55,273,120,NULL,0,'Elite',NULL,NULL,1,0),
(801,29,56,135,160,NULL,0,'Elite',NULL,NULL,1,0),
(802,29,57,470,320,NULL,0,'Elite',NULL,NULL,1,0),
(803,29,58,156,100,NULL,3,'Elite',NULL,NULL,1,0),
(804,29,59,66,40,NULL,0,'Elite',NULL,NULL,1,0),
(805,29,60,153,150,NULL,0,'Elite',NULL,NULL,1,0),
(806,29,61,310,120,NULL,26,'Elite',NULL,NULL,1,0),
(807,29,62,225,300,NULL,0,'Honneur',NULL,NULL,1,0);
/*!40000 ALTER TABLE `test_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_start_order`
--

DROP TABLE IF EXISTS `test_start_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `test_start_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `crew_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `start_order` int(11) DEFAULT NULL,
  `take_off_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `crew_group` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_test_crew` (`test_id`,`crew_id`),
  KEY `IDX_DF0232D75FE259F6` (`crew_id`),
  KEY `IDX_DF0232D71E5D0459` (`test_id`),
  CONSTRAINT `FK_DF0232D71E5D0459` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`),
  CONSTRAINT `FK_DF0232D75FE259F6` FOREIGN KEY (`crew_id`) REFERENCES `crews` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_start_order`
--

LOCK TABLES `test_start_order` WRITE;
/*!40000 ALTER TABLE `test_start_order` DISABLE KEYS */;
INSERT INTO `test_start_order` VALUES
(28,19,24,17,'2025-09-06 11:20:00',3),
(29,20,24,2,'2025-09-06 10:05:00',1),
(30,21,24,13,'2025-09-06 11:00:00',2),
(31,22,24,12,'2025-09-06 10:55:00',2),
(32,23,24,10,'2025-09-06 10:45:00',2),
(33,24,24,11,'2025-09-06 10:50:00',2),
(34,25,24,15,'2025-09-06 11:10:00',2),
(35,26,24,1,'2025-09-06 10:00:00',1),
(36,27,24,4,'2025-09-06 10:15:00',1),
(37,29,24,18,'2025-09-06 11:25:00',3),
(38,30,24,7,'2025-09-06 10:30:00',2),
(39,31,24,3,'2025-09-06 10:10:00',1),
(40,33,24,16,'2025-09-06 11:15:00',2),
(41,34,24,6,'2025-09-06 10:25:00',2),
(42,41,24,19,'2025-09-06 11:30:00',3),
(43,42,24,8,'2025-09-06 10:35:00',2),
(44,43,24,5,'2025-09-06 10:20:00',2),
(45,44,24,14,'2025-09-06 11:05:00',2),
(46,46,24,9,'2025-09-06 10:40:00',2),
(48,19,26,17,'2025-09-06 14:30:00',3),
(49,20,26,2,'2025-09-06 13:15:00',1),
(50,21,26,13,'2025-09-06 14:10:00',2),
(51,22,26,12,'2025-09-06 14:05:00',2),
(52,23,26,10,'2025-09-06 13:55:00',2),
(53,24,26,11,'2025-09-06 14:00:00',2),
(54,25,26,15,'2025-09-06 14:20:00',2),
(55,26,26,1,'2025-09-06 13:10:00',1),
(56,27,26,4,'2025-09-06 13:25:00',1),
(57,29,26,14,'2025-09-06 14:15:00',2),
(58,30,26,7,'2025-09-06 13:40:00',2),
(59,31,26,3,'2025-09-06 13:20:00',1),
(60,33,26,16,'2025-09-06 14:25:00',2),
(61,34,26,6,'2025-09-06 13:35:00',2),
(62,41,26,19,'2025-09-06 14:40:00',3),
(63,42,26,8,'2025-09-06 13:45:00',2),
(64,43,26,5,'2025-09-06 13:30:00',2),
(65,44,26,18,'2025-09-06 14:35:00',3),
(66,46,26,9,'2025-09-06 13:50:00',2),
(67,19,27,17,'2025-09-06 17:40:00',2),
(68,20,27,2,'2025-09-06 16:25:00',1),
(69,21,27,13,'2025-09-06 17:20:00',2),
(70,22,27,12,'2025-09-06 17:15:00',2),
(71,23,27,10,'2025-09-06 17:05:00',2),
(72,24,27,11,'2025-09-06 17:10:00',2),
(73,25,27,15,'2025-09-06 17:30:00',2),
(74,26,27,1,'2025-09-06 16:20:00',1),
(75,27,27,4,'2025-09-06 16:35:00',1),
(76,29,27,14,'2025-09-06 17:25:00',2),
(77,30,27,7,'2025-09-06 16:50:00',2),
(78,31,27,3,'2025-09-06 16:30:00',1),
(79,33,27,16,'2025-09-06 17:35:00',2),
(80,34,27,6,'2025-09-06 16:45:00',2),
(81,41,27,19,'2025-09-06 17:50:00',3),
(82,42,27,8,'2025-09-06 16:55:00',2),
(83,43,27,5,'2025-09-06 16:40:00',2),
(84,44,27,18,'2025-09-06 17:45:00',2),
(85,46,27,9,'2025-09-06 17:00:00',2),
(86,19,31,1,NULL,NULL),
(87,20,31,2,NULL,NULL),
(88,21,31,3,NULL,NULL),
(89,22,31,4,NULL,NULL),
(90,23,31,5,NULL,NULL),
(91,24,31,6,NULL,NULL),
(92,25,31,7,NULL,NULL),
(93,26,31,8,NULL,NULL),
(94,27,31,9,NULL,NULL),
(95,29,31,10,NULL,NULL),
(96,30,31,11,NULL,NULL),
(97,31,31,12,NULL,NULL),
(98,33,31,13,NULL,NULL),
(99,34,31,14,NULL,NULL),
(100,41,31,15,NULL,NULL),
(101,42,31,16,NULL,NULL),
(102,43,31,17,NULL,NULL),
(103,44,31,18,NULL,NULL),
(104,46,31,19,NULL,NULL),
(105,49,28,16,'2026-01-12 15:36:00',2),
(106,50,28,14,'2026-01-12 15:28:00',2),
(107,51,28,23,'2026-01-12 16:35:00',3),
(108,52,28,4,'2026-01-12 14:42:00',1),
(109,53,28,17,'2026-01-12 15:40:00',2),
(110,54,28,25,'2026-01-12 16:46:00',3),
(111,55,28,6,'2026-01-12 14:50:00',1),
(112,56,28,26,'2026-01-12 16:50:00',3),
(113,57,28,20,'2026-01-12 16:20:00',3),
(114,58,28,5,'2026-01-12 14:46:00',1),
(115,59,28,24,'2026-01-12 16:42:00',3),
(116,60,28,9,'2026-01-12 15:08:00',2),
(117,61,28,15,'2026-01-12 15:32:00',2),
(118,62,28,3,'2026-01-12 14:38:00',1),
(119,63,28,8,'2026-01-12 14:58:00',1),
(120,64,28,27,'2026-01-12 16:54:00',3),
(121,65,28,2,'2026-01-12 14:34:00',1),
(122,66,28,10,'2026-01-12 15:12:00',2),
(123,67,28,1,'2026-01-12 14:30:00',1),
(124,68,28,22,'2026-01-12 16:28:00',3),
(125,69,28,13,'2026-01-12 15:24:00',2),
(126,70,28,19,'2026-01-12 15:48:00',2),
(127,71,28,7,'2026-01-12 14:54:00',1),
(128,72,28,18,'2026-01-12 15:44:00',2),
(129,73,28,11,'2026-01-12 15:16:00',2),
(130,74,28,21,'2026-01-12 16:24:00',3),
(131,76,28,12,'2026-01-12 15:20:00',2);
/*!40000 ALTER TABLE `test_start_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tests`
--

DROP TABLE IF EXISTS `tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `competition_id` int(11) NOT NULL,
  `type` varchar(16) DEFAULT NULL,
  `code` varchar(16) NOT NULL,
  `name` varchar(20) NOT NULL,
  `in_progress` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1260FC5E77153098` (`code`),
  KEY `IDX_1260FC5E7B39D312` (`competition_id`),
  CONSTRAINT `FK_1260FC5E7B39D312` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tests`
--

LOCK TABLES `tests` WRITE;
/*!40000 ALTER TABLE `tests` DISABLE KEYS */;
INSERT INTO `tests` VALUES
(1,10,'nav&att','NAV1-XFPW','NAV1',NULL),
(2,10,'nav&att','NAV2-XZDW','NAV2',NULL),
(3,9,'nav','NAV1-TANJ','NAV1',NULL),
(4,9,'nav','NAV2-LWBI','NAV2',NULL),
(5,8,'nav&att','NAV1-BWRF','NAV1',NULL),
(6,8,'nav&att','NAV2-TRQD','NAV2',NULL),
(7,11,'nav&att','NAV1-VIBQ','NAV1',NULL),
(8,11,'nav&att','NAV2-JAQI','NAV2',NULL),
(9,11,'nav&att','NAV3-AWPV','NAV3',NULL),
(10,7,'nav','NAV1-IVNC','NAV1',NULL),
(11,7,'nav','NAV2-SJWG','NAV2',NULL),
(12,6,'nav&att','NAV1-YMDF','NAV1',NULL),
(13,6,'nav&att','NAV2-PLQB','NAV2',NULL),
(14,5,'nav','NAV1-HSIN','NAV1',NULL),
(15,5,'nav','NAV2-BPAD','NAV2',NULL),
(16,2,'nav','NAV1-WQJR','NAV1',NULL),
(17,2,'nav','NAV2-MRIY','NAV2',NULL),
(18,1,'nav&att','NAV1-WVXQ','NAV1',NULL),
(19,1,'nav&att','NAV2-QGIN','NAV2',NULL),
(20,4,'nav&att','NAV1-IPVS','NAV1',NULL),
(21,4,'nav&att','NAV2-AGRP','NAV2',NULL),
(22,3,'nav','NAV1-MFOK','NAV1',NULL),
(23,3,'nav','NAV2-XHUE','NAV2',NULL),
(24,12,'nav','ANR1-QTSR','ANR1',0),
(25,12,'nav','ANR2-CBLU','ANR2',0),
(26,12,'nav','ANR3-GDTP','ANR3',0),
(27,12,'nav','ANR4-SUQX','ANR4',0),
(28,13,'nav','NAV1-FPTG','NAV1',0),
(29,13,'nav','NAV2-FPHM','NAV2',0),
(30,13,'nav','ATT-XSVC','ATT',0),
(31,12,'nav','ANR5-LPJV','ANR5',0),
(32,15,'nav','ANR1-GAWS','ANR1',0),
(33,15,'nav','ANR2-FNIW','ANR2',0),
(34,15,'nav','ANR3-DQJZ','ANR3',0);
/*!40000 ALTER TABLE `tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `type_competition`
--

DROP TABLE IF EXISTS `type_competition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `type_competition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typecomp` varchar(30) NOT NULL,
  `championship_id` int(11) DEFAULT NULL,
  `fix_speed` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FE13FB2094DDBCE9` (`championship_id`),
  CONSTRAINT `FK_FE13FB2094DDBCE9` FOREIGN KEY (`championship_id`) REFERENCES `competitions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `type_competition`
--

LOCK TABLES `type_competition` WRITE;
/*!40000 ALTER TABLE `type_competition` DISABLE KEYS */;
INSERT INTO `type_competition` VALUES
(1,'Rallye',11,NULL),
(2,'Pilotage de précision',13,NULL),
(3,'ANR',12,'75'),
(4,'Rallye de précision',NULL,NULL);
/*!40000 ALTER TABLE `type_competition` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `license_ffa` varchar(9) DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `reset_token` varchar(100) DEFAULT NULL,
  `lastname` varchar(30) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `date_birth` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `flyingclub` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `committee` varchar(255) DEFAULT NULL,
  `polo_size` varchar(255) DEFAULT NULL,
  `is_competitor` tinyint(1) NOT NULL,
  `archived_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `end_validity` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `api_token` varchar(100) DEFAULT NULL,
  `api_token_expires_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'joel.tremblet@gmail.com','[\"ROLE_ADMIN\"]','$2y$13$oUuEadvN.Oek4/vlPn0GIOCloWz4h85oRjPxNkRdLfp4it8gLL4WG',1,'2090611','2025-06-05 17:20:32','2026-01-28 18:36:52','','TREMBLET','Joël','1958-11-17 00:00:00','CASI','0662274512','Masculin','3 Bretagne','XL',1,NULL,'2026-12-31 00:00:00','2ac9624c-dfde-42fa-8824-bc8778ae789a','2026-01-29 18:36:52'),
(2,'patrick.bats33@gmail.com','[\"ROLE_MANAGER\"]','$2y$13$zKp9BauoK8aai3WhtB8wgOKLweI8PVjMtjNiVziS0F/Bpt1Wj/E8W',1,'0188987','2025-06-06 08:01:38','2026-01-14 17:51:45',NULL,'BATS','Patrick','1954-08-11 00:00:00','Dassault Aéro-Club Aquitaine','0651543083','Masculin','10 Nouvelle-Aquitaine','XL',1,NULL,'2026-12-31 17:51:45',NULL,NULL),
(3,'cote-colisson.christian@orange.fr','[\"ROLE_MANAGER\"]','$2y$13$2cCrHFoaLmBQveIM1MJSp.MhN1W4DnvdzUgcA1EYANWG6XopGxy8u',1,'0781104','2025-06-06 08:57:55','2025-09-21 06:51:18','','COTE-COLISSON','Christian','1970-01-01 00:00:00',NULL,NULL,NULL,NULL,NULL,0,NULL,'2025-12-31 00:00:00',NULL,NULL),
(4,'geert.heiderijk@free.fr','[\"ROLE_MANAGER\"]','$2y$13$U9bVvrTQLmR3Oo92FTZr3OZIgqkcSM9gjMCBBrH0IG6GwIwH3xwTa',1,'2074664','2025-06-06 12:04:14','2026-01-09 14:38:21',NULL,'HEIDERIJK','Geert','1958-03-02 00:00:00','AC Aubigny','06 76 85 41 70','Masculin','4 Centre','L',1,NULL,'2026-12-31 14:38:21',NULL,NULL),
(5,'dambre.a@wanadoo.fr','[\"ROLE_MANAGER\"]','$2y$13$jrDu1ftaX7m2penmpTFwH.zHk8VcdmYZocuDZcw/X3iMWE7JKH.m.',1,'0492686','2025-06-06 15:56:47','2026-01-23 20:48:48',NULL,'DAMBRE','Alain','1958-06-16 00:00:00','CASI Brest','06 10 26 03 87','Masculin','7 Haut-de-France','XL',1,NULL,'2026-12-31 20:48:48',NULL,NULL),
(6,'gumy.aero@sfr.fr','[\"ROLE_MANAGER\"]','$2y$13$OSoPeIN0810vvpVG3i5SleIJSFc3VQH1puldVZKjUBPXUA1DlH6Sy',1,'3058054','2025-06-07 16:57:50','2025-06-20 04:38:17','UHibtCXKDUH3fxg4XOvG0u1-0uZLBYqgVKjJg21vGKM','GUMY','Didier','1961-09-11 00:00:00','Les Ailes Nancéiennes','0651252500','Masculin','6 Grand-est','L',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(7,'michelfrere@gmail.com','[\"ROLE_MANAGER\"]','$2y$13$cWU5.Qa.mNkH7G3wrOvkIOCbQSyCRD9Kc7iEuWtc8keafpnFR63ki',1,'0042499','2025-06-07 18:06:36','2025-06-08 15:11:00',NULL,'FRERE','Michel','1970-01-01 00:00:00',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL),
(8,'gilles.aero@free.fr','[\"ROLE_MANAGER\"]','$2y$13$sOsAtbqviXmguNS50TQdOOGpQb9whiBpXPvHPOw7M333.V9Hf0sy.',1,'2799922','2025-06-07 22:15:17','2025-11-29 18:15:01','','JEANBLANC','Gilles','1966-08-04 00:00:00','AC du Pays de Montbéliard','0677088052','Masculin','2 Bourgogne - Franche-Comté','L',1,NULL,'2026-12-31 18:15:01',NULL,NULL),
(9,'crppidf@yahoo.fr','[\"ROLE_MANAGER\"]','$2y$13$w6V37w1ik/Oxc3Skg7GuMeH2tEPqh.uxfA7.AOBIfodWSX83wNZy6',1,'0531863','2025-06-08 08:17:15','2026-01-27 12:13:38','mQMxFHKT3HHRoE3IXbniLjrLnTmD1hHoSaPGf_t86Wg','DE GREEF','Bertrand','1962-01-01 00:00:00','Hispano-Suiza','0686002517','Masculin','8 Ile-de-France','M',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(10,'breizhaeronautik@gmail.com','[\"ROLE_MANAGER\"]','$2y$13$GjxYt3EN7SD/tyxbsJGfqOj5TUIM.G9HZ8ialM/m5rU62MCnOt6J6',1,'6053565','2025-06-11 07:31:46','2026-01-24 17:28:04',NULL,'LOCHET','Jean-Claude','1959-11-17 00:00:00','CASI Brest','0677219340','Masculin','3 Bretagne','L',0,NULL,'2026-12-31 17:27:08',NULL,NULL),
(11,'marreaudclaude@hotmail.com','[\"ROLE_USER\"]','$2y$13$A9dNSK9LJK8mGqv2kqBmB.OAdVx.P/fbo.gkzUE31icwxuc3flFLu',1,'0074583','2025-06-15 16:59:55','2025-06-19 17:37:13',NULL,'MARREAUD','Claude','1962-11-02 00:00:00','Mayenne Air Loisir','0629182642','Masculin','13 Pays-de-la-Loire','XL',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(12,'docpmuller@gmail.com','[\"ROLE_ADMIN\"]','$2y$13$PJLzDzm9FHwhCtOISzrE6OBfOrM12e1Upift4b0stRzNWz0VHDzyy',1,'0752956','2025-06-15 19:38:11','2026-01-28 11:21:48',NULL,'MULLER','Philippe','1955-12-07 00:00:00','UACA',NULL,NULL,NULL,NULL,0,NULL,'2025-12-31 00:00:00',NULL,NULL),
(13,'flugan@free.fr','[\"ROLE_USER\"]','$2y$13$l8qBCBEutGyJjXwiBp1C9.MMO8HT6xwyiCtxh8qZGv7f7DbVUg/uG',1,'2716108','2025-06-16 06:49:39','2026-01-23 10:42:52',NULL,'LUGAN','Françoise','1963-04-04 00:00:00','AC Royan','0612511637','Féminin','10 Nouvelle-Aquitaine','M',1,NULL,'2026-12-31 10:42:52',NULL,NULL),
(14,'cyrille.goursaud@gmail.com','[\"ROLE_USER\"]','$2y$13$p8z0EmEOXWobKzG6RwFjKuELakBnXbaawue73KDMm2uOFnDGFGCou',1,'6204515','2025-06-16 06:49:41','2025-06-19 17:39:08',NULL,'GOURSAUD','Cyrille','1968-12-16 00:00:00','AC Brocard','0663761027','Masculin','8 Ile-de-France','XL',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(15,'herve.renaudin@orange.fr','[\"ROLE_USER\"]','$2y$13$zSUoEggdPOZzYmpME315rOYBB4AiAGLdYLn3yonzJVw0RfXnd3Jfu',1,'7043300','2025-06-16 07:28:21','2025-08-25 17:09:45','','RENAUDIN','Herve','1963-07-14 00:00:00','AC Angers Marcé','0633858214','Masculin','13 Pays-de-la-Loire','L',1,NULL,'2025-12-31 12:57:46',NULL,NULL),
(16,'dcossardeaux@free.fr','[\"ROLE_USER\"]','$2y$13$CzmesHqlpbR/Ydcl/kEq6.MW1To1FMtoGGl7g1pIIlt6asbjAuESK',1,'6173801','2025-06-16 14:50:15','2025-06-19 17:40:32',NULL,'COSSARDEAUX','Delphine','1971-09-10 00:00:00','AC Royan','0611450395','Féminin','10 Nouvelle-Aquitaine','XL',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(17,'mehdi.m.aero@gmail.com','[\"ROLE_USER\"]','$2y$13$4iRxxfbp96OfutzPRmyuGeFjowoeH4e.V06rGtumwK2MlZVBG1nSK',1,'3245396','2025-06-16 19:22:50','2026-01-23 10:35:17',NULL,'MOUTAMANI','Mehdi','1984-03-28 00:00:00','AC du Pays de Montbéliard','0786294107','Masculin','2 Bourgogne - Franche-Comté','L',1,NULL,'2026-12-31 10:35:17',NULL,NULL),
(18,'rgomes91750@gmail.com','[\"ROLE_USER\"]','$2y$13$yzccS/ktK0JWqoBQSvASn.6/sKOcWXnfe7/HWPmwaLGuvvzV9c4mC',1,'8408379','2025-06-18 17:28:32','2025-06-21 13:11:13',NULL,'GOMES','Romain','1994-08-23 00:00:00','AC Brocard','0761557849','Masculin','8 Ile-de-France','XL',1,NULL,'2025-12-31 13:11:13',NULL,NULL),
(19,'joannesbrulat@hotmail.fr','[\"ROLE_USER\"]','$2y$13$gw01ygu1M/OajRqF948FNOQFvMVYCToSa2LHbnUvX2BdqCedCKdDW',1,'2467835','2025-06-18 17:30:16','2025-06-19 17:41:24',NULL,'BRULAT','Johannes','1984-02-28 00:00:00','AC du Poitou','0689706679','Masculin','10 Nouvelle-Aquitaine','L',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(20,'saucepaul55@gmail.com','[\"ROLE_USER\"]','$2y$13$sunfYQ/IdfoDiBMCxbKaHuADjnRKvrTh/8aRAfbl6Wx3ACBf5lJiG',1,'7160559','2025-06-18 17:47:52','2025-06-19 17:33:51',NULL,'SAUCE','Paul','1996-05-26 00:00:00','AC Bedarieux','0672332155','Masculin','11 Occitanie','L',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(21,'les.cazaux@orange.fr','[\"ROLE_USER\"]','$2y$13$WsqQztgBWRobVLHQBrG.PeUnzb6a131DRkC0VDkbfWyqE3omCTv7u',1,'7999261','2025-06-18 19:33:53','2025-07-12 16:14:25','wkIejxIebejXQRTmIk8pe07f2Yusunlf9qY5WbjeRbY','CAZAUX','Geneviève','1970-03-22 00:00:00','UNAC Châteauneuf sur Cher','0620825345','Féminin','4 Centre','L',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(22,'bruno.bettig@gmail.com','[\"ROLE_USER\"]','$2y$13$Ca.b3xCRp631eaFgpadaeeaZbvOO9EtREqYK8gljtUUf0e/w9MaEW',1,'2251973','2025-06-18 19:44:38','2025-06-19 17:41:39',NULL,'BETTIG','Bruno','1974-05-13 00:00:00','AC Angers Marcé','0683893521','Masculin','13 Pays-de-la-Loire','XL',1,NULL,NULL,NULL,NULL),
(23,'t.fressencourt@gmail.com','[\"ROLE_USER\"]','$2y$13$U8XWIfbWfoGR5odofuLE2O37X.2KN7pVj3Sblqe9R1dMyD8HcWTG.',1,'2647162','2025-06-18 19:51:54','2025-06-19 06:43:08',NULL,'FRESSENCOURT','Thierry','1976-02-14 00:00:00','AVMB Bourges','0609249494','Masculin','4 Centre','4XL',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(24,'francois.colonge@gmail.com','[\"ROLE_USER\"]','$2y$13$pX9Ev0WnxbvB679Ow6vUWO4SQdZ7KRGKsgQT5U./ertWLrFWmry0i',1,'7008295','2025-06-18 19:55:04','2025-09-18 07:13:15',NULL,'COLONGE','François','1959-07-22 00:00:00','AC Brocard - Etampes','06 21 14 01 49','Masculin','8 Ile-de-France','2XL',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(26,'patrice.riquier@gmail.com','[\"ROLE_USER\"]','$2y$13$vrtoLzrn0vbHjOkZAQ6eCuid2n/O82DKOpki.gaCqCpaQwsomPaN.',1,'0481580','2025-06-19 04:16:21','2025-09-09 17:52:51',NULL,'RIQUIER','Patrice','1963-12-03 00:00:00','AC Brocard - Etampes','0612703326','Masculin','8 Ile-de-France','L',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(27,'hugolfad@gmail.com','[\"ROLE_USER\"]','$2y$13$UUeHCO2a4XyQdVBnA4CubOjJW3SMXfXcmrzeoC2PM.m.fp2jAOu56',1,'2929057','2025-06-19 06:59:59','2025-06-19 17:35:59',NULL,'MULLER','Hugo','1991-05-29 00:00:00','ACCM Compiègne','0687341535','Masculin','7 Haut-de-France','L',1,NULL,NULL,NULL,NULL),
(28,'nicolbn@hotmail.fr','[\"ROLE_USER\"]','$2y$13$x2WUDA2dWGVLzZ0DIyylmO2qrEsvdXfMlxMB/qo28.OJKoeB.MuUa',1,'2990968','2025-06-19 09:10:33','2025-09-09 17:58:59',NULL,'LEBRETON','Nicolas','1986-02-13 00:00:00','AC de la Haute Saône','0612578362','Masculin','2 Bourgogne - Franche-Comté','M',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(29,'oksana.medyna31@orange.fr','[\"ROLE_USER\"]','$2y$13$PXXrZU.4m0TsU037oVZkcO9Tlt7JPc3eDXmaIXEpmEGBPG853RtUa',1,'2880367','2025-06-19 16:42:34','2025-06-19 17:37:02',NULL,'MEDYNA','Oksana','1984-04-28 00:00:00','ACAT Toulouse','0616672127','Féminin','11 Occitanie','L',1,NULL,NULL,NULL,NULL),
(30,'solange.mirigay@orange.fr','[\"ROLE_MANAGER\"]','$2y$13$dLEIlVvwxnnxmEvLW76CN.R3hnWSh2A1bEj4KzDmF4Bn1tcjHDgnG',1,'2574242','2025-06-19 21:58:35','2025-09-09 17:51:52','','MIRIGAY','Solange','1959-08-05 00:00:00','Banque de France - Etampes','0661465050','Féminin','8 Ile-de-France','L',1,NULL,'2025-12-31 21:30:56',NULL,NULL),
(31,'jeromejireau@hotmail.com','[\"ROLE_USER\"]','$2y$13$T.oiAjzk/tq/YoOtI8hymOKCiY9E93o/UsejZCLyg./ssklCfehUa',1,'2267201','2025-06-20 14:14:05','2025-09-18 07:11:50',NULL,'JIREAU','Jérôme','1984-11-01 00:00:00','Aéroclub du Var','0665703232','Masculin','12 Provences-Alpes-Côtes-d\'Azur','M',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(32,'martin.guillerault@orange.fr','[\"ROLE_USER\"]','$2y$13$Hj0veCyka8OnMyv3zUUE2ueiXwERnNoDhSbhNN5yK15EkpXAJ.6Nq',1,'3091014','2025-06-22 19:25:25','2025-06-23 16:33:44',NULL,'GUILLERAULT','Martin','1987-02-06 00:00:00','UNAC Châteauneuf sur Cher','06 84 79 64 76','Masculin','4 Centre','M',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(33,'marieannetamaro@gmail.com','[\"ROLE_USER\"]','$2y$13$NLXd/tSIh5PlAfz/QFX40.UOCG21TsSn07EmyYcj8vDZFx8JivMu.',1,'7418916','2025-06-23 15:29:12','2025-09-08 19:26:05','','TAMARO','Marie-Anne','1994-05-27 00:00:00','Aéroclub de l\'ENAC','0622340902','Féminin','11 Occitanie','M',0,NULL,'2025-12-31 00:00:00',NULL,NULL),
(34,'guillaume91710@hotmail.fr','[\"ROLE_USER\"]','$2y$13$zL3o2YaoiIDn/glPtf7rtOKb4S4QFRHW/ryiDPv/aATJ4EC9hYnRO',1,'7995723','2025-06-24 21:49:47','2025-09-21 07:21:58',NULL,'BROCHARD','Guillaume','1994-05-28 00:00:00','AC ENAC - Toulouse','0679702941','Masculin','11 Occitanie','M',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(35,'herve.mouterde@gmail.com','[\"ROLE_USER\"]','$2y$13$3GPfgQLsgp9agu9T0m49WeBZEP5TWGwfprWZVp1ETxplgl0aH8COy',1,'7388192','2025-06-26 16:53:48','2025-07-01 13:21:57',NULL,'MOUTERDE','Hervé','1989-06-16 00:00:00','Aéroclub du Poitou','0619226932','Masculin','10 Nouvelle-Aquitaine','S',1,NULL,NULL,NULL,NULL),
(36,'sonnet.nicolas@gmail.com','[\"ROLE_USER\"]','$2y$13$AKqxXx.F1.QW/43XGKVTb.ayIbL2NPGfqUfVgU8YoBRGrw23JexoC',1,'2758654','2025-06-30 16:42:36','2025-08-24 18:32:43',NULL,'SONNET','Nicolas','1986-03-20 00:00:00','AC Hispano Suiza','0618329945','Masculin','8 Ile-de-France','XL',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(38,'aprouet@duck.com','[\"ROLE_USER\"]','$2y$13$.OdXxTz6ZALRikujA5nlAuqVTq//RjkqXEkvMr93bUVIFjvHteeDq',1,'3108453','2025-07-01 19:42:04','2025-07-03 05:29:27',NULL,'PROUET','Antoine','1981-11-08 00:00:00','Tours Aeroclub','0666320303','Masculin','4 Centre','L',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(39,'catmarreaud@hotmail.com','[\"ROLE_USER\"]','$2y$13$3IxtfyKjq45uPkb.Mk/Wa.PUR092gi12Ri5N7hzCD9mT2J7nuFuea',1,'1194430','2025-07-02 08:00:05','2025-08-24 18:31:50',NULL,'MARREAUD','Catherine','1961-12-03 00:00:00','Mayenne Air Loisirs','0603544518','Féminin','13 Pays-de-la-Loire','M',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(41,'jerome.houdier@ff-aero.fr','[\"ROLE_ADMIN\"]','$2y$13$v3d/OxtdKlcfMWrUpqHgQOMMh2IoAKiwjT7WtEgboVbPIXPNrzdmm',1,NULL,'2025-07-06 16:41:39','2025-09-18 07:10:33',NULL,'HOUDIER','Jerôme',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL),
(42,'robert.riutort@orange.fr','[\"ROLE_MANAGER\"]','$2y$13$hlADr5S9Nw6xZOhIeHG..eN336X1YqcGEmmjJQbDD8ZDo4V./Q2Cu',1,'0240838','2025-07-08 14:54:11','2025-07-09 13:30:57',NULL,'RIUTORT','Robert','1953-04-12 00:00:00','AC Angers Marcé','0685827863','Masculin','13 Pays-de-la-Loire','XL',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(48,'alexis.aero@free.fr','[\"ROLE_USER\"]','$2y$13$/AR.U4iC1DUVC93QEUSPIuEwp27AuAMV6M600OMvkZuJqRaNFnKTy',1,'6097356','2025-07-12 10:28:04','2025-07-12 10:28:38',NULL,'FUCHS','Alexis','1972-08-06 00:00:00','ACHR','0615150215','Masculin','6 Grand-est','M',1,NULL,'2025-12-31 10:28:24',NULL,NULL),
(49,'2air@numericable.fr','[\"ROLE_USER\"]','$2y$13$kGtx7to/ylTv9R03dpOimejx7P/2iKmxESdtXU8yxVoQeMLagbb66',1,'0529180','2025-07-12 13:48:04','2025-09-09 17:39:24',NULL,'BOSSARD','Philippe','1964-06-15 00:00:00','AC du Gatinais','0608691399','Masculin','4 Centre','L',1,NULL,'2025-12-31 13:48:43',NULL,NULL),
(50,'racine.lenaic@hotmail.fr','[\"ROLE_USER\"]','$2y$13$brqjLlj9xWyvIC5dsiCH0O.tVL4lckaitcs/FCPjZeVJreey79AIu',1,'2533974','2025-07-18 13:32:11','2025-07-18 13:32:39',NULL,'RACINE','Lénaïc','1984-09-27 00:00:00','Mayenne','0603912288','Masculin','13 Pays-de-la-Loire','M',1,NULL,'2025-12-31 13:32:21',NULL,NULL),
(51,'bebcocteaux@gmail.com','[\"ROLE_USER\"]','$2y$13$WKpSy1skwA77egOESjwFY..IK.yrx21wlreqcBEcj//bBarOxhMgC',1,'8720484','2025-07-29 14:44:36','2025-08-20 12:19:21','','LANGELLIER','Bérengère','1971-11-11 00:00:00','Aeroclub de Champagne','0612373097','Féminin','6 Grand-est','S',1,NULL,'2025-12-31 14:45:54',NULL,NULL),
(52,'cmalidor@gmail.com','[\"ROLE_USER\"]','$2y$13$I7O42QRYyPaqemX35FGb7e8tN2xu/D525t7rR/6HdqIBg/LrxJWZW',1,'2861029','2025-07-30 12:13:50','2025-07-30 13:30:30',NULL,'MALIDOR','Cédric','1981-11-30 00:00:00','ACACG','0690729695','Masculin','14 Amérique-Antilles','5XL',1,NULL,'2025-12-31 13:30:30',NULL,NULL),
(53,'colas.mgt@gmail.com','[\"ROLE_USER\"]','$2y$13$MlN7LwOKaXOQZ7Rn02vryOXpRKXWHprIxfVYPnWKlJzipNfQtwOya',1,'7807563','2025-07-30 13:06:41','2025-08-24 18:41:11',NULL,'LE MAISTRE','Colas','1991-05-25 00:00:00','ACAC Guadeloupe','0690656613','Masculin','14 Amérique-Antilles','XL',1,NULL,'2025-12-31 13:06:48',NULL,NULL),
(54,'yoann.cstn@outlook.fr','[\"ROLE_USER\"]','$2y$13$3eh3dDIPvVhf1s2SmW5M3eGN8GOmnY3DILx3Mf1rgJr8VPZPYMO1u',1,'7233703','2025-07-30 16:20:02','2025-07-30 16:20:46',NULL,'CASTAN','Yoann','1998-01-06 00:00:00','Aeroclub Vauclusien','0761377214','Masculin','12 Provences-Alpes-Côtes-d\'Azur','M',1,NULL,'2025-12-31 16:20:46',NULL,NULL),
(55,'drjacquesrahal@gmail.com','[\"ROLE_USER\"]','$2y$13$hWxIxhmpg7RQp9tIz49xv./fPWNiXD7uMpkvt2stZgv8hfDkS5Vwe',1,'8842858','2025-07-30 21:42:16','2025-07-30 21:42:49',NULL,'RAHAL','Jacques','1990-06-04 00:00:00','Chateaudun','0633412267','Masculin','4 Centre','M',1,NULL,'2025-12-31 21:42:30',NULL,NULL),
(56,'zbogar91@gmail.com','[\"ROLE_USER\"]','$2y$13$ueSQQBW3iIQYU6.hQLT8iu9UqLumV2YAnA7Vqe4W4iH7WvK/Wz/g.',1,'0893651','2025-07-31 06:15:57','2025-08-24 18:33:35',NULL,'ZBOGAR','Jean-Albert','1970-08-18 00:00:00','Aéro-club des Finances','0670818064','Masculin','8 Ile-de-France','L',1,NULL,'2025-12-31 06:16:07',NULL,NULL),
(57,'louis.bernot@hotmail.fr','[\"ROLE_USER\"]','$2y$13$ucHIskPs.QK0/zjqp12gb.YiIuMtSVWFVaGE1M4skFEHTykCZQKKi',1,'8927097','2025-07-31 10:14:50','2025-07-31 10:15:28',NULL,'BERNOT','Louis','1997-11-10 00:00:00','Aéro-Club des Finances','0628740860','Masculin','8 Ile-de-France','L',1,NULL,'2025-12-31 10:15:00',NULL,NULL),
(58,'alexia.progin@hotmail.com','[\"ROLE_USER\"]','$2y$13$8A6oGAfXh3q363ZB8Y832uyAwqo8ZJ2SWiT3sSJ8iNnMaVewOxs7C',1,'8097917','2025-07-31 13:14:21','2025-07-31 13:14:45',NULL,'PROGIN','Alexia','1993-03-14 00:00:00','Du Pays de Montbéliard','0796127312','Féminin','2 Bourgogne - Franche-Comté','M',1,NULL,'2025-12-31 13:14:31',NULL,NULL),
(59,'sylvain.rodier@gmail.com','[\"ROLE_USER\"]','$2y$13$DHVTfEmmu4CVYjwWxXKkle/c9DZ68kBoSMshSRdfR/v2v3H9dZsDW',1,'2049989','2025-08-04 09:52:01','2025-08-24 18:32:59',NULL,'RODIER','Sylvain','1982-02-09 00:00:00','AC Hispano Suiza','0675397623','Masculin','8 Ile-de-France','L',1,NULL,'2025-12-31 09:53:23',NULL,NULL),
(60,'heraudeauc@yahoo.fr','[\"ROLE_USER\"]','$2y$13$G7uUMwdyri6qWGx.nh..EOeiiTshjQAC4.ZfYxBCMYLVZnRhKOrCO',1,'2354793','2025-08-09 15:00:18','2025-09-14 16:54:48',NULL,'HERAUDEAU','Claude','1970-03-23 00:00:00','Montélimar Porte de Provence','0633445954','Masculin','1 Auvergne - Rhône-Alpes','L',0,NULL,'2025-12-31 15:00:46',NULL,NULL),
(61,'francois.heraudeau@free.fr','[\"ROLE_USER\"]','$2y$13$ufyl.YJq6oY8YAwyOiVQ9u6zGlbXWV/zsWWpInaAcSPRPMCFNGPv.',1,'3265949','2025-08-10 08:18:04','2025-09-18 07:08:31',NULL,'HERAUDEAU','François','1967-06-27 00:00:00','AC Jean Piquenot - Cherbourg','0629281278','Masculin','9 Normandie','XL',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(62,'enoraheraudeau@gmail.com','[\"ROLE_USER\"]','$2y$13$nKi2iCO7x4e1yMOK1Vy0Q.4.iaPkNDDxkh1GbL/5tNOkB6WDjmQH.',1,'8753147','2025-08-10 08:31:02','2025-08-13 16:18:32',NULL,'HERAUDEAU','Marie','1997-05-25 00:00:00','JEAN PIQUENOT','0767284307','Féminin','9 Normandie','M',1,NULL,'2025-12-31 16:16:12',NULL,NULL),
(63,'quentin.zaluski@wanadoo.fr','[\"ROLE_USER\"]','$2y$13$X.Drjo6d4cLaaLcJ9BBfOujIBPymBOz8Xz4IJ05YnYi8colz5m6gW',1,'7151673','2025-08-10 09:04:30','2025-08-10 09:08:58',NULL,'ZALUSKI','Quentin','1998-02-12 00:00:00','Uzein Aeroclub Turbomeca','0675455044','Masculin','10 Nouvelle-Aquitaine','M',1,NULL,'2025-12-31 09:08:58',NULL,NULL),
(64,'nicolas.lazcano.pro@gmail.com','[\"ROLE_USER\"]','$2y$13$DY6Bl9BS4g6JWaQAXVMq/.f9vHrmiBRTY.xk.64q/9JiTWJRBJkRa',1,'8680852','2025-08-14 09:56:34','2025-08-24 18:38:23',NULL,'LAZCANO','Nicolas','1998-02-17 00:00:00','Annecy, CLAP73','0666990425','Masculin','1 Auvergne - Rhône-Alpes','L',1,NULL,'2025-12-31 18:17:25',NULL,NULL),
(65,'arnaud.daly@orange.fr','[\"ROLE_USER\"]','$2y$13$gowHzpGCTVExpuser4lzoOOIvYfcobkFNezv7dGZ47Q01I4eR9x3G',1,'0118539','2025-08-14 10:57:15','2025-08-24 18:34:44',NULL,'DALY','Arnaud','1972-04-07 00:00:00','AC de Laon','0681855485','Masculin','7 Haut-de-France','2XL',1,NULL,'2025-12-31 10:57:42',NULL,NULL),
(66,'benoit.duranquet@sfr.fr','[\"ROLE_USER\"]','$2y$13$wEsllDYwaAeFI3ivEJoE/ejnSvxl3dUCN.PeR9ZF3XO9bZix19ZZO',1,'2302719','2025-08-14 19:31:32','2025-08-14 19:32:40',NULL,'DU RANQUET','Benoît','1960-11-16 00:00:00','Aéroclub de Versailles','0678524671','Masculin','8 Ile-de-France','M',1,NULL,'2025-12-31 19:31:58',NULL,NULL),
(67,'arnaudfaivre.af@gmail.com','[\"ROLE_USER\"]','$2y$13$ugCfFhhrIV1Sic/2lMI4r.6Vt8bzA77r1I5Wd1N7VHmWigIlSpQv2',1,'8716128','2025-08-14 20:19:24','2025-08-15 21:01:59','','FAIVRE','Arnaud','1989-05-15 00:00:00','Aeroclub de Versailles','0627065321','Masculin','8 Ile-de-France','L',1,NULL,'2025-12-31 20:42:28',NULL,NULL),
(68,'m_cabau@hotmail.fr','[\"ROLE_USER\"]','$2y$13$p5pmIIMkNHnXZVyi5H6i8uHFABH5bT5Xbp5n8ZvR35LK8EhSDvHWe',1,'2114031','2025-08-15 07:09:12','2025-08-24 18:34:32',NULL,'CABAU','Matthieu','1980-06-14 00:00:00','AC de Laon','0618125560','Masculin','7 Haut-de-France','XL',1,NULL,'2025-12-31 07:09:39',NULL,NULL),
(69,'corinne.bertreix@gmail.com','[\"ROLE_USER\"]','$2y$13$zZVT.hIl4ku4FxHKmyTWBu0KnNekPf1pQqZ/PQEDn88ZkqSa5Q7Oa',1,'7213614','2025-08-16 15:12:44','2026-01-24 20:53:22',NULL,'BERTREIX','Corinne','1983-11-29 00:00:00','Paris Aéro','0661537072','Féminin','8 Ile-de-France','L',1,NULL,'2026-12-31 20:53:22',NULL,NULL),
(70,'david.tintillier@gmail.com','[\"ROLE_USER\"]','$2y$13$Zn66cODtvf8TVo9RTl4XVOg.q./gs.8XoPlI9LhAw8TNQuiiyrRNK',1,'7565021','2025-08-16 20:18:55','2025-08-16 20:19:47',NULL,'TINTILLIER-VAN TROYS','David','1977-10-08 00:00:00','Aéroclub des Cheminots','0611794018','Masculin','8 Ile-de-France','2XL',1,NULL,'2025-12-31 20:19:15',NULL,NULL),
(71,'capuarom@gmail.com','[\"ROLE_USER\"]','$2y$13$8mAh4Wdxsap0IM3iWwPm0OuguTE2Y3.SjJwdKbJjIHWrnZ0wpDCB6',1,'7116577','2025-08-17 10:44:24','2025-08-17 10:46:12','','CAPUANO','Romain','1985-07-20 00:00:00','Ac Courbevoie','0631306607','Masculin','4 Centre','M',1,NULL,'2025-12-31 10:44:38',NULL,NULL),
(72,'jeanclaudenomblot@yahoo.fr','[\"ROLE_USER\"]','$2y$13$q.mt5cQtQprb/u5DAoB93.Iqr2VF5j84Ud0yegx3SZzQGtSSQwTD6',1,'0309096','2025-08-17 15:02:03','2025-08-17 15:10:44',NULL,'NOMBLOT','Jean-Claude','1964-01-18 00:00:00','Aéroclub des Cheminots','0789984110','Masculin','8 Ile-de-France','L',1,NULL,'2025-12-31 15:10:44',NULL,NULL),
(73,'sambuchi.nicolas@gmail.com','[\"ROLE_USER\"]','$2y$13$oAcy7a1Nie/L6BcRp6AhJOHKxsgtk55r6LXdYOzKcLbHldOdXpONq',1,'8659278','2025-08-17 20:59:44','2025-08-24 18:39:00',NULL,'SAMBUCHI','Nicolas','1995-01-23 00:00:00','Annecy, CLAP73','0672900788','Masculin','1 Auvergne - Rhône-Alpes','XL',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(74,'timothee.audouin@gmail.com','[\"ROLE_USER\"]','$2y$13$H56Fn77b4XlsX6JC5EHWieTXUFAndueJXYpMAQzOTE5SVQVDefdgW',1,'7670458','2025-08-19 17:11:04','2025-08-19 17:11:38',NULL,'AUDOUIN','Timothée','1980-11-03 00:00:00','Mayenne','+336 37 36 10 09','Masculin','13 Pays-de-la-Loire','M',1,NULL,'2025-12-31 17:11:38',NULL,NULL),
(75,'eric.pageron@free.fr','[\"ROLE_USER\"]','$2y$13$D/GHyTwa8fAZD465jmpybOCzZc4FbOnkd/sGJOWjVtBaJVT7.KO9.',1,'7391527','2025-08-20 11:29:28','2025-08-24 18:37:05',NULL,'PAGERON','Eric','1975-03-25 00:00:00','AC de Versailles','0695163418','Masculin','8 Ile-de-France','L',1,NULL,'2025-12-31 11:29:53',NULL,NULL),
(76,'lazzarotto_2@hotmail.com','[\"ROLE_USER\"]','$2y$13$emgwN8TW9v74NbCZ45BxHeMbG8etM2cvuZLwnJ/grqgQ0NV8KwTCS',1,'7402449','2025-08-20 11:59:33','2025-08-24 18:37:20',NULL,'LAZZAROTTO','Frederic','1969-09-02 00:00:00','AC de Versailles','0616771360','Masculin','8 Ile-de-France','S',1,NULL,'2025-12-31 11:59:44',NULL,NULL),
(77,'vincent.del.medico@gmail.com','[\"ROLE_USER\"]','$2y$13$5CLTm46XSglM65XShd2ze./RzQsaaHp7s5mGP9KmD//DCC.olbzKa',1,'7396435','2025-08-20 12:17:45','2025-08-20 16:24:19',NULL,'DEL MEDICO','Vincent','1984-12-27 00:00:00','Aéroclub de Champagne','0752651935','Masculin','6 Grand-est','L',1,NULL,'2025-12-31 12:17:55',NULL,NULL),
(78,'adele.aero@gmail.com','[\"ROLE_USER\"]','$2y$13$tuo5PA85IxAjGmn8PaZ5M.o5TrbSAl5Vz/MhkAqksCMpXDvJcCuU2',1,'2850188','2025-08-20 16:12:00','2025-09-09 17:57:50',NULL,'SCHRAMM','Adèle','1980-04-25 00:00:00','Polygone 67 - Strasbourg','0698041083','Féminin','6 Grand-est','S',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(79,'marsuriviere@wanadoo.fr','[\"ROLE_USER\"]','$2y$13$BrSOez7anZSbs4kSX6NesuF.iDxSWFrSafKp7zGzSMZLq4gwzN.7K',1,'2887222','2025-08-20 16:14:34','2026-01-23 16:06:26','','RIVIERE','Olivier','1972-04-02 00:00:00','AC Hispano Suiza - Pontoise','0674247778','Masculin','8 Ile-de-France','M',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(80,'fuster.mariano@gmail.com','[\"ROLE_USER\"]','$2y$13$e4zFt1wrV/cWCM0vQwYib.bkKHG9NfRW9Nnuefes.BUQqROYsN6CO',1,'7513377','2025-08-20 16:31:25','2025-08-20 16:31:26',NULL,'FUSTER','Mariano','1990-01-01 00:00:00','Robert Thiery','0760813731','Masculin','6 Grand-est','L',1,NULL,'2025-12-01 00:00:00',NULL,NULL),
(81,'francois.hatrisse@free.fr','[\"ROLE_USER\"]','$2y$13$HmrM.A5sgcGK3d7zqwjDCunnaM5.oq1PWH/mgFLB2bstBZ8RCELMK',1,'0848929','2025-08-20 17:08:19','2026-01-23 16:28:44','','HATRISSE','François','1968-01-08 00:00:00','Robert Thiery - Verdun','0615075904','Masculin','6 Grand-est','L',1,NULL,'2026-12-31 16:28:44',NULL,NULL),
(82,'corinne.heraudeau@yahoo.fr','[\"ROLE_USER\"]','$2y$13$soklctaXm79Lhs/BmAtcKu8TUC2dnJE2FbdCrbz22zE8YFHBADir2',1,'8761835','2025-08-21 15:59:06','2025-08-24 18:29:23',NULL,'HERAUDEAU','Corinne','1968-04-29 00:00:00','Montélimar Porte de Provence','0645240778','Féminin','1 Auvergne - Rhône-Alpes','XL',1,NULL,'2025-12-31 15:59:28',NULL,NULL),
(83,'aupy.pascal@orange.fr','[\"ROLE_USER\"]','$2y$13$harDq0Eo5uqZ6JoPdob8mekZMFthe/u4VSXrYhDTu8h0nCYVudRnK',1,'7984453','2025-08-21 19:00:27','2025-08-24 18:36:20',NULL,'AUPY','Pascal','1960-02-25 00:00:00','UNAC Châteauneuf sur Cher','0681428743','Masculin','4 Centre','L',1,NULL,'2025-12-31 19:02:29',NULL,NULL),
(84,'vandamme.rita-marieke@hotmail.fr','[\"ROLE_USER\"]','$2y$13$uP3G4HOJhwVDl/qU7DsWnO78I/.ntn9EzUOICmbFo.2iMR/ErBs3a',1,'8714115','2025-08-22 19:42:59','2025-12-20 21:01:51',NULL,'VANDAMME','Rita-Marieke','1980-12-09 00:00:00','Aéroclub de l\'Aisne','0677634151','Féminin','7 Haut-de-France','M',1,NULL,'2026-12-31 21:01:51',NULL,NULL),
(85,'eric.zaluski@wanadoo.fr','[\"ROLE_USER\"]','$2y$13$CxmJR0u6n0K42aRgYmgzJOt3S22j6sSlyDOdNWFZ8SCybdOEJoxcy',1,'7151681','2025-08-22 19:46:58','2026-01-30 07:59:33',NULL,'ZALUSKI','Eric','1968-09-02 00:00:00','Aéroclub de l\'Aisne','0681026311','Masculin','7 Haut-de-France','XL',1,NULL,'2026-12-31 07:59:33',NULL,NULL),
(86,'laurent.valencia@yahoo.fr','[\"ROLE_USER\"]','$2y$13$XVpH5dUHo7PLbPsZqvvPC.6ELX077th3CZbGOLl.ddcK4Ax3kYoQ6',1,'7043979','2025-08-24 12:09:10','2025-08-24 18:39:34',NULL,'VALENCIA','Laurent','1975-02-08 00:00:00','AC Clément Ader','0666109730','Masculin','11 Occitanie','XL',1,NULL,'2025-12-31 12:09:47',NULL,NULL),
(87,'anjoutechniquesservices@orange.fr','[\"ROLE_USER\"]','$2y$13$t53orfTp7ofP6EPA6U4Pie96Yy8oemQMzgNuU1kdgjXKqjjsmmvxm',1,'7135403','2025-08-25 17:37:50','2025-08-26 10:23:03','aDv8Hhj99V8WXoXvSiofyeK4FKyddP8P3Ig4pO4q7pQ','LERAY','Jean-Jacques','1961-02-14 00:00:00','ANGERS MARCE','0674534648','Masculin','13 Pays-de-la-Loire','XL',1,NULL,'2025-12-31 09:34:52',NULL,NULL),
(89,'1.virgule@free.fr','[\"ROLE_USER\"]','$2y$13$jMwW5Et89KOlQ6JesJ2suO.c/76xvLQftU.CNpnc.qgFFtwAIc/62',1,'6073191','2025-08-26 07:12:55','2025-08-26 10:25:39',NULL,'CHABANNE','Thierry','1966-01-15 00:00:00','beaujolais','0614844903','Masculin','1 Auvergne - Rhône-Alpes','XL',1,NULL,'2025-12-31 07:13:13',NULL,NULL),
(93,'jp-aero@outlook.com','[\"ROLE_MANAGER\"]','$2y$13$QyT0KvlIfamuAA0XcdIWXed1N5priK1eDB7meDbf43SDkaj5Bmg8W',1,'7391055','2025-09-04 16:14:03','2025-09-05 14:25:58',NULL,'CHABAUD','Jp','1966-12-07 00:00:00','Les Ailes Nancéennes','0765765002','Masculin','6 Grand-est','XL',1,NULL,'2025-12-31 16:14:37','1b4682fc-4f36-427b-b333-92856b5aa20a','2025-09-06 14:25:58'),
(96,'audrey.cofourain@gmail.com','[\"ROLE_USER\"]','$2y$13$W4CaK26cRlbJeofDkmLZTOuPJ45nDKZ8KX5Kx6IgCN7uYoO7Zp.Sy',1,'7706641','2025-09-09 10:48:10','2025-09-09 14:59:31',NULL,'DE BELLEFON-COFOURAIN','Audrey','1979-06-18 00:00:00','Air Club Blois Vendôme','0687451488','Féminin','4 Centre','S',1,NULL,'2025-12-31 14:59:04',NULL,NULL),
(97,'pataplume@orange.fr','[\"ROLE_USER\"]','$2y$13$rIKVwR590yPaHIr.RhNjzehSlMpcXXEPzh.bKJwk4PASk4R2NuG/u',1,'0385302','2025-09-09 17:15:44','2025-09-09 17:56:31',NULL,'LEGER','Fabien','1969-08-08 00:00:00','SEPAVIA - Arcachon','0612072420',NULL,'10 Nouvelle-Aquitaine','XL',1,NULL,'2025-12-31 17:16:32',NULL,NULL),
(98,'thomas.letissier@gmail.com','[\"ROLE_USER\"]','$2y$13$B9aOd9DlTUL95H1Q.JT.XeFGJkrinj9rkBHXm6P4Jeg2D8eGmEEwG',1,'1145879','2025-09-09 18:40:47','2025-09-16 22:27:43',NULL,'LETISSIER','Thomas','1974-02-13 00:00:00','AC  du Pays de Montbéliard','0608701479','Masculin','2 Bourgogne - Franche-Comté','L',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(99,'geoffrey.cuiengnet@hotmail.fr','[\"ROLE_USER\"]','$2y$13$g5seRSH63z6Q/bIBnvgMY.To30sixTnPe72p6i9kxlOVMm.4EUBGK',1,'2697795','2025-09-09 18:43:10','2026-01-23 19:36:12',NULL,'CUIENGNET','Geoffrey','1984-04-09 00:00:00','Aéroclub du Pays Rochefortais','0632389334','Masculin','10 Nouvelle-Aquitaine','L',1,NULL,'2026-12-31 19:36:12',NULL,NULL),
(100,'adrien.saponjic@gmail.com','[\"ROLE_USER\"]','$2y$13$zvbonF.d3lP4PrWDKGWsR.S2uZTFbw/dSXeVkP0Zejv.2gM2adOu6',1,'6260384','2025-09-09 19:57:33','2026-01-23 19:24:50',NULL,'SAPONJIC','Adrien-Joris','1987-11-14 00:00:00','Aéroclub d\'Annonay et de la Vallée du Rhône','0673267385','Masculin','1 Auvergne - Rhône-Alpes','M',1,NULL,'2026-12-31 19:24:50',NULL,NULL),
(101,'pascalrochard@aol.com','[\"ROLE_USER\"]','$2y$13$gQAvJjsjP14S0JiU7hHjVeapRXICUlsfYfB6jzQw.Q7ElVyIIzH5i',1,'2868966','2025-09-12 20:20:46','2025-09-16 22:29:07',NULL,'ROCHARD','Pascal','1954-02-06 00:00:00','Aéroclub des Grands Lacs - Biscarrosse','0660468374','Masculin','10 Nouvelle-Aquitaine','M',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(102,'coulonfelix@ymail.com','[\"ROLE_USER\"]','$2y$13$Op18aKsBNcM923mQsu1zjelH0RcuMogtSWpHFcqsg9sRor0EXbzDa',1,'6235998','2025-09-14 09:33:48','2025-09-16 16:31:40',NULL,'COULON','Félix','1995-06-18 00:00:00','Aéroclub de Champagne','0677157989','Masculin','6 Grand-est','M',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(103,'florianebofor@hotmail.com','[\"ROLE_USER\"]','$2y$13$//y5YDzEUvOC440pHDRAQOm6NSr18KaC7ek6cbltMsHQhXv/hMIvm',1,'3013141','2025-09-14 20:14:37','2025-09-16 22:25:36',NULL,'SCHRAMM','Floriane','1985-03-10 00:00:00','AC  Aix Marseille','0660935008','Féminin','12 Provences-Alpes-Côtes-d\'Azur','L',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(104,'jb.trouche@gmail.com','[\"ROLE_USER\"]','$2y$13$1Px/yYtVl3jQhcfD8KAqrO4GsVeu0G7.DzyxW8beFcY7eDOQsCg2a',1,'7052665','2025-09-15 09:08:39','2025-09-15 09:09:11',NULL,'TROUCHE','Jean-Baptiste','1991-06-17 00:00:00','Aéroclub d\'Annecy Haute Savoie','+33684872890','Masculin','1 Auvergne - Rhône-Alpes','M',1,NULL,'2025-12-31 09:09:11',NULL,NULL),
(105,'plienart@free.fr','[\"ROLE_USER\"]','$2y$13$r7j4AWeJqNOjoLGPgDrfi.d4rXTtx3M5VGnKhwbHAQs0mPIk2EkZC',1,'2219731','2025-09-15 18:44:57','2025-09-16 22:26:39',NULL,'LIENART','Patrick','1963-07-17 00:00:00','Banque de France - Les Mureaux','0630097661','Masculin','8 Ile-de-France','XL',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(106,'Boris.devenne@gmail.com','[\"ROLE_USER\"]','$2y$13$NuX8w8.2AJ1G5MWGSwxIeOdDWWT1IuXK.5FkbjIgNIcz/rUzNhLD2',1,'2918068','2025-09-16 02:02:09','2025-09-16 02:02:46',NULL,'DEVENNE','Boris','1988-08-29 00:00:00','Air Club Blois Vendôme','0673023046','Masculin','4 Centre','2XL',1,NULL,'2025-12-31 02:02:46',NULL,NULL),
(107,'jean.duprez@gmx.com','[\"ROLE_USER\"]','$2y$13$UEpmR9xTQSNRIOsBVDNGdOzBPa6HDgdb44hOaV2TiT5/2B2cGuYW2',1,'2403517','2025-09-16 19:13:17','2025-09-16 22:16:32',NULL,'DUPREZ','Jean','1978-02-10 00:00:00','ACAT Airbus - Toulouse','06 15 26 03 33','Masculin','11 Occitanie','L',1,NULL,'2025-12-31 00:00:00',NULL,NULL),
(108,'cts@ff-aero.fr','[\"ROLE_USER\"]','$2y$13$hcODzc4zrgXWr1Eyaawhi.y.ZBWYYlsoyXbwJko9PsYD7tfFBWXly',1,NULL,'2025-10-01 07:52:46','2026-01-26 08:20:21',NULL,'THIRION','Jean-Patrick',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL),
(109,'fminair@yahoo.fr','[\"ROLE_USER\"]','$2y$13$besYvkJ3lDwsqF3E.LSeZueqJ68UHQGGr1.ofFYphGkqO//jwp.dG',1,'8053514','2025-10-10 14:02:17','2025-10-10 14:02:39',NULL,'MINAIR','Franck','1977-05-09 00:00:00','Aéroclub du Dauphiné','0682334467','Masculin','1 Auvergne - Rhône-Alpes','M',1,NULL,'2025-12-31 14:02:39',NULL,NULL),
(119,'jtremblet@gmail.com','[\"ROLE_MANAGER\"]','$2y$13$FZlJ6yJ.dUr5ZXq0YC7ETeDxRBHxzn0Qy2m08EZaaoDn5EDr40l5q',1,NULL,'2026-01-15 18:09:08','2026-01-28 09:29:16','aRkig2MiCUGmSzojLkxBPpP1eLpQsNh_MRpsvuCkBpI','TREMBLET','Joel',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL),
(120,'jujuvx@wanadoo.fr','[\"ROLE_MANAGER\"]','$2y$13$GUUY4KT2Szqk0410YRVEBuMI4mc32tWAoM13vc0wZAa47u0inAigy',1,'2487569','2026-01-21 09:37:07','2026-01-21 09:39:00',NULL,'CHERIOUX','Julien','1985-04-18 00:00:00','Giennois','0680386221','Masculin','4 Centre','L',1,NULL,'2026-12-31 00:00:00',NULL,NULL),
(121,'thomasbizet02@gmail.com','[\"ROLE_USER\"]','$2y$13$uNvww58DqUT0CaOVGMl.7OIsATOfwr1PdAwbNy8aOH0vr5B/2jnJm',1,'9036435','2026-01-23 10:36:53','2026-01-23 10:37:16',NULL,'BIZET','Thomas','2003-02-02 00:00:00','Air France Toussus','0651645239','Masculin','8 Ile-de-France','M',1,NULL,'2026-12-31 10:36:54',NULL,NULL),
(122,'marc.duleba@gmail.com','[\"ROLE_USER\"]','$2y$13$uZ4UQ5ZnQL0i2zMDPUMWMeqrc2g/2BAN2Cpf//90OHDMuUH3MK1Ya',1,'3064219','2026-01-23 14:41:29','2026-01-23 14:41:43',NULL,'DULEBA','Marc','1961-07-14 00:00:00','AC andré Tesson','0680102043','Masculin','8 Ile-de-France','3XL',1,NULL,'2026-12-31 14:41:30',NULL,NULL),
(123,'fgrange2@wanadoo.fr','[]','$2y$13$vjpHRuJIri0d0NIxuEekC.j8ysgm5Lu22GV5SxIKzZDulrwMkgb7W',0,NULL,'2026-01-23 15:52:09','2026-01-23 15:52:10',NULL,'GRANGE','Francois',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL),
(124,'michel.collin.lr@gmail.com','[\"ROLE_USER\"]','$2y$13$27F6xg9qdiJQuz3q6zoDO.NBxsksr6CA8PJYJhX572flwMMDTpfja',1,'5862057','2026-01-23 17:06:28','2026-01-23 17:07:07',NULL,'COLLIN','Michel','1961-01-15 00:00:00','La Rochelle Charente-Maritime','0778632522','Masculin','10 Nouvelle-Aquitaine','2XL',1,NULL,'2026-12-31 17:06:29',NULL,NULL),
(125,'loudot@wanadoo.fr','[\"ROLE_USER\"]','$2y$13$K5tS7iDtV337u1TTYH.sZutEW6yevKf.ZagCSKzrnluG0nAaLYeyu',1,'7560113','2026-01-24 17:57:30','2026-01-24 17:58:44',NULL,'OUDOT','Laurent','1964-03-30 00:00:00','d\'Annonay et de la Vallée du Rhône','0662478222','Masculin','1 Auvergne - Rhône-Alpes','XL',1,NULL,'2026-12-31 17:57:31',NULL,NULL),
(126,'parispilot8677@gmail.com','[\"ROLE_USER\"]','$2y$13$rHJsG4AWmiPumgt1DQ/74etDzBWchQ6zK3ldlpnwwOizq3X2Cj7j2',1,NULL,'2026-01-25 08:03:37','2026-01-25 08:04:35',NULL,'MURTETZIKOGLU','Ronny',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL),
(127,'leo.simonin6@gmail.com','[\"ROLE_USER\"]','$2y$13$MtospoYYN2HE9NjbFlvQ..ucDnGyHzddDztbZcuWf0l0F15fkywfi',1,'8791956','2026-01-26 07:21:44','2026-01-26 07:25:05',NULL,'SIMONIN','Leo','2006-12-12 00:00:00','acco darois','0688786190','Masculin','2 Bourgogne - Franche-Comté','S',1,NULL,NULL,NULL,NULL),
(128,'lylian.kubiak@gmail.com','[]','$2y$13$WGhTrTeQOfAuR6SRu2CioealcxlI3QWCpsJLWqONv6gcTeVgWbD0K',0,'9073776','2026-01-27 13:38:16','2026-01-27 13:38:18',NULL,'KUBIAK','Lylian','1993-02-18 00:00:00','Aéroclub de la Lys et de l\'Artois','06 99 53 41 30','Masculin','7 Haut-de-France','L',1,NULL,'2026-12-31 13:38:17',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-30 10:08:08
