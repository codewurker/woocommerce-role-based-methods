<?php
/**
 * Represents the view for the Role Based Methods Shipping Method Settings/
 *
 *
 * @package   WC_Role_Methods
 * @author    Bryan Purcell <support@wpbackoffice.com>
 * @license   GPL-2.0+
 * @link      http://woothemes.com/woocommerce
 * @copyright 2015 WPBackOffice
 */
?>

<div class='wrap'>
  <h3>
    <?php _e('Roles', 'woocommerce-role-based-methods'); ?>
  </h3>
  <p style='width:800px;'>
    <?php _e('Please select the payment gateways that you would like activated for each respective role currently configured.', 'woocommerce-role-based-methods'); ?>
  </p>
  <form method='post' action='' id='psroles_settings'>
    <div class='paymentrolepanel'>
      <table class='wc_shipping widefat' cellspacing='0'>
        <thead>
          <tr><th>&nbsp;</th>
            <?php foreach($payment_methods as $method): ?>
              <th><p><?php echo $this->get_col_title($method); ?></p></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>

          <?php foreach($wp_roles->get_names() as $roll): ?>
            <?php $this->print_gateway_row(strtolower($roll), $payment_methods, $gatewayarray); ?>
          <?php endforeach; ?>
          <?php $this->print_gateway_row("Guest", $payment_methods, $gatewayarray); ?>

        </tbody>
      </table>
    </div>
    <?php if($groups): ?>
      <div style="clear:both;"></div>

      <h3><?php _e('Groups', 'woocommerce-role-based-methods'); ?></h3>

      <div class="enable-groups-toggle">
        <label for="pay-groups-enable"><?php _e('Enable Group Based Method Control', 'woocommerce-role-based-methods'); ?>
          <input type="checkbox" <?php if(isset($options['pay-groups-enable']) && $options['pay-groups-enable'] == "Yes"): ?> checked <?php endif; ?> name="woocommerce_role_methods_options[pay-groups-enable]" id="pay-groups-enable" class="groups-toggle" value="Yes"><br>
        </label>
      </div>

      <div class="group-settings <?php if(isset($options['pay-groups-enable']) && $options['pay-groups-enable'] == "Yes"): ?> groups--visible <?php endif; ?> ">

        <label for="operator"><?php _e('Operator', 'woocommerce-role-based-methods'); ?></label>

        <select name="woocommerce_role_methods_options[payment_operator]">
          <option <?php echo (isset($options['payment_operator']) && $options['payment_operator'] == 'and') ? "selected" : ""; ?> value="and">
            <?php _e('AND', 'woocommerce-role-based-methods'); ?>
          </option>
          <option <?php echo ((isset($options['payment_operator']) && $options['payment_operator'] == 'or') || !isset($options['payment_operator'])) ? "selected" : ""; ?> value="or" >
            <?php _e('OR', 'woocommerce-role-based-methods'); ?>
          </option>
        </select>

        <p class="description">
          <?php _e("The operator will control how the Group/Role Settings will affect the available gateways. When 'AND' is selected, both group-based rules and role-based rules must match to use the gateway. When 'OR' is selected, the gateway is available if either rule sections match for the purchasing customer.", "woocommerce-role-based-methods"); ?>
        </p>

        <div class='group-role-panel'>
          <table class='wc_shipping widefat' cellspacing='0'>
            <thead>
              <tr>
                <th>&nbsp;</th>
                <?php foreach($payment_methods as $col): ?>
                  <th><p><?php echo $col->title; ?></p></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach($groups as $group): ?>
                <?php $this->print_group_gateway_row($group, $payment_methods, $group_gatewayarray); ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <div style='clear:both;'></div>
    <p class='submit'>
      <input type='submit' name='Submit' class='button-primary' value='<?php _e('Save Changes', 'woocommerce-role-based-methods'); ?>' />
      <input type='hidden' name='settings-updated' value='true'/>
    </p>
  </form>
</div>
