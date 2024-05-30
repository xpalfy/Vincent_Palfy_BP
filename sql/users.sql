CREATE TABLE users (
    id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username varchar(50) NOT NULL UNIQUE KEY,
    password varchar(255) NOT NULL,
    first_name varchar(100) NOT NULL,
    last_name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    telephone varchar(15) NOT NULL,
    created_at timestamp NULL DEFAULT current_timestamp(),
    role varchar(255) NOT NULL,
    active int(11) NOT NULL,
    two_factor_secret varchar(255) DEFAULT NULL
);
