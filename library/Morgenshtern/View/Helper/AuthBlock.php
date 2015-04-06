<?php
/**
 * View-хелпер для формирования авторизационной формы
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
class Morgenshtern_View_Helper_AuthBlock extends Zend_View_Helper_Abstract
{
    private $_view;

	/**
	 * Получает объект вида и записывает его
	 *
	 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
	 * @copyright	Copyright (c) 2010, Webdoka
	 * @version		1.0.0
	 */
    public function setView( Zend_View_Interface $view ) {
        $this->_view = $view;
    }
	/**
	 * Данный хелпер формирует авторизационную форму,
	 * исходя из того залогинен ли пользователь или нет.
	 * – Если залогинен - то рендерит представление с приветствием
	 * и передает туда переменную, содержащую email пользователя
	 * – Если не залогинен - то рендерит представление с авторизационной формой
	 *
	 * @see			$view->authBlock()
	 *
	 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
	 * @copyright	Copyright (c) 2010, Webdoka
	 * @version		1.0.0
	 */
	public function authBlock() {
		if ( $this->_view->auth->hasIdentity() ) {
			$user = $this->_view->auth->getIdentity();
			$char = new Morgenshtern_Char();
			$login = $char->format( (int) $user->id );
			return $this->_view->partial( 'user.phtml', array( 'login' => $login ) );
		} else {
			return $this->_view->partial( 'guest.phtml' );
		}
    }
}