-- If you dont have the user
CREATE USER 'PHPSCRIPT'@'localhost' IDENTIFIED BY 'phpscript';

-- If you already have the user
ALTER USER 'PHPSCRIPT'@'localhost' IDENTIFIED BY 'phpscript';

-- Grant privileges
GRANT ALL PRIVILEGES ON *.* TO 'PHPSCRIPT'@'localhost';
FLUSH PRIVILEGES;
