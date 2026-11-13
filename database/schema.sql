-- Structure-only snapshot of the production chores database.
-- Captured 2026-11-14 from MariaDB 11.4.13 using phpMyAdmin.
-- No application rows, credentials, or production database name are included.
-- Review DATABASE.md before using this as a basis for migrations.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `chores` (
  `choreid` int(11) DEFAULT NULL,
  `name` varchar(500) DEFAULT NULL,
  `val` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `chores_done` (
  `cdid` int(11) NOT NULL AUTO_INCREMENT,
  `peopleid` int(11) DEFAULT NULL,
  `choreid` int(11) DEFAULT NULL,
  `whendone` datetime DEFAULT current_timestamp(),
  `approved` varchar(1) DEFAULT NULL,
  `val` double DEFAULT NULL,
  PRIMARY KEY (`cdid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `payments` (
  `paymentid` int(11) DEFAULT NULL,
  `peopleid` int(11) DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `whendone` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `people` (
  `peopleid` int(11) DEFAULT NULL,
  `email` varchar(500) DEFAULT NULL,
  `name` varchar(500) DEFAULT NULL,
  `password` varchar(500) DEFAULT NULL,
  `parent` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `chores_history` AS
SELECT
  `cd`.`cdid` AS `cid`,
  `p`.`peopleid` AS `peopleid`,
  `p`.`name` AS `peoplename`,
  `p`.`email` AS `email`,
  `p`.`parent` AS `parent`,
  `c`.`name` AS `chorename`,
  `cd`.`val` AS `val`,
  `cd`.`whendone` AS `whendone`,
  `cd`.`approved` AS `approved`
FROM (`chores_done` AS `cd`
  JOIN `people` AS `p` ON (`cd`.`peopleid` = `p`.`peopleid`))
  JOIN `chores` AS `c` ON (`cd`.`choreid` = `c`.`choreid`);

CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `people_money` AS
SELECT
  `chores_history`.`peopleid` AS `peopleid`,
  `chores_history`.`peoplename` AS `peoplename`,
  SUM(`chores_history`.`val`) AS `total`
FROM `chores_history`
WHERE `chores_history`.`approved` = 'Y'
GROUP BY `chores_history`.`peopleid`, `chores_history`.`peoplename`;
