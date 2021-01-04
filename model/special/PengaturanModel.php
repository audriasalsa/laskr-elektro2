<?php

namespace model\special;

use m\Model;

class PengaturanModel extends Model
{
    private static $_instance;

    public function __construct()
    {
        parent::__construct('v2_pengaturan');
    }

    public function getLatest()
    {
        $sql = "SELECT * FROM {$this->tableName} ORDER BY id DESC LIMIT 1";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function getTahunProposalSekarang()
    {
        $latest = $this->getLatest();

        return $latest['tahun_proposal_sekarang'];
    }

    public static function getInstance()
    {
        if(self::$_instance == null)
            self::$_instance = new PengaturanModel();

        return self::$_instance;
    }

    public static function createPredicateTahunProposalSekarang($comparedColumnName, $logicOperator = 'AND')
    {
        $p = PengaturanModel::getInstance();

        $tahunProposal = $p->getTahunProposalSekarang();

        // WHERE (.....)
        $predicate = "$logicOperator ($comparedColumnName = '{$tahunProposal}')";

        return $predicate;
    }
}