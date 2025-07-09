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
                    <form action="<?= base_url($role . '/reportpengeluaran') ?>" method="post">
                        <div class="row mb-2">
                            <label for="cari" class="col-sm-2 col-form-label">No Model</label>
                            <div class="col-sm-2">
                                <input class="form-control" type="text" name="cari1">
                            </div>
                            <label for="cari2" class="col-sm-1 col-form-label">Tgl Keluar</label>
                            <div class="col-sm-2">
                                <input class="form-control" type="date" name="cari2">
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-info">Search</button>
                            </div>
                            <!-- Icon Excel -->
                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit" style="display: flex; align-items: center;" formaction="<?= base_url($role . '/excelreportpengeluaran') ?>">
                                    <i class="ri-file-excel-line" style="font-size: 20px;"></i>
                                    Export Excel
                                </button>
                            </div>
                        </div>
                    </form>
                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Tgl Keluar</th>
                                <th scope="col">Area</th>
                                <th scope="col">Buyer</th>
                                <th scope="col">No Model</th>
                                <th scope="col">In</th>
                                <th scope="col">Style</th>
                                <th scope="col">Qty</th>
                                <th scope="col">Box</th>
                                <th scope="col">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($dataKeluar as $data) : ?>
                                <tr>
                                    <th scope="row"><?= $no++ ?></th>
                                    <td><?= $data['tgl_keluar'] ?></td>
                                    <td><?= $data['area'] ?></td>
                                    <td><?= $data['kode_buyer'] ?></td>
                                    <td><?= $data['no_model'] ?></td>
                                    <td><?= $data['inisial'] ?></td>
                                    <td><?= $data['style'] ?></td>
                                    <td><?= $data['qty_keluar'] ?></td>
                                    <td><?= $data['box_keluar'] ?></td>
                                    <td><?= $data['ket_keluar'] ?></td>
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