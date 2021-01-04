<div class="dropdown">
    <div class="drop-btn">
        <div class="dropdown-title">Akun</div>
    </div>
    <div class="dropdown-container">
        <div class="dropdown-item">
            <a href="<?php echo $this->homeAddress('/index/logout'); ?>">Keluar</a>
        </div>
    </div>
</div>
<div class="dropdown">
    <div class="drop-btn">
        <div class="dropdown-title">Dokumen</div>
    </div>
    <div class="dropdown-container">
        <div class="dropdown-item">
            <a href="<?php echo $this->homeAddress('/panitia/dokumen/generate'); ?>">Generate</a>
        </div>
    </div>
</div>
<div class="dropdown">
    <div class="drop-btn">
        <div class="dropdown-title">Impor<!--&#9660;--></div>
    </div>
    <div class="dropdown-container">
        <div class="dropdown-item">
            <a href="<?php echo $this->homeAddress('/panitia/sempro/impor-jadwal'); ?>">Jadwal Sempro</a>
        </div>
        <div class="dropdown-item">
            <a href="<?php echo $this->homeAddress('/panitia/sempro/impor-hasil'); ?>">Hasil Sempro</a>
        </div>
        <div class="dropdown-item">
            <a href="<?php echo $this->homeAddress('/panitia/impor/impor-dosen'); ?>">Impor Dosen</a>
        </div>
        <div class="dropdown-item">
            <a href="<?php echo $this->homeAddress('/panitia/impor/impor-bimbingan'); ?>">Impor Bimbingan</a>
        </div>
    </div>
</div>
<div class="dropdown">
    <div class="drop-btn">
        <div class="dropdown-title">Rekap<!--&#9660;--></div>
    </div>
    <div class="dropdown-container">
        <div class="dropdown-item">
            <a href="<?php echo $this->homeAddress('/pra-proposal/jumlah-pembimbingan-utama'); ?>">Jumlah Pembimbingan Utama</a>
        </div>
        <div class="dropdown-item">
            <a href="<?php echo '#' ?>">Mahasiswa</a>
        </div>
        <div class="dropdown-item">
            <a href="<?php echo $this->homeAddress('/panitia/rekap/log-bimbingan'); ?>">Log Bimbingan</a>
        </div>
        <div class="dropdown-item">
            <a href="<?php echo $this->homeAddress('/panitia/rekap/log-bimbingan-dosen'); ?>">Log Bimbingan per Dosen</a>
        </div>
        <div class="dropdown-item">
            <a href="<?php echo $this->homeAddress('/panitia/rekap/pendaftaran-ujian-akhir'); ?>">Pendaftaran Ujian Akhir</a>
        </div>
        <div class="dropdown-item">
            <a href="<?php echo $this->homeAddress('/panitia/rekap/yudisium-ujian-akhir'); ?>">Yudisium Ujian Akhir</a>
        </div>
    </div>
</div>