<form method="post" action="<?php $this->homeAddress('/judul/impor'); ?>" enctype="multipart/form-data">
    <label for="txt_name">File Excel untuk Impor:</label>
    <br/>
    <input style="margin-bottom: 16px; width: 20%" id="txt_name" type="file" name="file_impor"/>
    <br />
    <input type="submit" name="submit" value="Unggah" />
</form>
