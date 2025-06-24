<?php $this->extend($role . '/layout'); ?>
<?php $this->section('content'); ?>
<section class="section">
    <div class="row justify-content-center">
        <div class="col-lg-12 col-md-10 col-sm-12">

            <!-- Flashdata SweetAlert: bisa dipindahkan ke layout utama atau partial -->
            <?php if (session()->getFlashdata('success') || session()->getFlashdata('error')) : ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        <?php if (session()->getFlashdata('success')): ?>
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: <?= json_encode(session()->getFlashdata('success')) ?>,
                            });
                        <?php endif; ?>
                        <?php if (session()->getFlashdata('error')): ?>
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: <?= json_encode(session()->getFlashdata('error')) ?>,
                            });
                        <?php endif; ?>
                    });
                </script>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Input Stock</h5>

                    <form action="<?= base_url($role . '/inputstock') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="no_model" class="form-label">No Model</label>
                                    <select class="form-select" id="no_model" name="no_model" onchange="selectModel()" required>
                                        <option value="" selected disabled>Pilih No Model</option>
                                        <!-- Option diisi dari server atau AJAX -->
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="inisial" class="form-label">Inisial</label>
                                    <select class="form-select" id="inisial" name="inisial" required>
                                        <option value="" selected disabled>Pilih Inisial</option>
                                        <!-- Inisial akan diisi via AJAX berdasarkan no_model -->
                                    </select>
                                    <input type="hidden" id="id_anak" name="id_anak" value="">
                                </div>
                                <div class="mb-3">
                                    <label for="qty_masuk" class="form-label">Qty Masuk (Dz)</label>
                                    <input type="number" class="form-control" id="qty_masuk" name="qty_masuk" min="0" step="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="area" class="form-label">Area</label>
                                    <input type="text" class="form-control" id="area" name="area" required>
                                </div>
                                <div class="mb-3">
                                    <label for="style_size" class="form-label">Style Size</label>
                                    <input type="text" class="form-control" id="style_size" name="style_size" required>
                                </div>
                                <div class="mb-3">
                                    <label for="box_masuk" class="form-label">Box Masuk</label>
                                    <input type="number" class="form-control" id="box_masuk" name="box_masuk" min="0" step="1" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <!-- Tombol submit biasa -->
                            <button type="submit" class="btn btn-primary">
                                Simpan
                            </button>
                            <!-- Tombol aksi clustering -->
                            <button type="button" class="btn btn-info" onclick="arahkanKeCluster()">
                                Arahkan ke Cluster
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Skrip JS: letakkan di bagian bawah halaman atau dalam file terpisah -->
<script>
    // Contoh: fungsi dipanggil saat no_model diubah
    function selectModel() {
        const noModel = document.getElementById('no_model').value;
        if (!noModel) return;
        // Lakukan AJAX untuk mendapatkan inisial dan id_anak
        fetch("<?= base_url($role . '/getInisialByModel') ?>/" + encodeURIComponent(noModel))
            .then(response => response.json())
            .then(data => {
                const inisialSelect = document.getElementById('inisial');
                inisialSelect.innerHTML = '<option value="" disabled selected>Pilih Inisial</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.inisial; // sesuaikan field
                    opt.textContent = item.inisial; // sesuaikan tampilan
                    opt.dataset.idAnak = item.id_anak; // misal ada id_anak
                    inisialSelect.appendChild(opt);
                });
            })
            .catch(err => console.error(err));
    }

    // Tangkap perubahan inisial untuk set hidden id_anak (jika perlu)
    document.getElementById('inisial').addEventListener('change', function() {
        const selectedOpt = this.options[this.selectedIndex];
        const idAnak = selectedOpt.dataset.idAnak || '';
        document.getElementById('id_anak').value = idAnak;
    });

    // Fungsi Arahkan ke Cluster: misal submit via AJAX untuk menghitung cluster
    function arahkanKeCluster() {
        const form = document.querySelector('form');
        const formData = new FormData(form);

        // Contoh AJAX POST ke endpoint clustering
        fetch("<?= base_url($role . '/calculateCluster') ?>", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_token() ?>'
                },
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                // Tampilkan hasil cluster, misalnya di modal atau alert
                if (result.success) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Cluster Ditetapkan',
                        text: 'Cluster: ' + result.clusterName,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: result.message || 'Tidak dapat menentukan cluster',
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat menghitung cluster',
                });
            });
    }
</script>

<?php $this->endSection(); ?>