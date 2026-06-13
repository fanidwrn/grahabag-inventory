<?php
$is_submitted = isset($_GET['filter']);
$jenis_laporan = $_GET['jenis_laporan'] ?? 'Mingguan';
$val_bulan = $_GET['bulan'] ?? ($is_submitted ? '' : date('n'));
$val_tahun = $_GET['tahun'] ?? date('Y');

$report_rows = [];
$months_to_process = [];

$nama_bulan_id = [
    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', 
    '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', 
    '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
];
$nama_bulan_singkat = [
    '01'=>'Jan', '02'=>'Feb', '03'=>'Mar', '04'=>'Apr', 
    '05'=>'Mei', '06'=>'Jun', '07'=>'Jul', '08'=>'Agt', 
    '09'=>'Sep', '10'=>'Okt', '11'=>'Nov', '12'=>'Des'
];

if (empty($val_bulan)) {
    // Jika tidak ada bulan yang dipilih, proses dari Januari hingga Desember
    for ($i = 1; $i <= 12; $i++) {
        $months_to_process[] = str_pad($i, 2, '0', STR_PAD_LEFT);
    }
    $header_title_suffix = "Tahun $val_tahun";
} else {
    $m_pad = str_pad($val_bulan, 2, '0', STR_PAD_LEFT);
    $months_to_process[] = $m_pad;
    $header_title_suffix = $nama_bulan_id[$m_pad] . " $val_tahun";
}

foreach ($months_to_process as $m) {
    $year = $val_tahun;
    $month = $m;
    $date_str = "$year-$month-01";
    $last_day = date('t', strtotime($date_str));
    $month_name_full = $nama_bulan_id[$m] . " $val_tahun";
    $month_name = $nama_bulan_singkat[$m] . " $val_tahun";

    if ($jenis_laporan == 'Bulanan') {
        $report_rows[] = [
            'judul' => 'Bulanan',
            'bulan_label' => $month_name_full,
            'rentang' => "01 $month_name - $last_day $month_name",
            'start' => "$year-$month-01",
            'end' => "$year-$month-$last_day"
        ];
    } else if ($jenis_laporan == 'Mingguan') {
        $report_rows[] = [
            'judul' => 'Minggu ke-1',
            'bulan_label' => $month_name_full,
            'rentang' => "01 $month_name - 07 $month_name",
            'start' => "$year-$month-01",
            'end' => "$year-$month-07"
        ];
        $report_rows[] = [
            'judul' => 'Minggu ke-2',
            'bulan_label' => $month_name_full,
            'rentang' => "08 $month_name - 14 $month_name",
            'start' => "$year-$month-08",
            'end' => "$year-$month-14"
        ];
        $report_rows[] = [
            'judul' => 'Minggu ke-3',
            'bulan_label' => $month_name_full,
            'rentang' => "15 $month_name - 21 $month_name",
            'start' => "$year-$month-15",
            'end' => "$year-$month-21"
        ];
        $report_rows[] = [
            'judul' => 'Minggu ke-4',
            'bulan_label' => $month_name_full,
            'rentang' => "22 $month_name - 28 $month_name",
            'start' => "$year-$month-22",
            'end' => "$year-$month-28"
        ];
        if ($last_day > 28) {
            $report_rows[] = [
                'judul' => 'Minggu ke-5',
                'bulan_label' => $month_name_full,
                'rentang' => "29 $month_name - $last_day $month_name",
                'start' => "$year-$month-29",
                'end' => "$year-$month-$last_day"
            ];
        }
    }
}
?>
