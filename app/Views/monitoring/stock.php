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
                            <a class="nav-link collapsed" href="<?= base_url($role . '/excelstockgudang') ?>">
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
                                <th scope="col">Kapasitas</th>
                                <th scope="col">Space</th>
                                <th scope="col">Qty Jalur</th>
                                <th scope="col">Box</th>
                                <th scope="col">No Model</th>
                                <th scope="col">Keterangan</th>
                                <th scope="col">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($jalur as $data) : ?>
                                <tr>
                                    <th scope="row"><?= $no ?></th>
                                    <td><?= $data['jalur'] ?></td>
                                    <td><?= $data['jumlah_box'] ?></td>
                                    <td><?= $data['space'] ?></td>
                                    <td><?= $data['qty_stock'] ?></td>
                                    <td><?= $data['box_stock'] ?></td>
                                    <td><?= $data['no_model'] ?></td>
                                    <td><?= $data['keterangan'] ?></td>
                                    <td><a href="<?= base_url('/' . $role . '/detailstock/' . $data['jalur']) ?>"><i class="bi bi-eye"></a></td>
                                </tr>
                            <?php
                                $no++;
                            endforeach; ?>
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->
                </div>
            </div>

        </div>
    </div>
</section>
<script>
</script>

<?php $this->endSection(); ?>