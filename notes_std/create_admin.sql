CREATE USER 'admin'@'localhost' IDENTIFIED BY 'socialvoid';
GRANT ALL PRIVILEGES ON *.* TO 'socialvoid'@'localhost';
CREATE USER 'socialvoid'@'%' IDENTIFIED BY 'socialvoid';
GRANT ALL PRIVILEGES ON *.* TO 'socialvoid'@'%';
FLUSH PRIVILEGES;
