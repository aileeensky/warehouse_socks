<?php $this->extend($role . '/layout'); ?>
<?php $this->section('content'); ?>
<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?= $title ?></h5>
                    <form action="<?= base_url($role . '/editpemasukan') ?>" method="post">
                        <div class="row mb-2">
                            <label for="cari" class="col-sm-2 col-form-label">No Model</label>
                            <div class="col-sm-2">
                                <input class="form-control" type="text" name="cari1">
                            </div>
                            <label for="cari2" class="col-sm-1 col-form-label">Tgl Masuk</label>
                            <div class="col-sm-2">
                                <input class="form-control" type="date" name="cari2">
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-info">Search</button>
                            </div>
                        </div>
                        <button class="nav-link collapsed" type="submit" formaction="<?= base_url($role . '/excelreportpemasukan') ?>">
                            <i class="ri-file-excel-line" style="font-size: 30px;"></i>
                        </button>
                    </form>
                    <p></p>
                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Tgl Masuk</th>
                                <th scope="col">Area</th>
                                <th scope="col">Buyer</th>
                                <th scope="col">No Model</th>
                                <th scope="col">In</th>
                                <th scope="col">Style</th>
                                <th scope="col">Qty Masuk</th>
                                <th scope="col">Box Masuk</th>
                                <th scope="col">Keterangan</th>
                                <th scope="col">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($dataMasuk as $dt) : ?>
                                <tr>
                                    <th scope="row"><?= $no++ ?></th>
                                    <td><?= $dt['created_at'] ?></td>
                                    <td><?= $dt['area'] ?></td>
                                    <td><?= $dt['kode_buyer'] ?></td>
                                    <td><?= $dt['no_model'] ?></td>
                                    <td><?= $dt['inisial'] ?></td>
                                    <td><?= $dt['style'] ?></td>
                                    <td><?= $dt['qty_masuk'] ?></td>
                                    <td><?= $dt['box_masuk'] ?></td>
                                    <td><?= $dt['ket_masuk'] ?></td>
                                    <td>
                                        <i class="ri-edit-line"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editPemasukanModal"
                                            data-id_masuk="<?= $dt['id_masuk'] ?>"
                                            data-tgl_masuk="<?= $dt['created_at'] ?>"
                                            data-area="<?= $dt['area'] ?>"
                                            data-kode_buyer="<?= $dt['kode_buyer'] ?>"
                                            data-no_model="<?= $dt['no_model'] ?>"
                                            data-inisial="<?= $dt['inisial'] ?>"
                                            data-style="<?= $dt['style'] ?>"
                                            data-qty_masuk="<?= $dt['qty_masuk'] ?>"
                                            data-box_masuk="<?= $dt['box_masuk'] ?>"
                                            data-ket_masuk="<?= $dt['ket_masuk'] ?>">
                                        </i>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->
                    <!-- Basic Modal -->
                    <div class="modal fade" id="editPemasukanModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Pemasukan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="<?= base_url('/' . $role . '/editpemasukan') ?>" method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="admin" value="<?= $admin ?>">
                                        <input type="hidden" name="id_masuk">
                                        <div class="col-12">
                                            <label for="tgl_masuk" class="form-label">Tgl Masuk</label>
                                            <input type="text" class="form-control" name="tgl_masuk" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="no_model" class="form-label">No Model</label>
                                            <select class="form-select" name="no_model" aria-label="Default select example" onchange="selectModel()">
                                                <option selected></option>
                                                <?php foreach ($pdk as $data) : ?>
                                                    <option value="<?= $data['id_induk'] ?>"><?= $data['no_model'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="area" class="form-label">Area</label>
                                            <select class="form-select" name="area" aria-label="Default select example">
                                                <option selected></option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="inisial" class="form-label">Inisial</label>
                                            <select class="form-select" name="inisial" aria-label="Default select example">
                                                <option selected></option>
                                            </select>
                                            <input type="hidden" name="id_anak" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="qty_masuk" class="form-label">Qty Masuk</label>
                                            <input type="number" class="form-control" name="qty_masuk">
                                        </div>
                                        <div class="col-12">
                                            <label for="box_masuk" class="form-label">Box</label>
                                            <input type="number" class="form-control" name="box_masuk">
                                        </div>
                                        <div class="col-12">
                                            <label for="gd_setting" class="form-label">Gd Setting</label>
                                            <select class="form-select" name="gd_setting" aria-label="Default select example">
                                                <option selected></option>
                                                <option>GD SETTING</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="keterangan" class="form-label">Keterangan</label>
                                            <textarea class="form-control" name="keterangan"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div><!-- End Basic Modal-->
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('editPemasukanModal');

        modal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            var noModel = btn.getAttribute('data-no_model');
            var areaValue = btn.getAttribute('data-area');
            var inisial = btn.getAttribute('data-inisial');
            var idMasuk = btn.getAttribute('data-id_masuk');
            var qty = btn.getAttribute('data-qty_masuk');
            var box = btn.getAttribute('data-box_masuk');
            var ket = btn.getAttribute('data-ket_masuk');
            var tgl = btn.getAttribute('data-tgl_masuk');

            // Isi text / hidden fields biasa
            modal.querySelector('input[name="id_masuk"]').value = idMasuk;
            modal.querySelector('input[name="tgl_masuk"]').value = tgl;
            modal.querySelector('input[name="qty_masuk"]').value = qty;
            modal.querySelector('input[name="box_masuk"]').value = box;
            modal.querySelector('textarea[name="keterangan"]').value = ket;

            // 1) No Model select
            var selModel = modal.querySelector('select[name="no_model"]');
            selModel.innerHTML = '';
            // a) tambahkan option pilih lama
            var optOld = document.createElement('option');
            optOld.value = noModel;
            optOld.text = noModel;
            optOld.selected = true;
            selModel.appendChild(optOld);
            // b) lalu AJAX untuk daftar baru
            fetch("<?= base_url($role . '/stockmodal/') ?>" + noModel)
                .then(res => res.json())
                .then(data => {
                    // tambahkan opsi-opsi
                    data.area.forEach(a => {
                        if (a !== areaValue) {
                            var o = document.createElement('option');
                            o.value = a;
                            o.text = a;
                            selModel.after; // we'll fill area later
                        }
                    });
                    // NOTE: no_model biasanya fixed, skip refill selModel
                });

            // 2) Area select
            var selArea = modal.querySelector('select[name="area"]');
            selArea.innerHTML = '';
            var oldAreaOpt = document.createElement('option');
            oldAreaOpt.value = areaValue;
            oldAreaOpt.text = areaValue;
            oldAreaOpt.selected = true;
            selArea.appendChild(oldAreaOpt);
            // kemudan isi ulang via AJAX
            fetch("<?= base_url($role . '/stockmodal/') ?>" + noModel)
                .then(res => res.json())
                .then(data => {
                    data.area.forEach(a => {
                        if (a !== areaValue) {
                            var o = document.createElement('option');
                            o.value = a;
                            o.text = a;
                            selArea.appendChild(o);
                        }
                    });
                });

            // 3) Inisial select
            var selIni = modal.querySelector('select[name="inisial"]');
            var hidAnak = modal.querySelector('input[name="id_anak"]');
            selIni.innerHTML = '';
            var oldIniOpt = document.createElement('option');
            oldIniOpt.value = btn.getAttribute('data-id_anak');
            oldIniOpt.text = inisial;
            oldIniOpt.selected = true;
            selIni.appendChild(oldIniOpt);

            fetch("<?= base_url($role . '/stockmodal/') ?>" + noModel)
                .then(res => res.json())
                .then(data => {
                    data.inisial.forEach(item => {
                        if (item.inisial !== inisial) {
                            var o = document.createElement('option');
                            o.value = item.id_anak;
                            o.text = item.inisial;
                            selIni.appendChild(o);
                        }
                    });

                    // set id_anak sesuai initial value
                    hidAnak.value = oldIniOpt.value;
                });

        });
    });
</script>
<?php $this->endSection(); ?>