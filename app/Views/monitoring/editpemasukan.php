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
                                    <td><i class="ri-edit-line" data-bs-toggle="modal" data-bs-target="#editPemasukanModal" data-id_masuk="<?= $dt['id_masuk'] ?>" data-tgl_masuk="<?= $dt['created_at'] ?>" data-area="<?= $dt['area'] ?>" data-kode_buyer="<?= $dt['kode_buyer'] ?>" data-nomodel="<?= $dt['no_model'] ?>" data-inisial="<?= $dt['inisial'] ?>" data-style="<?= $dt['style'] ?>" data-qty_masuk="<?= $dt['qty_masuk'] ?>" data-kode_buyer="<?= $dt['box_masuk'] ?>" data-ket_masuk="<?= $dt['ket_masuk'] ?>"></td>
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
                                        <div class="col-12">
                                            <label for="tgl_masuk" class="form-label">Tgl Masuk</label>
                                            <input type="text" class="form-control" name="tgl_masuk" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="area" class="form-label">Area</label>
                                            <input type="text" class="form-control" name="space" value="<?= $data['area'] ?>" readonly>
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
                                            <label for="qty_masuk" class="form-label">Qty Stock</label>
                                            <input type="text" class="form-control" name="qty_masuk">
                                        </div>
                                        <div class="col-12">
                                            <label for="box_masuk" class="form-label">Box</label>
                                            <input type="text" class="form-control" name="box_masuk">
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
    const editPemasukanModal = document.getElementById('editPemasukanModal');
    editPemasukanModal.addEventListener('show.bs.modal', function(event) {
        // Tombol yang memicu modal
        const button = event.relatedTarget;

        // Ambil data dari atribut data-*
        const jalur = button.getAttribute('data-jalur');
        const noModel = button.getAttribute('data-no_model');
        const space = button.getAttribute('data-space');

        // Isi input di dalam modal dengan nilai dari atribut
        const inputJalur = editPemasukanModal.querySelector('input[name="jalur"]');
        const inputNoModel = editPemasukanModal.querySelector('select[name="no_model"]');
        const inputSpace = editPemasukanModal.querySelector('input[name="space"]');

        inputJalur.value = jalur;
        inputNoModel.value = noModel;
        inputSpace.value = space;
    });

    function selectModel() {
        var noModelId = document.querySelector('select[name="no_model"]').value;

        if (noModelId !== "") {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "<?= base_url('/' . $role . '/stockmodal/') ?>" + noModelId, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);

                    var areaSelect = document.querySelector('select[name="area"]');
                    var inisialSelect = document.querySelector('select[name="inisial"]');
                    var idAnakInput = document.querySelector('input[name="id_anak"]');
                    areaSelect.innerHTML = '<option selected></option>';
                    inisialSelect.innerHTML = '<option selected></option>';

                    response.area.forEach(function(area) {
                        var option = document.createElement('option');
                        option.value = area;
                        option.text = area;
                        areaSelect.appendChild(option);
                    });

                    response.inisial.forEach(function(item) {
                        var option = document.createElement('option');
                        option.value = item.id_anak; // Set value ke id_anak
                        option.text = item.inisial; // Tampilkan teks sebagai inisial
                        inisialSelect.appendChild(option);
                    });

                    // Update id_anak saat inisial dipilih
                    inisialSelect.addEventListener('change', function() {
                        var selectedOption = inisialSelect.options[inisialSelect.selectedIndex];
                        idAnakInput.value = selectedOption.value; // Set value id_anak ke input
                    });
                }
            };
            xhr.send();
        }
    }
</script>
<?php $this->endSection(); ?>