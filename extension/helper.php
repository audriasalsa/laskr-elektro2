<?php
require_once('simple_html_dom.php');

function test_uri($URI){
    $site = file_get_html($URI);
    $body = $site->find('h3[id=noaccess]');
    echo(count($body));
}

function encrypt($id){
    $id = (double)$id*525325.24;
    return base64_encode($id);
}

function decrypt($key){
    $key = base64_decode($key);
    $id = (double)$key/525325.24;
    return $id;
}

function sanitize($ID, $key){
    $ID = (int) $ID;
    $d = (int) decrypt($key);
    if($ID == $d){
        # Access granted
    } else {
        die('<h3 id="noaccess">Anda tidak diizinkan mengakses!</h3>');
    }
}

function get_data($perintahdb){
    $db = "db_tugasakhir";
    $connectdb = mysqli_connect("localhost","tugasakhir","JchJnwjSCJ0w7oom",$db);

    if (!$connectdb) {
        die( "Gagal koneksi MySQL! " . mysqli_error($connectdb) );
    }

    $bukadb = mysqli_select_db( $connectdb, $db );

    if (!$bukadb) {
        echo( "Gagal membuka database: " . mysqli_error($connectdb) . "\n" );
    }

    mysqli_query($connectdb,'SET CHARACTER SET utf8');

    if ( $querycols = mysqli_query( $connectdb, $perintahdb ) ) {

        return $querycols;

    } else {

        die("Failed Query!");
    }
}

function update_ruang($perintahdb){
    $db = "db_tugasakhir";
    $connectdb = mysqli_connect("tugasakhir.jti.polinema.ac.id","tugasakhir","JchJnwjSCJ0w7oom",$db);

    if (!$connectdb) {
        die( "Gagal koneksi MySQL! " . mysqli_error($connectdb) );
    }

    $bukadb = mysqli_select_db( $connectdb, $db );

    if (!$bukadb) {
        echo( "Gagal membuka database: " . mysqli_error($connectdb) . "\n" );
    }

    mysqli_query($connectdb,'SET CHARACTER SET utf8');

    if ( $querycols = mysqli_query( $connectdb, $perintahdb ) ) {

        return $querycols;

    } else {

        die("Failed Query!");
    }
}

?>
