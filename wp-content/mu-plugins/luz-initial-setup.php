<?php
/**
 * Plugin Name: Luz Uberlândia — configuração da primeira instalação
 * Description: Prepara o tema, o contato e as políticas somente ao instalar um WordPress novo.
 */
defined('ABSPATH') || exit;

add_action('wp_install', function ($user) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $plugin = 'luz-privacidade/luz-privacidade.php';
    $result = activate_plugin($plugin);
    if (is_wp_error($result)) {
        update_option('luz_setup_error', $result->get_error_message());
        return;
    }
    switch_theme('luz-uberlandia');
    if (!get_page_by_path('contato')) {
        wp_insert_post(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => 'Fale com a gente',
            'post_name' => 'contato',
            'post_content' => '<!-- wp:shortcode -->[luz_contato]<!-- /wp:shortcode -->',
            'post_author' => $user->ID,
        ));
    }
    update_option('blogname', 'Luz Uberlândia');
    update_option('blogdescription', 'Transformando o mundo, uma vida de cada vez.');
    update_option('timezone_string', 'America/Sao_Paulo');
    update_option('default_comment_status', 'closed');
    update_option('default_ping_status', 'closed');
    // A URL provisória não deve ser indexada antes da publicação definitiva.
    update_option('blog_public', 0);
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure('/%postname%/');
    flush_rewrite_rules();
    update_option('luz_initial_setup_completed', 1);
});
