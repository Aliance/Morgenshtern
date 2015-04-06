<?php
final class ErrorController extends Zend_Controller_Action
{
    public function getLog()
	{
        $bootstrap = $this->getInvokeArg( 'bootstrap' );
        if ( ! $bootstrap->hasPluginResource( 'Logger' ) ) {
            return false;
        }
        $log = $bootstrap->getResource( 'Logger' );
        return $log;
    }
    public function errorAction()
	{
        $errors = $this->_getParam( 'error_handler' );

        switch ( $errors->type ) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode( 404 );
                $this->view->message = 'Страница не найдена';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode( 500 );
                $this->view->message = 'сбой в работе скрипта';
                break;
        }

        // Log exception, if logger available
        if ( $log = $this->getLog() ) {
            $log->crit( $this->view->message, $errors->exception );
        }

        // conditionally display exceptions
        if ( $this->getInvokeArg( 'displayExceptions' ) == true ) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request   = $errors->request;
    }
	public function accessDenyAction()
	{
	}
}