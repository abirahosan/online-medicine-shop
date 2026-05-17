CREATE DATABASE IF NOT EXISTS medicine_shop;
USE medicine_shop;

CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)  NOT NULL,
    email           VARCHAR(150)  NOT NULL UNIQUE,
    password_hash   VARCHAR(255)  NOT NULL,
    role            ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    profile_picture VARCHAR(255)  DEFAULT NULL,
    address         TEXT          DEFAULT NULL,
    phone           VARCHAR(20)   DEFAULT NULL,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)  NOT NULL,
    category_type   ENUM('liquid','solid') NOT NULL,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS medicines (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150)  NOT NULL,
    category_id     INT           NOT NULL,
    vendor_name     VARCHAR(100)  NOT NULL,
    price           DECIMAL(10,2) NOT NULL CHECK (price > 0),
    availability    INT           NOT NULL DEFAULT 0,
    description     TEXT          DEFAULT NULL,
    image_path      VARCHAR(255)  DEFAULT NULL,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS cart (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT           NOT NULL,
    medicine_id     INT           NOT NULL,
    quantity        INT           NOT NULL DEFAULT 1,
    added_at        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT            NOT NULL,
    total_amount     DECIMAL(10,2)  NOT NULL,
    shipping_address TEXT           NOT NULL,
    status           ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
    payment_method   VARCHAR(50)    DEFAULT NULL,
    order_date       TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT           NOT NULL,
    medicine_id     INT           NOT NULL,
    quantity        INT           NOT NULL,
    unit_price      DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)    REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id)
);

CREATE TABLE IF NOT EXISTS payments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT           NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    payment_method  VARCHAR(50)   NOT NULL,
    transaction_id  VARCHAR(100)  DEFAULT NULL,
    payment_date    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);