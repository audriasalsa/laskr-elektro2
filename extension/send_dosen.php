<?php
require_once('helper.php');

// http://tugasakhir.jti.polinema.ac.id/v2/extension/

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
</head>

<body>
    <?php    
    $dosencols = get_data('SELECT no,nama_dosen FROM data_dosen WHERE no = '.$ID);
    $nama_dosen = '';
    if($dosenrow = mysqli_fetch_assoc($dosencols)){
        $nama_dosen = $dosenrow['nama_dosen'];
        echo("<p>Yth. Bapak/Ibu Dosen <b>" . $nama_dosen."</b><br>Berdasarkan hasil rapat panitia skripsi dan pertimbangan oleh Sekretaris Jurusan, maka ditetapkan dosen pembimbing untuk Skripsi D4 Teknik Informatika Tahun Ajaran 2019/2020 sebagai berikut.</p>");
        echo("<p>http://".$_SERVER['HTTP_HOST']."/v2/extension/index.php?id=".$dosenrow['no']."&k=".encrypt($dosenrow['no'])."</p>");
    }
    ?>
    <p>Disahkan tanggal 27 Februari 2020 dan telah diperiksa oleh Sekretaris Jurusan Teknologi Informasi. Atas perhatian Bapak/Ibu Dosen Kami sampaikan terima kasih.</p>
</body>
</html>

<?php mysqli_close($GLOBALS['connectdb']); ?>