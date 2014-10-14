<?php

if (!current_user_can('manage_options')) {
    wp_die(__('Vous n\'avez pas les autorisations suffisantes pour administrer cette page'));
}

echo '<div class="wrap">';
echo '<h2>Diatem WpGetConfig : configuration de l\'accès REST sécurisé</h2>';

echo '<form method="post" action="options.php">';

settings_fields( 'wpgetconfig-group' );
do_settings_sections( 'wpgetconfig-group' );

echo '<table class="form-table">';
echo '<tr valign="top">';
echo '<th scope="row">Clé publique</th>';
echo '<td><input type="text" name="publicKey" value="'.esc_attr( get_option('publicKey')).'" /></td>';
echo '</tr>';
echo '<tr valign="top">';
echo '<th scope="row">Clé privée</th>';
echo '<td><input type="text" name="privateKey" value="'.esc_attr( get_option('privateKey')).'" /></td>';
echo '</tr>';
echo '</table>';

submit_button();
echo '</form>';
echo '</div>';

