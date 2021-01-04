<?php
require_once('helper.php');

if(isset($_GET['r']) && isset($_GET['h'])){
    $ID = filter_var($_GET['r'], FILTER_VALIDATE_INT);
    $HARI = filter_var($_GET['h'], FILTER_VALIDATE_INT);
} else {
    die('Anda tidak diizinkan mengakses!');
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Presensi Tugas Akhir | JTI-Polinema 2020</title>
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
    <style type="text/css" media="print">
        @page { size: landscape; }
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
    <p></p>
    <p style="text-align: center;padding:0px;margin:0px;"><b>DAFTAR HADIR UJIAN TUGAS AKHIR - TAHAP 2</b></p>
    <p style="text-align: center;padding:0px;margin:0px;"><b>PROGRAM STUDI D4 TEKNIK INFORMATIKA</b></p>
    <p style="text-align: center;padding:0px;margin:0px;"><b>JURUSAN TEKNOLOGI INFORMASI</b></p>
    <p style="text-align: center;padding:0px;margin:0px;"><b>TAHUN AJARAN 2019/2020</b></p>
    <?php
    if($HARI == 1) $HARI = 'Selasa, 30 Juni 2020';
    if($HARI == 2) $HARI = 'Rabu, 1 Juli 2020';
    $data_hari = get_data('SELECT `Hari`,`Nama Ruang` FROM v2_print_absensi WHERE `ID Ruang` = "1" AND `Hari` = "Selasa, 30 Juni 2020" LIMIT 1');
    if($harirow = mysqli_fetch_assoc($data_hari)){
        $hari = $harirow['Hari'];
        $ruang = $harirow['Nama Ruang'];
    }
    ?>
    <table style="width:auto;border: 0px;">
        <tr style="border: 0px;">
            <td style="border: 0px;">Hari, Tanggal</td>
            <td style="border: 0px;">:</td>
            <td style="border: 0px;"><?=$hari?></td>
        </tr>
        <tr style="border: 0px;">
            <td style="border: 0px;">Ruang</td>
            <td style="border: 0px;">:</td>
            <td style="border: 0px;"><?=$ruang?></td>
        </tr>
    </table>
    <br>
    <table>
        <tr>
            <th>No.</th>
            <th>Waktu</th>
            <th>Moderator</th>
            <th>Pengganti</th>
            <th>Tanda Tangan</th>
            <th>Penguji 1</th>
            <th>Pengganti</th>
            <th>Tanda Tangan</th>
            <th>Penguji 2</th>
            <th>Pengganti</th>
            <th>Tanda Tangan</th>
        </tr>
        <?php
        $no = 1;
        $querycols = get_data("SELECT * FROM v2_print_absensi WHERE `ID Ruang` = ".$ID." AND `Hari` = '".$HARI."' ORDER BY `v2_print_absensi`.`Waktu` ASC");
        while ( $queryrow = mysqli_fetch_assoc($querycols) ) {

            echo("<tr>
                <td><center>".$no."</center></td>
                <td>".$queryrow['Waktu']."</td>
                <td>".$queryrow['Moderator']."</td>
                <td>".''."</td>
                <td>".''."</td>
                <td>".$queryrow['Penguji 1']."</td>
                <td>".''."</td>
                <td>".''."</td>
                <td>".$queryrow['Penguji 2']."</td>
                <td>".''."</td>
                <td>".''."</td>
            </tr>");
            $no++;
        }
        ?>
    </table>
</body>
<footer>
    <script>window.print();</script>
</footer>
</html>
