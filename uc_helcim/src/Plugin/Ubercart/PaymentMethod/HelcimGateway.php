<?php

namespace Drupal\uc_helcim\Plugin\Ubercart\PaymentMethod;

use Drupal\Core\Form\FormStateInterface;
use Drupal\uc_helcim\CreditCardPaymentMethodBase;
use Drupal\uc_order\OrderInterface;

/**
 * Defines the Helcim gateway payment method.
 *
 * This is a payment gateway for Helcim credit cards and
 * for Helcim JS implmentations
 *
 * @UbercartPaymentMethod(
 *   id = "helcim_gateway",
 *   name = @Translation("Helcim gateway"),
 * )
 */
class HelcimGateway extends CreditCardPaymentMethodBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'debug' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form['txn_type'] = [
      '#type' => 'select',
      '#title' => t('Transaction type'),
      '#description' => t('Transaction Type'),
      '#default_value' => $this->configuration['txn_type'],
      '#options' => [
        '0' => t('Purchase'),
        '1' => t('Pre-Authorization'),
      ]
    ];

    $form['payment_method'] = [
      '#type' => 'select',
      '#title' => t('Payment Method'),
      '#description' => t('Choose between using Helcim.js and Direct Integration.'),
      '#default_value' => $this->configuration['payment_method'],
      '#options' => [
        '#helcimJS' => t('Helcim JS'), 
        '#directIntegration' => t('Direct Integration'),
      ]
    ];

    $form['helcim_js_token'] = [
      '#type' => 'textfield',
      '#title' => t('Helcim JS Token'),
      '#description' => t('Your Helcim.js Configuration Token (Required for Helcim JS Method)'),
      '#default_value' => $this->configuration['helcim_js_token'],
    ];

    $form['account_id'] = [
      '#type' => 'textfield',
      '#title' => t('Account Id'),
      '#description' => t('Your Helcim Commerce Account Id'),
      '#default_value' => $this->configuration['account_id'],
    ];

    $form['api_token'] = [
      '#type' => 'textfield',
      '#title' => t('API Token'),
      '#description' => t('Your Helcim Commerce API Token'),
      '#default_value' => $this->configuration['api_token'],
    ];

    $form['terminal_id'] = [
      '#type' => 'textfield',
      '#title' => t('Terminal Id'),
      '#description' => t('Commerce Terminal Id'),
      '#default_value' => $this->configuration['terminal_id'],
    ];

    $form['test_mode'] = [
      '#type' => 'select',
      '#title' => t('Test Mode'),
      '#description' => t('Test Mode (Transactions will be considered as test if on)'),
      '#default_value' => $this->configuration['test_mode'],
      '#options' => [
        '0' => t('Off'),
        '1' => t('On'),
      ]
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['txn_type'] = $form_state->getValue('txn_type');
    $this->configuration['payment_method'] = $form_state->getValue('payment_method');
    $this->configuration['helcim_js_token'] = $form_state->getValue('helcim_js_token');
    $this->configuration['account_id'] = $form_state->getValue('account_id');
    $this->configuration['api_token'] = $form_state->getValue('api_token');
    $this->configuration['terminal_id'] = $form_state->getValue('terminal_id');
    $this->configuration['test_mode'] = $form_state->getValue('test_mode');
  }

  /**
   * {@inheritdoc}
   */
  protected function chargeCard(OrderInterface $order, $amount, $txn_type, $reference = NULL) {
    $user = \Drupal::currentUser();

    $month = $order->payment_details['cc_exp_month'];
    $year  = $order->payment_details['cc_exp_year'];

    if ($year < 100) {

      $year = $year + 2000;

    }

    // SET MONTH VALUE TO BE IN TWO DIGIT FORM
    if($month < 10){

      $month = 0 . $month;

    }

    // Card is expired at 0:00 on the first day of the next month.
    $expiration_date = mktime(0, 0, 0, $month + 1, 1, $year);

    //
    // CURL TO COMMERCE
    //
 
    $twoDigitYear = substr($year, -2);

    // CHECK TRANSACTION TYPE
    if($this->configuration['txn_type'] == '0'){

      $txn_type = 'purchase';

    }elseif($this->configuration['txn_type'] == '1'){

      $txn_type = 'preauth';

    }

    // SET POST FIELDS
    $postData = array(
      'test'=>$this->configuration['test_mode'],
      'ipAddress'=>$_SERVER["HTTP_CLIENT_IP"],
      'transactionType'=>$txn_type,
      'accountId'=>trim($this->configuration['account_id']),
      'apiToken'=>trim($this->configuration['api_token']),
      'terminalId'=>trim($this->configuration['terminal_id']),
      'customerCode'=> $user->id(),
      'amount'=>$amount,
      'comments'=>'',
    );

    if($this->configuration['payment_method'] == '#helcimJS'){

      //
      // PROCESS WITH CARD TOKEN
      //
      
      // UPDATE POST FIELDS
      if(isset($order->payment_details['card_token'])){ $postData['cardToken'] = $order->payment_details['card_token']; }
      if(isset($order->payment_details['card_f4l4'])){ $postData['cardF4L4'] = $order->payment_details['card_f4l4']; }
    
    }else{

      //
      // PROCESS WITH CARD DATA
      //

      // UPDATE POST FIELDS

      $postData['cardNumber'] = $order->payment_details['cc_number'];
      $postData['cardExpiry'] = $month.$twoDigitYear;
      $postData['cardCVV'] = $order->payment_details['cc_cvv'];

    }

    $billingInformation = $order->getAddress('billing');
    $shippingInformation = $order->getAddress('delivery');

    // BILLING FIELDS
    $postData['billing_contactName'] = $billingInformation->getFirstName() . ' ' . $billingInformation->getLastName();
    $postData['billing_businessName'] = $billingInformation->getCompany();
    $postData['billing_street1'] = $billingInformation->getStreet1();
    $postData['billing_street2'] = $billingInformation->getStreet2();
    $postData['billing_city'] = $billingInformation->getCity();
    $postData['billing_province'] = $billingInformation->getZone();
    $postData['billing_country'] = $billingInformation->getCountry();
    $postData['billing_postalCode'] = $billingInformation->getPostalCode();
    $postData['billing_phone'] = $billingInformation->getPhone();
    $postData['billing_fax'] = '';
    $postData['billing_email'] = $order->getEmail();

    // SHIPPING FIELDS
    $postData['shipping_contactName'] = $shippingInformation->getFirstName() . ' ' . $shippingInformation->getLastName();
    $postData['shipping_businessName'] = $shippingInformation->getCompany();
    $postData['shipping_street1'] = $shippingInformation->getStreet1();
    $postData['shipping_street2'] = $shippingInformation->getStreet2();
    $postData['shipping_city'] = $shippingInformation->getCity();
    $postData['shipping_province'] = $shippingInformation->getZone();
    $postData['shipping_country'] = $shippingInformation->getCountry();
    $postData['shipping_postalCode'] = $shippingInformation->getPostalCode();
    $postData['shipping_phone'] = $shippingInformation->getPhone();
    $postData['shipping_fax'] = '';
    $postData['shipping_email'] = $order->getEmail();

    $lineItems = $order->getLineItems();

    foreach($lineItems as $lineItem){

      // SET SHIPPING AMOUNT
      if($lineItem['type'] == 'shipping'){

        $postData['amountShipping'] = round($lineItem['amount'], 2);
        $postData['shippingMethod'] = $lineItem['title'];

      }

    }
    
    $i = 1;
    foreach($order->products as $product){

      // SET
      $postData['itemSKU' . $i] = $product->model->value;
      $postData['itemDescription' . $i]=$product->title->value;
      $postData['itemSerialNumber' . $i] = '';
      $postData['itemQuantity' . $i]=$product->qty->value;
      $postData['itemPrice' . $i]=$product->price->value;
      $postData['itemTotal' . $i] = $product->qty->value * $product->price->value;

      $i++;

    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://secure.myhelcim.com/api/",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => http_build_query($postData),
      CURLOPT_HTTPHEADER => array(
        "Cache-Control: no-cache"
      ),
    ));

    $errorMessage = '';
    $response = curl_exec($curl);
    $err = curl_error($curl);
    $success = TRUE;
    curl_close($curl);

    if($err){

      $errorMessage = 'Failed Connection.';
      $success = FALSE;

    }else{

      // TRY CONVERTING
      $responseXML = simplexml_load_string($response);
      if($responseXML){

        // CHECK RESPONSE
        if($responseXML->response == 1){

          // TRANSACTION GOOD

        }else{

          // TRANSACTION DECLINED
          $errorMessage = (string)$responseXML->responseMessage;
          $success = FALSE;

        }
      }else{

        // FAILED CONVERTING
        $errorMessage = 'Failed Response.';
        $success = FALSE;

      }

    }

    if ($success) {
      $message = $this->t('Credit card charged: @amount', ['@amount' => uc_currency_format($amount)]);
      uc_order_comment_save($order->id(), $user->id(), $message, 'admin');
    }
    else {
      $message = $this->t('Credit card charge failed.');
      uc_order_comment_save($order->id(), $user->id(), $message, 'admin');
    }

    $result = array(
      'success' => $success,
      'comment' => $success ? $this->t('Credit card payment processed successfully. Approval Code: ' . $this->t((string)$responseXML->transaction->approvalCode)) : $this->t('Credit card charge failed. ' . $errorMessage),
      'message' => $success ? $this->t('Credit card approved by Helcim. Order ID: ' . $order->id() . ' Amount: ' . $amount) : $this->t('Credit card charge failed. ' . $errorMessage),
      'uid' => $user->id(),
    );

    return $result;
  }

}
