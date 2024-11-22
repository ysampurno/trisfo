<?php
/**
PHP Admin Template 
Author	: Agus Prawoto Hadi
Year	: 2021
*/

function set_select($name, $value) 
{
	if (@$_REQUEST['name'] == $value) {
		return 'selected="selected"';
	}
	return '';
}