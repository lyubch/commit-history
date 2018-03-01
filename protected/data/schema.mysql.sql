SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `commit_history` DEFAULT CHARACTER SET utf8 ;
USE `commit_history`;

-- -----------------------------------------------------
-- Table `commit_history`.`environments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `commit_history`.`environments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `server_url` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `commit_history`.`branches`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `commit_history`.`branches` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `last_loading_date` DATETIME NOT NULL,
  `env_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_branches_environments_idx` (`env_id` ASC),
  CONSTRAINT `fk_branches_environments`
    FOREIGN KEY (`env_id`)
    REFERENCES `commit_history`.`environments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `commit_history`.`commits`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `commit_history`.`commits` (
  `id` VARCHAR(60) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `date` DATETIME NOT NULL,
  `type` TINYINT(1) NOT NULL,
  `task_id` INT(11) NOT NULL,
  `env_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`, `env_id`),
  INDEX `fk_commits_environments_idx` (`env_id` ASC),
  CONSTRAINT `fk_commits_environments`
    FOREIGN KEY (`env_id`)
    REFERENCES `commit_history`.`environments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `commit_history`.`emails`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `commit_history`.`emails` (
  `email` VARCHAR(100) NOT NULL,
  `env_id` INT(11) NOT NULL,
  PRIMARY KEY (`email`, `env_id`),
  INDEX `fk_emails_environments_idx` (`env_id` ASC),
  CONSTRAINT `fk_emails_environments`
    FOREIGN KEY (`env_id`)
    REFERENCES `commit_history`.`environments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
