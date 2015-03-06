<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* NOTE: THIS MODULE REQUIRES AN INSTALLED VirtueMart Component!
*
* @module Phoca - VirtueMart Category List
* @copyright Copyright (C) Jan Pavelka www.phoca.cz
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @component - VirtueMart
* @copyright (C) 2004-2008 soeren - All Rights Reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/

// Load the virtuemart main parse code
if( file_exists(dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' )) {
	require_once( dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' );
	$mosConfig_absolute_path = realpath( dirname(__FILE__).'/../..' );
} else {
	require_once( dirname(__FILE__).'/../components/com_virtuemart/virtuemart_parser.php' );
}

//$category_id = vmGet( $_REQUEST, 'category_id');

global $VM_LANG, $sess;
if( vmIsJoomla('1.5' )) {
	$vm_path = $mosConfig_absolute_path.'/modules/mod_virtuemart';
} else {
	$vm_path = $mosConfig_absolute_path.'/modules';
}

require_once(CLASSPATH.'ps_product.php');
// PARAMS
$numColumns 	= $params->get('columns_num', 3);
$displayImg 	= $params->get('display_img', 1);
		
$categories 	= array();
$ip 			= 0;
$categoriesAll 	=	getPhocaCategoryTreeArray( );
foreach ($categoriesAll as $key => $value){
	$categories[$ip]['category_name'] 			= $value['category_name'];
	$categories[$ip]['category_child_id'] 		= $value['category_child_id'];
	$categories[$ip]['category_thumb_image'] 	= $value['category_thumb_image'];
	$ip++;
}

$columns 			= (int)$numColumns;
$countCategories 	= count($categories);
$begin				= array();
$end				= array();
$begin[0]			= 0;// first
$begin[1]			=  ceil($countCategories / $columns);
$end[0]				= $begin[1] -1;

for ( $jp = 2; $jp < $columns; $jp++ ) {
	$begin[$jp]	= ceil(($countCategories / $columns) * $jp);
	$end[$jp-1]	= $begin[$jp] - 1;
}

$end[$jp-1]		= $countCategories - 1;// last
$endFloat		= $countCategories - 1;


for ($kp = 0; $kp < $countCategories; $kp++) {
	if ( $columns == 1 ) {
		echo '<table border="0">';
	} else {
		$float = 0;
		foreach ($begin as $key2 => $value2)
		{
			if ($kp == $value2) {
				$float = 1;
			}
		}
		if ($float == 1) {		
			echo '<div style="position:relative;float:left;margin:10px;"><table border="0">';
		}
	}

	echo '<tr>';
	
	if ($displayImg == 2) {
		echo '<td>'. ps_product::image_tag( $categories[$kp]["category_thumb_image"], "alt=\"".$categories[$kp]["category_name"]."\"", 0, "category") . '</td>';
	} else if ($displayImg == 1) {
		echo '<td>'. JHTML::_('image', 'modules/mod_virtuemart_category_list/assets/images/icon-folder-small.png', $categories[$kp]['category_name']). '</td>';
	}
	echo '<td align="left" valign="middle" ><a href="'. $sess->url( URL .'index.php?page=shop.browse&amp;category_id=' . $categories[$kp]['category_child_id']). '">'.$categories[$kp]['category_name'].'</a></td>';
	echo '</tr>';
	
	if ( $columns == 1 ) {
		echo '</table>';
	} else {
		if ($kp == $endFloat) {
			echo '</table></div><div style="clear:both"></div>';
		} else {
			$float = 0;
			foreach ($end as $k => $v)
			{
				if ($kp == $v) {
					$float = 1;
				}
			}
			if ($float == 1) {		
				echo '</table></div>';
			}
		}
	}
}

function getPhocaCategoryTreeArray( $only_published=true, $keyword = "" ) {

	$db = new ps_DB;

	// Get published categories
	$query  = "SELECT category_id, category_description, category_name,category_child_id as cid, category_parent_id as pid,list_order, category_publish, category_thumb_image
				FROM #__{vm}_category, #__{vm}_category_xref WHERE ";
	if( $only_published ) {
		$query .= "#__{vm}_category.category_publish='Y' AND ";
	}
	$query .= "#__{vm}_category.category_id=#__{vm}_category_xref.category_child_id ";
	if( !empty( $keyword )) {
		$query .= "AND ( category_name LIKE '%$keyword%' ";
		$query .= "OR category_description LIKE '%$keyword%' ";
		$query .= ") ";
	}
	$query .= "ORDER BY #__{vm}_category.list_order ASC, #__{vm}_category.category_name ASC";

	// initialise the query in the $database connector
	// this translates the '#__' prefix into the real database prefix
	$db->query( $query );

	$categories = Array();
	// Transfer the Result into a searchable Array

	while( $db->next_record() ) {
		$categories[$db->f("cid")]["category_child_id"] = $db->f("cid");
		$categories[$db->f("cid")]["category_parent_id"] = $db->f("pid");
		$categories[$db->f("cid")]["category_name"] = $db->f("category_name");
		$categories[$db->f("cid")]["category_description"] = $db->f("category_description");
		$categories[$db->f("cid")]["list_order"] = $db->f("list_order");
		$categories[$db->f("cid")]["category_publish"] = $db->f("category_publish");
		$categories[$db->f("cid")]["category_thumb_image"] = $db->f("category_thumb_image");
	}
		
	return $categories;
}
?>