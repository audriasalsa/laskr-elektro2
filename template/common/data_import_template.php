<h2><?php $this->echoData('page_title', 'Impor Data'); ?></h2>
<p><?php $this->echoData('page_description', "Pilih file CSV berisi data untuk diimpor ke sistem, lalu klik '<strong>Impor</strong>'"); ?></p>
<br/>
<form onsubmit="return showConfirmation();" method="post" action="" enctype="multipart/form-data">
    <label for="file_csv">Pilih file CSV: </label>
    <input type="file" id="file_csv" name="file_csv" />
    <br/>
    <input type="submit" value="Impor" name="submit" id="submit" />
</form>
<p class="error-message-label"><?php $this->echoData('error_message'); ?></p>
<?php if($this->data('displayed_data') == null) return; ?>
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