CREATE TABLE lesson (
    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slovak_name varchar(255) NOT NULL,
    english_name varchar(255) NOT NULL,
    learn varchar(255) NOT NULL,
    test varchar(255) NOT NULL,
    pdf varchar(255) NOT NULL,
    page int(11) NOT NULL,
    creator varchar(50) NOT NULL,
    num int(11) NOT NULL
);
