public function importstock()
{
$file = $this->request->getFile('file');

if ($file->isValid() && !$file->hasMoved()) {
$ext = $file->getClientExtension();

if (in_array($ext, ['xls', 'xlsx', 'csv'])) {
if ($ext == 'csv') {
$reader = new Csv();
} elseif ($ext == 'xls') {
$reader = new Xls();
} else {
$reader = new Xlsx();
}

$spreadsheet = $reader->load($file->getTempName());
$sheet = $spreadsheet->getActiveSheet()->toArray();

$dataTanggal = [];

// Asumsi data mulai dari baris kedua (baris pertama header)
foreach (array_slice($sheet, 1) as $row) {
// Contoh, misal tanggal ada di kolom ke-3 (index 2)
$tglExp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('Y-m-d');

$dataTanggal[] = strtotime($tglExp); // Ubah ke timestamp biar numerik
}

// --- Proses K-Means ---
$clusterJumlah = 3; // Misal, mau dibagi ke 3 cluster gudang
$hasilCluster = $this->kmeans($dataTanggal, $clusterJumlah);

// Gabungkan hasil ke data akhir
$dataAkhir = [];
foreach ($sheet as $key => $row) {
if ($key == 0) continue; // Skip header
$tglExp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('Y-m-d');
$dataAkhir[] = [
'data' => $row,
'tanggal' => $tglExp,
'cluster' => $hasilCluster[$key - 1] // -1 karena array hasilCluster ga ada header
];
}

// Contoh, tampilkan hasil
echo '
<pre>';
            print_r($dataAkhir);
            echo '</pre>';

// Bisa lanjut simpan ke DB atau tampilin ke view
} else {
return redirect()->back()->with('error', 'Format file tidak didukung.');
}
} else {
return redirect()->back()->with('error', 'File tidak valid.');
}
}




private function kmeans($data, $k = 3, $maxIter = 100)
{
$centroids = array_rand(array_flip($data), $k); // Ambil k random centroid awal
$clusters = [];

for ($iter = 0; $iter < $maxIter; $iter++) {
    $clusters=array_fill(0, $k, []);

    // Assign data ke cluster terdekat
    foreach ($data as $point) {
    $distances=array_map(fn($c)=> abs($point - $c), $centroids);
    $minIndex = array_keys($distances, min($distances))[0];
    $clusters[$minIndex][] = $point;
    }

    $newCentroids = [];
    foreach ($clusters as $cluster) {
    $newCentroids[] = !empty($cluster) ? array_sum($cluster) / count($cluster) : 0;
    }

    if ($centroids === $newCentroids) {
    break; // Konvergen
    }
    $centroids = $newCentroids;
    }

    // Mapping data ke nomor cluster
    $result = [];
    foreach ($data as $point) {
    $distances = array_map(fn($c) => abs($point - $c), $centroids);
    $minIndex = array_keys($distances, min($distances))[0];
    $result[] = $minIndex + 1; // Cluster dimulai dari 1
    }

    return $result;
    }




    public function importStock()
    {
    $admin = session()->get('username');
    $file = $this->request->getFile('file');
    $today = new \DateTime('now');

    // Array untuk menampung titik‐titik data yang nanti akan di–cluster
    $points = [];

    if ($file && $file->isValid() && !$file->hasMoved()) {
    $filePath = WRITEPATH . 'uploads/' . $file->getName();
    $file->move(WRITEPATH . 'uploads');

    // Baca spreadsheet
    $sheet = IOFactory::load($filePath)
    ->getActiveSheet()
    ->toArray(null, true, true, true);

    // Loop data Excel
    foreach (array_slice($sheet, 17) as $idx => $row) {
    $lineno = $idx + 2; // Excel baris ke‑nya
    // 1) DEBUG: print baris
    echo "<strong>Baris {$lineno}:</strong> ";
    print_r($row);
    echo "<br />";

    // 2) Validasi minimal
    if (empty($row['E']) || empty($row['M']) || empty($row['V']) || empty($row['AA'])) {
    echo " → GAGAL validasi kolom E/M/V/AA<br /><br />";
    continue;
    }

    echo " → Lolos validasi kolom<br />";

    // 3) Query induk
    $dataInduk = $this->indukModel
    ->select('id_induk, delivery, kode_buyer')
    ->where('no_model', $row['V'])
    ->first();
    if (! $dataInduk) {
    echo " → GAGAL: no_model '{$row['V']}' tidak ditemukan di tabel_induk<br /><br />";
    continue;
    }
    echo " → Ditemukan INDUK (id_induk={$dataInduk['id_induk']})<br />";

    // 4) Query anak
    $dataAnak = $this->anakModel
    ->select('id_anak')
    ->where('id_induk', $dataInduk['id_induk'])
    ->where('area', $row['AA'])
    ->where('style', $row['E'])
    ->first();
    if (! $dataAnak) {
    echo " → GAGAL: anak (area='{$row['AA']}', style='{$row['E']}') tidak ditemukan<br /><br />";
    continue;
    }
    echo " → Ditemukan ANAK (id_anak={$dataAnak['id_anak']})<br />";

    // 5) Query stock
    $stock = $this->stockModel
    ->select('id_stock')
    ->where('id_anak', $dataAnak['id_anak'])
    ->first();
    if (! $stock) {
    // Jika stock tidak ada, skip baris ini
    echo " → SKIP baris {$idx}: stock untuk id_anak={$dataAnak['id_anak']} tidak ditemukan<br />";
    continue;
    }

    // Kalau stock ditemukan, update qty_stock
    $tambahQty = floatval($row['M']) / 24;
    $newQty = floatval($stock['qty_stock']) + $tambahQty;

    $this->stockModel->update(
    $stock['id_stock'],
    ['qty_stock' => $newQty]
    );

    echo " → UPDATE STOCK id_stock={$stock['id_stock']}: qty_stock {$stock['qty_stock']} + {$tambahQty} = {$newQty}<br />";

    // Hitung days_to_export
    $delivDate = new \DateTime($dataInduk['delivery']);
    $daysToExp = $today->diff($delivDate)->days;

    // Masukkan ke points: nanti dipakai clustering
    $points[] = [
    'id_stock' => $stock['id_stock'],
    'buyer' => $dataInduk['buyer'], // teks
    'days' => $daysToExp, // numerik
    ];
    }
    exit;
    var_dump($points);

    // Hapus file upload
    unlink($filePath);

    if (empty($points)) {
    return redirect()->back()->with('error', 'Tidak ada data valid untuk diimport.');
    }

    //
    // ——————— PREPROCESSING ———————
    //

    // 1) Label-encode buyer
    $buyers = array_unique(array_column($points, 'buyer'));
    $encode = array_flip($buyers); // e.g. ['AILEEN'=>0, 'BUDI'=>1,...]

    foreach ($points as &$p) {
    $p['bcode'] = $encode[$p['buyer']];
    }
    unset($p);

    // 2) Normalisasi Min‑Max
    $bcArr = array_column($points, 'bcode');
    $dyArr = array_column($points, 'days');
    $minB = min($bcArr);
    $maxB = max($bcArr);
    $minD = min($dyArr);
    $maxD = max($dyArr);

    foreach ($points as &$p) {
    $p['nb'] = ($p['bcode'] - $minB) / max(1, $maxB - $minB);
    $p['nd'] = ($p['days'] - $minD) / max(1, $maxD - $minD);
    }
    unset($p);

    //
    // ——————— K‑MEANS (k=3) ———————
    //
    $k = 3;
    $eps = 0.001;
    $maxIter = 100;

    // Inisialisasi centroid acak
    $keys = array_rand($points, $k);
    $centroids = [];
    foreach ($keys as $i) {
    $centroids[] = ['x' => $points[$i]['nb'], 'y' => $points[$i]['nd']];
    }

    // Iterasi assign dan recompute
    for ($iter = 0; $iter < $maxIter; $iter++) {
        // a) Assign
        foreach ($points as &$pt) {
        $dists=array_map(
        fn($c)=>
        sqrt(($pt['nb'] - $c['x']) ** 2 + ($pt['nd'] - $c['y']) ** 2),
        $centroids
        );
        $pt['cluster'] = array_search(min($dists), $dists);
        }
        unset($pt);

        // b) Recompute
        $sums = array_fill(0, $k, ['sx' => 0, 'sy' => 0, 'cnt' => 0]);
        foreach ($points as $pt) {
        $i = $pt['cluster'];
        $sums[$i]['sx'] += $pt['nb'];
        $sums[$i]['sy'] += $pt['nd'];
        $sums[$i]['cnt']++;
        }
        $changed = false;
        foreach ($sums as $i => $val) {
        if ($val['cnt'] === 0) continue;
        $nx = $val['sx'] / $val['cnt'];
        $ny = $val['sy'] / $val['cnt'];
        if (abs($nx - $centroids[$i]['x']) > $eps || abs($ny - $centroids[$i]['y']) > $eps) {
        $changed = true;
        }
        $centroids[$i] = ['x' => $nx, 'y' => $ny];
        }
        if (! $changed) break;
        }

        //
        // ——————— SIMPAN HASIL CLUSTER ———————
        //
        foreach ($points as $pt) {
        $this->stockModel
        ->update($pt['id_stock'], ['cluster_id' => $pt['cluster'] + 1]);
        }

        return redirect()->back()->with('success', 'Import & clustering selesai!');
        }
        }