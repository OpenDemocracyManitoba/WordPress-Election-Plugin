<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

get_header(); 
require_once plugin_dir_path( __FILE__ ) . 'ed_functions.php';
$candidate_id = get_the_ID();
$candidate = get_candidate( $candidate_id );
$party = get_party_from_candidate( $candidate_id );
$constituency = get_constituency_from_candidate( $candidate_id );
$news = get_news( $candidate_id );
 ?>

<div id="primary">
    <div id="content" role="main">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header>
				<h2 class="title"><?php echo $candidate['name']; ?></h2>
			</header>
			<?php display_candidate( $candidate, $constituency, $party, $news, array( 'name', 'news' ), 'constituency' ); ?>
		</article>
	</div>
</div>
<?php get_footer(); ?>
