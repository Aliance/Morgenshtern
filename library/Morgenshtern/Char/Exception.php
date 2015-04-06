<?php

/**
 * Exception for Morgenshtern_Char class.
 *
 * @category   Morgenshtern
 * @package    Morgenshtern_Char
 * @copyright  Copyright (c) 2010 Aliance spb (http://www.morgenshtern.com)
 * @license    http://www.gnu.org/copyleft/lesser.html     LGPL
 */
class Morgenshtern_Char_Exception extends Exception
{
    /**
     * Construct the exception
     *
     * @param  string $msg
     * @return void
     */
    public function __construct( $msg = '' )
    {
        parent::__construct( $msg );
    }
}