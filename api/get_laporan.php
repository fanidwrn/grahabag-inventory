<?php
require_once '../includes/db_connect.php';

function getLaporanData($conn, $start, $end) {
    // Query Barang Masuk
    $stmt_in = $conn->prepare("SELECT si.*, m.material_name, m.unit, u.full_name as pendaftar, s.supplier_name 
                               FROM stock_in si 
                               JOIN material m ON si.material_id = m.material_id 
                               JOIN users u ON si.user_id = u.user_id 
                               LEFT JOIN suppliers s ON si.supplier_id = s.supplier_id 
                               WHERE si.date_stock_in BETWEEN ? AND ?
                               ORDER BY si.date_stock_in ASC");
    $stmt_in->bind_param("ss", $start, $end);
    $stmt_in->execute();
    $res_in = $stmt_in->get_result();
    
    // Query Barang Keluar
    $stmt_out = $conn->prepare("SELECT so.*, m.material_name, m.unit, u.full_name as pendaftar 
                                FROM stock_out so 
                                JOIN material m ON so.material_id = m.material_id 
                                JOIN users u ON so.user_id = u.user_id 
                                WHERE so.date_stock_out BETWEEN ? AND ?
                                ORDER BY so.date_stock_out ASC");
    $stmt_out->bind_param("ss", $start, $end);
    $stmt_out->execute();
    $res_out = $stmt_out->get_result();
    
    // Query Pengajuan Supplier
    $stmt_purchase = $conn->prepare("SELECT mp.*, m.material_name, m.unit, u.full_name as pendaftar, s.supplier_name 
                                     FROM material_purchase mp 
                                     JOIN material m ON mp.material_id = m.material_id 
                                     JOIN users u ON mp.user_id = u.user_id 
                                     JOIN suppliers s ON mp.supplier_id = s.supplier_id 
                                     WHERE mp.purchase_date BETWEEN ? AND ?
                                     ORDER BY mp.purchase_date ASC");
    $stmt_purchase->bind_param("ss", $start, $end);
    $stmt_purchase->execute();
    $res_purchase = $stmt_purchase->get_result();

    // Query Keseluruhan Bahan Baku (Stok sampai dengan tanggal akhir periode)
    $stmt_material = $conn->prepare("SELECT m.*, c.category_name,
                                     (m.stock 
                                      - COALESCE((SELECT SUM(total_in) FROM stock_in WHERE material_id = m.material_id AND date_stock_in > ?), 0)
                                      + COALESCE((SELECT SUM(total_out) FROM stock_out WHERE material_id = m.material_id AND date_stock_out > ?), 0)
                                     ) AS calculated_stock
                                     FROM material m 
                                     JOIN category_id c ON m.category_id = c.category_id
                                     ORDER BY c.category_name ASC");
    $stmt_material->bind_param("ss", $end, $end);
    $stmt_material->execute();
    $res_material = $stmt_material->get_result();

    return [
        'in' => $res_in,
        'out' => $res_out,
        'purchase' => $res_purchase,
        'material' => $res_material
    ];
}
?>
