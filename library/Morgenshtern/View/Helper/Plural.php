<?php
class Morgenshtern_View_Helper_Plural extends Zend_View_Helper_Abstract
{
    public function plural( $n, $form1, $form2, $form3 )
	{
        $plural = ($n % 10 == 1 && $n % 100 != 11 
				? 0 : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 or $n % 100 >= 20) 
				? 1 : 2));
        switch ( $plural ) {
            case 0:
            default:
                return $form1;
            case 1:
                return $form2;
            case 2:
                return $form3;
        }
    } 
}