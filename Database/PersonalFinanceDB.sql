
-- MySQL Script for PersonalFinanceDB

DROP DATABASE IF EXISTS PersonalFinanceDB;
CREATE DATABASE PersonalFinanceDB DEFAULT CHARACTER SET utf8;
USE PersonalFinanceDB;

-- Users Table
CREATE TABLE Users (
  user_id INT NOT NULL AUTO_INCREMENT,
  firstname VARCHAR(50),
  surname VARCHAR(50),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(100),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id)
) ENGINE=InnoDB;

-- Accounts Table
CREATE TABLE Accounts (
  account_id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  account_name VARCHAR(100),
  balance DECIMAL(12,2),
  type ENUM('bank', 'cash'),
  PRIMARY KEY (account_id),
  FOREIGN KEY (user_id) REFERENCES Users(user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Transactions Table
CREATE TABLE Transactions (
  transaction_id INT NOT NULL AUTO_INCREMENT,
  account_id INT NOT NULL,
  amount DECIMAL(10,2),
  category VARCHAR(50),
  type ENUM('income', 'expense'),
  date DATE,
  note TEXT,
  PRIMARY KEY (transaction_id),
  FOREIGN KEY (account_id) REFERENCES Accounts(account_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Budgets Table
CREATE TABLE Budgets (
  budget_id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  category VARCHAR(50),
  `limit` DECIMAL(10,2),
  start_date DATE,
  end_date DATE,
  PRIMARY KEY (budget_id),
  FOREIGN KEY (user_id) REFERENCES Users(user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Goals Table
CREATE TABLE Goals (
  goal_id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  goal_name VARCHAR(100),
  target_amount DECIMAL(12,2),
  saved_amount DECIMAL(12,2) DEFAULT 0.00,
  deadline DATE,
  PRIMARY KEY (goal_id),
  FOREIGN KEY (user_id) REFERENCES Users(user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;
