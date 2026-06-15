<?php
// Protect direct access
if (!defined('ABSPATH'))
    exit;

// Load saved values
$options = get_option('wc_booking_cancel_options', []);
?>
<style>
    form.cancel-opt input[type="text"],
    form.cancel-opt textarea,
    form.cancel-opt input[type="number"] {
        width: 500px;
    }
    form.cancel-opt th{
        width: 250px;;
    }
</style>
<div class="wrap">
    <h1>Booking Cancelation Options</h1>

    <form method="post" action="options.php" class="cancel-opt">
        <?php settings_fields('wc_booking_cancel_group'); ?>

        <table class="form-table">
            <tr>
                <th><label for="before_72_charge">Cancelation % before 72 hours</label></th>
                <td><input type="number" name="wc_booking_cancel_options[before_72_charge]" min="0" max="100"
                        value="<?php echo esc_attr($options['before_72_charge'] ?? ''); ?>"> %</td>
            </tr>

            <tr>
                <th><label for="within_72_charge">Cancelation % within 72 hours</label></th>
                <td><input type="number" name="wc_booking_cancel_options[within_72_charge]" min="0" max="100"
                        value="<?php echo esc_attr($options['within_72_charge'] ?? ''); ?>"> %</td>
            </tr>

            <tr>
                <th><label for="popup_before_72">Popup Message (Before 72 Hours)</label></th>
                <td><textarea name="wc_booking_cancel_options[popup_before_72]"
                        rows="3"><?php echo esc_textarea($options['popup_before_72'] ?? ''); ?></textarea></td>
            </tr>

            <tr>
                <th><label for="popup_within_72">Popup Message (Within 72 Hours)</label></th>
                <td><textarea name="wc_booking_cancel_options[popup_within_72]"
                        rows="3"><?php echo esc_textarea($options['popup_within_72'] ?? ''); ?></textarea></td>
            </tr>

            <tr>
                <th><label for="info_message">Info Icon Message</label></th>
                <td><textarea name="wc_booking_cancel_options[info_message]"
                        rows="3"><?php echo esc_textarea($options['info_message'] ?? ''); ?></textarea></td>
            </tr>

            <tr>
                <th><label for="cancel_label">Cancel Button Label</label></th>
                <td><input type="text" name="wc_booking_cancel_options[cancel_label]"
                        value="<?php echo esc_attr($options['cancel_label'] ?? ''); ?>"></td>
            </tr>
        </table>

        <?php submit_button('Save Options'); ?>
    </form>
</div>