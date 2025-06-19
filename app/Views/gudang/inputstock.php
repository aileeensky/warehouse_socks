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
                    <h5 class="card-title">Input Stock</h5>
                    <div style="display: flex; align-items: center;">
                        <div class="col-md-2">
                            <!-- Icon Excel -->
                            <a class="nav-link collapsed" href="<?= base_url($role . '/excelstockgudang') ?>">
                                <i class="ri-file-excel-line" style="font-size: 30px;"></i>
                            </a>
                        </div>
                    </div>

                    <form action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_model">No Model</label>
                                    <select class="form-control" id="no_model" name="no_model" onchange="selectModel()">
                                        <option selected></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="inisial">Inisial</label>
                                    <select class="form-control" id="inisial" name="inisial">
                                        <option selected></option>
                                        <!-- Inisial akan diisi melalui AJAX -->
                                    </select>
                                    <input type="hidden" name="id_anak" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jalur">Area</label>
                                    <input type="text" class="form-control" id="area" name="area">
                                </div>
                            </div>
                        </div>
                    </form>

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
        const inputNoModel = inputStockModal.querySelector('select[name="no_model"]');
        const inputSpace = inputStockModal.querySelector('input[name="space"]');

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