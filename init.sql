CREATE DATABASE IF NOT EXISTS dbuser;
GRANT ALL PRIVILEGES ON dbuser.* TO 'dbuserapp'@'%' IDENTIFIED BY 'dbuserpass';

CREATE DATABASE IF NOT EXISTS dbuser_test;
GRANT ALL PRIVILEGES ON dbuser_test.* TO 'test_dbuserapp'@'%' IDENTIFIED BY 'test_dbuserpass';

FLUSH PRIVILEGES;