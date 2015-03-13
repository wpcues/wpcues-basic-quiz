<div id="quiz_options_section">
	<ul class="innertabs">
		<li><a href="#settab-1"><?php _e('Randomization','wpcues-basic-quiz');?></a></li>
		<li><a href="#settab-2"><?php _e('Login / Email','wpcues-basic-quiz');?></a></li>
		<li><a href="#settab-3"><?php _e('Display','wpcues-basic-quiz');?></a></li>
		<li><a href="#settab-4"><?php _e('Dialogs','wpcues-basic-quiz');?></a></li>
	</ul>
	<div id='settab-1'>
	</div>
	<div id='settab-2'>
		<ul>
			<li>
				<ul class='logindep <?php if(($logindep==0) || (isset($quizmeta['setting']['usl']) && ($quizmeta['setting']['usl'] == 0))){echo 'hiddendiv'; } ?>'>
			
					
					<li class='lastunansquest multilogdep <?php if(($multilogdep==0) || ((isset($quizmeta['setting']['ml'])) && ($quizmeta['setting']['ml'] == 0))){echo 'hiddendiv';} ?>'>
						<input type="checkbox" name="firstunanswerd" class='requiredvar' value='1' <?php if(!(empty($quizmeta['setting']['unanshow']))){echo 'checked';} ?> /> Start from page having first unanswered question 
							
					</li>
				</ul>
			</li>
			
		</ul>
		<ul>
			</li>
		</ul>
		<div class='timerdetails'>
		<ul>
		
		</ul>
		</div>
	</div>
	<div id='settab-3'>
		<ul>
				
				
				
				<li class='quizmodedep'><input type='checkbox' name='undiscloseans' class='requiredvar' value='1' <?php if(isset($quizmeta['setting']['undiscloseans']) && ($quizmeta['setting']['undiscloseans']==1)){echo 'checked';} ?>>Do not disclose correct answer finally at all</li>
				<li class='quizmodedep'><input type='checkbox' name='undiscloseanstried' value='1' class='requiredvar' <?php if(isset($quizmeta['setting']['undiscloseanstried']) && ($quizmeta['setting']['undiscloseanstried']==1)){echo 'checked';} ?>>Do not disclose correct answer for untried and wrong attempts</li>
			</ul>
	</div>	
	<div id='settab-4'>
		<ul class="innertabs"><li><a href='#dialogtab-1'>Submit dialog</a></li>
			<li><a href='#dialogtab-2'>AutoSubmit dialog</a></li>
		</ul>
		<div id='dialogtab-1'>
			
		</div>
		<div id='dialogtab-2'>
			<ul><li><span class='entitymsg'>(will be displayed when quiz is submitted automoatically after complition of time assigned to quiz)</span></li>
				<li><?php if(!(isset($quizmeta['setting']['atuosubmitdialog']))){$quizmeta['setting']['atuosubmitdialog']="Time's Up !";} wp_editor($quizmeta['setting']['atuosubmitdialog'],'quizsubmitdialog-'.$post_id,array('wpautop'=>false,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>20,'editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>false,'editor_height'=>200));?></li>
				<li><ul><li>Height (px) : 
					<input type='text' name='autodialogheight' class='requiredvar' value='<?php if(isset($quizmeta['setting']['autodialogheight'])){echo $quizmeta['setting']['autodialogheight'];}else{echo 400;} ?>' style="width:100px;"></li>
				<li>Width (px) : <input type='text' name='autodialogwidth' class='requiredvar' value='<?php if(isset($quizmeta['setting']['autodialogheight'])){echo $quizmeta['setting']['autodialogheight'];}else{echo 400;} ?>'  style="width:100px;"></li></ul></li>
			</ul>
		</div>
	</div>
</div>