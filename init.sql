CREATE TABLE utenti (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    password CHAR(32) NOT NULL
);

INSERT INTO utenti (username, password) VALUES ('ruben', MD5('scopacasa')), ('riccardo', MD5('andronaco'));