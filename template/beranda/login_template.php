<form method="post" action="<?php echo $this->homeAddress('/index/login'); ?>" enctype="multipart/form-data">
    <label for="txt_username">Username:</label>
    <br/>
    <input style="margin-bottom: 16px; width: 20%" id="txt_username" type="text" name="username"/>
    <br />
    <label for="txt_password">Password:</label>
    <br/>
    <input style="margin-bottom: 16px; width: 20%" id="txt_password" type="password" name="password"/>
    <br/>
    <input type="submit" name="submit" value="Login" />
    <br/>
    <p style="color: #ee555e;"><?php echo $this->data('error_message'); ?></p>
    Belum punya akun? Klik&nbsp;<a href="<?php echo $this->homeAddress('/index/pendaftaran'); ?>">di sini</a>&nbsp;untuk mendaftar.
</form>