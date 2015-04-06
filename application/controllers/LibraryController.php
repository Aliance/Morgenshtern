<?php

class LibraryController extends Zend_Controller_Action 
{
    function init()
    {
        $this->getResponse()->setHeader( 'Content-Type', 'text/html; charset=utf-8' );
    }
    public function indexAction() 
    {
        //
    }
    public function encyclopediaAction() 
    {
		#$this->view->headLink()->appendStylesheet( '/css/index.charter.css', 'screen' );
		#$this->view->headScript()->appendFile( '/js/charter.js', 'text/javascript' );
		$this->view->headTitle()->prepend( 'Энциклопедия предметов' );
		
		$itemModel = new Application_Model_Item;
		$this->view->items = $itemModel->getItems();
    }
    public function experienceAction() 
    {
		$this->view->headScript()->appendFile( '/js/library.experience.js', 'text/javascript' );
		$this->view->headLink()->appendStylesheet( '/css/global.table.css', 'screen' );
		$this->view->headTitle()->prepend( 'Таблица опыта' );
    }
    public function towerAction() 
    {
		$this->view->headTitle()->prepend( 'Карта Башни Смерти' );
    }
    public function clansAction() 
    {
		$this->view->headTitle()->prepend( 'Список кланов' );
    }
    public function metallAction() 
    {
		$this->view->headTitle()->prepend( 'Повелитель Металла' );
    }
}
