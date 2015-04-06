<?php

class PrintController extends Zend_Controller_Action 
{
    protected $_bootstrap = null;
    function init()
    {
        $this->_bootstrap = $this->getInvokeArg( 'bootstrap' );

		$this->getResponse()->setHeader( 'Content-Type', 'text/html; charset=utf-8' );
		
		$this->_helper->_layout->setLayout('print');
    }

    public function invitationAction() 
    {
        $this->view->headLink()->setStylesheet( '//static.lesnykh.ru/css/classica.css', 'screen' );
        $this->view->headStyle()->appendStyle('h1 { font-family: "Classica Two"; font-size: 29px; text-align: center; font-weight: normal; }');
        $this->view->headStyle()->appendStyle('.text { font-family: "Classica Two"; font-size: 26px; }');
    }

    public function seatingAction() 
    {
        $this->view->headLink()->setStylesheet( '//static.lesnykh.ru/css/classica.css', 'screen' );
        $this->view->headStyle()->appendStyle('.block { font-family: "Classica Two", cursive; font-size: 0.72cm; text-align: center; float: left; padding: 1cm 0; width: 8.85cm; border: 1px solid #eee; border-bottom: none; }');
        $this->view->headStyle()->appendStyle('.block:nth-child(even) { border-left: none; }');
        $this->view->headStyle()->appendStyle('.block:nth-child(4n+3), .block:nth-child(4n+4) { border-bottom: 1px solid #eee; }');
    }

    public function tableAction() 
    {
        $this->view->headLink()->setStylesheet( 'http://fonts.googleapis.com/css?family=Pinyon+Script', 'screen' );
        $this->view->headStyle()->appendStyle('.page { font-family: "Pinyon Script", cursive; font-size: 10cm; text-align: center; }');
    }
}
