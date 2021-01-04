<h2>Pendaftaran Seminar Proposal</h2>
<p>
    <a>Mekanisme pendaftaran sempro:</a>
    <ul>
        <li>Lengkapi terlebih dahulu form pendaftaran di sistem. Dengan mengunggah:
            <ol>
                <li>Scan form persetujuan mengikuti seminar proposal.</li>
                <li>Scan form activity control bimbingan proposal.</li>
                <li>File proposal yang telah direvisi (masih dapat diubah hingga sesaat sebelum Anda maju seminar).</li>
            </ol>
        </li>
        <li>Setelah itu, silahkan datang untuk mengumpulkan berkas-berkas berikut (hard copy) ke panitia, yang terdiri dari:
            <ol>
                <li>Asli form persetujuan mengikuti seminar proposal, 1 lembar.</li>
                <li>Asli form activity control bimbingan, 1 lembar.</li>
                <li>Printout bukti pendaftaran dari sistem, 1 lembar.</li>
            </ol>
        </li>
        <li>
            Pada saat Anda <b>melaksanakan seminar</b>, wajib membawa berkas-berkas berikut:
            <ol>
                <li>Naskah berita acara seminar proposal, 1 lembar.</li>
                <li>Print-out proposal skripsi yang telah final, 3 eksemplar.</li>
                <li>Lembar revisi, 3 lembar.</li>
                <li>Form penilaian seminar proposal, 3 lembar.</li>
                <li>Print-out bukti pendaftaran seminar proposal, 3 lembar.</li>
            </ol>
            Masukkan semua persyaratan tersebut ke dalam map plastik putih bening (yang ada kancingnya), dan <b>jangan lupa dibawa saat sempro</b>.</li>
        </li>
        <li>Proposal yang dibawa saat seminar harus sudah direvisi sesuai dengan arahan verifikasi grup riset.</li>
        <li>Pastikan koneksi internet Anda stabil karena jika berkas Anda berukuran besar, proses upload akan memakan waktu relatif lama.</li>
        <li>Ukuran maksimal untuk masing-masing file yang diupload adalah 5mb.</li>
        <li>Jika revisi proposal Anda belum selesai, upload dulu seadanya agar form ini bisa disubmit.</li>
        <li>Anda masih <b>DAPAT MENGUBAH PROPOSAL</b> nantinya, sampai dengan sesaat sebelum Anda memulai sempro pada hari-H.</li>
    </ul>
</p>
<br/>
<!-- TAMPILAN JIKA BELUM MENDAFTAR SEMPRO -->
<p class="error-message-label"><?php echo $this->echoData('error_message'); ?></p>
<?php $eventData = $this->data('event_data'); ?>
<?php $proposalTerdaftar = $this->data('proposal_terdaftar'); ?>
<?php $actionUrlParam = $this->data('action_url_param'); ?>
<?php if($this->data('hide_forms') == true) return; ?>
<?php if($proposalTerdaftar == null) { ?>
    <form onsubmit="return showConfirmation();" method="post" action="<?php echo $this->homeAddress('/proposal/detail-pendaftaran-sempro' . $actionUrlParam); ?>" enctype="multipart/form-data">
        <table class="data-table">
            <tbody>
                <tr>
                    <td style="width: 40%;" class="data-table-td data-display-caption">
                        <label for="txt_nama_event">Pendaftaran: </label>
                    </td>
                    <td style="width: auto;" class="data-table-td data-display-content">
                        <input style="width: 100%; height: 28px;" type="text" id="txt_nama_event" name="txt_nama_event" value="<?php echo $eventData['nama']; ?>" disabled/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 40%;" class="data-table-td data-display-caption">
                        <label for="txt_judul">Judul (Gantilah bila perlu, biarkan jika sudah benar)</label>
                    </td>
                    <td style="width: auto;" class="data-table-td data-display-content">
                        <input style="width: 100%; height: 28px;" type="text" id="txt_judul" name="txt_judul" value="<?php $this->echoData('existing_judul'); ?>"/>
                    </td>
                </tr>
                <!-- 'SISTEM INFORMASI','SISTEM CERDAS','VISI KOMPUTER','JARKOM, ARSITEKTUR DAN KEAMANAN DATA','MULTIMEDIA DAN GAME' -->
                <tr>
                    <td style="width: 40%;" class="data-table-td data-display-caption">
                        <label for="cbx_grup_riset">Grup Riset (Gantilah bila perlu, biarkan jika sudah benar)</label>
                    </td>
                    <td style="width: auto;" class="data-table-td data-display-content">
                        <?php $egr = $this->data('existing_grup_riset'); ?>
                        <select style="width: 100%; height: 28px;" id="cbx_grup_riset" name="cbx_grup_riset">
                            <option value="">-- Pilih Salah Satu --</option>
                            <option value="SISTEM INFORMASI" <?php if($egr == 'SISTEM INFORMASI') echo 'selected'; ?>>SISTEM INFORMASI</option>
                            <option value="SISTEM CERDAS" <?php if($egr == 'SISTEM CERDAS') echo 'selected'; ?>>SISTEM CERDAS</option>
                            <option value="VISI KOMPUTER" <?php if($egr == 'VISI KOMPUTER') echo 'selected'; ?>>VISI KOMPUTER</option>
                            <option value="JARKOM, ARSITEKTUR DAN KEAMANAN DATA" <?php if($egr == 'JARKOM, ARSITEKTUR DAN KEAMANAN DATA') echo 'selected'; ?>>JARKOM, ARSITEKTUR DAN KEAMANAN DATA</option>
                            <option value="MULTIMEDIA DAN GAME" <?php if($egr == 'MULTIMEDIA DAN GAME') echo 'selected'; ?>>MULTIMEDIA DAN GAME</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="width: 40%;" class="data-table-td data-display-caption">
                        <label for="file_scan_activiy_control_bimbingan_proposal">Scan Activity Control Bimbingan (*.jpeg/*.jpg/*png)</label>
                    </td>
                    <td style="width: auto;" class="data-table-td data-display-content">
                        <input style="width: 100%; height: 28px;" type="file" id="file_scan_activiy_control_bimbingan_proposal" name="file_scan_activiy_control_bimbingan_proposal"/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 40%;" class="data-table-td data-display-caption">
                        <label for="file_scan_persetujuan_mengikuti_sempro">Scan Persetujuan Mengikuti Sempro (*.jpeg/*.jpg/*png)</label>
                    </td>
                    <td style="width: auto;" class="data-table-td data-display-content">
                        <input style="width: 100%; height: 28px;" type="file" id="file_scan_persetujuan_mengikuti_sempro" name="file_scan_persetujuan_mengikuti_sempro"/>
                    </td>
                </tr>

                <tr>
                    <td style="width: 40%;" class="data-table-td data-display-caption">
                        <label for="file_proposal_sempro">Soft file proposal yang telah direvisi (*.pdf)</label>
                    </td>
                    <td style="width: auto;" class="data-table-td data-display-content">
                        <input style="width: 100%; height: 28px;" type="file" id="file_proposal_sempro" name="file_proposal_sempro"/>
                    </td>
                </tr>
                <tr>
                    <td class="data-table-td data-display-caption">&nbsp;</td>
                    <td class="data-table-td data-display-content">
                        <input type="submit" name="submit" id="submit" value="Daftar!" class="form-submit-button"/>
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" id="hid_id_event" name="hid_id_event" value="<?php echo $eventData['id']; ?>" />
    </form>
    <script type="text/javascript">
        function showConfirmation()
        {
            return confirm("Apakah Anda yakin isian Anda sudah benar semua? Setelah data disubmit, TIDAK BISA diubah kembali!");
        }
    </script>
<?php } else { ?>
    <?php $uploadDir = ($this->data('upload_directory')); ?>
    <h3>Anda sudah terdaftar pada seminar proposal</h3>
    <table class="data-table">
        <tbody>
            <?php foreach($proposalTerdaftar as $key => $value) { ?>
                <tr>
                    <td style="width: 40%;" class="data-table-td data-display-caption"><?php echo \m\Util::strFormatTableColumnName($key); ?></td>
                    <?php if(strpos($key, 'file_') === false) { ?>
                        <td style="width: auto;" class="data-table-td data-display-content"><?php echo $value; ?></td>
                    <?php } else { ?>
                        <?php if($key !== 'file_proposal_revisi') { ?>
                            <td style="width: auto;" class="data-table-td data-display-content">
                                <a href="<?php echo "$uploadDir/$value"; ?>"><?php echo $value; ?></a>
                            </td>
                        <?php } else { ?>
                            <td style="width: auto;" class="data-table-td data-display-content">
                                <a href="<?php echo "$uploadDir/$value"; ?>"><?php echo $value; ?></a>
                                <br />
                                <br />
                                <a class="form-submit-button" href="<?php echo $this->homeAddress('/proposal/ganti-proposal-sempro'); ?>">Ganti Proposal</a>
                            </td>
                        <?php } ?>
                    <?php }?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <br/>
    <a href="<?php echo $this->homeAddress('/proposal/cetak-bukti-pendaftaran-sempro?id_event=' . $eventData['id']); ?>">Cetak Bukti Pendaftaran</a>
<?php } ?>
