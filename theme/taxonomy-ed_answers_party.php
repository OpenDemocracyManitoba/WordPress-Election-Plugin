<?php 
global $ed_taxonomies;
if ( 'POST' == $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['party_id'] ) ) {
	$answer_party_id = $_POST['party_id'];
	if ( can_edit_answers( 'party', $answer_party_id ) && wp_verify_nonce( $_POST['update_party_qanda_nonce'], "update_party_qanda_$answer_party_id" ) ) {
		$questions = get_qanda_questions( 'party', get_term( $answer_party_id, $ed_taxonomies['answer_party'] ) );
		foreach ( $questions as $answer_id => $question ) {
			$answer = array(
				'ID' => $answer_id,
				'post_content' => $_POST["question_$answer_id"],
			);
			wp_update_post( $answer );
		}
	} else {
		wp_die( "Unauthorized access" );
	}
}


$answer_party = get_queried_object();
$party = get_candidate_party_from_answer_party( $answer_party );
$party_id = $party['id'];

$has_qanda = count( $party['answers'] ) > 0;
$show_edit = can_edit_answers( 'party', $party_id );
$questions = get_qanda_questions( 'party', get_queried_object() );

get_header(); ?>
<h2 class="title"><?php echo $party['long_title']; ?></h2>
<div class="flow_it">
	<div class="one_column_flow">
		<div class="flow_it">
			<div class="parties">
				<?php display_party( $party ); ?>	
			</div>
		</div>
	</div>
	<?php if ( $show_edit && ! empty( $questions ) ) : ?>
		<div class="three_columns q">
			<h2>Questionnaire</h2>
			<p>Please enter your responses for the questions listed below. Questions that do not have a response will not be displayed on the site.<p>
			<form id="party" class="post-edit front-end-form" method="post" enctype="multipart/form-data">
				<input type="hidden" name="party_id" value="<?php echo $answer_party->term_id; ?>" />
				<?php wp_nonce_field( "update_party_qanda_{$answer_party->term_id}", 'update_party_qanda_nonce' ); ?>
				
				<?php foreach ( $questions as $answer_id => $question ) : ?>
					<p><strong><?php echo $question; ?></strong></p>
					<p><?php wp_editor( isset( $party['answers'][$question] ) ? $party['answers'][$question] : '', "question_$answer_id" ); ?></p>
				<?php endforeach; ?>
				<input type="submit" id="submit_answers" value="Update Answers" />
			</form>
		</div>
	<?php endif; ?>
	<?php if ( $has_qanda ) : ?>
	<div class="three_columns questionnaire">
		<h2 id='qanda'><?php echo $show_edit ? 'Current ' : ''; ?>Questionnaire Response</h2>
		<?php foreach ( $party['answers'] as $question => $answer ) : ?>
			<p><strong><?php echo $question; ?></strong></p>
			<p><?php echo $answer; ?></p>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>