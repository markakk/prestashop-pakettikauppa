<?php
namespace PS_Pakettikauppa_Core;

if (!defined('_PS_VERSION_')) {
  exit;
}

if ( ! class_exists(__NAMESPACE__ . '\AdditionalServices') ) {
  class AdditionalServices
  {
    private $core = null;

    public function __construct(Core $module)
    {
      $this->core = $module;
    }

    public function add_service_to_order($id_cart, $service_code)
    {
      if (empty($id_cart) || empty($service_code)) {
        return false;
      }

      $sql_selected_services = $this->core->sql->get_single_row(array(
          'table' => 'orders',
          'get_values' => array('method_code', 'additional_services'),
          'where' => array(
            'id_cart' => $id_cart,
          ),
      ));

      $available_services = $this->core->api->get_additional_services($sql_selected_services['method_code']);

      $selected_services = (!empty($sql_selected_services['additional_services'])) ? unserialize($sql_selected_services['additional_services']) : array();

      if (empty($selected_services)) { //If unserialize returned false
        $selected_services = array();
      }

      if (!in_array($service_code, $selected_services) && isset($available_services[$service_code])) {
        $selected_services[] = $service_code;
        $this->core->sql->update_row(array(
          'table' => 'orders',
          'update' => array(
            'additional_services' => (!empty($selected_services)) ? serialize($selected_services) : '',
          ),
          'where' => array(
            'id_cart' => $id_cart,
          ),
        ));

        return true;
      }

      return false;
    }

    public function get_order_dangerous_goods($order)
    {
      $dangerous_goods = array(
        'weight' => 0,
        'count' => 0,
        'products_ids' => array(),
      );

      $order_products = $order->getProducts();
      foreach ($order_products as $product) {
        $sql_product = $this->core->sql->get_single_row(array(
          'table' => 'products',
          'get_values' => array('params'),
          'where' => array(
            'id_product' => $product['product_id'],
          ),
        ));
        if (!empty($sql_product['params'])) {
          $product_params = unserialize($sql_product['params']);
          if (!empty($product_params['lqweight'])) {
            $dangerous_goods['weight'] += (float)$product_params['lqweight'] * $product['product_quantity'];
            $dangerous_goods['count'] += (int)$product['product_quantity'];
            $dangerous_goods['products_ids'][] = $product['product_id'];
          }
        }
      }

      return $dangerous_goods;
    }

    public function payment_is_cod($payment_module_name)
    {
      $is_cod = false;
      $cod_modules = unserialize(\Configuration::get('PAKETTIKAUPPA_COD_MODULES'));
      if (!empty($payment_module_name) && !empty($cod_modules)) {
          foreach (\PaymentModule::getInstalledPaymentModules() as $module) {
              if (in_array($module['id_module'], $cod_modules)) {
                  if ($module['name'] === $payment_module_name) {
                      $is_cod = true;
                  }
              }
          }
      }

      return $is_cod;
    }

    public function get_available_services($method_code)
    {
      $all_additional_services = $this->core->api->get_additional_services($method_code, false);
      if (!empty($all_additional_services)) {
        return $all_additional_services;
      }

      return false;
    }

    public function get_service_params($method_code, $service_code, $required_data)
    {
      $service_params = array(
        'service' => '',
        'params' => array(),
      );

      $associations = array( //If service code not using in outside
        'pickup_point' => '2106',
        'cod' => '3101',
        'multiple' => '3102',
        'dangerous' => '3143',
      );
      if (isset($associations[$service_code])) {
        $service_code = $associations[$service_code];
      }

      $service_params['service'] = $service_code;

      $available_services = $this->get_available_services($method_code);
      if (!isset($available_services[$service_code])) {
        return $service_params;
      }

      /**
       * 2106 - Pickup points
       * Required: pickup_point
       **/
      if ($service_code == '2106' && !empty($required_data['pickup_point'])) {
        $service_params['params']['pickup_point_id'] = $required_data['pickup_point'];
      }

      /**
       * 3101 - COD
       * Required: payment_module, amount
       **/
      if ($service_code == '3101' && !empty($required_data['payment_module'])) {
        $cod_modules = unserialize(\Configuration::get('PAKETTIKAUPPA_COD_MODULES'));
        if (!empty($cod_modules)) {
          foreach (\PaymentModule::getInstalledPaymentModules() as $module) {
            if (in_array($module['id_module'], $cod_modules)) {
              if ($module['name'] === $required_data['payment_module']) {
                $bank_account_number = \Configuration::get('PAKETTIKAUPPA_BANK_ACCOUNT');
                if (!empty($bank_account_number)) {
                  $bank_account_number = chunk_split(str_replace(' ', '', $bank_account_number), 4, ' '); //Remove spaces and add space after every 4th character
                }
                $amount = (!empty($required_data['amount'])) ? $required_data['amount'] : 0;
                $service_params['params'] = array(
                  'amount' => \Tools::ps_round($amount, 2),
                  'account' => $bank_account_number,
                  'reference' => \Configuration::get('PAKETTIKAUPPA_BANK_REFERENCE'),
                  'codbic' => \Configuration::get('PAKETTIKAUPPA_BANK_BIC'),
                );
                break;
              }
            }
          }
        }
      }

      /**
       * 3102 - Multiple shipments
       * Required: count
       */
      if ($service_code == '3102' && !empty($required_data['count'])) {
        $service_params['params']['count'] = $required_data['count'];
      }

      /**
       * 3143 - Dangerous goods
       * Required: order
       */
      if ($service_code == '3143' && !empty($required_data['order'])) {
        $dangerous_goods = $this->get_order_dangerous_goods($required_data['order']);
        if (!empty($dangerous_goods['weight'])) {
          $service_params['params']['lqweight'] = $dangerous_goods['weight'];
          $service_params['params']['lqcount'] = $dangerous_goods['count'];
        }
      }

      return $service_params;
    }
  }
}
