<style>
    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        padding: 3px;
    }
    th{
        background-color: darkgray;
    }
</style>
<h3>Jadwal Ujian TA Per Dosen - Tahap 2 - Dibuat 29 Juni 2020</h3>
<table>
    <tr>
        <th>No.</th>
        <th>ID</th>
        <th>Nama Dosen</th>
        <th>Key</th>
        <th>Result Key</th>
        <th>URI</th>
        <th>WA</th>
    </tr>
<?php
require_once('helper.php');

$dosencols = get_data('select a.a, c.id, c.nomor_ponsel from (
    SELECT Moderator as a FROM db_tugasakhir.jadwal_skripsi_tahap_2
     union  select Penguji1 as a from db_tugasakhir.jadwal_skripsi_tahap_2
     union select Penguji2 as a from db_tugasakhir.jadwal_skripsi_tahap_2) a
    join db_tugasakhir.jadwal_skripsi_tahap_2 b on a.a = b.Moderator or a.a = b.Penguji1 or a.a = b.Penguji2 
    join db_tugasakhir.v2_dosen c on a.a = c.nama
    GROUP by a.a, c.id');
$no = 1;
while ( $dosenrow = mysqli_fetch_assoc($dosencols) ) {
    $URI = "http://".$_SERVER['HTTP_HOST']."/v2/extension/ujian.php?id=".$dosenrow['id']."&k=".encrypt($dosenrow['id']);
    $text = urlencode("Yth. Bapak dan Ibu dosen Pembimbing LA/TA 2019-2020, berikut kami sampaikan jadwal menguji Bapak dan Ibu yang dapat dilihat di laman di bawah ini. Terima kasih.\n ".$URI);
    echo("<tr>
        <td>".$no."</td>
        <td>".$dosenrow['id']."</td>
        <td>".$dosenrow['a']."</td>
        <td>".encrypt($dosenrow['id'])."</td>
        <td>".decrypt(encrypt($dosenrow['id']))."</td>
        <td><a href='".$URI."'>Tautan</a></td>
        <td><a href='https://api.whatsapp.com/send?phone=".$dosenrow['nomor_ponsel']."&text=".$text."'>WA</a></td>
    </tr>");
    $no++;
    #usleep(300000);
}
#test_uri('http://localhost/skripsi/index.php?id=66&k=MzQ2NzE0NjUuODQ=');<td>".test_uri($URI)."</td>
?>
</table>
