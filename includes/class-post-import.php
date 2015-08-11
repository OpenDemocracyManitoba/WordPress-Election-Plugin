<?php

class Post_Import {
	static function read_csv_line( $csv, $headings )
	{
		$data = fgetcsv( $csv );
		return array_combine( $headings, $data );
	}
	
	static function get_or_create_term( $taxonomy, $data, $term_fields, $parent_field, $mode ) {
		$args = array();
		$term = null;
		$name = $data['name'];
		$slug = $data['slug'];
		$description = in_array( 'description', $term_fields ) ? $data['description'] : '';
		if ( empty( $data[$parent_field] ) ) {
			$parent = 0;
		} else {
			$parent_term = get_term_by( 'slug', $data[$parent_field], $taxonomy );
			$parent = $parent_term ? $parent_term->term_id : 0;
		}
		
		if ( term_exists( $name, $taxonomy ) )
		{
			$term = get_term_by( 'name', $name, $taxonomy, ARRAY_A );
		}
		
		if ( !empty( $slug ) ) {
			$args['slug'] = $slug;
			$term_slug = get_term_by( 'slug', $slug, $taxonomy, ARRAY_A );
			if ( $term_slug )
			{
				if ( $term ) {
					if ( $term_slug['term_id'] != $term['term_id'] && 'overwrite' == $mode ) {
						return false;
					}
				} else {
					$term = $term_slug;
				}
			}
		}
		
		if ( !$term ) {
			if ( $description ) {
				$args['description'] = $description;
			}
			if ( $parent ) {
				$args['parent'] = $parent;
			}
			
			$term = wp_insert_term( $name, $taxonomy, $args );
		} else {
			$args = array( 'name' => $name );
			if ( !empty( $slug ) && 'overwrite' == $mode) {
				$args['slug'] = $slug;
			}
			if ( !empty( $parent ) && ( !$term['parent'] || 'overwrite' == $mode ) ) {
				$args['parent'] = $parent;
			}
			if ( !empty( $description ) && ( !$term['description'] || 'overwrite' == $mode ) ) {
				$args['description'] = $description;
			}
			wp_update_term( $term['term_id'], $taxonomy, $args );
				
		}
		return $term;
	}
	
	static function add_image_data( $data, $field_name, $post_id = 0 ) {
		$attachment_id = 0;
		if ( !empty( $data["{$field_name}_base64"] ) && !empty( $data["{$field_name}_filename"] ) ) {
			$file_name = $data["{$field_name}_filename"];
			$src_name = tempnam( "tmp", "img" );
			$src = fopen( $src_name, 'wb' );
			fputs( $src, base64_decode( $data["{$field_name}_base64"] ) );
			fclose( $src );
		} elseif ( !empty( $data["{$field_name}_url"] ) ) {
			$file_name = $data["{$field_name}_url"];
			$src_name = wp_download( $file_name );
			if ( is_wp_error( $src_name ) )
			{
				return 0;
			}
		} else {
			return 0;
		}
		$desc = !empty( $data["{$field_name}_description"] ) ? $data["{$field_name}_description"] : '';
		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file_name, $matches );
		$file_array = array();
		$file_array['name'] = basename( $matches[0] );
		$file_array['tmp_name'] = $src_name;
		
		$id = media_handle_sideload( $file_array, $post_id, $desc );
		
		return $id;
	}
	
	static function get_taxonomy_terms( $taxonomies, $data ) {
		$taxonomy_data = array();
		foreach ( $taxonomies as $taxonomy_name => $taxonomy_label ) {
			if ( !empty( $data[$taxonomy] ) ) {
				$term = get_term_by( 'slug', $data[$taxonomy_label], $taxonomy_name );
				$taxonomy_data[$taxonomy_name][] = $term->term_id;
			}
		}
		
		return $taxonomy_data;
	}
	
	static function get_or_create_post( $post_type, $current_posts, $data, $post_fields, $mode ) {
		$posts_by_title = $current_posts['post_title'];
		$posts_by_name = $current_posts['post_name'];
		$post = null;
		if ( isset( $post_fields['post_name'] ) && !empty( $data[$post_fields['post_name']] ) && !empty( $posts_by_title[$data[$post_fields['post_name']]] ) ) {
			$post = $posts_by_name[$data[$post_fields['post_name']]];
		}
		if ( isset( $post_fields['post_title'] ) && !empty( $data[$post_fields['post_title']] ) && !empty( $posts_by_title[$data[$post_fields['post_title']]] ) ) {
			$posts_title = $posts_by_title[$data[$post_fields['post_title']]];
			if ( count( $posts_title ) == 1 ) {
				if ( $post && $post->ID != $posts_title[0]->ID ) {
					return 0;
				} elseif ( !$post ) {
					$post = $posts_title[0];
				}
			} elseif ( $post && count( $posts_title ) > 1 ) {
				$found = false;
				foreach ( $posts_title as $post_title ) {
					if ( $post_title->ID == $post->ID ) {
						$found = true;
						break;
					}
				}
				
				if ( !$found )
				{
					return 0;
				}
			} else {
				return 0;
			}
		}
		
		if ( !$post ) {
			$args = array( 'post_type' => $post_type, 'post_status' => 'publish' );
			foreach ( $post_fields as $post_name => $field_name ) {
				$args[$post_name] = $data[$field_name];
			}
			
			$post_id = wp_insert_post( $args );
			return get_post( $post_id );
		}
		
		$args = array( 'post_type' => $post_type, 'ID' => $post->ID );
		$updated = false;
		foreach ( $post_fields as $post_name => $field_name ) {
			if ( 'overwrite' == $mode || empty( $post->$post_name ) ) {
				$args[$post_name] = $data[$field_name];
				$updated = true;
			}
		}
		
		if ( $updated ) {
			wp_update_post( $args );
		}
		
		return $post;
	}
	
	static function get_current_posts( $post_type ) {
		$query = new WP_Query( array( 'post_type' => $post_type ) );
		$posts = array('post_title' => array(), 'post_name' => array());
		while ( $query->have_posts() ) {
			$query->the_post();
			$post = $query->post;
			$posts['post_title'][$post->post_title][] = $post;
			$posts['post_name'][$post->post_name] = $post;
		}
		
		return $posts;
	}

	static function import_post_csv( $csv, $mode, $post_type, $post_meta, $post_fields, $post_image_heading, $taxonomies ) {
		$meta_fields = empty( $post_meta ) ? array() : $post_meta->get_field_names();
		$headings = fgetcsv( $csv );
		$found = true;
		foreach ( array_merge( $post_fields, $taxonomies, $meta_fields ) as $field ) {
			$found &= in_array( $field, $headings );
		}
		if ( $post_image_heading ) {
			foreach ( array( 'base64', 'filename', 'url' ) as $suffix ) {
				$post_image_fields[$suffix] = "{$post_image_heading}_$suffix";
			}
			
			$found &= ( in_array( $post_image_fields['base64'], $headings ) && in_array( $post_image_fields['filename'], $headings ) ) || in_array( $post_image_fields['url'], $headings );
		}
		
		if ( !$found ) {
			return false;
		}
		
		$current_posts = self::get_current_posts( $post_type );
		while ( ( $data = fgetcsv( $csv ) ) !== false ) {
			$data = array_combine( $headings, $data );
			$post = self::get_or_create_post( $post_type, $current_posts, $data, $post_fields, $mode );
			
			if ( $post_image_heading ) {
				$image_id = get_post_thumbnail_id( $post->ID );
				if ( 'overwrite' == $mode || empty( $image_id ) ) {
					$attachment_id = self::add_image_data( $data, $post_image_heading, $post->ID );
					set_post_thumbnail( $post, $attachment_id );
				}
			}
			
			foreach ( $taxonomies as $taxonomy_name => $taxonomy_label ) {
				if ( !empty( $data[$taxonomy_label] ) ) {
					$new_term = get_term_by( 'slug', $data[$taxonomy_label], $taxonomy_name );
					$existing_terms = wp_get_post_terms( $post->ID, $taxonomy_name, array( 'fields' => 'ids' ) );
					if ( 'overwite' == $mode || !$existing_terms ) {
						wp_set_object_terms( $post->ID, $new_term->term_id, $taxonomy_name );
					}
				}
			}
			
			if ( $post_meta ) {
				$post_meta->update_field_values( $post->ID, $data, $mode );
			}
		}
	}
	
	function import_taxonomy_csv( $csv, $mode, $taxonomy, $taxonomy_name, $taxonomy_fields, $taxonomy_meta = null, $parent_field = null ) {
		$headings = fgetcsv( $csv );
		$found = true;
		$meta_fields = empty( $taxonomy_meta ) ? array() : $taxonomy_meta->get_field_names( 'non_image' );
		$image_fields = empty( $taxonomy_meta ) ? array() : $taxonomy_meta->get_field_names( 'image' );
		foreach ( array_merge( $taxonomy_fields, $meta_fields ) as $field ) {
			$found &= in_array( $field, $headings );
		}
		if ( $parent_field ) {
			$found &= in_array( $parent_field, $headings );
		}
		foreach ( $image_fields as $field ) {
			$found &= ( in_array( $field['base64'], $headings ) && in_array( $field['filename'], $headings ) ) || in_array( $field['url'], $headings );
		}
		
		if ( !$found )
		{
			return false;
		}

		while ( ( $data = fgetcsv( $csv ) ) !== false ) {
			$data = array_combine( $headings, $data );
			$term = self::get_or_create_term( $taxonomy_name, $data, $taxonomy_fields, $parent_field, $mode );
			$current_meta = get_tax_meta_all( $term['term_id'] );
			foreach ( $meta_fields as $field_name ) {
				if ( $mode == 'overwrite' || empty( $current_meta[$field_name] ) ) {
					update_tax_meta( $term['term_id'], $field_name, $data[$field_name] );
				}
			}
			foreach ( $image_fields as $field_name ) {
				if ( $mode == 'overwrite' || empty( $current_meta[$field_name['']] ) ) {
					$attachment_id = self::add_image_data( $data, $field_name[''] );
					if ( $attachment_id ) {
						$image_data = array (
							'id' => $attachment_id,
							'url' => wp_get_attachment_url( $attachment_id ),
						);
						update_tax_meta( $term['term_id'], $field_name[''], $image_data );
					}
				}
			}
		}
		
		return true;
	}
}