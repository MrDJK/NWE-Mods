<?php
$title = getConfigValue ( 'sb_menuTitle' );
if ( !is_string ( $title ) || trim ( $title ) == '' ) {
	$title = 'Simple Banking';
}

$menuEntries[] = new MenuEntry ( $title, 'Facilities' );
?>