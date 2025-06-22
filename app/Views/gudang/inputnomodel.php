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
                    <h5 class="card-title">Input Database</h5>
                    <form action="<?= base_url('/' . $role . '/importdatabase') ?>" method="post" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <label for="formFile" class="col-sm-2 col-form-label">File Upload</label>
                            <div class="col-sm-3">
                                <input class="form-control" type="file" name="file">
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-info" type="submit">Save</button>
                            </div>
                            <!-- Icon Excel -->
                            <div class="col-sm-2">
                                <a href="<?= base_url($role . '/exceldataorder') ?>" class="btn btn-success" style="display: flex; align-items: center;">
                                    <i class="ri-file-excel-line" style="font-size: 20px; margin-right: 5px;"></i>
                                    Export Excel
                                </a>
                            </div>
                        </div>
                    </form>
                    <p></p>
                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Waktu Input</th>
                                <th scope="col">No Order</th>
                                <th scope="col">Area</th>
                                <th scope="col">No Model</th>
                                <th scope="col">In</th>
                                <th scope="col">Style</th>
                                <th scope="col">Color</th>
                                <th scope="col">Delivery</th>
                                <th scope="col">Qty PO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($db as $data) : ?>
                                <tr>
                                    <th scope="row"><?= $no; ?></th>
                                    <td><?= $data['waktu_input'] ?></td>
                                    <td><?= $data['no_order'] ?></td>
                                    <td><?= $data['area'] ?></td>
                                    <td><?= $data['no_model'] ?></td>
                                    <td><?= $data['inisial'] ?></td>
                                    <td><?= $data['style'] ?></td>
                                    <td><?= $data['warna'] ?></td>
                                    <td><?= $data['delivery'] ?></td>
                                    <td><?= $data['qty_po_inisial'] ?></td>
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
<?php $this->endSection(); ?>