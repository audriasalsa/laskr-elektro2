<h2><?php $this->echoData('page_title', 'Generate Dokumen'); ?></h2>
<p><?php $this->echoData('page_description', "Klik button <strong>Generate</strong> di tiap jenis dokumen, lalu klik URL yang muncul untuk mengunduh."); ?></p>
<br/>

<form method="post" action="">
    <table class="sortable data-table">
        <thead>
            <th class="sortable-th data-display-caption">Dokumen</th>
            <th class="sortable-th data-display-caption">URL</th>
        </thead>
        <tbody>
            <tr>
                <td class="data-table-td data-display-content"><input type="submit" name="submit" value="Generate Berita Acara Ujian Akhir"></td>
                <td class="data-table-td data-display-content"><a href="<?php $this->echoData('url_berita_acara_ujian_akhir', '#'); ?>">Unduh</a></td>
            </tr>
        </tbody>
    </table>
</form>