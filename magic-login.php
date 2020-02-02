<?php

/**
 * Plugin Name: ✨ Magic Login ✨
 * Plugin URI:  https://princeboss.com
 * Description: Login to the dashboard in just one click..
 * Version:     1.0.0
 * Author:      Prince
 * Author URI:  http://princeboss.com
 */

defined( 'ABSPATH' ) || die();

class Magic_Login {

	public function __construct() {
		add_action( 'wp_ajax_magic_login', [ $this, 'magic_login' ] );
		add_action( 'wp_ajax_nopriv_magic_login', [ $this, 'magic_login' ] );
		add_action( 'login_enqueue_scripts', [ $this, 'login_enqueue_scripts' ] );
		add_action( 'login_form', [ $this, 'login_form' ] );
	}


	public function login_enqueue_scripts() {
		wp_enqueue_script( 'jquery' );
	}

	public function login_form() { ?>
        <p>
            <label for="magic_login" style="width: 100%;">Magic Login<br>
                <select id="magic_login" style="width:100%;margin:10px 0 30px;">
                    <option value='-1' selected='selected'>Choose username...</option>

					<?php
					$users = get_users( 'number=100&orderby=ID' );

					foreach ( $users as $user ) {

						$caps     = $user->{$user->cap_key};
						$wp_roles = new WP_Roles();

						$roles = [];
						foreach ( $wp_roles->role_names as $role => $name ) {
							if ( array_key_exists( $role, $caps ) ) {
								$roles[] = $role;
							}
						}

						printf(
							'<option value="%1$s">%2$s - (%3$s)</option>>',
							$user->ID,
							$user->user_login,
							! empty( $roles ) ? implode( ', ', $roles ) : ''
						);

					}

					// Remember redirect URL or default to admin
					$url = get_admin_url();
					if ( ! empty( $_REQUEST['redirect_to'] ) ) {
						$url = esc_url( $_REQUEST['redirect_to'] );
					}

					?>
                </select>
        </p>

        <script type="text/javascript">
            (function ($) {
                $(document).ready(function () {

                    $("#magic_login").change(function () {
                        const user_id = $(this).val();

                        if (user_id !== '-1') {
                            const login = {
                                action: 'magic_login',
                                user_id: user_id
                            };

                            $.post(
                                '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                                login,
                                response => response < 1 ? alert('Login error: ' + response) : window.location.href = '<?php echo $url; ?>'
                            )
                        }
                    });

                });
            })(jQuery);
        </script>
		<?php
	}

	public function magic_login() {
		if ( empty( $_REQUEST['user_id'] ) ) {
			wp_send_json_error();
		}
		wp_set_auth_cookie( intval( $_REQUEST['user_id'] ), true );
		wp_send_json_success( true );
	}
}

new Magic_Login();