	<?php include(sprintf("%s/templates/quiz_submit_box.php", realpath(dirname(__FILE__) . '/..')));
	  ?>
	  <div id="randomizdiv" class="postbox">
		<div class="handlediv" title="Click to toggle"><br /></div><h3 class='hndle'><span class="rightsidebarspan"><?php _e('Randomization','wpcues-basic-quiz');?></span></h3>
		<div class="inside">
		<div id="randomizationsetting" class="randomdiv">
		<ul>
		<?php if(!(empty($quizmeta['randomizsetting']))){$randomizsetting=maybe_unserialize($quizmeta['randomizsetting'][0]);}?>
			<li><input type='checkbox' name='randomquest' value='1' class='requiredvar' <?php if(!empty($randomizsetting['randomquest'])){echo 'checked';}?>><?php _e('Randomize Questions','wpcues-basic-quiz');?></li>
			<li><input type='checkbox' name='randomans' value='1' class='requiredvar' <?php if(!empty($randomizsetting['randomans'])){echo 'checked';}?>><?php _e('Randomize Answers','wpcues-basic-quiz');?></li>
			<li class='randquestcat'><input type='checkbox' name='randomquestcat' value='1' class='requiredvar' <?php if(!empty($randomizsetting['randomquestcat'])){echo 'checked';}?>><?php _e('Group Questions by Categories','wpcues-basic-quiz');?></li>
			<?php global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
				$sectionids=$wpdb->get_col($wpdb->prepare("SELECT entityid from $table_name where quizid=%d and parentid = -1",$post_id));?>
			<li id='secquizrand' <?php if(empty($sectionids)){echo "class='hiddendiv'";}?>><?php _e('Exclude Sections from randomization.','wpcues-basic-quiz');?><span class="procontent"></span>
				<ul>
					<?php if(!empty($sectionids)){ 
							$args=array('post__in'=>$sectionids,'post_status'=>'publish','orderby'=>'post__in','post_type'=>'wpcuebasicsection','posts_per_page' => -1);
							$sections=new WP_Query($args);
							while ($sections->have_posts()){
								$sections->the_post();
								$section=$sections->post;
								$sectext='<li id="randsecexc-'.$section->ID.'"><input type="checkbox" name="randsecexc[]" value="'.$section->ID.'"  class="requiredvar" disabled';
								if((!empty($randomizsetting['randsecexc'])) && (in_array($section->ID,$randomizsetting['randsecexc']))){
									$sectext.="checked";}
								$sectext.='>'.$section->post_title.'</li>';
								echo $sectext;
							}
							wp_reset_postdata();
						}
						?>
				</ul>
			</li>
		</ul>
		</div>
		</div>
	</div>
	<div id="categorydiv" class="postbox " >
<div class="handlediv" title="Click to toggle"><br /></div><h3 class='hndle'><span><?php _e('Categories','wpcues-basic-quiz');?></span></h3>
<div class="inside">
	<div id="taxonomy-category" class="categorydiv">
	<div id="category-all" class="tabs-panel">
	<ul id="categorychecklist" data-wp-lists="list:category" class="categorychecklist form-no-clear">
	<?php $taxonomy='wpcuebasicquizcat';$tax = get_taxonomy($taxonomy);wp_terms_checklist($post->ID,array('taxonomy'=>'wpcuebasicquizcat','walker'=>'walker'));?>
	</ul>
	</div>
	<?php if ( current_user_can($tax->cap->edit_terms) ) { ?>
	<div id="category-adder" class="wp-hidden-children">
				<h4>
					<a id="category-add-toggle" href="#category-add" class="hide-if-no-js">
						+ <?php _e('Add New Category','wpcues-basic-quiz');?>					</a>
				</h4>
				<p id="category-add" class="category-add wp-hidden-child">
					<label class="screen-reader-text" for="newcategory"><?php _e('Add New Category');?></label>
					<input type="text" name="newcategory" id="newcategory" class="form-required form-input-tip" value="New Category Name" aria-required="true"/>
					<label class="screen-reader-text" for="newcategory_parent">
						<?php _e('Parent Category','wpcues-basic-quiz');?>:					</label>
					<?php wp_dropdown_categories( array( 'taxonomy' => $taxonomy, 'hide_empty' => 0, 'name' => 'Parent Category','id'=>'parent_category', 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => '&mdash; ' . $tax->labels->parent_item . ' &mdash;' ) ); ?>
					<input type="button" id="category-add-submit" data-wp-lists="add:categorychecklist:category-add" class="button category-add-submit" value="<?php _e('Add New Category','wpcues-basic-quiz');?>" />
					<?php $ajax_nonce = wp_create_nonce( "wpprocue-wpcuebasicquizcat-nyspecial" );?>
					<input type="hidden" id="_ajax_nonce-add-category" name="_ajax_nonce-add-category" value="<?php echo $ajax_nonce;?>" />					<span id="category-ajax-response"></span>
				</p>
			</div>
			<?php  }?>
	</div>
	</div>
	</div>
	
	<div id="displaysettingdiv" class="postbox">
		<div class="handlediv" title="Click to toggle"><br /></div><h3 class='hndle'><span><?php _e('Display','wpcues-basic-quiz');?></span></h3>
		<div class="inside">
		<div id="displaysetting" class="displaydiv">
			<ul>
			<?php if(!(empty($quizmeta['displaysetting']))){$displaysetting=maybe_unserialize($quizmeta['displaysetting'][0]); }?>
				<li><input type='checkbox' name='timer' value='1' class='requiredvar'  <?php if(!empty($displaysetting['timer'])){echo 'checked';} ?>><?php _e('Show timer','wpcues-basic-quiz');?></li>
				<li><input type='checkbox' name='disablequizdesc' class='requiredvar' value='1' <?php if(!empty($displaysetting['disablequizdesc'])){echo 'checked';}?>><?php _e('Do not show description','wpcues-basic-quiz');?></li>
				<li><input type='checkbox' name='disablestartbutton' value='1' class='requiredvar' <?php if(!empty($displaysetting['disablestartbutton'])){echo 'checked';}?>><?php _e('Do not show start button','wpcues-basic-quiz');?></li>
				<li><input type='checkbox' name='submitbuttonstat' value='1' class='requiredvar' <?php if(!empty($displaysetting['submitbuttonstat'])){echo 'checked';} ?>><?php _e('Show Submit button at every page','wpcues-basic-quiz');?></li>
				<li><input type='checkbox' name='disableintermediate' class='requiredvar' value='1' <?php if(!empty($displaysetting['disableintermediate'])){echo 'checked';}?> disabled><?php _e('Do not show intermediate screen','wpcues-basic-quiz');?></li>
				<li><input type='checkbox' name='intermediatecontrol' value='1' class='requiredvar' <?php if(!empty($displaysetting['intermediatecontrol'])){echo 'checked';}?> disabled><?php _e('Do not show Continue button','wpcues-basic-quiz');?></li>
				<li><input type='checkbox' name='savebuttonstat' value='1' class='requiredvar' <?php if(!empty($displaysetting['savebuttonstat'])){echo 'checked';} ?> disabled><?php _e('Show Save button','wpcues-basic-quiz');?></li>
				
			</ul>
		</div>
		</div>
	</div>
	<div id="questiontooldiv" class="postbox">
		<div class="handlediv" title="Click to toggle"><br/></div><h3 class="hndle"><span><?php _e('Question tools','wpcues-basic-quiz');?></span></h3>
		<div class="inside">
		<div id="questiontoolsetting" class="displaydiv">
		<?php if(!(empty($quizmeta['questtools']))){$questtools=maybe_unserialize($quizmeta['questtools'][0]);} ?>
			<ul>
				<li class='quizmodedep'><input type='checkbox' name='showanswer' value='1' class='requiredvar' <?php if(!(empty($questtools['showanswer']))){echo 'checked';}?>><?php _e('View correct answer','wpcues-basic-quiz');?></li>
				<li class='quizmodedep'><input type='checkbox' name='showhint' value='1' class='requiredvar' <?php if(!(empty($questtools['showhint']))){echo 'checked'; }?>><?php _e('View hint','wpcues-basic-quiz');?></li>	
				<li><input type='checkbox' name='reportquest' value='1' class='requiredvar' <?php if(!(empty($questtools['reportquest']))){echo 'checked';} ?> disabled><?php _e('Report error','wpcues-basic-quiz');?><span class="procontent"></span></li>
			</ul>
		</div>
	</div>