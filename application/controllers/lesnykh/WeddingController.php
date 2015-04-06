<?php

class WeddingController extends Zend_Controller_Action 
{
    protected $_bootstrap = null;
    function init()
    {
        $this->_bootstrap = $this->getInvokeArg( 'bootstrap' );

		$this->getResponse()->setHeader( 'Content-Type', 'text/html; charset=utf-8' );
		
		$this->view->headTitle()->set('Свадьба Лесных Ильи');
    }

    public function indexAction() 
    {
        $this->view->headLink()->appendStylesheet( '//static.lesnykh.ru/css/wedding.styles.css', 'screen' );
    }

    public function spendingAction() 
    {
        $this->view->headLink()->appendStylesheet( '//static.lesnykh.ru/css/wedding.styles.css', 'screen' );
        
        /**
		 * @var array[]
		 * - категория      string
		 * - запланированно array|int
		 * - фактически     array|int
		 * - оплачено       int
		 */
		$this->view->expenses = array(
			array('Банкет', 110000, 110000, 110000),
			array('Ведущий', 45000, 45000, 10000),
			array('Мальчишник', 10000, array(26123, '20.300 сауна + 2.113 алкоголь + 3.710 Хаус Бар'), 26123),
			array('Алкоголь', 20000, array(25154, '8.094 Jack Daniels + 4.457 Mondoro Asti + 7.109 Jim Beam + 3.060 Veuve Clicquot + 2.434 кола и вино'), 25154),
			array('Выездная регистрация', array(20000, 50000), array(16300, '800 столик + 2.000 дорожка + 13.500 арка'), array(8000, '0 столик + 0 дорожка + 8.000 арка')),
			array('Декорации', 15000, array(15360, '15.000 цветы + 360 зеркала'), 15360),
			array('Девичник', 10000, 15300, 15300),
			array('Фотограф', array(15000, 20000), 15000, 2000),
			array('Костюм', array(10000, 20000), 13997, 13997),
			array('Платье', array(20000, 35000), 10750, 10750),
			array('Кольца', 15000, 6929, 6929),
			array('Ведущая церемонии', 5000, array(6300, '5.500 церемония + 800 выезд'), 2000),
			array('Фокусник', 6000, 6000, 6000),
			array('Туфли (м)', array(2000, 10000), 3320, 3320),
			array('Приглашения', 5000, array(1409, '160 ленты + 1249 бумага/ножницы/клей'), 1409),
			array('Туфли (ж)', array(2000, 10000), 1300, 1300),
			array('Фуршетные столы', 5000, 0, 0),
			array('Трансфер', 5000, 4000, 0),
			array('Видео оператор', array(10000, 15000), 0, 0),
			//array('Торт', 5000, 0, 0),
			//array('Фейерверк', 10000, 0, 0),
		);
		
		$this->view->total = array(array(0, 0), 0, 0);
    }
}
