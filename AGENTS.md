Instructions for Codex Agents
=============================

Environment Setup
-----------------

1. Install PHP 7.4 and PHP 8.4 (or the closest available 8.x release) along with common extensions:
   ```bash
   add-apt-repository ppa:ondrej/php -y
   apt-get update
   apt-get install -y php7.4-cli php7.4-xml php7.4-mbstring php7.4-mysql
   apt-get install -y php8.4-cli php8.4-xml php8.4-mbstring php8.4-mysql || apt-get install -y php8.3-cli php8.3-xml php8.3-mbstring php8.3-mysql
   ```
   The PHP binaries will be available as `php7.4` and `php8.4` (or `php8.3`).
2. Install Composer:
   ```bash
   apt-get install -y composer
   ```
3. Install MariaDB and start the service:
   ```bash
   apt-get install -y mariadb-server
   service mariadb start
   mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'root'; FLUSH PRIVILEGES;"
   mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS test;"
   echo "127.0.0.1 mysql" >> /etc/hosts
   ```
4. Install PHP dependencies for the project:
   ```bash
   composer install --ignore-platform-reqs
   ```

Testing
-------
Run the unit tests on both PHP versions:
```bash
php8.4 vendor/bin/phpunit --configuration phpunit.xml.dist # or php8.3 if 8.4 not available
php7.4 vendor/bin/phpunit --configuration phpunit.xml.dist
```

Rules of design:
- The language of the project is English, so all comments, branch names, and documentation are written in English

Package Notes
-------------
- The project uses PHPUnit 9.x and requires the `dom`, `json`, `libxml`, `mbstring`, `tokenizer`, and `xmlwriter` extensions.
- Database tests expect a MySQL server reachable via the DSN `mysql:dbname=test;host=mysql` with credentials `root`/`root`.
