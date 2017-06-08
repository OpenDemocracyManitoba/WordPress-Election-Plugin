<?php
global $ed_taxonomies;
$answer_candidate = get_queried_object();
$candidate = get_candidate_from_answer_candidate( $answer_candidate );
$candidate_id = $candidate['id'];
$can_edit = can_edit_answers( 'candidate', $candidate_id );
$questions = get_qanda_questions( 'candidate', $answer_candidate );

if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
    $nonce_verified = wp_verify_nonce( $_POST['update_candidate_qanda_nonce'], "update_candidate_qanda_{$answer_candidate->term_id}" );
	if ( $can_edit && $nonce_verified ) {
		foreach ( $questions as $answer_id => $question ) {
			$answer = array(
				'ID' => $answer_id,
				'post_content' => $_POST["question_$answer_id"],
			);
			wp_update_post( $answer );
		}
        $candidate = get_candidate_from_answer_candidate( $answer_candidate );
        $questions = get_qanda_questions( 'candidate', $answer_candidate );
	} else {
        wp_die( "Unauthorized access" );
    }
}

$has_qanda = count( $candidate['answers'] ) > 0;
$party = get_party_from_candidate( $candidate_id );
$constituency = get_constituency_from_candidate( $candidate_id );

get_header(); ?>
<h2 class="title"><?php echo $candidate['name']; ?></h2>
<div class="flow_it">
	<div class="one_column_flow">
		<div class="flow_it" >
			<div class="politicians">
				<?php display_candidate( $candidate, $constituency, $party, array( 'constituency', 'party' ), 'constituency' ); ?>
			</div>
		</div>
	</div>
	<?php if ( $can_edit && ! empty( $questions ) ) : ?>
		<div class="three_columns q">
			<h2>Questionnaire</h2>
			<p>Please enter your responses for the questions listed below. Questions that do not have a response will not be displayed on the site.<p>
			<form id="candidate" class="post-edit front-end-form" method="post" enctype="multipart/form-data">
				<input type="hidden" name="candidate_id" value="<?php echo $answer_candidate->term_id; ?>" />
				<?php wp_nonce_field( "update_candidate_qanda_{$answer_candidate->term_id}", 'update_candidate_qanda_nonce' ); ?>
				
				<?php foreach ( $questions as $answer_id => $question ) : ?>
					<p><strong><?php echo $question; ?></strong></p>
					<p><?php wp_editor( isset( $candidate['answers'][$question] ) ? $candidate['answers'][$question] : '', "question_$answer_id", array('teeny' => true, 'media_buttons' => false ) ); ?></p>
				<?php endforeach; ?>
				<input type="submit" id="submit_answers" value="Update Answers" />
			</form>
		</div>
	<?php endif; ?>
	<?php  if ( $has_qanda ) :  ?>
	<div class="three_columns questionnaire">
		<h2 id='qanda'><?php echo $can_edit ? 'Current ' : ''; ?>Questionnaire Response</h2>
		<?php foreach ( $candidate['answers'] as $question => $answer ) :?>
			<p><strong><?php echo $question; ?></strong></p>
			<p><?php echo $answer; ?></p>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
