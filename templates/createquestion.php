		<div id="questions_section">	
		<div id='divbuttons'>
			<input type='button' name='addquestion' id='add_question_button' value='Add Question' class='button button-secondary'>
			<input type='button' name='addsection' id='add_section_button' value='Add Section' class='button button-secondary' disabled><span class="procontent"></span>
			<input type='button' id='importquestion' name='importquestion' value='Import Questions' class='button button-secondary' disabled><span class="procontent"></span>
		</div>
		<div id='quizeditor' class="hiddendiv">
			
		</div>
		<?php $entityids=$WpCueBasicQuiz->entityids($post_id); ?>
		<div id='addedquestion' <?php if(!empty($entityids)){echo 'style="display:block;"';}else{echo 'style="display:none;"';}?>>
			<h2> Added Questions</h2>
			<div class='addedquesttools hiddendiv'>
				<span class='questview' title='View Showing only questions'></span>
				<span class='normview selected' title='Normal view with Questions in Sections'></span>
				<span class='sortquest' title='sort questions and sections'></span>
			</div>
			<table id='questiontable' class="widefat fixed">
				<tbody>
					<?php 
						if(!empty($entityids)){
							WpCueBasicQuestion::getadded_questions($entityids,$post_id);
						}
						echo '</tbody></table>'; 	
					?>
		</div>
		<div id="disabledentities"></div>
		</div>
		<div class='hiddendiv' id='clonequestsec'></div>