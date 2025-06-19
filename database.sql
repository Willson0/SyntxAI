use ai_bot;

-- Таблица users
CREATE TABLE users (
    user_id VARCHAR(255) NOT NULL PRIMARY KEY,
    date_sub VARCHAR(255),
    payment_data VARCHAR(255),
    model VARCHAR(255),
    referer_id VARCHAR(255),
    referals INT,
    ai_tokens FLOAT,
    order_id INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица models
CREATE TABLE models (
    model VARCHAR(255) NOT NULL PRIMARY KEY,
    name VARCHAR(255),
    description VARCHAR(255),
    if_active INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица subs
CREATE TABLE subs (
    type VARCHAR(255),
    name VARCHAR(255),
    price INT,
    srok INT,
    ai_tokens FLOAT,
    usage_by_type VARCHAR(255),
    `usage` VARCHAR(255),
    if_active INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица promocodes
CREATE TABLE promocodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    sale INT,
    if_sale INT,
    usage_attempts INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE gift_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    srok INT,
    if_used INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    payment_sum FLOAT NOT NULL,
    payment_date VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;