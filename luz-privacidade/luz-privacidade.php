<?php
/**
 * Plugin Name: Luz Uberlândia — Privacidade e contato
 * Description: Páginas editáveis, aviso de privacidade e formulário de contato.
 * Version: 1.0.0
 */
defined('ABSPATH') || exit;

register_activation_hook(__FILE__, function () {
    foreach (array('privacidade' => 'Política de Privacidade', 'cookies' => 'Política de Cookies') as $key => $title) {
        $slug = 'politica-de-' . $key;
        $page = get_page_by_path($slug);
        if (!$page) {
            $content = file_get_contents(__DIR__ . '/' . $key . '.html');
            $id = wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>$title,'post_name'=>$slug,'post_content'=>wp_slash($content)), true);
            if (!is_wp_error($id)) {
                update_option('luz_' . $key . '_page', $id);
                if ($key === 'privacidade') update_option('wp_page_for_privacy_policy', $id);
            }
        } else {
            update_option('luz_' . $key . '_page', $page->ID);
        }
    }
});

function luz_policy_url($key) {
    $id = (int) get_option('luz_' . $key . '_page');
    return $id ? get_permalink($id) : home_url('/politica-de-' . $key . '/');
}

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('luz-privacy', plugins_url('privacidade.css', __FILE__), array(), '1.0.0');
    wp_enqueue_script('luz-privacy', plugins_url('privacidade.js', __FILE__), array(), '1.0.0', true);
});

add_action('wp_footer', function () {
    ?>
    <nav class="luz-legal" aria-label="Privacidade">
      <a href="<?php echo esc_url(luz_policy_url('privacidade')); ?>">Política de Privacidade</a>
      <a href="<?php echo esc_url(luz_policy_url('cookies')); ?>">Política de Cookies</a>
      <button type="button" data-luz-reopen>Privacidade e cookies</button>
    </nav>
    <aside id="luz-privacy-notice" class="luz-notice" aria-label="Aviso de privacidade e cookies" hidden>
      <h2>Sua privacidade importa</h2>
      <p>Usamos armazenamento local para lembrar este aviso. Recursos de segurança da hospedagem podem usar cookies necessários. No formulário, nome, WhatsApp e e-mail são usados para responder ao seu contato, mediante autorização específica.</p>
      <p><a href="<?php echo esc_url(luz_policy_url('privacidade')); ?>">Política de Privacidade</a> · <a href="<?php echo esc_url(luz_policy_url('cookies')); ?>">Política de Cookies</a></p>
      <button type="button" data-luz-close>Entendi</button>
    </aside>
    <?php
});

add_shortcode('luz_contato', function () {
    ob_start();
    $result = isset($_GET['luz_result']) ? sanitize_key(wp_unslash($_GET['luz_result'])) : '';
    if ($result === 'ok') echo '<p role="status">Seu contato foi encaminhado para processamento. Se não receber retorno, escreva para contato@luzuberlandia.org.br.</p>';
    if ($result === 'error') echo '<p role="alert">Não foi possível encaminhar. Confira os campos e tente novamente ou escreva para contato@luzuberlandia.org.br.</p>';
    ?>
    <form class="luz-contact" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <input type="hidden" name="action" value="luz_contact">
      <?php wp_nonce_field('luz_contact', 'luz_nonce'); ?>
      <label>Nome completo <input name="full_name" autocomplete="name" required maxlength="150"></label>
      <label>Telefone (WhatsApp) <input type="tel" name="phone" autocomplete="tel" required maxlength="25" inputmode="tel"></label>
      <label>E-mail <input type="email" name="email" autocomplete="email" required maxlength="254"></label>
      <div class="luz-honeypot" aria-hidden="true"><label>Não preencha este campo<input name="website" tabindex="-1" autocomplete="off"></label></div>
      <p>Seus dados serão enviados à equipe da igreja para responder a esta solicitação. Não use este formulário para enviar dados sensíveis ou de outras pessoas.</p>
      <label class="luz-consent"><input type="checkbox" name="consent" value="yes" required> <span>Autorizo a Igreja da Paz Ministério Luz para os Povos a usar meu nome, WhatsApp e e-mail para responder a este contato. Compreendo o contexto religioso deste canal, sem que isso declare minha religião ou filiação. Li a <a href="<?php echo esc_url(luz_policy_url('privacidade')); ?>">Política de Privacidade</a> e posso revogar esta autorização por contato@luzuberlandia.org.br.</span></label>
      <button type="submit">Solicitar contato</button>
    </form>
    <?php return ob_get_clean();
});

add_action('admin_post_luz_contact', 'luz_handle_contact');
add_action('admin_post_nopriv_luz_contact', 'luz_handle_contact');
function luz_handle_contact() {
    $return = wp_get_referer();
    $return = wp_validate_redirect($return, home_url('/'));
    $return = remove_query_arg('luz_result', $return);
    $fail = function () use ($return) {
        wp_safe_redirect(add_query_arg('luz_result', 'error', $return)); exit;
    };
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') $fail();
    $nonce = isset($_POST['luz_nonce']) && is_string($_POST['luz_nonce']) ? sanitize_text_field(wp_unslash($_POST['luz_nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'luz_contact')) $fail();
    foreach (array('full_name','phone','email','consent','website') as $field) {
        if (!isset($_POST[$field]) || !is_string($_POST[$field])) $fail();
    }
    if ($_POST['website'] !== '' || $_POST['consent'] !== 'yes') $fail();
    $name = sanitize_text_field(wp_unslash($_POST['full_name']));
    $phone = sanitize_text_field(wp_unslash($_POST['phone']));
    $email = sanitize_email(wp_unslash($_POST['email']));
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($name) < 3 || strlen($name) > 150 || !is_email($email) || strlen($email) > 254 || strlen($digits) < 10 || strlen($digits) > 15 || strlen($phone) > 25) $fail();
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    $key = 'luz_rl_' . hash_hmac('sha256', $ip, wp_salt('nonce'));
    $count = (int) get_transient($key);
    if ($count >= 5) $fail();
    set_transient($key, $count + 1, 10 * MINUTE_IN_SECONDS);
    $message = "Solicitação de contato pelo site\n\nNome: $name\nWhatsApp: $phone\nE-mail: $email\n\nAutorização: sim, específica para resposta ao contato.\nAviso: versão 1, 22/11/2026\nData UTC: " . gmdate('c') . "\n\nNão incluir automaticamente em campanhas. Tratar conforme Política de Privacidade.";
    $sent = wp_mail('contato@luzuberlandia.org.br', 'Nova solicitação de contato — Luz Uberlândia', $message, array('Reply-To: ' . $email));
    wp_safe_redirect(add_query_arg('luz_result', $sent ? 'ok' : 'error', $return)); exit;
}
