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
                    <h5 class="card-title">Stock Gudang</h5>
                    <div style="display: flex; align-items: center;">
                        <div class="col-md-2">
                            <!-- Icon Excel -->
                            <a class="nav-link collapsed" href="">
                                <i class="ri-file-excel-line" style="font-size: 30px;"></i>
                            </a>
                        </div>
                    </div>
                    <p></p>
                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Jalur</th>
                                <th scope="col">Space</th>
                                <th scope="col">Qty Jalur</th>
                                <th scope="col">Box</th>
                                <th scope="col">No Model</th>
                                <th scope="col">Keterangan</th>
                                <th scope="col">Edit</th>
                                <th scope="col">Tambah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($jalur as $data) : ?>
                                <tr>
                                    <th scope="row"><?= $no ?></th>
                                    <td><?= $data['jalur'] ?></td>
                                    <td><?= $data['space'] ?></td>
                                    <td><?= $data['qty_stock'] ?></td>
                                    <td><?= $data['box_stock'] ?></td>
                                    <td><?= $data['no_model'] ?></td>
                                    <td><?= $data['keterangan'] ?></td>
                                    <td><a href="<?= base_url('/' . $role . '/detailstock/' . $data['jalur']) ?>"><i class="ri-edit-line"></a></td>
                                    <td><i class="bx bx-plus-medical" data-bs-toggle="modal" data-bs-target="#inputstockModal" data-jalur="<?= $data['jalur'] ?>" data-no_model="<?= $data['no_model'] ?>" data-space="<?= $data['space'] ?>"></i></td>
                                </tr>
                            <?php
                                $no++;
                            endforeach; ?>
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->
                    <!-- Basic Modal -->
                    <div class="modal fade" id="inputstockModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Input Stock</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="<?= base_url('/' . $role . '/inputstock') ?>" method="post">
                                    <div class="modal-body">
                                        <div class="col-12">
                                            <label for="jalur" class="form-label">Jalur</label>
                                            <input type="text" class="form-control" name="jalur" readonly>
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
                                            <select class="form-select" name="inisial" aria-label="Default select example" onchange="getIdAnak()">
                                                <option selected></option>
                                            </select>
                                            <input type="text" name="id_anak" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="qty" class="form-label">Qty Stock</label>
                                            <input type="text" class="form-control" name="qty">
                                        </div>
                                        <div class="col-12">
                                            <label for="box" class="form-label">Box</label>
                                            <input type="text" class="form-control" name="box">
                                        </div>
                                        <div class="col-12">
                                            <label for="keterangan" class="form-label">Keterangan</label>
                                            <textarea class="form-control" name="keterangan"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary">Save</button>
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
    const inputStockModal = document.getElementById('inputstockModal');
    inputStockModal.addEventListener('show.bs.modal', function(event) {
        // Tombol yang memicu modal
        const button = event.relatedTarget;

        // Ambil data dari atribut data-*
        const jalur = button.getAttribute('data-jalur');
        const noModel = button.getAttribute('data-no_model');
        const space = button.getAttribute('data-space');

        // Isi input di dalam modal dengan nilai dari atribut
        const inputJalur = inputStockModal.querySelector('input[name="jalur"]');
        const inputNoModel = inputStockModal.querySelector('input[name="no_model"]');
        const inputSpace = inputStockModal.querySelector('input[name="space"]');

        inputJalur.value = jalur;
        inputNoModel.value = noModel;
        inputSpace.value = space;
    });

    function selectModel() {
        // Ambil value dari select no_model
        var noModelId = document.querySelector('select[name="no_model"]').value;

        // Cek apakah no_model telah dipilih
        if (noModelId !== "") {
            // Lakukan request Ajax ke server
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "<?= base_url('/' . $role . '/stockmodal/') ?>" + noModelId, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Parse response JSON
                    var response = JSON.parse(xhr.responseText);

                    // Kosongkan opsi area dan inisial sebelumnya
                    var areaSelect = document.querySelector('select[name="area"]');
                    var inisialSelect = document.querySelector('select[name="inisial"]');
                    areaSelect.innerHTML = '<option selected></option>';
                    inisialSelect.innerHTML = '<option selected></option>';

                    // Tambahkan opsi untuk area
                    response.area.forEach(function(area) {
                        var option = document.createElement('option');
                        option.value = area;
                        option.text = area;
                        areaSelect.appendChild(option);
                    });

                    // Tambahkan opsi untuk inisial
                    response.inisial.forEach(function(inisial) {
                        var option = document.createElement('option');
                        option.value = inisial;
                        option.text = inisial;
                        inisialSelect.appendChild(option);
                    });
                }
            };
            xhr.send();
        }
    }

    function getIdAnak() {
        let selectedInisial = document.querySelector('select[name="inisial"]').value;

        if (selectedNoModel !== "" && selectedInisial !== "") {
            // Lakukan request Ajax untuk mendapatkan id_anak berdasarkan no_model dan inisial
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?= base_url('/' . $role . '/getidanak/') ?>", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);

                    if (response.id_anak) {
                        // Masukkan ID Anak ke input text
                        document.querySelector('input[name="id_anak"]').value = response.id_anak;
                    } else {
                        console.log("ID Anak tidak ditemukan.");
                        document.querySelector('input[name="id_anak"]').value = ""; // Kosongkan jika tidak ada id
                    }
                }
            };
            xhr.send("no_model=" + selectedNoModel + "&inisial=" + selectedInisial);
        }
    }
</script>

<?php $this->endSection(); ?>