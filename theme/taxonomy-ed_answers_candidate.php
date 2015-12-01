<?php
global $ed_taxonomies;
if ( 'POST' == $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['candidate_id'] ) ) {
	$answer_candidate_id = $_POST['candidate_id'];
	if ( can_edit_answers( 'candidate', $answer_candidate_id ) && wp_verify_nonce( $_POST['update_candidate_qanda_nonce'], "update_candidate_qanda_$answer_candidate_id" ) ) {
		$questions = get_qanda_questions( 'candidate', get_term( $answer_candidate_id, $ed_taxonomies['answer_candidate'] ) );
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

$answer_candidate = get_queried_object();
$candidate = get_candidate_from_answer_candidate( $answer_candidate );
$candidate_id = $candidate['id'];
$party = get_party_from_candidate( $candidate_id );
$constituency = get_constituency_from_candidate( $candidate_id );
$candidate_news = get_news( $candidate['news_article_candidate_id'] );
$show_edit = can_edit_answers( 'candidate', $candidate_id );
$has_qanda = count( $candidate['answers'] ) > 0;
$questions = get_qanda_questions( 'candidate', get_queried_object() );

get_header(); ?>
<h2 class="title"><?php echo $candidate['name']; ?></h2>
<div class="flow_it">
	<div class="one_column_flow">
		<div class="flow_it" >
			<div class="politicians">
				<?php display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'constituency', 'party' ), 'constituency' ); ?>
			</div>
		</div>
	</div>
	<?php if ( $show_edit && ! empty( $questions ) ) : ?>
		<div class="three_columns q">
			<h2>Questionnaire</h2>
			<p>Please enter your responses for the questions listed below. Questions that do not have a response will not be displayed on the site.<p>
			<form id="candidate" class="post-edit front-end-form" method="post" enctype="multipart/form-data">
				<input type="hidden" name="candidate_id" value="<?php echo $answer_candidate->term_id; ?>" />
				<?php wp_nonce_field( "update_candidate_qanda_{$answer_candidate->term_id}", 'update_candidate_qanda_nonce' ); ?>
				
				<?php foreach ( $questions as $answer_id => $question ) : ?>
					<p><strong><?php echo $question; ?></strong></p>
					<p><?php wp_editor( isset( $candidate['answers'][$question] ) ? $candidate['answers'][$question] : '', "question_$answer_id" ); ?></p>
				<?php endforeach; ?>
				<input type="submit" id="submit_answers" value="Update Answers" />
			</form>
		</div>
	<?php endif; ?>
	<?php  if ( $has_qanda ) :  ?>
	<div class="three_columns questionnaire">
		<h2>Questionnaire Response</h2>
		<?php foreach ( $candidate['answers'] as $question => $answer ) :?>
			<p><strong><?php echo $question; ?></strong></p>
			<p><?php echo $answer; ?></p>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>