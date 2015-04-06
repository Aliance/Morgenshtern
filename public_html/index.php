<?php
defined( 'APPLICATION_PATH' ) || define( 'APPLICATION_PATH', realpath( dirname( __FILE__ ) . '/../application' ) );
defined( 'APPLICATION_ENV' ) || define( 'APPLICATION_ENV', ( getenv( 'APPLICATION_ENV' ) ? getenv( 'APPLICATION_ENV' ) : 'production' ) );

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

date_default_timezone_set( 'Europe/Moscow' );
set_time_limit( 10 );

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();

$configs = array(
    'config' => array(
        APPLICATION_PATH . '/configs/application.ini',
        APPLICATION_PATH . '/configs/routes.ini',
        APPLICATION_PATH . '/configs/resources.ini'
    )
);
$application = new Zend_Application( APPLICATION_ENV, $configs );
$application->bootstrap()
            ->run();