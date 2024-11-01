<?php 
namespace SUL;
//Prevent direct requests
defined( 'ABSPATH' ) || exit;
/**
 * This code performs the serverside rendering of the Gutenberg block sul-sign-up.
 * It displays a sign-up-form in which a visitor can enter data to submit.
 * After clicking the sign-up button, the data is added to the list (if validated).
 * 
 * What is displayed, depends on the option for who can sign up (signupmode).
 * - If anyone can sign up, a CAPTCHA is displayed.
 * - If only visitors who have a special link can sign up, then the form will be invisible if 
 * the page is invoked without the special uid parameter/value.
 * 
 * If the list is already full, no form will be displayed. A message will be disp
 * 
 * The display style depends on the option for style.
 */
$options = get_option( 'sul_general_admin' );
$signupmode = $options['signupmode'];
$backend = defined( 'REST_REQUEST' ) && REST_REQUEST;
$entries = Database::get_entries();
$list_full =  ( $options['max_entries'] -  count($entries) ) <= 0;
$css_style = $options['style'].' sul-sign-up-wrapper';
?>
<div <?php echo get_block_wrapper_attributes( ['class' => $css_style, 'id' => 'sul-sign-up-form'] ) ; ?>>
	<?php
		switch ( $signupmode ) {
			case 'anyone':
				//Check if the list is full
				if ( $list_full ) { 
				?>
					<div id="list_full_msg"><?php echo esc_html( __( 'Sorry, the list is full. It is no longer possible to sign up', 'sign-up-list' ) ); ?></div>
				<?php 
					break;
				}
				?>
				<div id="sign_up_form">
					<div id="error_msg"></div>
					<form id="sign-up-full" method="post" action="<?php echo esc_attr( get_rest_url().'sign-up-list/v1/entries/add' ); ?>">
						<div class="sul-inline">
							<label for="firstname"><?php echo esc_html( __('First name', 'sign-up-list') ); ?></label>
							<input type="text" maxsize="255" name="firstname" size="20" id="firstname" />
						</div>
						<div class="sul-inline">
							<label for="lastname"><?php echo esc_html( __('Last name', 'sign-up-list') ); ?></label>
							<input type="text" maxsize="255" name="lastname" size="30" id="lastname" />
						</div>
						<label for="email"><?php echo esc_html( __('Email address', 'sign-up-list') ); ?></label>
						<input type="text" maxsize="350" name="email" size="57" id="email" />
						<?php if ( ! empty ( $options['extra_label'] ) ) { ?>
							<label for="extra_1"><?php echo esc_html( $options['extra_label'] ); ?></label>
							<input type="text" maxsize="80" name="extra_1" size="57" id="extra_1" />
						<?php 
						} 
						?>
						<label for="securitycode"><?php echo esc_html( __('Enter the characters in the image', 'sign-up-list') ); ?></label>
						<input type="text" maxsize="20" name="securitycode" size="20" id="securitycode" />
						<?php
						// SUL_URL is defined in the main plugin file as plugin_dir_url( __FILE__ ).
						?> 
						<img src="<?php echo esc_attr( SUL_URL.'/public/php/captcha.php' ); ?>">
						<?php wp_nonce_field( 'sul_sign_up', 'sul_sign_up_nonce' ) ?>
						<?php 
							$current_user = wp_get_current_user();
							if ( $current_user->exists() ) {
						?>
						<input type="hidden" name="user" id="user" value="<?php echo esc_attr( $current_user->id ); ?>" />
						<?php
						} 
						?>
						<p class="form-submit wp-block-button">
							<input type="submit" name="submit" id="submit" 
								value="<?php echo esc_attr( __('Sign up', 'sign-up-list') ); ?>" 
								<?php if ( $backend ) echo esc_html ( 'disabled' ); ?> 
								class="wp-block-button__link wp-element-button" />
						</p>
					</form>
				</div>
				<div id="success_msg">
					<p><?php echo esc_html( __( 'You have been added to the list', 'sign-up-list' ) ); ?></p>
				</div>
				<?php
				break;
			case 'email':
				//Check if the list is full
				if ( $list_full ) { 
					?>
					<div id="list_full_msg"><?php echo esc_html ( __( 'Sorry, the list is full. It is no longer possible to sign up.', 'sign-up-list' ) ); ?></div>
					<?php 
					break;
				}
				?>
				<div id="sign_up_form">
					<div id="error_msg"></div>
					<form id="sign-up-full" method="post" action="<?php echo esc_attr( get_rest_url().'sign-up-list/v1/entries/add' ); ?>">
						<div class="sul-inline">
							<label for="firstname"><?php echo esc_html( __('First name', 'sign-up-list') ); ?></label>
							<input type="text" maxsize="255" name="firstname" size="20" id="firstname" />
						</div>
						<div class="sul-inline">
							<label for="lastname"><?php echo esc_html( __('Last name', 'sign-up-list') ); ?></label>
							<input type="text" maxsize="255" name="lastname" size="30" id="lastname" />
						</div>
						<label for="email"><?php echo esc_html( __('Email address', 'sign-up-list') ); ?></label>
						<input type="text" maxsize="350" name="email" size="57" id="email" />
						<?php
						if ( ! empty ( $options['extra_label'] ) ) { ?>
							<label for="extra_1"><?php echo esc_html( $options['extra_label'] ); ?></label>
							<input type="text" maxsize="80" name="extra_1" size="57" id="extra_1" />
						<?php 
						}  
						wp_nonce_field( 'sul_sign_up', 'sul_sign_up_nonce' ); 
						$current_user = wp_get_current_user();
						if ( $current_user->exists() ) {
						?>
						<input type="hidden" name="user" id="user" value="<?php echo esc_attr( $current_user->id ); ?>" />
						<?php
						} 
						?>
						<p class="form-submit wp-block-button">
							<input type="submit" name="submit" id="submit" 
								   value="<?php echo esc_attr( __('Sign up', 'sign-up-list') ); ?>" 
								   <?php if ( $backend ) echo esc_html( 'disabled' ); ?> class="wp-block-button__link wp-element-button" />
						</p>
					</form>
				</div>
				<div id="success_msg">
					<p><?php echo esc_html( __( 'You have been added to the list', 'sign-up-list' ) ); ?></p>
				</div>
				<?php
				break;
			case 'link':
				//If we are in the back-end, we provide the link with uid
				if ( $backend ) {
					$base = get_permalink();
					if ( get_option( 'sul_link_uid' ) ){
						$link_uid = get_option( 'sul_link_uid' );
					} else {
						$link_uid = uniqid();
						update_option( 'sul_link_uid', $link_uid );
					}
					$separator = ( strpos( $base, '?' ) === false ) ? '?' : '&';
					?>
					<div id="invitation_msg">
					<p><?php echo esc_html ( __( 'Invite people to sign up using the URL below (copy, paste and distribute)', 
								'sign-up-list' ) ); ?></p>
					<p><strong><?php echo esc_html( $base.$separator.'uid='.$link_uid ); ?></strong></p>
					</div>
					<?php
				};

				/**
				* Check if the valid uid is in the URL and we have a stored uid
				* Only if these exist and match will we display the form
				**/
				if ( ( isset( $_GET['uid'] ) && ( get_option( 'sul_link_uid' ) ) ) or $backend ) {
					$uid = sanitize_text_field( $_GET['uid'] );
					if ( ( $uid == get_option( 'sul_link_uid' ) ) or $backend ) {
						
						//Check if the list is full
						if ( $list_full ) { 
							?>
							<p><?php echo esc_html( __( 'Sorry, the list is full. It is no longer possible to sign up', 'sign-up-list' ) ); ?></p>
							<?php 
							break;
						}
						?>
						<div id="sign_up_form">
						<div id="error_msg"></div>
						<form id="sign-up-full" method="post" action="<?php echo esc_attr( get_rest_url().'sign-up-list/v1/entries/add' ); ?>">
							<div class="sul-inline">
								<label for="firstname"><?php echo esc_html( __('First name', 'sign-up-list') ); ?></label>
								<input type="text" maxsize="255" name="firstname" size="20" id="firstname" />
							</div>
							<div class="sul-inline">
								<label for="lastname"><?php echo esc_html( __('Last name', 'sign-up-list') ); ?></label>
								<input type="text" maxsize="255" name="lastname" size="30" id="lastname" />
							</div>
							<label for="email"><?php echo esc_html( __('Email address', 'sign-up-list') ); ?></label>
							<input type="text" maxsize="350" name="email" size="57" id="email" />
							<?php if ( ! empty ( $options['extra_label'] ) ) { ?>
								<label for="extra_1"><?php echo esc_html( $options['extra_label'] ); ?></label>
								<input type="text" maxsize="80" name="extra_1" size="57" id="extra_1" />
							<?php 
							} 
							?>
							<input type="hidden" name="uid" id="uid" value="<?php echo esc_attr( $uid ); ?>" />
							<?php wp_nonce_field('sul_sign_up', 'sul_sign_up_nonce') ?>
							<?php 
								$current_user = wp_get_current_user();
								if ( $current_user->exists() ) {
							?>
							<input type="hidden" name="user" id="user" value="<?php echo esc_attr( $current_user->id ); ?>" />
							<?php
							} ?>
							<p class="form-submit wp-block-button">
							<input type="submit" name="submit" id="submit" 
										value="<?php echo esc_attr( __('Sign up', 'sign-up-list') ); ?>" 
										<?php if ( $backend ) echo esc_html( 'disabled' );?> 
										class="wp-block-button__link wp-element-button" />
						</p>
						</form>
						</div>
						<div id="success_msg">
							<p><?php echo esc_html( __( 'You have been added to the list', 'sign-up-list' ) ); ?></p>
						</div>
						<?php
					} // uid is correct
				} // uid exists
				else {
					echo '<div id="invitation_msg">'.
					esc_html( __('Sign-up form cannot be displayed. This list is by invitation only.', 
							'sign-up-list') ).'</div>';
				}
				break;
				default: echo esc_html( __('Sign-up form cannot be displayed', 'sign-up-list') ); 
		}
	?>
</div>
