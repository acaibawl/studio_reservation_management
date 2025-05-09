CREATE DATABASE IF NOT EXISTS `srm_local` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
GRANT ALL ON `srm_local`.* TO 'docker'@'%';
CREATE DATABASE IF NOT EXISTS `srm_testing` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
GRANT ALL ON `srm_testing`.* TO 'docker'@'%';
FLUSH PRIVILEGES;