-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Vært: localhost:3306
-- Genereringstid: 27. 02 2026 kl. 10:57:56
-- Serverversion: 5.7.24
-- PHP-version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `league_of_legends`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `champions`
--

CREATE TABLE `champions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `difficulty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `champions`
--

INSERT INTO `champions` (`id`, `name`, `title`, `description`, `difficulty`) VALUES
(50, 'Ahri', 'the Nine-Tailed Fox', 'Innately connected to the magic of the spirit realm, Ahri is a fox-like vastaya who can manipulate her prey\'s emotions and consume their essence—receiving flashes of their memory and insight from each soul she consumes. Once a powerful yet wayward predator, Ahri is now traveling the world in search of remnants of her ancestors while also trying to replace her stolen memories with ones of her own making.', 2),
(51, 'Milio', 'The Gentle Flame', 'Milio is a warmhearted boy from Ixtal who has, despite his young age, mastered the fire axiom and discovered something new: soothing fire. With this newfound power, Milio plans to help his family escape their exile by joining the Yun Tal - just like his grandmother once did. Having traveled through the Ixtal jungles to the capital of Ixaocan, Milio now prepares to face the Vidalion and join the Yun Tal, unaware of the trials and dangers that await him.', 2),
(97, 'Briar', 'the Restrained Hunger', 'A failed experiment by the Black Rose, Briar\'s uncontrollable bloodlust required a special pillory to focus her frenzied mind. After years of confinement, this living weapon broke free from her restraints and unleashed herself into the world. Now she\'s controlled by no one—following only her hunger for knowledge and blood—and relishes the opportunities to let loose, even if reining back the frenzy isn\'t easy.', 3),
(98, 'Ambessa', 'Matriarch of War', 'All who know the name Medarda respect and fear the family\'s leader, Ambessa. As a Noxian general, she embodies a deadly combination of ruthless strength and fearless resolve in battle. Her role as matriarch is no different, requiring great cunning to empower the Medardas while leaving no room for failure or compassion. Embracing the merciless ways of the Wolf, Ambessa will do whatever it takes to protect her family\'s legacy, even at the cost of her own children\'s love.', 3),
(99, 'Akshan', 'The Rogue Sentinel', 'Raising an eyebrow in the face of danger, Akshan fights evil with dashing charisma, righteous vengeance, and a conspicuous lack of shirts. He is highly skilled in the art of stealth combat, able to evade the eyes of his enemies and reappear when they least expect him. With a keen sense of justice and a legendary death-reversing weapon, he rights the wrongs of Runeterra\'s many scoundrels while living by his own moral code: \'Don\'t be an ass.\'', 1),
(100, 'Graves', 'the Outlaw', 'Malcolm Graves is a renowned mercenary, gambler, and thief—a wanted man in every city and empire he has visited. Even though he has an explosive temper, he possesses a strict sense of criminal honor, often enforced at the business end of his double-barreled shotgun Destiny. In recent years, he has reconciled a troubled partnership with Twisted Fate, and together they have prospered once more in the turmoil of Bilgewater\'s criminal underbelly.', 1),
(102, 'Jinx', 'the Loose Cannon', 'An unhinged and impulsive criminal from the undercity, Jinx is haunted by the consequences of her past—but that doesn\'t stop her from bringing her own chaotic brand of pandemonium to Piltover and Zaun. She uses her arsenal of DIY weapons to devastating effect, unleashing torrents of colorful explosions and gunfire, inspiring the disenfranchised to rebellion and resistance with the mayhem she leaves in her wake.', 2);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `champs_roles`
--

CREATE TABLE `champs_roles` (
  `id` int(11) NOT NULL,
  `champion_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `champs_roles`
--

INSERT INTO `champs_roles` (`id`, `champion_id`, `role_id`) VALUES
(38, 51, 8),
(52, 51, 3),
(84, 97, 3),
(85, 97, 5),
(86, 98, 6),
(87, 98, 4),
(115, 99, 5),
(116, 99, 8),
(123, 100, 5),
(124, 100, 8),
(130, 50, 3),
(131, 50, 4),
(132, 102, 5);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `difficulties`
--

CREATE TABLE `difficulties` (
  `id` int(11) NOT NULL,
  `difficulty` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `difficulties`
--

INSERT INTO `difficulties` (`id`, `difficulty`) VALUES
(1, 'low'),
(2, 'medium'),
(3, 'high');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lane_position` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `roles`
--

INSERT INTO `roles` (`id`, `role`, `lane_position`) VALUES
(3, 'mage', 'mid'),
(4, 'assasin', 'mid'),
(5, 'marksman', 'bot'),
(6, 'fighter', 'top'),
(7, 'tank', 'top'),
(8, 'support', 'bot');

--
-- Begrænsninger for dumpede tabeller
--

--
-- Indeks for tabel `champions`
--
ALTER TABLE `champions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_champions_diffuculties` (`difficulty`);

--
-- Indeks for tabel `champs_roles`
--
ALTER TABLE `champs_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_champs` (`champion_id`),
  ADD KEY `fk_champs_roles_roles` (`role_id`);

--
-- Indeks for tabel `difficulties`
--
ALTER TABLE `difficulties`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_roles_lanes` (`lane_position`);

--
-- Brug ikke AUTO_INCREMENT for slettede tabeller
--

--
-- Tilføj AUTO_INCREMENT i tabel `champions`
--
ALTER TABLE `champions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- Tilføj AUTO_INCREMENT i tabel `champs_roles`
--
ALTER TABLE `champs_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- Tilføj AUTO_INCREMENT i tabel `difficulties`
--
ALTER TABLE `difficulties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tilføj AUTO_INCREMENT i tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Begrænsninger for tabel `champions`
--
ALTER TABLE `champions`
  ADD CONSTRAINT `fk_champions_diffuculties` FOREIGN KEY (`difficulty`) REFERENCES `difficulties` (`id`);

--
-- Begrænsninger for tabel `champs_roles`
--
ALTER TABLE `champs_roles`
  ADD CONSTRAINT `fk_champs` FOREIGN KEY (`champion_id`) REFERENCES `champions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
