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
                    <h5 class="card-title"><?= $title ?></h5>
                    <form action="<?= base_url($role . '/stock') ?>" method="post">
                        <div class="row mb-2">
                            <label for="cari" class="col-sm-2 col-form-label">No Model</label>
                            <div class="col-sm-2">
                                <input class="form-control" type="text" name="cari1">
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-info">Search</button>
                            </div>
                            <!-- Icon Excel -->
                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit" style="display: flex; align-items: center;" formaction="<?= base_url($role . '/excelreportstock') ?>">
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
                                <th scope="col">Kode Buyer</th>
                                <th scope="col">No Model</th>
                                <th scope="col">Area</th>
                                <th scope="col">Inisial</th>
                                <th scope="col">Style</th>
                                <th scope="col">Warna</th>
                                <th scope="col">Delivery</th>
                                <th scope="col">Qty</th>
                                <th scope="col">Box</th>
                                <th scope="col">Create Schedule</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($stock as $data) : ?>
                                <tr>
                                    <th scope="row"><?= $no ?></th>
                                    <td><?= $data['kode_buyer'] ?></td>
                                    <td><input type="hidden" value="<?= $data['smv'] ?>"><?= $data['no_model'] ?></td>
                                    <td><?= $data['area'] ?></td>
                                    <td><input type="hidden" value="<?= $data['id_anak'] ?>"><?= $data['inisial'] ?></td>
                                    <td><?= $data['style'] ?></td>
                                    <td><?= $data['warna'] ?></td>
                                    <td><input type="hidden" value="<?= $data['qty_po_inisial'] ?>"><?= $data['delivery'] ?></td>
                                    <td><input type="hidden" value="<?= $data['qty_keluar'] ?? 0 ?>"><?= $data['qty_stock'] ?></td>
                                    <td><input type="hidden" value="<?= $data['sisa_jatah'] ?? 0 ?>"><?= $data['box_stock'] ?></td>
                                    <td><i class="bx bx-plus-medical" data-bs-toggle="modal" data-bs-target="#scheduleModal" data-gd_setting="<?= $data['gd_setting'] ?>" data-no_model="<?= $data['no_model'] ?>" data-area="<?= $data['area'] ?>" data-smv="<?= $data['smv'] ?>" data-id_anak="<?= $data['id_anak'] ?>" data-inisial="<?= $data['inisial'] ?>" data-style="<?= $data['style'] ?>" data-warna="<?= $data['warna'] ?>" data-delivery="<?= $data['delivery'] ?>" data-qty_po="<?= $data['qty_po_inisial'] ?>" data-sisa_jatah="<?= $data['sisa_jatah'] ?>" data-qty_stock="<?= $data['qty_stock'] ?>" data-box_stock="<?= $data['box_stock'] ?>" data-qty_kirim="<?= $data['qty_keluar'] ?? 0 ?>"></i></td>
                                </tr>
                            <?php
                                $no++;
                            endforeach; ?>
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->
                    <!-- Basic Modal -->
                    <div class="modal fade" id="scheduleModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Create Schedule Packing</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="<?= base_url('/' . $role . '/inputpermintaan') ?>" method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="admin" value="<?= $admin ?>">
                                        <div class="col-12">
                                            <label for="gd_setting" class="form-label">Gd Setting</label>
                                            <input type="text" class="form-control" name="gd_setting" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="no_model" class="form-label">No Model</label>
                                            <input type="text" class="form-control" name="no_model" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="delivery" class="form-label">Delivery</label>
                                            <input type="date" class="form-control" name="delivery" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="area" class="form-label">Area</label>
                                            <input type="text" class="form-control" name="area" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="inisial" class="form-label">Inisial</label>
                                            <input type="text" class="form-control" name="inisial" readonly>
                                            <input type="hidden" name="id_anak" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="smv" class="form-label">SMV</label>
                                            <input type="text" class="form-control" name="smv" id="smv" readonly onkeyup="sum()">
                                        </div>
                                        <div class="col-12">
                                            <label for="qty_po" class="form-label">Qty Po</label>
                                            <input type="text" class="form-control" name="qty_po" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="qty_stock" class="form-label">Qty Stock</label>
                                            <input type="text" class="form-control" name="qty_stock" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="qty_kirim" class="form-label">Qty Kirim</label>
                                            <input type="text" class="form-control" name="qty_kirim" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="sisa_jatah" class="form-label">Sisa Jatah</label>
                                            <input type="text" class="form-control" name="sisa_jatah" id="sisa_jatah" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label for="tgl_jalan" class="form-label">Tgl Jalan Packing</label>
                                            <input type="date" class="form-control" name="tgl_jalan">
                                        </div>
                                        <div class="col-12">
                                            <label for="wh" class="form-label">Working Hours</label>
                                            <input type="number" class="form-control" name="wh" id="wh" onkeyup="sum()">
                                        </div>
                                        <div class="col-12">
                                            <label for="eff" class="form-label">Effisiency(%)</label>
                                            <input type="number" class="form-control" name="eff" id="eff" onkeyup="sum()">
                                        </div>
                                        <div class="col-12">
                                            <label for="direct" class="form-label">Direct</label>
                                            <input type="number" class="form-control" name="direct" id="direct" onkeyup="sum()">
                                        </div>
                                        <div class="col-12">
                                            <label for="kapasitas" class="form-label">Kapasitas</label>
                                            <input type="number" class="form-control" name="kapasitas" id="kapasitas" readonly onkeyup="sum()">
                                        </div>
                                        <div class="col-12">
                                            <label for="qty_minta" class="form-label">Qty Minta(dz)</label>
                                            <input type="number" class="form-control" name="qty_minta" id="qty_minta" onkeyup="hitung()">
                                        </div>
                                        <div class="col-12">
                                            <label for="ket_pck" class="form-label">Keterangan Packing</label>
                                            <textarea class="form-control" name="ket_pck"></textarea>
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
    // Mengambil modal scheduleModal
    const scheduleModal = document.getElementById('scheduleModal');
    scheduleModal.addEventListener('show.bs.modal', function(event) {
        // Tombol yang memicu modal
        const button = event.relatedTarget;

        // Ambil data dari atribut data-*
        const gdSetting = button.getAttribute('data-gd_setting');
        const noModel = button.getAttribute('data-no_model');
        const area = button.getAttribute('data-area');
        const smv = button.getAttribute('data-smv');
        const id_anak = button.getAttribute('data-id_anak');
        const inisial = button.getAttribute('data-inisial');
        const delivery = button.getAttribute('data-delivery');
        const qtyPo = button.getAttribute('data-qty_po');
        const sisaJatah = button.getAttribute('data-sisa_jatah');
        const qtyStock = button.getAttribute('data-qty_stock');
        const boxStock = button.getAttribute('data-box_stock');
        const qtyKirim = button.getAttribute('data-qty_kirim');

        // Isi input di dalam modal dengan nilai dari atribut
        const inputGdSetting = scheduleModal.querySelector('input[name="gd_setting"]');
        const inputNoModel = scheduleModal.querySelector('input[name="no_model"]');
        const inputArea = scheduleModal.querySelector('input[name="area"]');
        const inputSmv = scheduleModal.querySelector('input[name="smv"]');
        const inputIdAnak = scheduleModal.querySelector('input[name="id_anak"]');
        const inputInisial = scheduleModal.querySelector('input[name="inisial"]');
        const inputDelivery = scheduleModal.querySelector('input[name="delivery"]');
        const inputQtyPo = scheduleModal.querySelector('input[name="qty_po"]');
        const inputSisaJatah = scheduleModal.querySelector('input[name="sisa_jatah"]');
        const inputQtyStock = scheduleModal.querySelector('input[name="qty_stock"]');
        const inputBoxStock = scheduleModal.querySelector('input[name="box_stock"]');
        const inputQtyKirim = scheduleModal.querySelector('input[name="qty_kirim"]');

        inputGdSetting.value = gdSetting;
        inputNoModel.value = noModel;
        inputArea.value = area;
        inputSmv.value = smv;
        inputIdAnak.value = id_anak;
        inputInisial.value = inisial;
        inputDelivery.value = delivery;
        inputQtyPo.value = qtyPo;
        inputSisaJatah.value = sisaJatah;
        inputQtyStock.value = qtyStock;
        inputBoxStock.value = boxStock;
        inputQtyKirim.value = qtyKirim;
    });

    function sum() { //hitung kapasitas
        var wh = document.getElementById('wh').value;
        var smv = document.getElementById('smv').value;
        var eff = document.getElementById('eff').value;
        var direct = document.getElementById('direct').value;
        var dz = 24; // banyaknya pcs dalam satuan dz
        // rumus kapasitas berdasarkan smv dalam satuan menit
        // ( ((wh*60) / smv) * eff * direct ) / 24pcs
        var result = ((((parseFloat(wh) * 60) / parseFloat(smv)) * (parseFloat(eff) / 100)) * parseFloat(direct)) / parseFloat(dz);
        //25.200 / 200 * (1,5 * 10)
        if (!isNaN(result)) {
            document.getElementById('kapasitas').value = result.toFixed(2);
        }
        if (isNaN(result)) {
            document.getElementById('kapasitas').value = "";
        }
    }

    function hitung() {
        var minta = parseFloat(document.getElementById('qty_minta').value) || 0;
        var kaps = parseFloat(document.getElementById('kapasitas').value) || 0;
        // Hapus karakter koma dari sisa_jatah sebelum parsing
        var sisaJatahRaw = document.getElementById('sisa_jatah').value.replace(/,/g, '');
        var sisa_jatah = parseFloat(sisaJatahRaw) || 0;

        console.log("Qty Minta: ", minta);
        console.log("Kapasitas: ", kaps);
        console.log("Sisa Jatah: ", sisa_jatah);

        if (isNaN(sisa_jatah) || sisa_jatah <= 0) {
            console.log("Sisa jatah tidak valid atau belum diisi.");
            return;
        }

        if (minta > kaps) {
            alert("JUMLAH QTY MINTA TIDAK BOLEH MELEBIHI KAPASITAS!");
            document.getElementById('qty_minta').value = 0;
        } else if (parseFloat(minta) > parseFloat(sisa_jatah)) {
            alert("JUMLAH QTY MINTA TIDAK BOLEH MELEBIHI QTY SISA JATAH !");
            document.getElementById('qty_minta').value = 0;
        }
    }
</script>

<?php $this->endSection(); ?>