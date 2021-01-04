<?php
require_once('helper.php');

if(isset($_GET['id'])){
    $ID = filter_var($_GET['id'], FILTER_VALIDATE_INT);
} else {
    die('Anda tidak diizinkan mengakses!');
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Distribusi Pembimbing Skripsi - D4 Teknik Informatika | JTI-Polinema 2020</title>
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
            PANITIA SKRIPSI D4 TEKNIK INFORMATIKA - JURUSAN TEKNOLOGI INFORMASI<br>
            Jalan Soekarno-Hatta No.9 Malang 65141 Telp (0341) 404424</b></td>
            <td style="text-align: center;"><img style="height:33%;" src="img/logo_iso.jpg" alt="Kop Surat"></td>
        </tr>
    </table>
    <p style="text-align: center;"><b>DISTRIBUSI PEMBIMBING SKRIPSI</b></p>
    <?php
    $querycols = get_data('SELECT nim,nama,judul,pembimbing1,pembimbing2,grup_riset FROM `data_bimbingan` WHERE nim="'.$ID.'" AND hasil_seminar = "Diterima dengan revisi"');
    if ( $queryrow = mysqli_fetch_assoc( $querycols) ) {
        echo("<p>Yth. Sdr./Sdri. <b>" . ucwords(strtolower($queryrow['nama'])) . "</b><br>Berdasarkan hasil rapat panitia skripsi dan pertimbangan oleh Sekretaris Jurusan, maka ditetapkan dosen pembimbing untuk Skripsi D4 Teknik Informatika Tahun Ajaran 2019/2020 sebagai berikut.</p>");
    ?>
    <br>
    <table>
        <tr>
            <th>No.</th>
            <th>NIM</th>
            <th>Nama</th>
            <th>Judul Skripsi</th>
            <th>Pembimbing 1</th>
            <th>Pembimbing 2</th>
            <th>Grup Riset</th>
        </tr>
        <?php
            echo("<tr>
                <td><center>1</center></td>
                <td>".$queryrow['nim']."</td>
                <td>".ucwords(strtolower($queryrow['nama']))."</td>
                <td>".ucwords(strtolower($queryrow['judul']))."</td>
                <td>".$queryrow['pembimbing1']."</td>
                <td>".$queryrow['pembimbing2']."</td>
                <td>".ucwords(strtolower($queryrow['grup_riset']))."</td>
            </tr>");
        } else {
            die("<tr>Mohon maaf, Anda belum lulus seminar proposal atau belum terekam dalam sistem kami.</tr>");
        }
        ?>
    </table>
    <p>Disahkan tanggal 27 Februari 2020 dan telah diperiksa oleh Sekretaris Jurusan Teknologi Informasi. Atas perhatian Sdr./Sdri. Kami sampaikan terima kasih.</p>
    <p id="footer"><img src="img/footer.jpg" alt="ttd"></p>
</body>
</html>

<?php mysqli_close($GLOBALS['connectdb']); ?>