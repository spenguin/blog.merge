<?php
/*
This page lists all the quizzes currently on the website and allows you to create more quizzes.
*/
/* 
Copyright 2013, My Local Webstop (email : fpcorso@mylocalwebstop.com)
*/

function mlw_generate_quiz_admin()
{
	global $wpdb;
	$table_name = $wpdb->prefix . "mlw_quizzes";
	$hasCreatedQuiz = false;
	$hasDeletedQuiz = false;
	$hasUpdatedQuizName = false;
	$hasDuplicatedQuiz = false;
	$mlw_qmn_isQueryError = false;
	$mlw_qmn_error_code = '0';

	//Create new quiz
	if ( isset( $_POST["create_quiz"] ) && $_POST["create_quiz"] == "confirmation" )
	{
		$quiz_name = htmlspecialchars($_POST["quiz_name"], ENT_QUOTES);
		//Insert New Quiz Into Table
		$mlw_leaderboard_default = "<h3>Leaderboard for %QUIZ_NAME%</h3>
			1. %FIRST_PLACE_NAME%-%FIRST_PLACE_SCORE%<br />
			2. %SECOND_PLACE_NAME%-%SECOND_PLACE_SCORE%<br />
			3. %THIRD_PLACE_NAME%-%THIRD_PLACE_SCORE%<br />
			4. %FOURTH_PLACE_NAME%-%FOURTH_PLACE_SCORE%<br />
			5. %FIFTH_PLACE_NAME%-%FIFTH_PLACE_SCORE%<br />";
		$mlw_style_default = "
				div.mlw_qmn_quiz input[type=radio],
				div.mlw_qmn_quiz input[type=submit],
				div.mlw_qmn_quiz label {
					cursor: pointer;
				}
				div.mlw_qmn_quiz input:not([type=submit]):focus,
				div.mlw_qmn_quiz textarea:focus {
					background: #eaeaea;
				}
				div.mlw_qmn_quiz {
					text-align: left;
				}
				div.quiz_section {
					
				}
				div.mlw_qmn_timer {
					position:fixed;
					top:200px;
					right:0px;
					width:130px;
					color:#00CCFF;
					border-radius: 15px;
					background:#000000;
					text-align: center;
					padding: 15px 15px 15px 15px
				}
				div.mlw_qmn_quiz input[type=submit],
				a.mlw_qmn_quiz_link
				{
					    border-radius: 4px;
					    position: relative;
					    background-image: linear-gradient(#fff,#dedede);
						background-color: #eee;
						border: #ccc solid 1px;
						color: #333;
						text-shadow: 0 1px 0 rgba(255,255,255,.5);
						box-sizing: border-box;
					    display: inline-block;
					    padding: 5px 5px 5px 5px;
   						margin: auto;
				}";
		$mlw_question_answer_default = "%QUESTION%<br /> Answer Provided: %USER_ANSWER%<br /> Correct Answer: %CORRECT_ANSWER%<br /> Comments Entered: %USER_COMMENTS%<br />";
		$insert = "INSERT INTO " . $table_name .
			"(quiz_id, quiz_name, message_before, message_after, message_comment, message_end_template, user_email_template, admin_email_template, submit_button_text, name_field_text, business_field_text, email_field_text, phone_field_text, comment_field_text, email_from_text, question_answer_template, leaderboard_template, system, randomness_order, loggedin_user_contact, show_score, send_user_email, send_admin_email, contact_info_location, user_name, user_comp, user_email, user_phone, admin_email, comment_section, question_from_total, total_user_tries, total_user_tries_text, certificate_template, social_media, social_media_text, pagination, pagination_text, timer_limit, quiz_stye, question_numbering, quiz_settings, quiz_views, quiz_taken, deleted) " .
			"VALUES (NULL , '" . $quiz_name . "' , 'Enter your text here', 'Enter your text here', 'Enter your text here', '', 'Enter your text here', 'Enter your text here', 'Submit Quiz', 'Name', 'Business', 'Email', 'Phone Number', 'Comments', 'Wordpress', '".$mlw_question_answer_default."', '".$mlw_leaderboard_default."', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '".get_option( 'admin_email', 'Enter email' )."', 0, 0, 0, 'Enter Your Text Here', 'Enter Your Text Here!', 0, 'I just score a %CORRECT_SCORE%% on %QUIZ_NAME%!', 0, 'Next', 0, '".$mlw_style_default."', 0, '', 0, 0, 0)";
		$results = $wpdb->query( $insert );
		if ($results != false)
		{
			$hasCreatedQuiz = true;
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'New Quiz Has Been Created: ".$quiz_name."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlw_qmn_error_code = '0001';
			$mlw_qmn_isQueryError = true;
		}
		
	}

	//Delete quiz
	if (isset( $_POST["delete_quiz"] ) && $_POST["delete_quiz"] == "confirmation")
	{
		
		//Variables from delete question form
		$mlw_quiz_id = $_POST["quiz_id"];
		$quiz_name = $_POST["delete_quiz_name"];
		$quiz_id = $_POST["quiz_id"];
		$update = "UPDATE " . $wpdb->prefix . "mlw_quizzes" . " SET deleted=1 WHERE quiz_id=".$mlw_quiz_id;
		$results = $wpdb->query( $update );
		$update = "UPDATE " . $wpdb->prefix . "mlw_questions" . " SET deleted=1 WHERE quiz_id=".$mlw_quiz_id;
		$delete_question_results = $wpdb->query( $update );
		if ($results != false)
		{
			$hasDeletedQuiz = true;
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Quiz Has Been Deleted: ".$quiz_name."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlw_qmn_error_code = '0002';
			$mlw_qmn_isQueryError = true;
		}
		
	}	

	//Edit Quiz Name
	if (isset($_POST["quiz_name_editted"]) && $_POST["quiz_name_editted"] == "confirmation")
	{
		$mlw_edit_quiz_id = $_POST["edit_quiz_id"];
		$mlw_edit_quiz_name = htmlspecialchars($_POST["edit_quiz_name"], ENT_QUOTES);
		$mlw_update_quiz_table = "UPDATE " . $wpdb->prefix . "mlw_quizzes" . " SET quiz_name='".$mlw_edit_quiz_name."' WHERE quiz_id=".$mlw_edit_quiz_id;
		$results = $wpdb->query( $mlw_update_quiz_table );
		if ($results != false)
		{
			$hasUpdatedQuizName = true;
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Quiz Name Has Been Edited: ".$mlw_edit_quiz_name."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlw_qmn_error_code = '0003';
			$mlw_qmn_isQueryError = true;
		}		
	}
	
	//Duplicate Quiz
	if (isset($_POST["duplicate_quiz"]) && $_POST["duplicate_quiz"] == "confirmation")
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_duplicate_quiz_id = $_POST["duplicate_quiz_id"];
		$mlw_duplicate_quiz_name = htmlspecialchars($_POST["duplicate_new_quiz_name"], ENT_QUOTES);
		$mlw_qmn_duplicate_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "mlw_quizzes WHERE quiz_id=%d", $mlw_duplicate_quiz_id ) );
		$results = $wpdb->query( "INSERT INTO ".$table_name." (quiz_id, quiz_name, message_before, message_after, message_comment, message_end_template, user_email_template, admin_email_template, submit_button_text, name_field_text, business_field_text, email_field_text, phone_field_text, comment_field_text, email_from_text, question_answer_template, leaderboard_template, system, randomness_order, loggedin_user_contact, show_score, send_user_email, send_admin_email, contact_info_location, user_name, user_comp, user_email, user_phone, admin_email, comment_section, question_from_total, total_user_tries, total_user_tries_text, certificate_template, social_media, social_media_text, pagination, pagination_text, timer_limit, quiz_stye, question_numbering, quiz_views, quiz_taken, deleted) VALUES (NULL , '".$mlw_duplicate_quiz_name."' , '".$mlw_qmn_duplicate_data->message_before."', '".$mlw_qmn_duplicate_data->message_after."', '".$mlw_qmn_duplicate_data->message_comment."', '".$mlw_qmn_duplicate_data->message_end_template."', '".$mlw_qmn_duplicate_data->user_email_template."', '".$mlw_qmn_duplicate_data->admin_email_template."', '".$mlw_qmn_duplicate_data->submit_button_text."', '".$mlw_qmn_duplicate_data->name_field_text."', '".$mlw_qmn_duplicate_data->business_field_text."', '".$mlw_qmn_duplicate_data->email_field_text."', '".$mlw_qmn_duplicate_data->phone_field_text."', '".$mlw_qmn_duplicate_data->comment_field_text."', '".$mlw_qmn_duplicate_data->email_from_text."', '".$mlw_qmn_duplicate_data->question_answer_template."', '".$mlw_qmn_duplicate_data->leaderboard_template."', ".$mlw_qmn_duplicate_data->system.", ".$mlw_qmn_duplicate_data->randomness_order.", ".$mlw_qmn_duplicate_data->loggedin_user_contact.", ".$mlw_qmn_duplicate_data->show_score.", ".$mlw_qmn_duplicate_data->send_user_email.", ".$mlw_qmn_duplicate_data->send_admin_email.", ".$mlw_qmn_duplicate_data->contact_info_location.", ".$mlw_qmn_duplicate_data->user_name.", ".$mlw_qmn_duplicate_data->user_comp.", ".$mlw_qmn_duplicate_data->user_email.", ".$mlw_qmn_duplicate_data->user_phone.", '".get_option( 'admin_email', 'Enter email' )."', ".$mlw_qmn_duplicate_data->comment_section.", ".$mlw_qmn_duplicate_data->question_from_total.", ".$mlw_qmn_duplicate_data->total_user_tries.", '".$mlw_qmn_duplicate_data->total_user_tries_text."', '".$mlw_qmn_duplicate_data->certificate_template."', ".$mlw_qmn_duplicate_data->social_media.", '".$mlw_qmn_duplicate_data->social_media_text."', ".$mlw_qmn_duplicate_data->pagination.", '".$mlw_qmn_duplicate_data->pagination_text."', ".$mlw_qmn_duplicate_data->timer_limit.", '".$mlw_qmn_duplicate_data->quiz_stye."', ".$mlw_qmn_duplicate_data->question_numbering.", 0, 0, 0)" );
		if ($results != false)
		{
			$hasDuplicatedQuiz = true;
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'New Quiz Has Been Created: ".$mlw_duplicate_quiz_name."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlw_qmn_error_code = '0011';
			$mlw_qmn_isQueryError = true;
		}		
	}

	//Retrieve list of quizzes
	global $wpdb;
	$mlw_qmn_table_limit = 10;
	$mlw_qmn_quiz_count = $wpdb->get_var( "SELECT COUNT(quiz_id) FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted='0'" );
	
	if( isset($_GET{'mlw_quiz_page'} ) )
	{
	   $mlw_qmn_quiz_page = $_GET{'mlw_quiz_page'} + 1;
	   $mlw_qmn_quiz_begin = $mlw_qmn_table_limit * $mlw_qmn_quiz_page ;
	}
	else
	{
	   $mlw_qmn_quiz_page = 0;
	   $mlw_qmn_quiz_begin = 0;
	}
	$mlw_qmn_quiz_left = $mlw_qmn_quiz_count - ($mlw_qmn_quiz_page * $mlw_qmn_table_limit);
	$mlw_quiz_data = $wpdb->get_results( $wpdb->prepare( "SELECT quiz_id, quiz_name, quiz_views, quiz_taken 
		FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted='0' 
		ORDER BY quiz_id DESC LIMIT %d, %d", $mlw_qmn_quiz_begin, $mlw_qmn_table_limit ) );
	?>
	<!-- css -->
	<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css" rel="stylesheet" />
	<!-- jquery scripts -->
	<?php
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'jquery-effects-blind' );
	wp_enqueue_script( 'jquery-effects-explode' );
	?>
	<!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.0/jquery.min.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>-->
	<script type="text/javascript">
		var $j = jQuery.noConflict();
		// increase the default animation speed to exaggerate the effect
		$j.fx.speeds._default = 1000;
		$j(function() {
			$j('#dialog').dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Ok: function() {
					$j(this).dialog('close');
					}
				}
			});
		
			$j('#opener').click(function() {
				$j('#dialog').dialog('open');
				return false;
		}	);
		});
		$j(function() {
			$j("#prev_page, #next_page").button();
		    $j("#new_quiz_button, #new_quiz_button_two").button({
		      icons: {
		        primary: "ui-icon-circle-plus"
		      }
		    });		
		});
		$j(function() {
			$j('#new_quiz_dialog').dialog({
				autoOpen: false,
				show: 'blind',
				width:700,
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
		
			$j('#new_quiz_button').click(function() {
				$j('#new_quiz_dialog').dialog('open');
				return false;
		}	);
			$j('#new_quiz_button_two').click(function() {
				$j('#new_quiz_dialog').dialog('open');
				return false;
		}	);
		});
		function deleteQuiz(id,quizName){
			$j("#delete_dialog").dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
			$j("#delete_dialog").dialog('open');
			var idText = document.getElementById("delete_quiz_id");
			var idHidden = document.getElementById("quiz_id");
			var idHiddenName = document.getElementById("delete_quiz_name");
			idText.innerHTML = id;
			idHidden.value = id;
			idHiddenName.value = quizName;
		};
		function editQuizName(id, quizName){
			$j("#edit_dialog").dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
			$j("#edit_dialog").dialog('open');
			document.getElementById("edit_quiz_name").value = quizName;
			document.getElementById("edit_quiz_id"). value = id;			
		}
		function duplicateQuiz(id, quizName){
			$j("#duplicate_dialog").dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
			$j("#duplicate_dialog").dialog('open');
			document.getElementById("duplicate_quiz_name").innerHTML = quizName;
			document.getElementById("duplicate_quiz_id"). value = id;			
		}
	</script>
	<style>
  		label {
    		display: inline-block;
    		width: 5em;
  		}
  	</style>
	<style type="text/css">
	div.mlw_quiz_options input[type='text'] {
		border-color:#000000;
		color:#3300CC; 
		cursor:hand;
		}
	</style>
	<div class="wrap">
	<div class='mlw_quiz_options'>
	<h2>Quizzes<a id="opener" href="">(?)</a></h2>
	<?php if ($hasCreatedQuiz)
		{
	?>
		<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> Your new quiz has been created successfully. To begin editing options and adding questions to your quiz, click on the edit link for that quiz.</p>
	</div>
	<?php
		}
		if ($hasDuplicatedQuiz)
		{
	?>
		<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> The quiz has been duplicated successfully.</p>
	</div>
	<?php
		}
	?>
	<?php if ($mlw_qmn_isQueryError)
		{
	?>
		<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Uh-Oh!</strong> There has been an error in this action! Please share this with the developer: Error Code <?php echo $mlw_qmn_error_code; ?></p>
	</div>
	<?php
		}
		if ($hasDeletedQuiz)
		{
	?>
		<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> The quiz has been deleted.</p>
	</div>
	<?php
		}
	?>
	<?php if ($hasUpdatedQuizName)
		{
	?>
		<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> The quiz name has been updated.</p>
	</div>
	<?php
		}
	?>
	<button id="new_quiz_button_two">Create New Quiz</button>
	<?php 
	$quotes_list = "";
	$display = "";
	$alternate = "";
	foreach($mlw_quiz_data as $mlw_quiz_info) {
		if($alternate) $alternate = "";
		else $alternate = " class=\"alternate\"";
		$quotes_list .= "<tr{$alternate}>";
		$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->quiz_id . "</span></td>";
		$quotes_list .= "<td class='post-title column-title'><span style='font-size:16px;'>" . esc_js($mlw_quiz_info->quiz_name) ." </span><span style='color:green;font-size:12px;'><a onclick=\"editQuizName('".$mlw_quiz_info->quiz_id."','".esc_js($mlw_quiz_info->quiz_name)."')\" href='#'>(Edit Name)</a></span><div><span style='color:green;font-size:12px;'><a href='admin.php?page=mlw_quiz_options&&quiz_id=".$mlw_quiz_info->quiz_id."'>Edit</a> | <a onclick=\"deleteQuiz('".$mlw_quiz_info->quiz_id."','".esc_js($mlw_quiz_info->quiz_name)."')\" href='#'>Delete</a> | <a href='admin.php?page=mlw_quiz_results&&quiz_id=".$mlw_quiz_info->quiz_id."'>Results</a> | <a href='#' onclick=\"duplicateQuiz('".$mlw_quiz_info->quiz_id."','".esc_js($mlw_quiz_info->quiz_name)."')\">Duplicate</a></span></div></td>";
		$quotes_list .= "<td><span style='font-size:16px;'>[mlw_quizmaster quiz=".$mlw_quiz_info->quiz_id."]</span></td>";
		$quotes_list .= "<td><span style='font-size:16px;'>[mlw_quizmaster_leaderboard mlw_quiz=".$mlw_quiz_info->quiz_id."]</span></td>";
		$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->quiz_views . "</span></td>";
		$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->quiz_taken ."</span></td>";
		$quotes_list .= "</tr>";
	}
	
	if( $mlw_qmn_quiz_page > 0 )
	{
	   	$mlw_qmn_previous_page = $mlw_qmn_quiz_page - 2;
	   	$display .= "<a id=\"prev_page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_previous_page\">Previous 10 Quizzes</a>";
	   	if( $mlw_qmn_quiz_left > $mlw_qmn_table_limit )
	   	{
			$display .= "<a id=\"next_page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_quiz_page\">Next 10 Quizzes</a>";
	   	}
	}
	else if( $mlw_qmn_quiz_page == 0 )
	{
	   if( $mlw_qmn_quiz_left > $mlw_qmn_table_limit )
	   {
			$display .= "<a id=\"next_page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_quiz_page\">Next 10 Quizzes</a>";
	   }
	}
	else if( $mlw_qmn_quiz_left < $mlw_qmn_table_limit )
	{
	   $mlw_qmn_previous_page = $mlw_qmn_quiz_page - 2;
	   $display .= "<a id=\"prev_page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_previous_page\">Previous 10 Quizzes</a>";
	}
	
	$display .= "<br />";

	$display .= "<table class=\"widefat\">";
		$display .= "<thead><tr>
			<th>Quiz ID</th>
			<th>Quiz Name</th>
			<th>Quiz Shortcode</th>
			<th>Leaderboard Shortcode</th>
			<th>Quiz Views</th>
			<th>Quiz Taken</th>
		</tr></thead>";
		$display .= "<tbody id=\"the-list\">{$quotes_list}</tbody>";
		$display .= "<tfoot><tr>
			<th>Quiz ID</th>
			<th>Quiz Name</th>
			<th>Quiz Shortcode</th>
			<th>Leaderboard Shortcode</th>
			<th>Quiz Views</th>
			<th>Quiz Taken</th>
		</tr></tfoot>";
		$display .= "</table>";
	echo $display;
	?>

	<button id="new_quiz_button">Create New Quiz</button>
	
	<?php echo mlw_qmn_show_adverts(); ?>
	<!--Dialogs-->
	
	<!--New Quiz Dialog-->
	<div id="new_quiz_dialog" title="Create New Quiz" style="display:none;">
		<?php
		echo "<form action='' method='post'>";
		echo "<input type='hidden' name='create_quiz' value='confirmation' />";
		?>
		<table class="wide" style="text-align: left; white-space: nowrap;">
		<thead>
		
		<tr valign="top">
		<th scope="row">&nbsp;</th>
		<td></td>
		</tr>
			
		<tr valign="top">
		<th scope="row"><h3>Create New Quiz</h3></th>
		<td></td>
		</tr>
		
		<tr valign="top">
		<th scope="row">Quiz Name</th>
		<td>
		<input type="text" name="quiz_name" value="" style="border-color:#000000;
			color:#3300CC; 
			cursor:hand;"/>
		</td>
		</tr>
		</thead>
		</table>
		<?php
		echo "<p class='submit'><input type='submit' class='button-primary' value='Create Quiz' /></p>";
		echo "</form>";
		?>
	</div>
	
	<!--Edit Quiz Name Dialog-->
	<div id="edit_dialog" title="Edit Quiz Name" style="display:none;">
		<h3>Quiz Name:</h3><br />
		<form action='' method='post'>
		<input type="text" id="edit_quiz_name" name="edit_quiz_name" />
		<input type="hidden" id="edit_quiz_id" name="edit_quiz_id" />
		<input type='hidden' name='quiz_name_editted' value='confirmation' />
		<input type="submit" class="button-primary" value="Edit" />
		</form>
	</div>
	
	<!--Duplicate Quiz Dialog-->
	<div id="duplicate_dialog" title="Duplicate Quiz" style="display:none;">
		<h3>This will create a new quiz with the same settings as <span id="duplicate_quiz_name"></span>. </h3><br />
		<p>This does not currently duplicate the questions, only the options, templates, settings, etc...</p>
		<form action='' method='post'>
		Name Of New Quiz:
		<input type="text" id="duplicate_new_quiz_name" name="duplicate_new_quiz_name" />
		<input type="hidden" id="duplicate_quiz_id" name="duplicate_quiz_id" />
		<input type='hidden' name='duplicate_quiz' value='confirmation' />
		<input type="submit" class="button-primary" value="Duplicate" />
		</form>
	</div>
	
	<!--Delete Quiz Dialog-->
	<div id="delete_dialog" title="Delete Quiz?" style="display:none;">
	<h3><b>Are you sure you want to delete Quiz <span id="delete_quiz_id"></span>?</b></h3>
	<?php
	echo "<form action='' method='post'>";
	echo "<input type='hidden' name='delete_quiz' value='confirmation' />";
	echo "<input type='hidden' id='quiz_id' name='quiz_id' value='' />";
	echo "<input type='hidden' id='delete_quiz_name' name='delete_quiz_name' value='' />";
	echo "<p class='submit'><input type='submit' class='button-primary' value='Delete Quiz' /></p>";
	echo "</form>";	
	?>
	</div>
	
	<!--Help Dialog-->
	<div id="dialog" title="Help" style="display:none;">
	<h3><b>Help</b></h3>
	<p>This page shows all of the quizzes currently on your website.</p>
	<p>The table shows the quiz id, the name of your quiz, the shortcode to use on your post or page to add the quiz, the shortcode to use on your post or page to add the leaderboard, the amount of views the quiz has had, and the amount of times the quiz was finished</p>
	<p>To create a new quiz, click the Create New Quiz button and fill out the name.</p>
	<p>To edit a quiz, click the Edit link underneath the name of the quiz.</p>
	<p>To edit a quiz's name, click the Edit Name link next to the name of the quiz.</p>
	<p>To delete a quiz, click the Delete link underneath the name of the quiz.</p>
	<p>To view the results of a quiz, click the Results link underneath the name of the quiz.</p>
	</div>	
	</div>
	</div>
<?php
}
?>