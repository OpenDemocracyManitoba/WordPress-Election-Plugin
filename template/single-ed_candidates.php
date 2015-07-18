<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

get_header(); ?>
<?php $candidate_id = get_the_ID(); ?>
<?php require plugin_dir_path( __FILE__ ) . 'ed_candidates.php' ; ?>

<div id="primary">
    <div id="content" role="main">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header>
				<h2 class="title"><?php echo $name; ?></h2>
			</header>
			<div class='politician show_constituency'>
				<div class='constituency'>
					<a href="<?php echo $constituency_url; ?>"><?php echo $constituency; ?></a>
					<?php if( $incumbent_year ) : ?>
						<p class='since'>Incumbent since <?php echo $incumbent_year; ?></p>
					<?php endif ?>
				</div>
				<div class='image' style='border-bottom: 8px solid <?php echo $party_colour ?>;'>
					<img alt='<?php echo $name ?>' src='<?php echo $image_url ?>' />
					<?php if( $party_leader ) :?>
						<p>party leader<p>
					<?php endif ?>
				</div>
				<p class='name'><strong><?php echo $name; ?></strong>
				<?php if ( $incumbent_year ) : ?>
					<span class='incumbent_link'>
						<a href='<?php echo $incumbent_link ?>' title='since <?php echo $incumbent_year ?>'>incumbent</a>
					</span>
				<?php endif ?>
				</p>
				<?php if ( $website ) : ?>
					<p class='election_website'>
						<a href='<?php echo $website ?>'>Election Website</a>
					</p>
				<?php endif ?>
				<p class='icons'>
					<?php foreach( $icon_data as $icon ) : ?>
						<?php if( $icon['url'] ) : ?>
							<a href='<?php echo $icon['url']; ?>'>
						<?php endif ?>
						<img alt='<?php echo $icon['alt']; ?>' src='<?php echo $icon['src']; ?>' />
						<?php if( $icon['url'] ): ?>
							</a>
						<?php endif ?>
					<?php endforeach ?>
				</p>
				<p class='candidate_party'>
					Political Party: <a href='<?php echo $party_url; ?>'><?php echo $party; ?></a>
				</p>
				<?php if ( $phone ) : ?>
					<p class='phone'>
						Phone: <?php echo $phone; ?>	
					</p>
				<?php endif ?>
			</div>
		</article>
	</div>
</div>
<?php get_footer(); ?>
