<?php
/**
 * Модель
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
class Application_Model_Diplomacy extends Zend_Db_Table_Abstract {
    protected $_name = 'diplomacy';
	protected $_referenceMap    = array(
        'diplomacy' => array(
            'columns'           => 'clan_id',
            'refTableClass'     => 'Application_Model_Clans',
            'refColumns'        => 'id'
        )
    );
	public function getAllies()
	{
		$rows = $this->fetchAll();
		$data = array();
		foreach ( $rows as $row ) {
			$info = $row->findParentRow( 'Application_Model_Clans', 'diplomacy' );
			$clan = array(
				'clan'  => $info['clan'],
				'title' => $row['clan'],
				'union' => $row['union'],
				'web'   => $row['web']
			);
			array_push( $data, $clan );
		}
		return $data;
	}
}