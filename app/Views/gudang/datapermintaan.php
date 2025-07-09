<?php $this->extend($role . '/layout'); ?>
<?php $this->section('content'); ?>
<section class="section">
    <div class="row">
        <?php if (session()->getFlashdata('success')) : ?>
            <script>
                $(document).ready(function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: '<?= session()->getFlashdata('success') ?>',
                    });
                });
            </script>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <script>
                $(document).ready(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: '<?= session()->getFlashdata('error') ?>',
                    });
                });
            </script>
        <?php endif; ?>
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Data Permintaan</h5>
                    <form action="<?= base_url($role . '/datapermintaan') ?>" method="post">
                        <div class="row mb-2">
                            <label for="cari" class="col-sm-2 col-form-label">No Model</label>
                            <div class="col-sm-2">
                                <input class="form-control" type="text" name="cari1">
                            </div>
                            <label for="cari2" class="col-sm-1 col-form-label">Tgl Jalan</label>
                            <div class="col-sm-2">
                                <input class="form-control" type="date" name="cari2">
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-info">Search</button>
                            </div>
                        </div>
                    </form>
                    <p></p>
                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Tgl Minta</th>
                                <th scope="col">Tgl Jalan</th>
                                <th scope="col">Packing</th>
                                <th scope="col">Area</th>
                                <th scope="col">Buyer</th>
                                <th scope="col">No Model</th>
                                <th scope="col">In</th>
                                <th scope="col">Style</th>
                                <th scope="col">Qty Minta</th>
                                <th scope="col">Qty Kirim</th>
                                <th scope="col">Tagihan</th>
                                <th scope="col">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($permintaan as $data) :
                                $maxKirim = $data['qty_minta'] + ($data['qty_minta'] * 0.2);
                            ?>
                                <tr>
                                    <th scope="row"><?= $no ?></th>
                                    <td><?= $data['tgl_minta'] ?></td>
                                    <td><?= $data['tgl_jalan'] ?></td>
                                    <td><?= $data['area_packing'] ?></td>
                                    <td><?= $data['area'] ?></td>
                                    <td><?= $data['kode_buyer'] ?></td>
                                    <td><?= $data['no_model'] ?></td>
                                    <td><?= $data['inisial'] ?></td>
                                    <td><?= $data['style'] ?></td>
                                    <td><?= $data['qty_minta'] ?></td>
                                    <td><?= $data['qty_keluar'] ?></td>
                                    <td><?= $data['tagihan'] ?></td>
                                    <td><i class="ri-edit-line" data-bs-toggle="modal" data-bs-target="#pengeluaranModal" data-packing="<?= $data['area_packing'] ?>" data-no_model="<?= $data['no_model'] ?>" data-area="<?= $data['area'] ?>" data-id_anak="<?= $data['id_anak'] ?>" data-inisial="<?= $data['inisial'] ?>" data-tgl_minta="<?= $data['tgl_minta'] ?>" data-tgl_jalan="<?= $data['tgl_jalan'] ?>" data-qty_minta="<?= $data['qty_minta'] ?>" data-id_minta="<?= $data['id_minta'] ?>" data-max_kirim="<?= $maxKirim ?>"></td>
                                </tr>
                            <?php
                                $no++;
                            endforeach; ?>
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->
                    <!-- Large Modal -->
                    <div class="modal fade" id="pengeluaranModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form action="<?= base_url('/' . $role . '/inputpengeluaran') ?>" method="post">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Pengeluaran</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-3">
                                                <label for="packing" class="form-label">Area Packing</label>
                                                <input type="text" class="form-control" name="packing" readonly>
                                            </div>
                                            <div class="col-3">
                                                <label for="no_model" class="form-label">No Model</label>
                                                <input type="text" class="form-control" name="no_model" readonly>
                                            </div>
                                            <div class="col-3">
                                                <label for="area" class="form-label">Area</label>
                                                <input type="text" class="form-control" name="area" readonly>
                                            </div>
                                            <div class="col-3">
                                                <label for="inisial" class="form-label">Inisial</label>
                                                <input type="hidden" class="form-control" name="id_anak" readonly>
                                                <input type="text" class="form-control" name="inisial" readonly>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-3">
                                                <label for="tgl_minta" class="form-label">Tgl Minta</label>
                                                <input type="hidden" class="form-control" name="id_minta">
                                                <input type="date" class="form-control" name="tgl_minta" readonly>
                                            </div>
                                            <div class="col-3">
                                                <label for="tgl_jalan" class="form-label">Tgl Jalan</label>
                                                <input type="date" class="form-control" name="tgl_jalan" readonly>
                                            </div>
                                            <div class="col-3">
                                                <label for="qty_minta" class="form-label">Qty Minta</label>
                                                <input type="number" class="form-control" name="qty_minta" readonly>
                                            </div>
                                            <div class="col-3">
                                                <label for="tgl_kirim" class="form-label">Tgl Kirim</label>
                                                <input type="date" class="form-control" name="tgl_kirim" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-3">
                                                <label for="tgl_kirim" class="form-label">Max Kirim</label>
                                                <input type="number" class="form-control" name="max_kirim" id="max_kirim" readonly>
                                            </div>
                                        </div>
                                        <table class="table datatable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">No</th>
                                                    <th scope="col">Jalur</th>
                                                    <th scope="col">Qty Stock</th>
                                                    <th scope="col">Box Stock</th>
                                                    <th scope="col">Qty Kirim</th>
                                                    <th scope="col">Box Kirim</th>
                                                    <th scope="col">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div><!-- End Large Modal-->
                </div>
            </div>

        </div>
    </div>
</section>
<script>
    const inputStockModal = document.getElementById('pengeluaranModal');
    inputStockModal.addEventListener('show.bs.modal', function(event) {
        // Tombol yang memicu modal
        const button = event.relatedTarget;

        // Ambil data dari atribut data-*
        const packing = button.getAttribute('data-packing');
        const noModel = button.getAttribute('data-no_model');
        const area = button.getAttribute('data-area');
        const idAnak = button.getAttribute('data-id_anak');
        const inisial = button.getAttribute('data-inisial');
        const tglMinta = button.getAttribute('data-tgl_minta');
        const tglJalan = button.getAttribute('data-tgl_jalan');
        const qtyMinta = button.getAttribute('data-qty_minta');
        const idMinta = button.getAttribute('data-id_minta');
        const maxKirim = button.getAttribute('data-max_kirim');

        // Log data untuk debugging
        console.log('Data Modal:', {
            packing,
            noModel,
            area,
            idAnak,
            inisial,
            tglMinta,
            tglJalan,
            qtyMinta,
            idMinta,
            maxKirim
        });

        // Isi input di dalam modal dengan nilai dari atribut
        const inputPacking = pengeluaranModal.querySelector('input[name="packing"]');
        const inputNoModel = pengeluaranModal.querySelector('input[name="no_model"]');
        const inputArea = pengeluaranModal.querySelector('input[name="area"]');
        const inputIdAnak = pengeluaranModal.querySelector('input[name="id_anak"]');
        const inputInisial = pengeluaranModal.querySelector('input[name="inisial"]');
        const inputTglMinta = pengeluaranModal.querySelector('input[name="tgl_minta"]');
        const inputTglJalan = pengeluaranModal.querySelector('input[name="tgl_jalan"]');
        const inputQtyMinta = pengeluaranModal.querySelector('input[name="qty_minta"]');
        const inputIdMinta = pengeluaranModal.querySelector('input[name="id_minta"]');
        const inputMaxKirim = pengeluaranModal.querySelector('input[name="max_kirim"]');

        inputPacking.value = packing;
        inputNoModel.value = noModel;
        inputArea.value = area;
        inputIdAnak.value = idAnak;
        inputInisial.value = inisial;
        inputTglMinta.value = tglMinta;
        inputTglJalan.value = tglJalan;
        inputQtyMinta.value = qtyMinta;
        inputIdMinta.value = idMinta;
        inputMaxKirim.value = maxKirim;

        // Kirimkan idAnak ke controller dengan AJAX
        $.ajax({
            url: '/gudang/getStockByIdAnak',
            type: 'POST',
            data: {
                id_anak: idAnak
            },
            success: function(response) {
                let tbody = '';

                response.forEach(function(stock, index) {
                    tbody += `
                    <tr>
                        <th scope="row">${index + 1}</th>
                        <td><input type="text" name="jalur" class="form-control" value="${stock.jalur}" required></td>
                        <td>${stock.qty_stock}</td>
                        <td>${stock.box_stock}</td>
                        <td><input type="number" name="qty_keluar" class="form-control" oninput="checkQty(this)" data-qty-stock="${stock.qty_stock}" data-max-kirim="${parseFloat(maxKirim) || 0}" required></td>
                        <td><input type="text" name="box_keluar" class="form-control" oninput="checkBoxQty(this)" data-qty-stock="${stock.box_stock}" required></td>
                        <td><input type="text" name="keterangan" class="form-control"></td>
                    </tr>
                    `;
                });

                // Masukkan data stock ke dalam table
                document.querySelector('#pengeluaranModal tbody').innerHTML = tbody;
            }
        });
    });

    function checkQty(input) {
        const qtyKirim = parseFloat(input.value) || 0;
        const qtyStock = parseFloat(input.getAttribute('data-qty-stock'));
        const maxKirim = parseFloat(input.getAttribute('data-max-kirim'));


        if (qtyKirim > qtyStock) {
            alert("Qty kirim tidak boleh melebihi qty stock!");
            input.value = ''; // Kosongkan input jika tidak valid
        } else if (qtyKirim > maxKirim) {
            alert("Qty kirim tidak boleh melebihi max kirim!");
            input.value = ''; // Kosongkan input jika tidak valid
        }
    }

    function checkBoxQty(input) {
        const boxKirim = parseFloat(input.value) || 0;
        const boxStock = parseFloat(input.getAttribute('data-qty-stock'));

        if (boxKirim > boxStock) {
            alert("Box kirim tidak boleh melebihi box stock!");
            input.value = ''; // Kosongkan input jika tidak valid
        }
    }
</script>
<?php $this->endSection(); ?>