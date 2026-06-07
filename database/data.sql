USE db_grahabag;

INSERT INTO users (username, full_name, password, role) VALUES
('admin_gudang', 'Admin Gudang', 'admin123', 'admin'),
('owner_grahabag', 'Owner', 'owner123', 'owner');

INSERT INTO category_id (category_name) VALUES
('Kain'),
('Benang'),
('Aksesoris');

INSERT INTO material (category_id, material_name, stock, unit, minimum_stock, price) VALUES
(1, 'Kain Katun Premium Hitam', 1250, 'Meter', 500, 45000.00),
(2, 'Benang Jahit Nilon Putih', 45, 'Roll', 100, 12500.00),
(3, 'Resleting YKK 15cm Besi', 320, 'Pcs', 200, 5000.00),
(1, 'Kain Denim 14oz Navy', 120, 'Yard', 300, 65000.00),
(3, 'Kancing Kemeja Plastik Putih', 5000, 'Gross', 1000, 25000.00);

INSERT INTO suppliers (supplier_name, email, no_telp, address) VALUES
('PT Tekstil Jaya', 'jaya@tekstil.com', '08123456789', 'Bandung No. 45'),
('CV Aksesoris Garment', 'sales@cvaksesoris.com', '0877123456', 'Jakarta Barat No. 12');