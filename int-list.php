<?php
/*
Plugin Name: Interesting Links List
Plugin URI: http://wp.linkzone.ro/interesting-links-list/
Author: Madalin F. 
Author URI: http://wp.linkzone.ro/
Description: Show in post or page, a list of links you choose and let any visitor contribute. To use it insert "[interesting]" in any post or page body and you're ready to go. For template use <code>&lt;?php show_interesting_links(); ?&gt;</code> .
Version: 0.3.5
Change Log:

2012-07-23  0.3.5:
* ability to generate as many list as the user wants
* multiple lists, can display one specific list [interesting  name={list name}]
* form can be disable on [interesting  name={list name} form=0]
* added pagination


2012-07-17  0.3:
* update from oldversion

2010-04-08  0.2.20:
* back to development 
* tested with WordPress Version 3.4.1  

2010-04-08  0.2.15: 
* anti-spam measures taken, no HTML tags allowed in URL title

2010-03-14  0.2.14: 
* e-mail notification of new link submissions
* strip slashes for special characters

2010-02-22  0.2.13: 
 - fix duplicate from submit bug

2010-02-22  0.2.12: 
More admin options
* jQuery can be enabled/disabled 
* "Submitted by" under each link can be shown or hidden

2010-02-18  0.2.11: updated css path
2010-02-15  0.1: First release
*/

add_action('admin_menu', 'i_list_menu');

function i_list_menu(){
	add_options_page('I-List Options', 'Interesting List Options', 'administrator', 'i_list_unique', 'i_list_options');
}

function i_list_options(){
	global $wpdb,$HTTP_POST_VARS;

	$i=1;$a=0;
	if($_POST['addnewform']){
		$f_q= $HTTP_POST_VARS;
			$form_questions["name"]=$f_q["name"];
			$form_questions["mailu"]=$f_q["mailu"];
			$form_questions["description"]=$f_q["description"];
			$form_questions["url"]=$f_q["url"];
			$form_questions["dsp"]=$f_q["dsp"];
			$form_questions["newlist"]=$f_q["newlist"];
		// Get them into the database
			$wpdb->insert($wpdb->prefix."i_list",array('name'=>$form_questions["name"], 'mailu'=>$_POST['mailu'], 'text'=> $form_questions["description"], 'url'=>$form_questions["url"],'dsp'=>$form_questions["dsp"],'l_name'=>$form_questions["newlist"]));
	}
	if($_POST['updateform']>0){
		$wpdb->update($wpdb->prefix."i_list",array('name'=>$_POST['name'],'dsp'=>$_POST["dsp"], 'mailu'=>$_POST['mailu'],'text'=>$_POST["description"], 'url'=>$_POST["url"], 'iorder'=>$_POST['order']),array( 'id' => $_POST['updateform'] ));
		$update_confirm = '<div id="message" class="updated fade"><p><strong>'.stripslashes($_POST['description']).' was updated successfully.</strong></p></div>';
	}
	if($_GET['update_order_i']){
		$myilink = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."i_list WHERE id =".$_GET['i_list_id']);
		$neword = $myilink->iorder + $_GET['update_order_i'];
		$wpdb->update($wpdb->prefix."i_list",array('iorder'=>$neword ),array( 'id' => $_GET['i_list_id'] ));
	}
	if($_GET['i_ls_mailconf']){
		$wpdb->query("UPDATE ".$wpdb->prefix."i_list SET dsp = 1 WHERE id = '".$_GET['i_ls_mailconf']."' ");
	}
	if($_GET['delete']){
		$wpdb->query("DELETE FROM ".$wpdb->prefix."i_list WHERE id = '".$_GET['delete']."'");
	}
	if($_POST['i_list_options']){
		$new = array("i_list_s_title" => $_POST['i_list_s_title'], "i_list_p_title" =>$_POST['i_list_p_title'], "i_list_form" => $_POST['i_list_form'], "i_list_contributor" => $_POST['i_list_contributor']);
update_option('i_list_all_options', $new);
		$update_confirm = '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
 
	}
	$i_options = get_option("i_list_all_options");
	if ($i_options['i_list_form'] == "no") { 
		$form = '<option value="yes" >Yes</option><option value="no" selected="selected">No</option>';
	 }
	else {
		$form = '<option value="yes" selected="selected" >Yes</option><option value="no" >No</option>'; 
	}

	if ($i_options['i_list_contributor'] == "no") { 
		$contr = '<option value="yes" >Yes</option><option value="no" selected="selected">No</option>';
	 }
	else {
		$contr= '<option value="yes" selected="selected" >Yes</option><option value="no" >No</option>'; 
	}

	echo '<div class="wrap">
			<h2>Interesting LINKS List</h2>
			<form method="post" action="options-general.php?page=i_list_unique">
				' .$update_confirm . ' 
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Your list name (will be displayed on top of your list)</th>
						<td width="200"><input type="text" name="i_list_p_title" value="'. stripslashes($i_options['i_list_p_title']) .'" size="30" /></td>


						<th scope="row" width="300" >Enable jQuery in Submit Form <br> (Produces sliding labels; may conflict with other plug-ins) </th>
						<td ><select name="i_list_form">'. $form .' </select></td>

					</tr>
 
					<tr valign="top">
						<th scope="row">Submit form text</th>
						<td><input type="text" name="i_list_s_title" value="'. stripslashes($i_options['i_list_s_title']).'" size="30" /></td>

						<th scope="row">Credit link contributors <br> (Submitted by: ...) </th>
						<td ><select name="i_list_contributor">'. $contr .' </select></td>
					</tr>

				</table>

				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="i_list_options" value="i_list_all_options" />

					<p class="submit">
					<input type="submit" class="button-primary" value="Save Changes" />
					</p>

</form>
<h2><b>Add New Item to List</b></h2>
			<form method="post" action="">
			<table class="form-table">
				<tr>
					<th scope="col">Name</th>
					<th scope="col">Mail</th>
					<th scope="col">URL Title</th>
					<th scope="col">URL</th>
					<th scope="col">Display</th>
					<th scope="col">List Name</th>
				</tr>
				<tr>
					<td><input type="text" name="name" size="25" /></td>
					<td><input type="text" name="mailu" size="25" /></td>
					<td><textarea name="description" ></textarea></td>
					<td><input type="text" name="url" size="40" /></td>
					<td><select name="dsp"><option value="1" selected="selected">Yes</option>
  <option value="0">No</option>
					<td id="newl"><select id="newlist" name="newlist" onchange="if(document.getElementById(\'newlist\').value==\'create\'){document.getElementById(\'newl\').innerHTML=\'<input type=\\\'text\\\' name=\\\'newlist\\\' size=\\\'25\\\' />\'}" ><option value="default" selected="selected">default</option>
  <option value="create">Create New List</option>';
		$db_lists = $wpdb->get_results("SELECT * from ".$wpdb->prefix."i_list GROUP BY l_name");
		foreach ($db_lists as $db_list) {
			if($db_list->l_name !="default"){
			echo "<option value='".$db_list->l_name."' >".$db_list->l_name."</option>'";
			}
		}
		echo '</select></td>
				</tr>
				<tr>
					<td colspan="5" align="center">
						<input type="hidden" name="addnewform" value="1"/>
						<input type="submit" class="button-primary" value="Add New" />
					</td>
				</tr>
		';
	echo 	'</table></form>';
	echo '<p>Interesting LINKS List <b>Change List: </b> <select style="" id="avllists" name="avllists" onchange="document.location.href =\'options-general.php?page=i_list_unique&amp;list=\'+this.options[this.selectedIndex].value"><option value="" selected="selected">Select list</option>';
	 
	
		foreach ($db_lists as $db_list) {
			echo "<option value='".$db_list->l_name."' >".$db_list->l_name."</option>'";
		}
	
	echo '</select></p>';

		if($_GET['start']){
			$start = $_GET['start'];
		}
		else{
			$start = 0;
		}
		if(!$_GET['list']){
			$list = "default";
		}
		else{
			
			$list = $_GET['list'];
		}
	echo "<h2>Editing List: $list. Display only this list by inserting into post/page [interesting name=$list]</h2>";
		$db_questions = $wpdb->get_results("SELECT * from ".$wpdb->prefix."i_list WHERE l_name = '$list' order by iorder DESC LIMIT $start , 10");
		foreach ($db_questions as $db_question) {
			echo 
				'
				<form method="post" action="options-general.php?page=i_list_unique&amp;list='.$list.'">
					<table class="form-table">
					
						<tr>
					<th scope="col">Name</th>
					<th scope="col">Mail</th>
					<th scope="col">URL Title</th>
					<th scope="col">URL</th>
					<th scope="col">Show</th>
					<th scope="col">Order</th>
					<th scope="col">Action</th>
				</tr>
				<tr>
					<td><input type="text" name="name" size="15" value="'.stripslashes($db_question->name).'"/></td>
					<td><input type="text" name="mailu" size="25" value="'.stripslashes($db_question->mailu).'"/></td>
					<td><textarea name="description" >'.stripslashes($db_question->text).'</textarea></td>
					<td><input type="text" name="url" size="35" value="'.stripslashes($db_question->url).'"/></td>
					<td>';

	if ($db_question->dsp == 1){
		echo'<select name="dsp">
		   <option value="1" selected="selected">Yes</option>
		   <option value="0">No</option>
		 </select>';
	}
	if ($db_question->dsp == 0){
		echo'<select name="dsp">
		   <option value="1" >Yes</option>
		   <option value="0"selected="selected" >No</option>
		 </select>';
	}



echo	'</td><td><input  type="text" name="order" size="3" value="'.stripslashes($db_question->iorder).'"/><br><strong><a style="text-decoration:none"  href="options-general.php?page=i_list_unique&amp;i_list_id='.$db_question->id.'&amp;update_order_i=1">&nbsp; &uarr; &nbsp;</a>
<a style="text-decoration:none"  href="options-general.php?page=i_list_unique&amp;i_list_id='.$db_question->id.'&amp;update_order_i=-1" ">&nbsp; &darr; &nbsp;</a></strong></td>
<td><input type="hidden" name="updateform" value="'.$db_question->id.'"/><input type="submit" value="Update" /><a href="options-general.php?page=i_list_unique&amp;delete='.$db_question->id.'&amp;list='.$list.'" class="submitdelete deletion" onclick="return confirm(\'You sure you want to delete?\');"><input type="button" value="Delete"></a>

</td>
</tr>
					</table>
				</form>';
		}
	
	$list_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM ".$wpdb->prefix."i_list WHERE l_name = '$list';" ) );

	$pag_t = floor($list_count/10);
	
	
	
	/////////////pagination
	$pagination = "";
	$pagination .= "<div class=\"tablenav-pages\"><span class=\"displaying-num\">$list_count items</span><span class=\"pagination-links\"><br />";
	// previous button
	$next_p=$start +10;
	$prv_p=$start -10;
	if($start != 0){	 
		$pagination.= "<a class='first-page' title='Go to the previous page' href='options-general.php?page=i_list_unique&amp;start=$prv_p&amp;list=$list'>&laquo;</a>";
		}
	//pages no.
	$i=1;
	for ($counter = 0; $counter <= $pag_t; $counter++){
		
		if ($counter == $start/10){
			$pagination.= "<span class=\"paging-input\">$i</span>";
			
			}
		else{
			$pagination.= "<a href=\"options-general.php?page=i_list_unique&amp;start=". $counter*10 . "&amp;list=$list\"><span class=\"paging-input\">$i</span></a>";
			
			}
			
		$i= $i +1;
		}
	//NEXT button
	if ($start < $pag_t *10){
			$pagination.= "<a class=\"last-page\" title=\"Go to the next page\" href=\"options-general.php?page=i_list_unique&amp;start=$next_p&amp;list=$list\">&raquo;</a>";
			
		}
		$pagination.= "</span></div>";
	echo $pagination;
	
	
	echo 	'</div>';
	
}

register_activation_hook(__FILE__, 'ilist_activate');


function ilist_activate() {
	global $wpdb;
	global $i_list_db_version;
	$i_list_db_version = "0.3.5";
	// Creating Table
	$table_name = $wpdb->prefix . "i_list";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
		  dsp bigint(11) DEFAULT '0' NOT NULL,
		  name tinytext NOT NULL,
		  mailu VARCHAR(100) NOT NULL,
		  text text NOT NULL,
		  url VARCHAR(500) NOT NULL,
		  iorder MEDIUMINT(9) NOT NULL DEFAULT '0',
		  l_name tinytext NOT NULL,
		  UNIQUE KEY id (id)
			);";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			 $insert = "INSERT INTO " . $table_name .
				" (name, mailu, text, url, dsp, l_name) " .
				"VALUES ('Madalin.F','contact@linkzone.ro', 'Interesting LINKS List WordPress Plugin','http://wp.linkzone.ro','1' ,'default')";
			$results = $wpdb->query( $insert );
			add_option("i_list_db_version", $i_list_db_version); 

		}

		
		
		$installed_ver = get_option( "i_list_db_version" );

	   if( $installed_ver != $i_list_db_version ) {

		    $sql = "ALTER TABLE $table_name ADD l_name TINYTEXT NOT NULL";
			$wpdb->query($sql);

      //require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      //dbDelta($sql);

	  $insert = "INSERT INTO " . $table_name .
				" (name, mailu, text, url, dsp) " .
				"VALUES ('Madalin.M','contact@linkzone.ro', 'Interesting LINKS List WordPress Plugin','http://wp.linkzone.ro','1')";
		$wpdb->query( $insert );
		$wpdb->query("UPDATE $table_name SET l_name = 'default' WHERE id > 0");

		  update_option( "i_list_db_version", $i_list_db_version);
		}


	

	$i_list_options = array("i_list_s_title" => "SUBMIT", "i_list_p_title" => "My List", "i_list_form" => "no", "i_list_contributor" => "yes");
	if(!get_option("i_list_all_options")){
		add_option("i_list_all_options", $i_list_options);
	}

}


function show_interesting_links($atts){
	global $wpdb, $post ;
	
	extract( shortcode_atts( array(
		'name' => 'default',
		'form' => '1',
		), $atts ) );
	add_props();
	if ($name != ''){
		$get_list = "and l_name ='$name'";
	}
	else{
		$get_list ="";		
	}
	$db_questions = $wpdb->get_results("SELECT * from ".$wpdb->prefix."i_list where dsp = 1 ".$get_list." order by iorder DESC");
	$i_options = get_option("i_list_all_options");
	if($_POST['newprop']){
	 $ok= "<script language=\"javascript\"> 
setTimeout(\"fade('fade')\",3500);
setTimeout(\"document.getElementById('fade').style.display = 'none'\",4500); 
</script><div id='fade'><h2>Thank you. Your LINK has been received.</h2></div>";
 }
	
	if(empty($db_questions)){return '<h2>'.stripslashes($i_options["i_list_p_title"]).'</h2><br clear="all" /><form action="" method="post" id="info" onsubmit="javascript:return validate(\'info\',\'email\')";>
				   <h2>'. stripslashes($i_options["i_list_s_title"]).'</h2>
'. $ok  .'
				   <div id="ilist-wrap" class="slider">
					  <label for="name">Name</label>
					  <input type="text" id="name" name="nano">
				   </div>
				   <div id="ilist-wrap"  class="slider">
					  <label for="email">E&ndash;mail</label>
					  <input type="text" id="email" name="mailu">
				   </div>
				  <div id="ilist-wrap"  class="slider">
    <label for="comment">URL Title</label>
    <textarea name="text" rows="3" id="comment"></textarea>
</div>
				   <div id="ilist-wrap"  class="slider">
					  <label for="city">URL</label>
					  <input type="text" id="city" name="url">
				   </div>

<input type="hidden" name="newprop" value="' . $post->ID . '" />
<input type="hidden" name="l_list" value="' . $name. '" />

				   <input type="submit" id="btn"  value="submit">
				</form>
				';}

				$mieru ='<h2>'.stripslashes($i_options["i_list_p_title"]).'</h2>';
				if ($form == '1'){
				$mieru .= '<br clear="all" /><form action="" method="post" id="info" onsubmit="javascript:return validate(\'info\',\'email\')";>
				   <h2>'. stripslashes($i_options["i_list_s_title"]).'</h2>
'. $ok  .'
				   <div id="ilist-wrap" class="slider">
					  <label for="name">Name</label>
					  <input type="text" id="name" name="nano">
				   </div>
				   <div id="ilist-wrap"  class="slider">
					  <label for="email">E&ndash;mail</label>
					  <input type="text" id="email" name="mailu">
				   </div>
				  <div id="ilist-wrap"  class="slider">
    <label for="comment">URL Title</label>
    <textarea name="text" rows="3" id="comment"></textarea>
</div>
				   <div id="ilist-wrap"  class="slider">
					  <label for="city">URL</label>
					  <input type="text" id="city" name="url">
				   </div>

<input type="hidden" name="newprop" value="' . $post->ID . '" />
<input type="hidden" name="l_list" value="' . $name. '" />
				   <input type="submit" id="btn"  value="submit">
				</form>
				';
				}
	$mieru .= '<ul>';
		foreach ($db_questions as $db_question) {$i++;
			$mieru.='<li><a href="'.$db_question->url.'" onmouseover="">'.stripslashes($db_question->text).'</a></li>';
			
			if ( $i_options["i_list_contributor"] == "yes" ){
				$mieru.='<span id="more'.$i.'">Submitted by: '.stripslashes($db_question->name).'</span>';
			}
			
			
		}
		$mieru .= '</ul><br clear="all" />';
		
	return $mieru ;
}

add_shortcode( 'interesting', 'show_interesting_links' );


function i_list_email_notification ($aa, $ab, $ac, $url, $ad )  {
    $friends = get_bloginfo('admin_email');
	$i_noti_sub = get_bloginfo('name')." received a new link via Interesting LINKS List Plugin ";
	$i_noti = '<p>A new link was submitted through Interesting Links List Plugin from.</p> <p>Name        : '. stripslashes($aa) . 
	'<br>Mail        : '. $ab .
	'<br>URL Title   : '. stripslashes($ac) .
	'<br>URL         : '. $url .
	'</p><p><a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=i_list_unique&amp;i_ls_mailconf='.$ad.'" >Approve and display it on your list</a><br>'.
	'<a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=i_list_unique&amp;delete='.$ad.'">Delete it </a>' ;
	
	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: Your  blog: '.get_bloginfo('name').' using Interesting Links List Plugin ' . "\r\n";
    mail($friends, $i_noti_sub , $i_noti , $headers );
    return $post_ID;
}

add_action ( 'i_list_mail', 'i_list_email_notification' , 10, 5);



function add_props(){

	global $wpdb, $post;
if($_POST['newprop'] == $post->ID ){
	
	$url =stripslashes($_POST['url']);
	if(!strstr($url, "http://")){
        $url = "http://".$url;
    }
// see if the description contains any code or HTML tags 
	if (preg_match("/\[[^\[]+?]|<[^<]+?>/", $_POST['text'])) {
    $eroareurl = "A  URL/HTML match was found. NO HTML ALLOWED";
} else {
    	$wpdb->insert($wpdb->prefix."i_list",array('name'=>$_POST['nano'], 'mailu'=>$_POST['mailu'], 'text'=> $_POST['text'], 'url'=>$url, 'l_name'=>$_POST['l_list']));
	$aa = stripslashes($_POST['nano']);
	$ab = stripslashes($_POST['mailu']);
	$ac = stripslashes($_POST['text']);
	$ad = $wpdb->insert_id;
	do_action( i_list_mail, $aa, $ab, $ac, $url, $ad );
}
}
}

    add_action('wp_print_styles', 'add_my_stylesheet');

    function add_my_stylesheet() {
        $myStyleUrl = WP_PLUGIN_URL . '/interesting-links-list/ilist.css';
        $myStyleFile = WP_PLUGIN_DIR . '/interesting-links-list/ilist.css';
        if ( file_exists($myStyleFile) ) {
            wp_register_style('myStyleSheets', $myStyleUrl);
            wp_enqueue_style( 'myStyleSheets');
        }
    }

add_filter('wp_head','add_i_list_js');

function add_i_list_js(){
	$i_options = get_option("i_list_all_options");
	if ($i_options['i_list_form'] == "yes") { 	
		echo '<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">google.load("jquery", "1");</script>';
		echo '<script type="text/javascript" src="'.WP_PLUGIN_URL .'/interesting-links-list/ilist.js"></script>';
	}
}

?>