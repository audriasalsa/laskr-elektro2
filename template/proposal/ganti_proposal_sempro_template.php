<h2>Ganti Proposal Sempro</h2>
<p>Pilih file yang baru untuk mengganti draft revisi proposal yang Anda daftarkan sebelumnya, lalu klik 'Simpan!'</p>
<br/>
<?php $hideForm = $this->data('hide_form'); ?>
<?php if($hideForm === true) { ?>
<p class="error-message-label"><?php $this->echoData('error_message'); ?></p>
<?php } else { ?>
<p class="error-message-label"><?php $this->echoData('error_message'); ?></p>
<form onsubmit="return showConfirmation();" method="post" action="<?php echo $this->homeAddress('/proposal/ganti-proposal-sempro'); ?>" enctype="multipart/form-data">
<table class="data-table">
    <tbody>
        <tr>
            <td style="width: 40%;" class="data-table-td data-display-caption">
                <label for="file_proposal_sempro_baru">Pilih file proposal yang baru (*.pdf)</label>
            </td>
            <td style="width: auto;" class="data-table-td data-display-content">
                <input style="width: 100%; height: 28px;" type="file" id="file_proposal_sempro_baru" name="file_proposal_sempro_baru"/>
            </td>
        </tr>
        <tr>
            <td class="data-table-td data-display-caption">&nbsp;</td>
            <td class="data-table-td data-display-content">
                <input type="submit" name="submit" id="submit" value="Simpan!" class="form-submit-button"/>
            </td>
        </tr>
    </tbody>
</table>
</form>
<script type="text/javascript">
function showConfirmation()
{
    return confirm("Apakah Anda yakin proposal yang baru sudah benar?");
}
</script>
<?php } ?>