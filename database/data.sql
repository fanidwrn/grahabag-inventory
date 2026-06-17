USE db_grahabag;

INSERT INTO users (username, full_name, password, role) VALUES
('admin_gudang', 'Admin Gudang', 'admin123', 'admin'),
('owner_grahabag', 'Owner', 'owner123', 'owner');

INSERT INTO category (category_name) VALUES
('Kain'),
('Benang'),
('Resleting'),
('Ring Besi'),
('Tali'),
('Aksesoris');

INSERT INTO material (category_id, material_name, stock, unit, minimum_stock, price) VALUES
(1, 'Kain Microfiber (Poliester) Halus Warna Maroon', 1250, 'Meter', 500, 25000.00),
(1, 'Kain Microfiber (Poliester) Halus Warna Hitam', 1250, 'Meter', 500, 25000.00),
(1, 'Kain Microfiber (Poliester) Halus Warna Biru Navy', 1250, 'Meter', 500, 25000.00),
(1, 'Kain Microfiber (Poliester) Halus Warna Coklat', 1250, 'Meter', 500, 25000.00),
(1, 'Kain Microfiber (Poliester) Halus Motif Kotak Coklat', 1250, 'Meter', 500, 25000.00),
(1, 'Kain Microfiber (Poliester) Halus Motif Kotak Maroon', 1250, 'Meter', 500, 25000.00),
(1, 'Kain Microfiber (Poliester) Halus Motif Kotak Hitam', 1250, 'Meter', 500, 25000.00),
(1, 'Kain Nylon (Parasut) Warna Hitam', 1250, 'Meter', 500, 20000.00),
(1, 'Kain Furing Warna Putih', 1250, 'Meter', 500, 15000.00),
(2, 'Benang Jahit Polyester Putih', 120, 'Roll', 100, 25000.00),
(2, 'Benang Jahit Polyester Hitam', 70, 'Roll', 100, 25000.00),
(2, 'Benang Jahit Polyester Maroon', 120, 'Roll', 100, 25000.00),
(2, 'Benang Jahit Polyester Coklat', 120, 'Roll', 100, 25000.00),
(2, 'Benang Jahit Polyester Biru Navy', 120, 'Roll', 100, 25000.00),
(3, 'Resleting Nilon (Coil Zipper) 20 cm Maroon', 100, 'Lusin', 50, 8000.00),
(3, 'Resleting Nilon (Coil Zipper) 20 cm Hitam', 100, 'Lusin', 50, 8000.00),
(3, 'Resleting Nilon (Coil Zipper) 20 cm Putih', 100, 'Lusin', 50, 8000.00),
(3, 'Resleting Nilon (Coil Zipper) 20 cm Coklat', 100, 'Lusin', 50, 8000.00),
(3, 'Resleting Nilon (Coil Zipper) 20 cm Biru Navy', 100, 'Lusin', 50, 8000.00),
(4, 'D-Ring Besi', 100, 'Pcs', 50, 2000.00),
(4, 'V-Ring Besi', 100, 'Pcs', 50, 2000.00),
(5, 'Tali Hitam', 100, 'Pcs', 50, 2000.00),
(6, 'Kaki Tas', 36, 'Lusin', 50, 1000.00);

INSERT INTO suppliers (supplier_name, email, no_telp, address) VALUES
('Supplier A', 'supplier_a@gmail.com', '08123456789', 'Jl. Bunga Anggrek No. 45, Jakarta Selatan'),
('Supplier B', 'supplier_b@gmail.com', '0877123456', 'Jl. Mawar Merah No. 12, Jakarta Selatan');