CREATE DATABASE smart_wallet ;
use smart_wallet ;

CREATE TABLE USERS (
    id INT PRIMARY KEY AUTO_INCREMENT ,
    fullname VARCHAR(100) NOT NULL ,
    email VARCHAR(100) NOT NULL UNIQUE ,
    password VARCHAR(100) NOT NULL 
);

CREATE TABLE cards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE incomes (
    id int PRIMARY KEY AUTO_INCREMENT , 
    montant DECIMAL(10,2) NOT NULL , 
    la_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
    descreption TEXT NOT NULL
);

ALTER TABLE incomes ADD card_id INT;

ALTER TABLE incomes
ADD CONSTRAINT fk_incomes_card
FOREIGN KEY (card_id) REFERENCES cards(id)
ON DELETE SET NULL;


CREATE TABLE expenses (
    id int PRIMARY KEY AUTO_INCREMENT,
    montant DECIMAL(10,2) NOT NULL , 
    la_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
    descreption TEXT NOT NULL
);

ALTER TABLE expenses ADD card_id INT;

ALTER TABLE expenses
ADD CONSTRAINT fk_expenses_card
FOREIGN KEY (card_id) REFERENCES cards(id)
ON DELETE SET NULL ;

ALTER TABLE expenses
ADD category_id INT;

ALTER TABLE expenses
ADD CONSTRAINT fk_expenses_category
FOREIGN KEY (category_id) REFERENCES categories(id)
ON DELETE SET NULL;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_categories_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
);


show databases;
DESCRIBE cards ;
use smart_wallet;
SELECT * FROM users WHERE id = 1;
INSERT INTO users (fullname, email, password)
VALUES ('Mohamed', 'fauxtaut@gmail.com', '1234567');

INSERT INTO categories (user_id, name)
VALUES 
(5, 'Food'),
(5, 'Shopping'),
(5, 'Transport'),
(5, 'Rent'),
(5, 'Utilities'),
(5, 'Entertainment'),
(5, 'Others');


INSERT INTO categories (user_id, name)
VALUES 
(1, 'Food'),
(1, 'Shopping'),
(1, 'Transport'),
(1, 'Rent'),
(1, 'Utilities'),
(1, 'Entertainment'),
(1, 'Others');

INSERT INTO cards (user_id, name) VALUES
(1, 'CIH'),
(2, 'Banque Populaire'),
(3, 'Attijari'),
(4, 'Cash');

INSERT INTO incomes (card_id, montant, description) VALUES
(1, 3000, 'Salaire'),
(1, 2500, 'Freelance'),
(4, 4000, 'Job'),
(4, 1200, 'Side hustle');

TRUNCATE incomes ;
