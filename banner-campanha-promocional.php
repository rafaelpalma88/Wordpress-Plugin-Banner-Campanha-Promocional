<?php

/**
 * Banner Campanha Promocional
 *
 * Plugin Name: Banner Campanha Promocional
 * Plugin URI:  
 * Description: Insere campanhas promocionais da Certisign nos posts.
 * Version:     1.0
 * Author:      Rafael Costa Palma
 * Author URI:  https://gitlab.com/rafaelpalma88/
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: banner-campanha-promocional
 * Domain Path: 
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

// Image size for single posts
add_image_size( 'banner-promocional-size', 800, 200 );

require dirname(__FILE__).'/lib/class-tgm-plugin-activation.php';

function check_required_plugins() {
    
    $plugins = array(

        array(
            'name' => 'Meta Box',
            'slug' => 'meta-box',
            'required' => true,
            'force_activation' => true,
            'force_desactivation' => false,

        ),

    );

    $config = array(
		'id'           => 'tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'plugins.php',            // Parent menu slug.
		'capability'   => 'update_plugins',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.		
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'theme-slug' ),
			'menu_title'                      => __( 'Install Plugins', 'theme-slug' ),
			'nag_type'                        => 'updated', // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
		)
    );
    
    tgmpa( $plugins, $config );

}

add_action( 'tgmpa_register', 'check_required_plugins' );

function insere_banner_dentro_conteudo( $content ) {

    global $post;
    $categories_post = get_the_category( $post->ID );
	
    $args_promocional = array(  
        'post_type' => 'banner_promocional',
        'post_status' => 'publish',                
    );

    $the_query = new WP_Query( $args_promocional );

    if(is_single()) {
        if ( $the_query->have_posts() ) {
            
            $exibir_proximo_post = true;
			$banner_promocional;            
            $banner_promocional .= '<div>';
            while ( $the_query->have_posts() ) {
                $the_query->the_post();
                $categories_banner = get_the_category();
				$localexibicao = esc_attr( get_post_meta( get_the_ID(), 'banner_customizado_local_exibicao', true ) );
                $link = esc_attr( get_post_meta( get_the_ID(), 'banner_customizado_link', true ) );
                $tag_google_analytics = esc_attr( get_post_meta( get_the_ID(), 'banner_customizado_tag_google_analytics', true ) );
				
				if($exibir_proximo_post) {
				
					foreach($categories_banner as $category_banner) { 
											
						foreach($categories_post as $category_post) {													
						
								if($category_post == $category_banner) {									
									$banner_promocional .=  '<a href="' . $link . '" target="_blank" onclick="' . $tag_google_analytics . '">';
									$banner_promocional .=  get_the_post_thumbnail( $post_id, 'full' );
									$banner_promocional .=  '</a>';
									
									$exibir_proximo_post = false;		
								
								}							
							
						}
					}	
				}				
            }
            $banner_promocional .= '</div>';
            
            wp_reset_postdata();
        } 
    }    
  
    $conteudoTotal;
    if($localexibicao == 'acima' || $localexibicao == 'acimaeabaixo') { $conteudoTotal .= $banner_promocional; }
    $conteudoTotal .= $content;
    if($localexibicao == 'abaixo' || $localexibicao == 'acimaeabaixo') { $conteudoTotal .= $banner_promocional; }

    return $conteudoTotal;
   

}

add_filter('the_content','insere_banner_dentro_conteudo');

function create_post_type_banner_promocional() {
    $args = array(
      'public' => true,
      'label'  => 'Banners Promocionais',
      'supports' => array('title','thumbnail'),
      'taxonomies'  => array( 'category' ),
    );
    register_post_type( 'banner_promocional', $args );
}
add_action( 'init', 'create_post_type_banner_promocional' );

function your_prefix_get_meta_box( $meta_boxes ) {
	
	$meta_boxes[] = array(
		'id' => 'untitled',
		'title' => esc_html__( 'Informações Customizadas', 'metabox-online-generator' ),
		'post_types' => array('banner_promocional', 'page' ),
		'context' => 'advanced',
		'priority' => 'default',
		'autosave' => 'false',
		'fields' => array(
			array(
				'id' => 'banner_customizado_link',
				'type' => 'text',
				'name' => esc_html__( 'Link', 'metabox-online-generator' ),
            ),
            array(
				'id' => 'banner_customizado_tag_google_analytics',
				'type' => 'text',
				'name' => esc_html__( 'Tag Google Analytics', 'metabox-online-generator' ),
			),
			array(
				'id' => 'banner_customizado_local_exibicao',
				'name' => esc_html__( 'Local de Exibição', 'metabox-online-generator' ),
				'type' => 'select',
				'placeholder' => esc_html__( 'Selecione o local de exibição', 'metabox-online-generator' ),
				'options' => array(
					'acima' => 'Acima',
					'abaixo' => 'Abaixo',
					'acimaeabaixo' => 'Acima e abaixo',
				),
			),
		),
	);

	return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'your_prefix_get_meta_box' );