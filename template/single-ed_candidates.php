<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

get_header(); 
$candidate_id = get_the_ID();
require_once plugin_dir_path( __FILE__ ) . 'ed_candidates.php';
require_once plugin_dir_path( __FILE__ ) . 'ed_candidates_party.php';
require_once plugin_dir_path( __FILE__ ) . 'ed_candidates_constituency.php';
$display_constituency = true;
$display_name = false;
$display_news = false;
$display_party = true;
$display_incumbent = 'constituency';
 ?>

<div id="primary">
    <div id="content" role="main">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header>
				<h2 class="title"><?php echo $name; ?></h2>
			</header>
			<?php require plugin_dir_path( __FILE__ ) . 'ed_candidate_details.php'; ?>
		</article>
	</div>
</div>
<?php get_footer(); ?>
