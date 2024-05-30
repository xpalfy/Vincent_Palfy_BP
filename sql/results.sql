CREATE TABLE results (
     id bigint(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
     test_id int(11) NOT NULL,
     username varchar(255) NOT NULL,
     points int(11) NOT NULL,
     time timestamp NULL DEFAULT current_timestamp(),
     passed tinyint(1) DEFAULT 0
);
