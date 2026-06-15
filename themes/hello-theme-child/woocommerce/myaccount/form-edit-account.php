<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined('ABSPATH') || exit;

/**
 * Hook - woocommerce_before_edit_account_form.
 *
 * @since 2.6.0
 */
do_action('woocommerce_before_edit_account_form');
?>

<style>
	.verify_box_profile {
		border-radius: 10px;
		padding: 25px;
		width: 420px;
		max-width: 100%;
		cursor: auto;
	}

	#resend_email.disabled,
	#resend_phone.disabled {
		opacity: 0.3;
		pointer-events: none;
	}

	.otp_box_inner label {
		font-family: "Inter", Sans-serif;
		font-size: 14px;
		font-weight: 400;
		letter-spacing: 2%;
		color: #2e2e2e;
		line-height: 2;
	}

	#otp_box_inner input {
		height: 48px;
		border: 1px solid #D4D7E3;
		background-color: #fff;
		font-family: "Inter", Sans-serif;
		font-size: 14px;
		font-weight: 400;
		border-radius: 12px;
		width: 100%;
		color: #000;
		padding: 8px 14px;
	}

	#otp_box_inner .email-status,
	#otp_box_inner .email-timer,
	#otp_box_inner .phone-status,
	#otp_box_inner .phone-timer {
		color: #69727d;
		font-size: 14px;
	}

	#otp_box_inner .button {
		height: 48px;
		background-color: transparent;
		background-image: linear-gradient(180deg, #F2F8FF 0%, #F2F8FF 100%);
		border: none;
		border-radius: 12px;
		margin-top: 20px;
		font-family: "Inter", Sans-serif;
		font-size: 14px;
		font-weight: 500;
		letter-spacing: 0.2px;
		color: #242424;
		transition: none;
		padding: 8px 32px;
	}

	#otp_box_inner .button:hover {
		background-color: transparent;
		background-image: linear-gradient(180deg, #2F87ED 0%, #2F87ED 100%);
		color: #fff;
	}

	#otp_box_inner .otp-resend {
		margin: 0;
		color: #69727d;
		font-size: 14px;
	}

	#otp_box_inner .otp-resend a {
		color: #1763b9;
	}

	#otp_box_inner h2 {
		margin-top: 0;
		text-align: center;
		font-size: 22px;
	}
</style>


<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>

	<?php do_action('woocommerce_edit_account_form_start'); ?>

	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="account_first_name"><?php esc_html_e('First name', 'woocommerce'); ?>&nbsp;<span class="required"
				aria-hidden="true">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name"
			id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr($user->first_name); ?>"
			aria-required="true" />
	</p>
	<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
		<label for="account_last_name"><?php esc_html_e('Last name', 'woocommerce'); ?>&nbsp;<span class="required"
				aria-hidden="true">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name"
			id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr($user->last_name); ?>"
			aria-required="true" />
	</p>
	<div class="clear"></div>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="account_display_name"><?php esc_html_e('Display name', 'woocommerce'); ?>&nbsp;<span
				class="required" aria-hidden="true">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name"
			id="account_display_name" aria-describedby="account_display_name_description"
			value="<?php echo esc_attr($user->display_name); ?>" aria-required="true" /> <span
			id="account_display_name_description"><em><?php esc_html_e('This will be how your name will be displayed in the account section and in reviews', 'woocommerce'); ?></em></span>
	</p>
	<div class="clear"></div>

	<div class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="account_email"><?php esc_html_e('Email address', 'woocommerce'); ?>&nbsp;<span class="required"
				aria-hidden="true">*</span></label>
		<?php
		if (strpos($user->user_email, 'example.com') !== false) {
			$email = '';
		} else {
			$email = esc_attr($user->user_email);
		}
		?>
		<input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email"
			id="account_email" autocomplete="off" value="<?php echo esc_attr($email); ?>" aria-required="true" />

		<!-- EMAIL OTP -->
		<div id="emailOtpModal" class="verify_box_profile" style="display:none">
			<div id="otp_box_inner" class="otp_box_inner">
				<h2>Verify OTP</h2>
				<label for="email_otp">Enter OTP sent to your email</label>
				<input id="email_otp" class="input-text" placeholder="Enter 6 digit OTP code">

				<div class="status_x email-status" style="margin-top: 5px;"></div>
				<div class="email-timer">OTP expires in 3:00</div>

				<button type="button" id="verify_email_otp" class="button">Verify Email</button>

				<p class="woocommerce-form-row otp-resend">
					Didn't receive OTP?
					<a href="#" id="resend_email" class="disabled">Resend OTP</a>
				</p>
			</div>
		</div>

	</div>
	<div class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
		<label for="phone"><?php esc_html_e('Phone Number', 'woocommerce'); ?>&nbsp;<span class="required"
				aria-hidden="true">*</span></label>
		<input type="number" class="woocommerce-Input woocommerce-Input--phone input-text" name="phone" id="phone"
			autocomplete="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'phone', true)); ?>"
			aria-required="true" />

		<!-- PHONE OTP -->
		<div id="phoneOtpModal" class="verify_box_profile" style="display:none">
			<div id="otp_box_inner" class="otp_box_inner">
				<h2>Verify OTP</h2>
				<label for="phone_otp">Enter OTP sent to your phone</label>
				<input id="phone_otp" class="input-text" placeholder="Enter 6 digit OTP code">

				<div class="status_x phone-status" style="margin-top: 5px;"></div>
				<div class="phone-timer">OTP expires in 3:00</div>

				<button type="button" id="verify_phone_otp" class="button">Verify Phone</button>

				<p class="woocommerce-form-row otp-resend">
					Didn't receive OTP?
					<a href="#" id="resend_phone" class="disabled">Resend OTP</a>
				</p>
			</div>
		</div>

		<div id="combinedOtpModal" class="verify_box_profile" style="display:none">
			<div id="otp_box_inner" class="otp_box_inner">
				<h2>Verify OTP</h2>
				<label for="combined_otp">Enter OTP sent to your email/phone no.</label>

				<input id="combined_otp" class="input-text" placeholder="Enter 6 digit OTP code">

				<div class="status_x combined-status" style="margin-top:5px;"></div>

				<div class="combined-timer">OTP expires in 3:00</div>

				<button type="button" id="verify_combined_otp" class="button">
					Verify
				</button>

				<p class="woocommerce-form-row otp-resend">
					Didn't receive OTP?
					<a href="#" id="resend_combined" class="disabled">Resend OTP</a>
				</p>

			</div>
		</div>


	</div>

	<?php
	/**
	 * Hook where additional fields should be rendered.
	 *
	 * @since 8.7.0
	 */
	do_action('woocommerce_edit_account_form_fields');
	?>

	<fieldset>
		<legend><?php esc_html_e('Password change', 'woocommerce'); ?></legend>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label
				for="password_current"><?php esc_html_e('Current password (leave blank to leave unchanged)', 'woocommerce'); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text"
				name="password_current" id="password_current" autocomplete="off" />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label
				for="password_1"><?php esc_html_e('New password (leave blank to leave unchanged)', 'woocommerce'); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1"
				id="password_1" autocomplete="off" />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="password_2"><?php esc_html_e('Confirm new password', 'woocommerce'); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2"
				id="password_2" autocomplete="off" />
		</p>
	</fieldset>
	<div class="clear"></div>

	<?php
	/**
	 * My Account edit account form.
	 *
	 * @since 2.6.0
	 */
	do_action('woocommerce_edit_account_form');
	?>

	<p>
		<input type="hidden" id="combined_verified" name="combined_verified">
		<input type="hidden" id="email_verified" name="email_verified">
		<input type="hidden" id="phone_verified" name="phone_verified">

		<?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
		<button type="submit"
			class="woocommerce-Button button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"
			name="save_account_details"
			value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>"><?php esc_html_e('Save changes', 'woocommerce'); ?></button>
		<input type="hidden" name="action" value="save_account_details" />
	</p>

	<?php do_action('woocommerce_edit_account_form_end'); ?>
</form>

<?php do_action('woocommerce_after_edit_account_form'); ?>

<script>

	var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";

	jQuery(function ($) {

		let form = $('form.edit-account');
		if (!form.length) return;

		let oldEmail = $('#account_email').val();
		let oldPhone = $('#phone').val();

		let emailTimer, phoneTimer, combinedTimer;

		// TIMER
		function startTimer(el) {

			let t = 180;

			//clearInterval(el === 'email' ? emailTimer : phoneTimer);
			if (el === 'email') {
				clearInterval(emailTimer);
			} else if (el === 'phone') {
				clearInterval(phoneTimer);
			} else {
				clearInterval(combinedTimer);
			}


			let timer = setInterval(function () {

				let m = Math.floor(t / 60);
				let s = t % 60;

				$('.' + el + '-timer').text('OTP expires in ' + m + ':' + (s < 10 ? '0' : '') + s);

				t--;

				if (t < 0) {
					clearInterval(timer);
				}

			}, 1000);

			// if(el === 'email') emailTimer = timer;
			// else phoneTimer = timer;

			if (el === 'email') {
				emailTimer = timer;
			} else if (el === 'phone') {
				phoneTimer = timer;
			} else {
				combinedTimer = timer;
			}
		}

		// FORM SUBMIT
		form.on('submit', function (e) {

			if (($('#combined_verified').val() == 1) || ($('#email_verified').val() == 1) || ($('#phone_verified').val() == 1)) {
				return;
			}

			let emailChanged = oldEmail !== $('#account_email').val();
			let phoneChanged = oldPhone !== $('#phone').val();

			let emailVal = $('#account_email').val().trim();
			let phoneVal = $('#phone').val();

			let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			let phoneRegex = /^\+?[0-9]{10,15}$/;

			// Remove previous errors to prevent them from stacking
			$('#account_email').next('.error').remove();
			$('#phone').next('.error').remove();

			if (emailChanged && emailVal === '') {
				e.preventDefault();
				$('#account_email').after('<span class="error" style="color:red;display:block;margin-top:5px;" role="alert">This field is required.</span>');
				return false;
			} 

			if (phoneChanged && phoneVal.trim() === '') {
				e.preventDefault();
				$('#phone').after('<span class="error" style="color:red;display:block;" role="alert">This field is required.</span>');
				return false;
			}

			if (phoneVal && (phoneVal.length > 10 || !phoneRegex.test(phoneVal))) {
				e.preventDefault();
				$('#phone').after('<span class="error" style="color:red;display:block;" role="alert">Invalid Phone Number.</span>');
				return false;
			}
			if (emailChanged && phoneChanged && !$('#combined_verified').val()) {
				e.preventDefault();

				//$('#emailOtpModal').fadeIn();
				Fancybox.show(
					[
						{
							src: "#combinedOtpModal",
							type: "inline"
						}
					],
					{
						dragToClose: false,
						closeButton: false
					}
				);
				$('#combined_otp').focus();

				$.post(ajaxurl, {
					action: 'send_profile_update_otp',
					mode: 'combined',
					email: $('#account_email').val(),
					phone: $('#phone').val()
				});

				startTimer('combined');
				enableResend('#resend_combined');

				return;
			} else if (emailChanged && !$('#email_verified').val()) {
				e.preventDefault();

				//$('#emailOtpModal').fadeIn();
				Fancybox.show(
					[
						{
							src: "#emailOtpModal",
							type: "inline"
						}
					],
					{
						dragToClose: false,
						closeButton: false
					}
				);
				$('#email_otp').focus();

				$.post(ajaxurl, {
					action: 'send_profile_update_otp',
					email: $('#account_email').val()
				});

				startTimer('email');
				enableResend('#resend_email');

				return;
			} else if (phoneChanged && !$('#phone_verified').val()) {
				e.preventDefault();

				//$('#phoneOtpModal').fadeIn();
				Fancybox.show(
					[
						{
							src: "#phoneOtpModal",
							type: "inline"
						}
					],
					{
						dragToClose: false,
						closeButton: false
					}
				);
				$('#phone_otp').focus();

				$.post(ajaxurl, {
					action: 'send_profile_update_otp',
					phone: $('#phone').val()
				});

				startTimer('phone');
				enableResend('#resend_phone');

				return;
			}

		});

		// VERIFY EMAIL
		$('#verify_email_otp').click(function () {

			$.post(ajaxurl, {
				action: 'verify_email_otp',
				otp: $('#email_otp').val()
			}, function (r) {

				if (r.success) {
					$('#email_verified').val('1');
					$('.fancybox__dialog').hide();
					form.submit();
				} else $('.email-status').html('Invalid OTP');

			});

		});

		// VERIFY PHONE
		$('#verify_phone_otp').click(function () {

			$.post(ajaxurl, {
				action: 'verify_phone_otp',
				otp: $('#phone_otp').val()
			}, function (r) {

				if (r.success) {
					$('#phone_verified').val('1');
					$('.fancybox__dialog').hide();
					form.submit();
				} else $('.phone-status').html('Invalid OTP');

			});
		});

		// VERIFY COMBINED
		$('#verify_combined_otp').click(function () {

			$.post(ajaxurl, {
				action: 'verify_combined_otp',
				otp: $('#combined_otp').val()
			}, function (r) {

				if (r.success) {
					$('#combined_verified, #email_verified, #phone_verified').val('1');
					$('.fancybox__dialog').hide();
					form.submit();
				} else $('.combined-status').html('Invalid OTP');

			});

		});

		// RESEND HANDLER
		function enableResend(btn) {

			$(btn).addClass('disabled');

			setTimeout(() => {
				$(btn).removeClass('disabled');
				if (btn == '#resend_email') {
					$('#resend_email').html("Resend OTP");
				} else if (btn == '#resend_phone') {
					$('#resend_phone').html("Resend OTP");
				} else {
					$('#resend_combined').html("Resend OTP");
				}
			}, 180000);
		}

		$('#resend_email').click(function (e) {

			e.preventDefault();
			if ($(this).hasClass('disabled')) return;

			$('#resend_email').html("Sending");
			$.post(ajaxurl, {
				action: 'send_profile_update_otp',
				email: $('#account_email').val()
			});

			$('#resend_email').html("Sent");

			startTimer('email');
			enableResend('#resend_email');
		});

		$('#resend_phone').click(function (e) {

			e.preventDefault();
			if ($(this).hasClass('disabled')) return;

			$('#resend_phone').html("Sending");

			$.post(ajaxurl, {
				action: 'send_profile_update_otp',
				phone: $('#phone').val()
			});

			$('#resend_phone').html("Sent");

			startTimer('phone');
			enableResend('#resend_phone');
		});

		$('#resend_combined').click(function (e) {

			e.preventDefault();
			if ($(this).hasClass('disabled')) return;

			$('#resend_combined').html("Sending");

			$.post(ajaxurl, {
				action: 'send_profile_update_otp',
				mode: 'combined',
				email: $('#account_email').val(),
				phone: $('#phone').val()
			});

			$('#resend_combined').html("Sent");

			startTimer('combined');
			enableResend('#resend_combined');
		});

	});

</script>