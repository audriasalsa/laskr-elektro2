<h2>Selamat Datang, <?php echo $this->sessionData('auth_username'); ?>!</h2>
<p>Sistem ini masih dalam tahap pengembangan. Silahkan akses fitur-fitur yang ada lewat menu di bagian atas layar.</p>
<H2 align="center">Timeline Kegiatan</H2>
<p align="center"> <img src="static/timeline1.png"></p>
<p align="center"> <img src="static/timeline2.png"></p>
<p align="center"> <img src="static/timeline3.png"></p>
<?php if($this->sessionData('auth_access_type') == 'mahasiswa') { ?>
    
<?php } else if ($this->sessionData('auth_access_type') == 'dosen') { ?>
    <a href="<?php echo $this->homeAddress('/panitia/rekap/yudisium-ujian-akhir'); ?>">Yudisium Ujian Akhir</a>
<?php } ?>




