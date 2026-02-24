CREATE DATABASE IF NOT EXISTS gaming_store;
USE gaming_store;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin user (password: admin123)
INSERT INTO users (username, password, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin');

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock_quantity INT NOT NULL DEFAULT 0,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sales table
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sale items table
CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default categories
INSERT INTO categories (name) VALUES
('Graphics Cards'),
('Processors'),
('RAM'),
('Storage'),
('Monitors'),
('Peripherals');

-- Seed 6 Gaming Mice (category: Peripherals = 6)
INSERT INTO products (category_id, name, description, price, stock_quantity, image_url) VALUES
(6, 'Logitech G Pro X Superlight 2', 'เมาส์เกมมิ่งไร้สาย 60g HERO 2 Sensor 32000 DPI', 4490.00, 15, 'assets/images/mouse_1.png'),
(6, 'Razer DeathAdder V3 Pro', 'เมาส์เกมมิ่ง Ergonomic ไร้สาย Focus Pro 30K Sensor', 4290.00, 12, 'assets/images/mouse_2.png'),
(6, 'Razer Viper V3 Pro', 'เมาส์เกมมิ่งไร้สาย 54g Focus Pro 35K Optical Sensor', 5490.00, 8, 'assets/images/mouse_3.png'),
(6, 'SteelSeries Aerox 5 Wireless', 'เมาส์เกมมิ่งไร้สาย 74g TrueMove Air Sensor', 3290.00, 20, 'assets/images/mouse_4.png'),
(6, 'Zowie EC2-CW', 'เมาส์เกมมิ่งไร้สาย Ergonomic 3370 Sensor สำหรับ eSports', 3590.00, 10, 'assets/images/mouse_5.png'),
(6, 'Pulsar X2V2 Mini', 'เมาส์เกมมิ่งไร้สาย Symmetrical 55g PAW3395 Sensor', 2890.00, 18, 'assets/images/mouse_6.png');

-- Seed 6 Gaming Keyboards (category: Peripherals = 6)
INSERT INTO products (category_id, name, description, price, stock_quantity, image_url) VALUES
(6, 'Razer Huntsman V3 Pro TKL', 'คีย์บอร์ดเกมมิ่ง Analog Optical Switch Rapid Trigger', 7990.00, 10, 'assets/images/kb_1.png'),
(6, 'Wooting 60HE+', 'คีย์บอร์ดเกมมิ่ง 60% Hall Effect Rapid Trigger', 6990.00, 7, 'assets/images/kb_2.png'),
(6, 'Logitech G Pro X TKL Rapid', 'คีย์บอร์ดเกมมิ่งไร้สาย GX2 Optical Switch', 5990.00, 14, 'assets/images/kb_3.png'),
(6, 'SteelSeries Apex Pro TKL (2023)', 'คีย์บอร์ดเกมมิ่ง OmniPoint 2.0 Adjustable Switch', 6490.00, 9, 'assets/images/kb_4.png'),
(6, 'Corsair K65 Plus Wireless', 'คีย์บอร์ดเกมมิ่งไร้สาย 75% MLX Red Switch RGB', 4990.00, 16, 'assets/images/kb_5.png'),
(6, 'Ducky One 3 SF', 'คีย์บอร์ดเกมมิ่ง 65% Hot-swap Cherry MX Switch PBT', 3990.00, 22, 'assets/images/kb_6.png');

-- Seed category: Monitors
INSERT INTO categories (name) VALUES ('Monitors');

-- Seed 6 Gaming Monitors (category: Monitors = 7)
INSERT INTO products (category_id, name, description, price, stock_quantity, image_url) VALUES
(7, 'ASUS ROG Swift PG27AQN', 'จอเกมมิ่ง 27 นิ้ว 360Hz IPS QHD Nvidia G-Sync', 29990.00, 5, 'assets/images/monitor_1.png'),
(7, 'Samsung Odyssey G7 32', 'จอเกมมิ่ง 32 นิ้ว Curved 1000R QLED 240Hz QHD', 16990.00, 8, 'assets/images/monitor_2.png'),
(7, 'LG UltraGear 27GP850-B', 'จอเกมมิ่ง 27 นิ้ว 165Hz Nano IPS QHD 1ms', 12990.00, 12, 'assets/images/monitor_3.png'),
(7, 'BenQ Zowie XL2546K', 'จอเกมมิ่ง eSports 24.5 นิ้ว 240Hz DyAc+ FHD', 15990.00, 6, 'assets/images/monitor_4.png'),
(7, 'Dell Alienware AW3423DWF', 'จอเกมมิ่ง 34 นิ้ว Curved QD-OLED Ultrawide 165Hz', 32990.00, 4, 'assets/images/monitor_5.png'),
(7, 'MSI MAG 274QRF-QD', 'จอเกมมิ่ง 27 นิ้ว 165Hz Rapid IPS QHD HDR', 11990.00, 15, 'assets/images/monitor_6.png');

-- Seed 6 Gaming GPUs (category: Graphics Cards = 1)
INSERT INTO products (category_id, name, description, price, stock_quantity, image_url) VALUES
(1, 'NVIDIA GeForce RTX 4090', 'การ์ดจอเกมมิ่ง 24GB GDDR6X Ada Lovelace DLSS 3.0 Ray Tracing', 62990.00, 3, 'assets/images/gpu_1.png'),
(1, 'NVIDIA GeForce RTX 4070 Ti Super', 'การ์ดจอเกมมิ่ง 16GB GDDR6X Ada Lovelace DLSS 3.0', 29990.00, 7, 'assets/images/gpu_2.png'),
(1, 'AMD Radeon RX 7900 XTX', 'การ์ดจอเกมมิ่ง 24GB GDDR6 RDNA 3 Ray Tracing', 34990.00, 5, 'assets/images/gpu_3.png'),
(1, 'NVIDIA GeForce RTX 4060 Ti', 'การ์ดจอเกมมิ่ง 8GB GDDR6 Ada Lovelace DLSS 3.0', 15990.00, 12, 'assets/images/gpu_4.png'),
(1, 'NVIDIA GeForce RTX 4070 Super', 'การ์ดจอเกมมิ่ง 12GB GDDR6X Ada Lovelace DLSS 3.0', 22990.00, 8, 'assets/images/gpu_5.png'),
(1, 'AMD Radeon RX 7800 XT', 'การ์ดจอเกมมิ่ง 16GB GDDR6 RDNA 3 Ray Tracing FSR 3', 18990.00, 10, 'assets/images/gpu_6.png');
