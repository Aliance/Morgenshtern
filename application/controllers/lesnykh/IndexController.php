<?php

class IndexController extends Zend_Controller_Action 
{
    protected $_bootstrap = null;
    function init()
    {
        $this->_bootstrap = $this->getInvokeArg( 'bootstrap' );

		$this->getResponse()->setHeader( 'Content-Type', 'text/html; charset=utf-8' );
    }
    public function indexAction() 
    {
        $this->view->headLink()->appendStylesheet( '//static.lesnykh.ru/css/wedding.styles.css', 'screen' );
        //$this->view->headScript()->appendFile( '/js/paginator.js', 'text/javascript' );
    }
}
