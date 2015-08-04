<?php

get_header(); 
require_once plugin_dir_path( __FILE__ ) . 'ed_functions.php';
$constituency_id = get_queried_object()->term_id; 
$constituency = get_constituency( $constituency_id );

if ( $constituency['children'] ) : ?> 
	<div class='flow_it'>
		<?php if ( $constituency['map'] ) : ?>
			<div class='two_columns hidden_block_when_mobile'>
				<img alt='<?php echo $constituency['name']; ?>' class='highmap' src='<?php echo $constituency['map']; ?>' usemap='#constituency_map' />
				<map id="constituency_map" name="constituency_map">
					<?php foreach ( $constituency['children'] as $name => $child ) :?>
						<?php if ( $child['coordinates'] ) : ?>
							<area alt='<?php echo $name; ?>' coords='<?php echo $child['coordinates']; ?>' href='<?php echo $child['url']; ?>' shape='poly' title='<?php echo $name; ?>'>
						<?php endif; ?>
					<?php endforeach; ?>
				</map>
			</div>
		<?php endif; ?>
		<div class='one_column'>
			<h3>The <?php echo $constituency['name']; ?> Constituencies</h3>
			<ul>
				<?php foreach ( $constituency['children'] as $name => $child ) :?>
					<li><a href="<?php echo $child['url']; ?>"><?php echo $name; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
   </div>
<?php else :
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

	?>
	<div id="primary">
		<div id="content" role="main">
			<?php while ( $the_query->have_posts() ) :
				$the_query->the_post();
				$candidate_id = get_the_ID();
				$candidate = get_candidate( $candidate_id );
				$party = get_party_from_candidate( $candidate_id );
				$candidate_news = get_candidate_news( $candidate_id )?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<?php display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'constituency' ), 'name' ); ?>
				</article>
			<?php endwhile; ?>
		</div>
	</div>
<?php endif;
get_footer(); ?>
