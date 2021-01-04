<h2><?php $this->echoData('page_title', 'Impor Data'); ?></h2>
<p><?php $this->echoData('page_description', "Pilih dahulu tahap proposal yang sesuai, lalu pilih file CSV berisi data hasil untuk diimpor ke sistem, lalu klik '<strong>Impor</strong>'"); ?></p>
<br/>
<form onsubmit="return showConfirmation();" method="post" action="" enctype="multipart/form-data">
    <label for="cbx_id_event">Pilih tahap sempro: </label>
    <select name="id_event" id="cbx_id_event">
        <option value="" selected>-- Pilih Salah Satu --</option>
        <?php foreach ($this->data('event_options') as $id => $nama) { ?>
            <option value="<?php echo $id; ?>"><?php echo $nama; ?></option>
        <?php } ?>
    </select>
    <br/>
    <label for="file_csv">Pilih file CSV: </label>
    <input type="file" id="file_csv" name="file_csv" />
    <br/>
    <input type="submit" value="Impor" name="submit" id="submit" />
</form>
<p class="error-message-label"><?php $this->echoData('error_message'); ?></p>
<?php if($this->data('displayed_data') == null) return; ?>
<h3>Baris-baris berikut <u>berhasil</u> diimpor:</h3>
<?php $headers = $this->data('headers'); ?>
<?php $displayedData = $this->data('displayed_data'); ?>
<div style="width: 100%; overflow: scroll; padding: 4px; border: 1px solid #CCCCCC;">
    <table class="data-table">
        <thead>
        <?php foreach ($headers as $header) { ?>
            <th class="data-table-td data-display-caption"><?php echo $header; ?></th>
        <?php } ?>
        </thead>
        <tbody>
        <?php foreach ($displayedData as $row) { ?>
            <tr>
                <?php foreach ($row as $column => $value) { ?>
                    <td class="data-table-td data-display-content"><?php echo $value; ?></td>
                <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php if($this->data('failed_imports') == null) return; ?>
<h3>Baris-baris berikut <u>gagal</u> diimpor: </h3>
<?php $failedImports = $this->data('failed_imports'); ?>
<div style="width: 100%; overflow: scroll; padding: 4px; border: 1px solid #CCCCCC;">
    <table class="data-table">
        <thead>
        <?php foreach ($headers as $header) { ?>
            <th class="data-table-td data-display-caption"><?php echo $header; ?></th>
        <?php } ?>
        </thead>
        <tbody>
        <?php foreach ($failedImports as $row) { ?>
            <tr>
                <?php foreach ($row as $column => $value) { ?>
                    <td class="data-table-td data-display-content"><?php echo $value; ?></td>
                <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
