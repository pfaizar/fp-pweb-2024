# Airline Ticketing

> Use PHP to serve and don't forget to run composer install to install all dependencies package

---

### Setup PHP

**Host PHP**

```sh
php -S localhost:6969
```

**Install dependencies**

> Make sure to have composer installed

```sh
composer install
```

### Setup MySQL

**Admin login**

```
email : admin@admin.com
pass : admin123
```

**Seeding**
```sql
CREATE DATABASE airline_ticketing;
USE airline_ticketing;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    photo_path VARCHAR(255),
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE flights (
    id INT PRIMARY KEY AUTO_INCREMENT,
    flight_number VARCHAR(10) NOT NULL UNIQUE,
    departure_city VARCHAR(100) NOT NULL,
    arrival_city VARCHAR(100) NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    total_seats INT NOT NULL,
    available_seats INT NOT NULL,
    price DECIMAL(20,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE users
ADD role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
ADD no_telp VARCHAR(15) NOT NULL;

CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    seats_booked INT NOT NULL,
    total_price DECIMAL(20,2) NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (flight_id) REFERENCES flights(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password, role)
VALUES ('Admin', 'admin@admin.com', '$2y$10$2tW/DFbSCUa16G4VZIhKnuw/VrQl5WAymV7UjJv8h3KqoRUWSVAWi', 'admin');

INSERT INTO flights (flight_number, departure_city, arrival_city, departure_time, arrival_time, total_seats, available_seats, price) VALUES
('FL001', 'Surabaya', 'Arab', '2024-12-01 10:00:00', '2024-12-01 22:00:00', 180, 180, 850000.00),
('FL002', 'Padang', 'Amerika', '2024-12-02 14:30:00', '2024-12-02 20:30:00', 150, 150, 450000.00),
('FL003', 'ITS', 'Dubai', '2024-12-03 08:15:00', '2024-12-03 16:45:00', 200, 200, 6000000.00),
('FL004', 'Jakarta', 'Tokyo', '2024-12-04 09:00:00', '2024-12-04 18:00:00', 220, 220, 5500000.00),
('FL005', 'Bali', 'Sydney', '2024-12-05 06:30:00', '2024-12-05 15:00:00', 200, 200, 7500000.00),
('FL006', 'Medan', 'Kuala Lumpur', '2024-12-06 12:00:00', '2024-12-06 14:00:00', 180, 180, 1200000.00),
('FL007', 'Bandung', 'Singapore', '2024-12-07 07:00:00', '2024-12-07 09:30:00', 160, 160, 1000000.00),
('FL008', 'Surabaya', 'Seoul', '2024-12-08 10:00:00', '2024-12-08 18:00:00', 200, 200, 6000000.00),
('FL009', 'Makassar', 'Manila', '2024-12-09 11:00:00', '2024-12-09 14:00:00', 170, 170, 2000000.00),
('FL010', 'Yogyakarta', 'Bangkok', '2024-12-10 05:30:00', '2024-12-10 08:30:00', 190, 190, 1500000.00),
('FL011', 'Semarang', 'Hong Kong', '2024-12-11 13:00:00', '2024-12-11 17:00:00', 210, 210, 5000000.00),
('FL012', 'Balikpapan', 'Taipei', '2024-12-12 08:00:00', '2024-12-12 13:30:00', 180, 180, 4000000.00),
('FL013', 'Denpasar', 'Los Angeles', '2024-12-13 22:00:00', '2024-12-14 10:00:00', 250, 250, 15000000.00);
```
