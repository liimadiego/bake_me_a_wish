# PHP Orders

A simple PHP project for managing orders securely

## Requirements

- PHP 7.4 or higher
- MySQL 8.1+
- PHP extensions: PDO, PDO_MySQL, GD

## How to Run

1. Clone the repository:
   ```bash
   git clone https://github.com/liimadiego/bake_me_a_wish
   cd bake_me_a_wish
   ```

2. Set up the MySQL database and create the table:
    ```bash
        CREATE TABLE `orders` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `gift_message` text NOT NULL,
            `quantity` int(11) NOT NULL CHECK (`quantity` >= 1 AND `quantity` <= 50),
            `delivery_date` date NOT NULL,
            `photo_filename` varchar(255) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        )
    ```

3. Configure your database connection in src/db.php:
    ```bash
        Configure host and database credentials
    ```


4. Start the PHP built-in server:
    ```bash
        php -S localhost:8000 -t public
    ```