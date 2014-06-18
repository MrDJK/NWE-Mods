<?php

$interest = ( int ) getConfigValue ( 'sb_interestPerc' );
$interestRequired = ( int ) getConfigValue ( 'sb_interestRequiredAmount' );
if ( $interest > 0 ) {
	$query = 'UPDATE user_variables SET value = value + FLOOR( ( value / 100 ) * ? ) WHERE ( value > ? && variable_id = ? )';
	$db->Execute ( $query, $interest, $interestRequired, sb_bankAmount );
}
