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
                    <h5 class="card-title">Tabel <?= $title ?></h5>
                    <form action="<?= base_url($role . '/reportpemasukan') ?>" method="post">
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

                            <!-- Icon Excel -->
                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit" style="display: flex; align-items: center;" formaction="<?= base_url($role . '/excelreportpemasukan') ?>">
                                    <i class="ri-file-excel-line" style="font-size: 20px;"></i>
                                    Export Excel
                                </button>
                            </div>
                        </div>
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->
                </div>
            </div>

        </div>
    </div>
</section>

<?php $this->endSection(); ?>