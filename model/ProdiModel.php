<?php


namespace model;


use m\Model;

class ProdiModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_prodi');
    }

    public static function isD3($kodeProdi)
    {
        return (explode('-', $kodeProdi))[0] == 'D3';
    }

    public static function isD4($kodeProdi)
    {
        return (explode('-', $kodeProdi))[0] == 'D4';
    }
}