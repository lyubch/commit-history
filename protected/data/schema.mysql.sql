SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP SCHEMA IF EXISTS `commit-history` ;
CREATE SCHEMA IF NOT EXISTS `commit-history` DEFAULT CHARACTER SET utf8 ;
USE `commit-history` ;

-- -----------------------------------------------------
-- Table `commit-history`.`environments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `commit-history`.`environments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `server_url` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `commit-history`.`commits`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `commit-history`.`commits` (
  `id` VARCHAR(60) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `date` DATETIME NOT NULL,
  `type` TINYINT(1) NOT NULL,
  `task_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `commit-history`.`emails`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `commit-history`.`emails` (
  `email` VARCHAR(100) NOT NULL,
  `env_id` INT(11) NOT NULL,
  PRIMARY KEY (`email`, `env_id`),
  INDEX `fk_emails_environments_idx` (`env_id` ASC),
  CONSTRAINT `fk_emails_environments`
    FOREIGN KEY (`env_id`)
    REFERENCES `commit-history`.`environments` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
