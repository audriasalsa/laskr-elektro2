<?php

$GLOBALS['connectdb'] = mysqli_connect("localhost","tugasakhir","JchJnwjSCJ0w7oom","db_tugasakhir");

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

function display_error()
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

function get_data($perintahdb){

    display_error();

    if (!$GLOBALS['connectdb']) {
        die( "Gagal koneksi MySQL! " . mysqli_error() );
    }

    $bukadb = mysqli_select_db($GLOBALS['connectdb'], "db_tugasakhir" );

    if (!$bukadb) {
        echo( "Gagal membuka database: " . mysqli_error() . "\n" );
    }

    mysqli_query($GLOBALS['connectdb'],'SET CHARACTER SET utf8');

    if ( $querycols = mysqli_query($GLOBALS['connectdb'], $perintahdb ) ) {

        return $querycols;

    } else {

        die("Failed Query!");
    }
}

?>