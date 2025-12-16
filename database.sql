CREATE DATABASE smart_wallet ;
use smart_wallet ;

CREATE TABLE incomes (
    id int PRIMARY KEY AUTO_INCREMENT , 
    montant DECIMAL(10,2) NOT NULL , 
    la_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
    descreption TEXT NOT NULL
);

CREATE TABLE expenses (
    id int PRIMARY KEY AUTO_INCREMENT,
    montant DECIMAL(10,2) NOT NULL , 
    la_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
    descreption TEXT NOT NULL
);

CREATE TABLE USERS (
    id INT PRIMARY KEY AUTO_INCREMENT ,
    fullname VARCHAR(100) NOT NULL ,
    email VARCHAR(100) NOT NULL UNIQUE ,
    password VARCHAR(100) NOT NULL 
);

SELECT DATABASE();

ALTER TABLE incomes ADD user_id INT NOT NULL ;
ALTER TABLE expenses ADD user_id INT NOT NULL ;
UPDATE incomes SET user_id = 1;
UPDATE expenses SET user_id = 1;

ALTER TABLE incomes MODIFY user_id INT NOT NULL;
ALTER TABLE expenses MODIFY user_id INT NOT NULL;

ALTER TABLE expenses ADD COLUMN user_id INT ;
ALTER TABLE expenses MODIFY COLUMN user_id INT NOT NULL ;

 ALTER TABLE incomes ADD COLUMN user_id INT NOT NULL ;

ALTER TABLE expenses 
ADD CONSTRAINT fk_expense_user 
FOREIGN KEY (user_id) REFERENCES users(id);

ALTER TABLE incomes 
ADD CONSTRAINT fk_incomes_user 
FOREIGN KEY (user_id) REFERENCES users(id);

INSERT INTO users (fullname,email,password) VALUES ("RAZANE WAKHIDI","wakhidirazane@gmail.com","Ra2004Za");

SELECT * FROM users ;

ALTER TABLE users
ADD COLUMN username VARCHAR(50) UNIQUE AFTER id,
ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER email,
CHANGE full_name fullname VARCHAR(100) NOT NULL;

ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL;

ALTER TABLE incomes DROP COLUMN user_id;
ALTER TABLE expenses DROP COLUMN  user_id;

ALTER TABLE incomes ADD COLUMN user_id INT NOT NULL AFTER la_date;
ALTER TABLE expenses ADD COLUMN user_id INT NOT NULL AFTER la_date;

SELECT * FROM users ;
INSERT INTO users (fullname,email,password)
VALUES ("RAZANE WAKHIDI","wakhidirazane@gmail.com","Ra2004Za");
 SELECT * FROM incomes;
SELECT * FROM expenses;
UPDATE incomes SET user_id = 1 WHERE user_id IS NULL;
UPDATE expenses SET user_id = 1 WHERE user_id IS NULL;

SELECT id , fullname FROM users ;

UPDATE incomes SET user_id = 1 WHERE user_id = 0;
UPDATE expenses SET user_id = 1 WHERE user_id = 0;
ALTER TABLE incomes 
ADD CONSTRAINT fk_incomes_user 
FOREIGN KEY (user_id) REFERENCES users(id);

ALTER TABLE expenses 
ADD CONSTRAINT fk_expense_user 
FOREIGN KEY (user_id) REFERENCES users(id);

ALTER TABLE users 
ADD COLUMN username VARCHAR(50) UNIQUE AFTER id;

ALTER TABLE users 
ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER fullname;

UPDATE users 
SET username = 'razane', avatar = NULL 
WHERE id = 1;

ALTER TABLE users 
CHANGE full_name fullname VARCHAR(100) NOT NULL;
SELECT * FROM users;
