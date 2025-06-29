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