<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

do_action('woocommerce_before_customer_login_form'); ?>

<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')): ?>
	<style>
		p.status:empty {
			display: none;
		}

		#password-form {
			display: none;
		}

		form.woocommerce-form-register.register {
			/* display: none;
						transition: all 1s ease; */
		}
	</style>
	<script>
		// jQuery(function($){
		//   $('.to-register').on('click', function(e){
		//     e.preventDefault();
		//     $('.login_form').hide();
		//     $('.register_form').show();

		//     // Scroll to top smoothly
		//     $('html, body').animate({ scrollTop: 0 }, 'slow');
		//   });

		//   $('.to-login').on('click', function(e){
		//     e.preventDefault();
		//     $('.register_form').hide();
		//     $('.login_form').show();

		//     // Scroll to top smoothly
		//     $('html, body').animate({ scrollTop: 0 }, 'slow');
		//   });
		// });


		jQuery(document).ready(function ($) {
			$(".login-img-slider").owlCarousel({
				loop: true,
				margin: 10,
				nav: false,
				items: 1,
				dots: true,
				responsive: {
					0: { items: 1 },
					600: { items: 1 },
					1000: { items: 1 }
				}
			});
		});
		jQuery(document).ready(function ($) {
			var owl = $('.owl-carousel');
			// Fix: force refresh after load
			setTimeout(function () {
				owl.trigger('refresh.owl.carousel');
			}, 500);
		});

	</script>

	<div class="login_top d-flex" style="gap:35px;align-items: start;">

		<div class="u-columns col2-set" id="customer_login">

			<div class="login_form">
				<h2><?php esc_html_e('Login', 'woocommerce'); ?></h2>
			<?php endif; ?>

			<!-- <div class="login-buttons login">
		<div class="gw-pass-login">
			<a href="javascript:void(0);">
				Login with Email
			</a>
		</div>
		<p><b>OR</b></p>
		<div class="gw-otp-login">
			<a href="javascript:void(0);">
				Login with OTP (Mobile or Email)
			</a>
		</div>
		<p><b>OR</b></p>
		<?php // do_action('woocommerce_login_form_end'); ?>

		<p></p>
		<div class="signup-text">
			<p>Don't have an account? <a class="gw-register" href="javascript:void(0);">Register Now</a></p>
		</div>
	</div> -->

			<form class="woocommerce-form woocommerce-form-login login" method="post" id="password-form"
				style="display: none;" novalidate>

				<?php do_action('woocommerce_login_form_start'); ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="username"><?php esc_html_e('Email address', 'woocommerce'); ?><span class="required"
							aria-hidden="true">*</span><span
							class="screen-reader-text"><?php esc_html_e('Required', 'woocommerce'); ?></span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username"
						id="username" autocomplete="username"
						value="<?php echo (!empty($_POST['username']) && is_string($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>"
						required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="password"><?php esc_html_e('Password', 'woocommerce'); ?><span class="required"
							aria-hidden="true">*</span><span
							class="screen-reader-text"><?php esc_html_e('Required', 'woocommerce'); ?></span></label>
					<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password"
						id="password" autocomplete="current-password" required aria-required="true" />
				</p>

				<?php do_action('woocommerce_login_form'); ?>

				<p class="form-row">
					<label
						class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
						<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme"
							type="checkbox" id="rememberme" value="forever" />
						<span><?php esc_html_e('Remember me', 'woocommerce'); ?></span>
					</label>
					<?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
					<button type="submit"
						class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"
						name="login"
						value="<?php esc_attr_e('Log in', 'woocommerce'); ?>"><?php esc_html_e('Log in', 'woocommerce'); ?></button>
				</p>
				<p class="woocommerce-LostPassword lost_password">
					<a
						href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Lost your password?', 'woocommerce'); ?></a>
				</p>
				<!-- <p><button class="method woocommerce-button button" style="display: block;">Use Another Method</button></p> -->

				<? php// do_action('woocommerce_login_form_end'); ?>
				<p><b>OR</b></p>
				<?php do_action('woocommerce_login_form_end'); ?>
				<!-- <p></p>
			<div class="signup-text">
				<p>Don't have an account? <a class="gw-register" href="javascript:void(0);">Register Now</a></p>
			</div> -->

			</form>
			<!-- <div class="otp-login-box"> -->

			<form id="ajax-otp-form" method="post" class="woocommerce-form woocommerce-form-login login">


				<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide phone-field">
					<p class="status">Enter registered phone number or email to get OTP.</p>
					<label for="otp_phone">Mobile No. / Email Address</label>
					<input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="otp_phone"
						id="otp_phone" placeholder="Enter Mobile No. / Email Id">
					<p class="status_x" style="margin-top: 5px;"></p>
					<button type="submit" class="button" id="otp-submit">Get OTP</button>
				</div>

				<input type="hidden" id="otp_sent" name="otp_sent" value="0">

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide otp-field">
					<label for="otp_code">Enter OTP</label>
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code"
						name="otp_code" id="otp_code" placeholder="Enter 6 digit OTP code"
						style="margin-top: 0 !important;">
					<!-- <p class="status_x"></p> -->


				</p>

				<p class="woocommerce-form-row" id="verify-wrap">
					<button type="submit" class="button" id="otp-submit1">Verify OTP</button>
				</p>

				<p class="woocommerce-form-row otp-resend" style="display:none;">
					Didn’t receive OTP? <a href="#" class="resend disabled" style="font-weight:600;">Resend OTP</a>
				</p>

				<p class="login_or_sec" style="text-align: center;"><span>Or</span></p>

				<div class="gw-google-login">
					<a href="javascript:void(0);" onclick="gw_open_google_popup()">
						<img decoding="async" src="/wp-content/uploads/2025/11/Google2.png" alt="Google">
						Sign In with Google
					</a>
				</div>

				<div class="signup-text">
					<p>Don't have an account? <a href="<?php echo home_url('register'); ?>" class="to-register">Register
							Now</a></p>
				</div>

				<?php wp_nonce_field('ajax-login-nonce', 'security'); ?>
				<?php wp_nonce_field('ajax-otp-verify-nonce', 'security_verify'); ?>
			</form>

		</div>
		<div class="register_form" style="display:none;">
			<img src="<?php echo get_bloginfo('url'); ?>/wp-content/uploads/2025/12/mynaui_arrow-up.svg" alt="Back"
				class="back-arrow" style="display:none">
			<h2><?php esc_html_e('Create Account', 'woocommerce'); ?></h2>
			<form method="post" class="woocommerce-form woocommerce-form-register register" id="register_form" <?php do_action('woocommerce_register_form_tag'); ?>>

				<?php do_action('woocommerce_register_form_start'); ?>
				<!-- 			<?php if ('no' === get_option('woocommerce_registration_generate_username')): ?>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_username"><?php esc_html_e('Username', 'woocommerce'); ?><span
							class="required">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username"
						id="reg_username" autocomplete="username"
						value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>"
						required aria-required="true" required/>
				</p>
			<?php endif; ?> -->

				<p class="woocommerce-form-row woocommerce-form-row--half form-row form-row-first ott">
					<label for="reg_first_name"><?php esc_html_e('First Name', 'woocommerce'); ?><span
							class="required">*</span></label>
					<input type="text" class="input-text" name="first_name" id="reg_first_name"
						value="<?php echo !empty($_POST['first_name']) ? esc_attr($_POST['first_name']) : ''; ?>"
						placeholder="First name" required />
				</p>

				<p class="woocommerce-form-row woocommerce-form-row--half form-row form-row-last ott">
					<label for="reg_last_name"><?php esc_html_e('Last Name', 'woocommerce'); ?></label>
					<input type="text" class="input-text" name="last_name" id="reg_last_name"
						value="<?php echo !empty($_POST['last_name']) ? esc_attr($_POST['last_name']) : ''; ?>"
						placeholder="Last name" />
				</p>

				<!-- <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_email"><? php// esc_html_e('Email address', 'woocommerce'); ?><span
						class="required">*</span></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email"
					id="reg_email" autocomplete="email"
					value="<? php// echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>"
					required aria-required="true" />
			</p> -->


				<div
					class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide lgn_Register_email ott">
					<label for="reg_phone_email"> Mobile No. / Email Address <span class="required">*</span></label>
					<div class="d-flex no-style" style="margin-bottom: 0px !important">
						<input type="text" class="input-text" name="phone" id="reg_phone_email" value=""
							placeholder="Enter Mobile No. / Email Id" required />
						<div class="consent-text">
							<p class="consent" style="display: block;margin-top: 24px;text-align:left;margin-left: 0px;">By continuing, you agree to
								Accordhub's <a href="<?php echo home_url('terms-and-conditions'); ?>" target="_blank" class="">Terms & Conditions</a> and <a href="<?php echo home_url('privacy-policy'); ?>" target="_blank" class="">Privacy
									Policy</a>.</p>
						</div>
						<button type="button" id="send_otp_btn" class="button">Send OTP</button>
					</div>
				</div>

				<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide otf"
					style="display:none">
					<label for="email_mobile_otp"><?php esc_html_e('Enter OTP', 'woocommerce'); ?><span
							class="required">*</span></label>
					<input type="text" class="input-text" name="email_mobile_otp" id="email_mobile_otp"
						inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" />
					<p class="status_x" style="margin-top: 5px;"></p>
				</div>

				<!-- Hidden field to track OTP verification -->





				<!-- <p class="woocommerce-form-row woocommerce-form-row--half form-row form-row-first">
					<label for="country"><?php //esc_html_e('Country', 'woocommerce'); ?><span
							class="required">*</span></label>
					<select name="country" id="billing_country">
						<option value="">Select a country / region…</option>
						<option value="IN" selected>India</option>
					</select>
				</p>

				<p class="woocommerce-form-row woocommerce-form-row--half form-row form-row-last">
					<label for="state"><?php //esc_html_e('State', 'woocommerce'); ?><span
							class="required">*</span></label>
					<select name="state" id="billing_state">
						<option value="">Select an option…</option>
						<option value="AP">Andhra Pradesh</option>
						<option value="AR">Arunachal Pradesh</option>
						<option value="AS">Assam</option>
						<option value="BR">Bihar</option>
						<option value="CT">Chhattisgarh</option>
						<option value="GA">Goa</option>
						<option value="GJ">Gujarat</option>
						<option value="HR">Haryana</option>
						<option value="HP">Himachal Pradesh</option>
						<option value="JK">Jammu and Kashmir</option>
						<option value="JH">Jharkhand</option>
						<option value="KA">Karnataka</option>
						<option value="KL">Kerala</option>
						<option value="LA">Ladakh</option>
						<option value="MP">Madhya Pradesh</option>
						<option value="MH">Maharashtra</option>
						<option value="MN">Manipur</option>
						<option value="ML">Meghalaya</option>
						<option value="MZ">Mizoram</option>
						<option value="NL">Nagaland</option>
						<option value="OD">Odisha</option>
						<option value="PB">Punjab</option>
						<option value="RJ" selected>Rajasthan</option>
						<option value="SK">Sikkim</option>
						<option value="TN">Tamil Nadu</option>
						<option value="TS">Telangana</option>
						<option value="TR">Tripura</option>
						<option value="UK">Uttarakhand</option>
						<option value="UP">Uttar Pradesh</option>
						<option value="WB">West Bengal</option>
						<option value="AN">Andaman and Nicobar Islands</option>
						<option value="CH">Chandigarh</option>
						<option value="DN">Dadra and Nagar Haveli</option>
						<option value="DD">Daman and Diu</option>
						<option value="DL">Delhi</option>
						<option value="LD">Lakshadweep</option>
						<option value="PY">Pondicherry (Puducherry)</option>
					</select>
				</p>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_billing_address"><? php// _e( 'Address', 'woocommerce' ); ?></label>
					<input type="text" class="input-text" name="billing_address_1" id="reg_billing_address" value="<? php// if ( ! empty( $_POST['billing_address_1'] ) ) echo esc_attr( $_POST['billing_address_1'] ); ?>" />
				</p>



				<p class="woocommerce-form-row woocommerce-form-row--half form-row form-row-first">
					<label for="reg_city"><? php// esc_html_e('City', 'woocommerce'); ?><span
							class="required">*</span></label>
					<input type="text" class="input-text" name="city" id="reg_city"
						value="<? php// echo !empty($_POST['city']) ? esc_attr($_POST['city']) : ''; ?>" required />
				</p>

				<p class="woocommerce-form-row woocommerce-form-row--half form-row form-row-last">
					<label for="reg_billing_postcode"><? php// _e( 'Postal Code', 'woocommerce' ); ?></label>
					<input type="text" class="input-text" name="billing_postcode" id="reg_billing_postcode" value="<? php// if ( ! empty( $_POST['billing_postcode'] ) ) echo esc_attr( $_POST['billing_postcode'] ); ?>" />
				</p> -->

				<!-- <? php// if ('no' === get_option('woocommerce_registration_generate_password')): ?>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_password"><?php //esc_html_e('Password', 'woocommerce'); ?><span
								class="required">*</span></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password"
							id="reg_password" autocomplete="new-password" required />
					</p>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_password2"><? php// esc_html_e( 'Confirm password', 'woocommerce' ); ?><span class="required">*</span></label>
						<input type="password" class="input-text" name="password2" id="reg_password2" autocomplete="new-password" />
					</p>
				<? php// else: ?>
					<p><? php// esc_html_e('A link to set a new password will be sent to your email address.', 'woocommerce'); ?>
					</p>
				<? php// endif; ?> -->



				<?php // do_action('woocommerce_login_form_end'); ?>

				<p class="woocommerce-form-row form-row otf" style="display:none">
					<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
					<button type="submit"
						class="woocommerce-Button woocommerce-button button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?> woocommerce-form-register__submit"
						name="register"
						value="<?php esc_attr_e('Register', 'woocommerce'); ?>"><?php esc_html_e('Register', 'woocommerce'); ?></button>
				</p>

				<p class="login_or_sec" style="text-align: center;"><span>Or</span></p>
				<div class="gw-google-login">
					<a href="javascript:void(0);" onclick="gw_open_google_popup()">
						<img decoding="async" src="/wp-content/uploads/2025/11/Google2.png" alt="Google">
						Sign Up with Google
					</a>
				</div>

				<div class="signup-text">
					<p style="justify-content: center;">Already have an account? <a
							href="<?php echo home_url('login'); ?>" class="to-login">Login Now</a></p>
				</div>


			</form>

			<?php // do_action('woocommerce_login_form_end'); ?>

		</div>


		<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')): ?>

			<!-- </div> -->
			<span class="rights-reserved-sec"><span>© <?php echo date('Y'); ?> ALL RIGHTS RESERVED</span></span>
		</div>
		<div class="lgn-slider-sec-top" style="width: 50%;">
			<ul class="owl-carousel login-img-slider">
				<li class="slider-item" style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/images/kautilya.jpg');">
					<h6>Dedicated ADR Service</h6>
					<p>Affordable spaces for arbitration, mediation and conciliation hearings.</p>
				</li>
				<li class="slider-item" style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/images/launge.jpg');">
					<h6>Professional ADR accessible to all</h6>
					<p>On-demand facilities built for convenience and confidentiality.</p>
				</li> 
				<li class="slider-item" style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/images/kautilya2.jpg');">
					<h6>Bridging the gap for uninterrupted hearings.</h6>
					<p>Fully integrated video and audio conferencing setup with dependable backup systems for seamless proceedings.</p>
				</li> 
			</ul>
		</div>

	</div>
<?php endif; ?>


<?php do_action('woocommerce_after_customer_login_form'); ?>