<?php
require_once('helper.php');

if(isset($_GET['id']) && isset($_GET['k'])){
    $ID = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $key = $_GET['k'];
    sanitize($ID, $key);
} else {
    die('Anda tidak diizinkan mengakses!');
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Jadwal Ujian Tugas Akhir | JTI-Polinema 2020</title>
    <style type="text/css">
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 3px;
        }
        th{
            background-color: darkgray;
        }
        table{
            width: 100%;
        }
        #header{
            text-align: center;
        }
        #footer{
            text-align: right;
        }
    </style>
</head>

<body>
    <table style="border: 0px;">
        <tr style="border: 0px;">
            <td style="text-align: center;"><img style="height:33%;" src="img/logo_polinema.jpg" alt="Kop Surat"></td>
            <td style="text-align: center;"><b>KEMENTERIAN PENDIDIKAN DAN KEBUDAYAAN<br>
            POLITEKNIK NEGERI MALANG<br>
            PANITIA TUGAS AKHIR - JURUSAN TEKNOLOGI INFORMASI<br>
            Jalan Soekarno-Hatta No.9 Malang 65141 Telp (0341) 404424</b></td>
            <td style="text-align: center;"><img style="height:33%;" src="img/logo_iso.jpg" alt="Kop Surat"></td>
        </tr>
    </table>
    <p style="text-align: center;"><b>JADWAL UJIAN TUGAS AKHIR - TAHAP 2</b></p>
    <?php    
    $dosencols = get_data('SELECT nama FROM v2_dosen WHERE id = '.$ID);
    $nama_dosen = '';
    if($dosenrow = mysqli_fetch_assoc($dosencols)){
        $nama_dosen = $dosenrow['nama'];
        echo("<p>Yth. Bapak/Ibu Dosen <b>" . $nama_dosen."</b><br>Terlampir jadwal ujian tugas akhir Tahap 2 Tahun Ajaran 2019/2020 sebagai berikut.</p>");
    }
    ?>
    <br>
    <table>
        <tr>
            <th>No.</th>
            <th>Prodi</th>
            <th>Hari, Tanggal</th>
            <th>Waktu</th>
            <th>Ruang</th>
            <th>Ruang Daring</th>
            <th>Nama</th>
            <th>Judul</th>
            <th>Moderator</th>
            <th>Penguji 1</th>
            <th>Penguji 2</th>
        </tr>
        <?php
        $no = 1;
        $querycols = get_data("SELECT a.a, CONCAT(SUBSTR(c.waktu_mulai, 1, 5), ' - ', SUBSTR(c.waktu_selesai, 1, 5)) AS Waktu, d.id_zoom,e.nama,b.* from (
        SELECT moderator as a 
        FROM db_tugasakhir.jadwal_skripsi_tahap_2  
        union  select penguji1 as a from db_tugasakhir.jadwal_skripsi_tahap_2  
        union select penguji2 as a from db_tugasakhir.jadwal_skripsi_tahap_2) a 
        join db_tugasakhir.jadwal_skripsi_tahap_2 b on a.a = b.moderator or a.a = b.penguji1 or a.a = b.penguji2
        join db_tugasakhir.v2_sesi c on b.sesi = c.id  
        join db_tugasakhir.v2_ruang_daring d on d.id_ruang_daring = ruang AND d.sesi = b.sesi
        join db_tugasakhir.v2_ruang e on d.id_ruang_daring = e.id
        where a = '".$nama_dosen."' ORDER BY hari DESC,sesi;");
        while ( $queryrow = mysqli_fetch_assoc($querycols) ) {

            if ($queryrow['moderator'] == $nama_dosen) {
                $queryrow['moderator'] = "<b>".$queryrow['moderator']."</b>";
            } else if ($queryrow['penguji1'] == $nama_dosen) {
                $queryrow['penguji1'] = "<b>".$queryrow['penguji1']."</b>";
            } else if ($queryrow['penguji2'] == $nama_dosen) {
                $queryrow['penguji2'] = "<b>".$queryrow['penguji2']."</b>";
            }

           /*if($queryrow['PesertaUjian2'] != ''){
           	$queryrow['PesertaUjjian1'] .= "<hr>". $queryrow['PesertaUjian2'];
           }*/

            echo("<tr>
                <td><center>".$no."</center></td>
                <td>".$queryrow['prodi']."</td>
                <td>".$queryrow['hari']."</td>
                <td>".$queryrow['Waktu']."</td>
                <td>".$queryrow['nama']."</td>
                <td><a href='https://zoom.us/j/".str_replace(' ','',$queryrow['id_zoom'])."'>".$queryrow['id_zoom']."</a></td>
                <td>".ucwords(strtolower($queryrow['nama_pengusul']))."</td>
                <td>".ucwords(strtolower($queryrow['judul_proposal']))."</td>
                <td>".$queryrow['moderator']."</td>
                <td>".$queryrow['penguji1']."</td>
                <td>".$queryrow['penguji2']."</td>
            </tr>");
            $no++;
        }
        ?>
    </table>
    <p>Disahkan tanggal 29 Juni 2020 dan telah diperiksa oleh Sekretaris Jurusan Teknologi Informasi. Atas perhatian Bapak/Ibu Dosen Kami sampaikan terima kasih. <a href="cetakujian.php?id=<?=$ID?>&k=<?=$key?>" target="_blank">Cetak Ke PDF</a></p>
<p><br><b>Catatan:</b></p>
<p>1. Dimohon bapak/ibu dosen untuk membawa laptop dan earphone/headset.</p>
<p>2. Diharapkan bapak/ibu dosen moderator membawa converter VGA/HDMI (jika ada).</p>
</body>
</html>
