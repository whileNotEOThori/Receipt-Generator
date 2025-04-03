CREATE DATABASE coding_club;

USE coding_club;

CREATE TABLE receipts(
	id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_num VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    phone_num VARCHAR(10) NOT NULL,
    student_num VARCHAR(20) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    exec_member VARCHAR(50) NOT NULL,
    create_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE testing(
	id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_num VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    phone_num VARCHAR(10) NOT NULL,
    student_num VARCHAR(20) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    exec_member VARCHAR(50) NOT NULL,
    create_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

select * from testing;

delete from coding_club.testing where (id  = 51);
