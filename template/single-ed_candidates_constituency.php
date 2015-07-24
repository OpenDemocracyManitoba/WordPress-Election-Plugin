<?php

get_header(); 
$constituency_id = get_queried_object()->term_id; 
$args = array(
	'post_type' => 'ed_candidates',
	'tax_query' => array(
		array(
			'taxonomy' => 'ed_candidates_constituency',
			'terms' => array( $constituency_id ),
			'field' => 'term_id',
		),
	),
	'orderby' => 'rand',
);

$the_query = new WP_Query( $args );
require_once plugin_dir_path( __FILE__ ) . 'ed_candidates_constituency.php';
?>
<div id="primary">
    <div id="content" role="main">
		<?php while ( $the_query->have_posts() ) :
			$the_query->the_post();
			$candidate_id = get_the_ID();
			unset( $party_id );
			require plugin_dir_path( __FILE__ ) . 'ed_candidates.php';
			require plugin_dir_path( __FILE__ ) . 'ed_candidates_party.php';
			require plugin_dir_path( __FILE__ ) . 'ed_news_articles.php'; ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class='politician'>
					<div class='image' style='border-bottom: 8px solid <?php echo $party_colour ?>;'>
						<img alt='<?php echo $name ?>' src='<?php echo $image_url ?>' />
						<?php if ( $party_leader ) :?>
							<p>party leader</p>
						<?php endif ?>
					</div>
					<p class='name'><strong><a href='<?php echo $candidate_url ?>'><?php echo $name; ?></a></strong>
					<?php if ( $incumbent_year ) : ?>
						<p class='since'>Incumbent since <?php echo $incumbent_year; ?></p>
					<?php endif ?>
					</p>
					<?php if ( $website ) : ?>
						<p class='election_website'>
							<a href='<?php echo $website ?>'>Election Website</a>
						</p>
					<?php endif ?>
					<p class='icons'>
						<?php foreach ( $icon_data as $icon ) : ?>
							<?php if ( $icon['url'] ) : ?>
								<a href='<?php echo $icon['url']; ?>'>
							<?php endif ?>
							<img alt='<?php echo $icon['alt']; ?>' src='<?php echo $icon['src']; ?>' />
							<?php if ( $icon['url'] ): ?>
								</a>
							<?php endif ?>
						<?php endforeach ?>
					</p>
					<p class='news'>News: <a href='<?php echo $candidate_url; ?>'><?php echo $news_count; ?> Related Articles</a></p>
					<p class='candidate_party'>Political Party: <a href='<?php echo $party_url; ?>'><?php echo $party; ?></a></p>
					<?php if ( $phone ) : ?>
						<p class='phone'>Phone: <?php echo $phone; ?></p>
					<?php endif ?>
				</div>
			</article>
		<?php endwhile ?>
	</div>
</div>
<?php get_footer(); ?>
