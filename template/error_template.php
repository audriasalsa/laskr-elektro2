<h2>Ups....</h2>
<p>Aksi terakhir Anda tidak dapat dijalankan karena terjadi galat:</p>
<?php if(($this->data('error_message')) !== null) { ?>
    <p class="error-message-label"><?php echo $this->data('error_message'); ?></p>
<?php } ?>
<br/>
<?php $backLink = $this->data('back_link'); ?>
<?php if($backLink != null) { ?>
    <a href="<?php echo $backLink; ?>"><< Kembali</a>
<?php } ?>
