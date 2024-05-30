CREATE TABLE learn (
       id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
       name varchar(255) NOT NULL UNIQUE,
       img varchar(255) NOT NULL,
       english_text text NOT NULL,
       slovak_text text NOT NULL,
       english_test_database varchar(255) NOT NULL,
       slovak_test_database varchar(255) NOT NULL
);
