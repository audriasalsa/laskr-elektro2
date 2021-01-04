<?php

namespace model;

use lib\AppUtil;
use m\Model;
use m\Util;
use model\special\PengaturanModel;

class EventModel extends Model
{
    const KATEGORI_VERIFIKASI_GRUP_RISET = 'verifikasi_grup_riset';
    const KATEGORI_SEMINAR_PROPOSAL = 'seminar_proposal';
    const KATEGORI_UJIAN_AKHIR = 'ujian_akhir';

    public function __construct()
    {
        parent::__construct('v2_event');
    }

    public function findByKategori($kategori, $currentPeriodeOnly = true)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE kategori = '$kategori'";

        //pre_print($sql);

        if($currentPeriodeOnly)
        {
            $currentProposalPeriod = PengaturanModel::getInstance()->getTahunProposalSekarang();
            $sql .= " AND periode_proposal = {$currentProposalPeriod}";
        }

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result;

        return null;
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = '$id'";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result[0];

        return null;
    }

    public static function isActive($eventData)
    {
        $start = $eventData['tanggal_mulai'];
        $end   = $eventData['tanggal_selesai'];

        $startPassed     = AppUtil::dateIsStartedOrPassed($start);
        $endNotYetPassed = AppUtil::dateIsNotYetPassed($end, true);

        /*
        if($startPassed)
            pre_print('Sudah dimulai');

        if($endNotYetPassed)
            pre_print('Belum berakhir!');

        if($startPassed && $endNotYetPassed)
            pre_print('Masih aktif!!!');
        */

        return $startPassed && $endNotYetPassed;
    }

    public static function isStarted($eventData)
    {
        $start = $eventData['tanggal_mulai'];

        return AppUtil::dateIsStartedOrPassed($start);
    }

    public static function isEnded($eventData)
    {
        $end = $eventData['tanggal_selesai'];

        return !(AppUtil::dateIsNotYetPassed($end, true));
    }

    public function findIdNamaPairs($kategori = 'seminar_proposal')
    {
        $sql = "SELECT id, nama FROM {$this->tableName} WHERE kategori = '$kategori'";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
        {
            $pairs = array();

            foreach ($result as $row)
            {
                $pairs[($row['id'])] = $row['nama'];
            }

            return $pairs;
        }

        return null;
    }

    public function findLatestActiveEvent($kategori = 'ujian_akhir')
    {
        $sql = "SELECT * FROM v2_event WHERE NOW() >= tanggal_selesai AND kategori = '$kategori' ORDER BY tanggal_selesai DESC LIMIT 1;";

        $result = $this->executeReadSQL($sql);

        if(count($result) < 1)
            return null;

        return $result;
    }

    public function findLatestByKategori($kategori = self::KATEGORI_UJIAN_AKHIR)
    {
        $sql = "SELECT * FROM v2_event WHERE kategori = '$kategori' ORDER BY id DESC LIMIT 1;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function findSecondLatestByKategori($kategori = self::KATEGORI_UJIAN_AKHIR)
    {
        $sql = "SELECT * FROM v2_event WHERE kategori = '$kategori' ORDER BY id DESC LIMIT 2;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 2)
            return null;

        return $records[1];
    }

    public function addEventNamesToFilterArray(array $filterArray, $kategori = 'ujian_akhir', $filterKey = 'tahap_ujian', $currentPeriodOnly = false)
    {
        // Add Event
        $ujianEvents = $this->findByKategori($kategori, $currentPeriodOnly);

        // pre_print($ujianEvents);

        if($ujianEvents == null)
            return $filterArray; // Dont do anything

        $eventNames = Util::arrayTableRemoveSomeColumns($ujianEvents, ['id', 'deskripsi', 'kategori', 'tanggal_mulai', 'tanggal_selesai']);

        for($i = 0; $i < count($eventNames); $i++)
        {
            $filterArray[$i][$filterKey] = $eventNames[$i]['nama'];
        }

        return $filterArray;
    }

    public function isValid($idEvent, $eventType)
    {
        $sql = "SELECT * FROM v2_event WHERE id = '$idEvent';";

        $records = $this->executeReadSQL($sql);

        if(count($records) > 0)
        {
            $eventData = $records[0];

            $valid = self::isActive($eventData);

            if($valid)
            {
                if($eventData['kategori'] == $eventType)
                    return true;
            }
        }

        return false;
    }
}