<?php

/*
Plugin Name: Index That
Plugin URI: http://puzzlessoftware.com/
Description: index that
Author: Puzzles Software (Wyatt Morgan)
Version: 1.2
Author URI: http://puzzlessoftware.com/2011/index-that/
*/

 
add_shortcode('index-that', 'index_that');
function index_that($params) {
    extract(shortcode_atts(array('cat' => 'Uncategorized', 'sort' => 'post_date', 'order' => 'ASC', 'col' => 1, 'perpg' => 60, 'escape' => 'false'), $params));
    if ($escape != 'true') {
	$perrow = $perpg/$col;
        $content = index_posts($cat, $perrow, $perpg, $sort, $order, $col);        
    } else {
        // We just want to display the shortcode itself, not a substitution.
        $content = '[index-that id="'.$id.'"]';
    }
    return $content;
}


function index_posts($cat, $perrow, $perpg, $sort, $order, $col) {
	global $wpdb;
	global $post;
	$querystr = "SELECT * FROM $wpdb->posts
	LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
	LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
	LEFT JOIN $wpdb->terms ON($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id)
	WHERE $wpdb->terms.name = '$cat'
	AND $wpdb->term_taxonomy.taxonomy = 'category'
	AND $wpdb->posts.post_type = 'post'
	AND $wpdb->posts.post_status = 'publish'
	ORDER BY $wpdb->posts.post_date DESC";
 	$row = mysql_query($querystr);
	$num_rows = mysql_num_rows($row);
	$total = $num_rows/$perpg;
	$pagenum =$_GET['pagenum'];
	$total= ceil($num_rows/$perpg);
	if (!(isset($pagenum))) { 
		$pagenum = 1; 
	}
	if ($pagenum < 1) { 
		$pagenum = 1; 
	} elseif ($pagenum > $total) { 
		$pagenum = $total; 
	} 
	
	$pagenum2 = $pagenum-1;
	$start = $pagenum2*$perpg;


	$querystr2 = "SELECT * FROM $wpdb->posts
	LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
	LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
	LEFT JOIN $wpdb->terms ON($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id)
	WHERE $wpdb->terms.name = '$cat'
	AND $wpdb->term_taxonomy.taxonomy = 'category'
	AND $wpdb->posts.post_type = 'post'
	AND $wpdb->posts.post_status = 'publish'
	ORDER BY $wpdb->posts.$sort $order LIMIT $start, $perpg";

$pageposts = $wpdb->get_results($querystr2, OBJECT);

global $post; 
$count = 0; 


// begin pagination displays
$content = '<div style="text-align:center;"> --Page '.$pagenum.' of '.$total.'-- <p>';
 $thisurl=selfURL();
 // First we check if we are on page one. If we are then we don't need a link to the previous page or the first page so we do nothing. If we aren't then we generate links to the first page, and to the previous page.
 if ($pagenum == 1) {
 	$content .= '<<-First  ';
 	$previous = $pagenum-1;
 $content .= '<-Previous ';
 } else {
	$content .= " <a href='$thisurl?pagenum=1'> <<-First</a>  ";
 	$previous = $pagenum-1;
 $content .= " <a href='$thisurl?pagenum=$previous'> <-Previous</a> ";
 } 

 //just a spacer
 $content .= " ---- ";

 //This does the same as above, only checking if we are on the last page, and then generating the Next and Last links
 if ($pagenum == $total)  {
 $next = $pagenum+1;
 $content .= " Next -> ";
 $content .= " ";
 $content .= " Last ->> ";
 } else {
 $next = $pagenum+1;
 $content .= " <a href='$thisurl?pagenum=$next'>Next -></a> ";
$content .= " ";
$content .=" <a href='$thisurl?pagenum=$total'>Last ->></a> ";
 } 
$content .= '</div>';

if ($total <= 1) {
	$content = '';
} 
// end pagination displays

$divwidth = 100/$col;

$content .= '<div style="float:left; width:'.$divwidth.'%;">';

$divs = 1;
foreach ($pageposts as $post):
setup_postdata($post); 
if ($count<$perrow) {
	//$title = $post['Post_title'];
	
 	$content .= '<a href="'.get_page_link($post->ID);
	$content.'" rel="bookmark" title="Permanent Link to '.$post->post_title;
	$content .= '"> '.$post->post_title;
	$content .= '</a><br />';
	$count=$count+1;
} else if ($count==$perrow) { 
	
	
	$content .= '</div><div style="float:left; width: '.$divwidth.'%; "><a href="'.get_page_link($post->ID).'" rel="bookmark" title="Permanent Link to '.$post->post_title.'"> '.$post->post_title.'</a><br />';
	$count = 1;
	$divs=$divs+1;
	
}
endforeach;
while ($divs < $col) {
	$content .= '</div><div style="float:left; width: '.$divwidth.'%;"><ul></ul>';
	$divs=$divs+1;
}

$content .= '</div><div style="clear: both;"> </div>';


return $content;

}

function selfURL() {
	$full = explode("?", $_SERVER['REQUEST_URI']);
	return $full[0];
}



?>
