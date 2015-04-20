<?php

/*

  Plugin Name: Transferuj Gateway Easy Digital Download
  Plugin URL: http://transferuj.pl
  Description: Transferuj gateway for Easy Digital Download
  Version: 1.0
  Author: Transferuj
  Author URI: http://transferuj.pl

 */

// rejestracja bramki
function transferuj_register_gateway($gateways) {
    global $edd_options;
    $gateways['transferuj'] = array('admin_label' => 'Transferuj', 'checkout_label' => $edd_options['transferuj_display_name']);
    return $gateways;
}

add_filter('edd_payment_gateways', 'transferuj_register_gateway');

// widoki dla platnosci
function edd_transferuj_show_cc_form() {
    global $edd_options;
    
    $id =  $edd_options['transferuj_merchantid'];
    $view = $edd_options['transferuj_view'];
    $jQuery = '$jQuery';
    if ($view == 0) {


        $str = <<<MY_MARKER
               <fieldset> <tr><td>
   <input type="hidden" id="channel"  name="kanal" value=" ">
            <style type="text/css">                 
            .checked_v {
                box-shadow: 0px 0px 10px 3px #15428F !important;;

            }
            .channel {
                display: inline-block; 
                width: 130px; 
                height:63px; 
               
                text-align:center;
            }
             </style>   
            
            <script type="text/javascript">
                function ShowChannelsCombo()
                {
                    var $jQuery = jQuery.noConflict();
                    var str = '<div  style="margin:20px 0 15px 0"  id="kanal"><label>Wybierz bank:</label></div>';

                    for (var i = 0; i < tr_channels.length; i++) {
                        str += '<div   class="channel" ><img id="' + tr_channels[i][0] + '" class="check" style="height: 80%" src="' + tr_channels[i][3] + '"></div>';
                    }

                    var container = jQuery("#kanaly_v");
                    container.append(str);
                    
                    
                      jQuery(document).ready(function () {
                        
                        jQuery(".check").click(function () {
                            
                            $jQuery(".check").removeClass("checked_v");
                            $jQuery(this).addClass("checked_v");
                            var n = $jQuery(document).height();
                            jQuery('html, body').animate({ scrollTop: n }, 500)
                            var kanal = 0;
                            kanal = jQuery(this).attr("id");
                             $jQuery('#channel').val(kanal);

                         });
                        });
                     


                }
                 jQuery.getScript("https://secure.transferuj.pl/channels-{$id}0.js", function () {
                    ShowChannelsCombo()
                });
            </script>
            <div style="background: white " id="kanaly_v"></div>
            <div id="descriptionBox"></div> <br/>
            <div id="termsCheckboxBox">
                <input type="checkbox" id="termsCheckbox" checked name="terms_t">
                    <a href="https://transferuj.pl/regulamin.pdf" target="blank">
                                Akceptuję warunki regulaminu korzystania z serwisu Transferuj.pl
                    </a>
                </input> </br></br>
                 <div style="text-align: justify"> 
             <b>UWAGA</b></br>
                 Po opłaceniu zamówienia w systemie Transferuj.pl nastąpi powrót na stronę sklepu. 
                 Po odebraniu emaila z potwierdzeniem opłacenia transakcji w systemie Transferuj.pl prosimy odświeżyć stronę sklepu, a zakupione pordukty 
                 staną się dostępne do pobrania.
            </div></div>
            </td></tr></fieldset>
MY_MARKER;
    }



    if ($view == 1) {
        if(is_numeric($id)){
        $channels_url = "https://secure.transferuj.pl/channels-" . $id . "0.js";
        $JSON = file_get_contents($channels_url);

        // parse the channel list
        $pattern = "!\['(?<id>\d{1,2})','(?<name>.+)','(.+)','(.+)','!";
        preg_match_all($pattern, $JSON, $matches);

        // create list of channels
        $channels = '<select class="channelSelect" id ="channelSelect" name="kanal">';
        for ($i = 0; $i < count($matches['id']); $i++) {
            $channels .= '<option value="' . $matches['id'][$i] . '">' .
                    $matches['name'][$i] . "</option>";
        }
        $channels .= '</select>';

        $str = <<<MY_MARKER
             <fieldset><tr>        
   <td>  <div id="descriptionBox"></div> <br/>
            <div  style="margin:20px 0 15px 0"  id="kanal"><label>Wybierz bank:</label></div>
            <div id="channelSelectBox">{$channels}</div>
            <div style="margin:30px 0 15px 0" id="termsCheckboxBox">
                <input  type="checkbox" checked id="termsCheckbox" name="terms_t">
                    <a href="https://transferuj.pl/regulamin.pdf" target="blank">
                                Akceptuję warunki regulaminu korzystania z serwisu Transferuj.pl
                    </a>
                </input> <br/>
            </div>
            </td></br>
            <div style="text-align: justify"> 
             <b>UWAGA</b></br>
                  Po opłaceniu zamówienia w systemie Transferuj.pl nastąpi powrót na stronę sklepu. 
                 Po odebraniu emaila z potwierdzeniem opłacenia transakcji w systemie Transferuj.pl prosimy odświeżyć stronę sklepu, a zakupione pordukty 
                 staną się dostępne do pobrania.
            </div></div>
            </tr></fieldset>
          
            
MY_MARKER;
    }
    }
    if ($view == 2) {
        //$img = plugins_url('images/baner.png', __FILE__);

        $str = <<<MY_MARKER
            <fieldset><div style="text-align: justify">
           <b>UWAGA</b></br>
                 Po opłaceniu zamówienia w systemie Transferuj.pl nastąpi powrót na stronę sklepu. 
                 Po odebraniu emaila z potwierdzeniem opłacenia transakcji w systemie Transferuj.pl prosimy odświeżyć stronę sklepu, a zakupione pordukty 
                 staną się dostępne do pobrania.
            </div></fieldset>
            
MY_MARKER;
    }
    echo $str;
}

add_action('edd_transferuj_cc_form', 'edd_transferuj_show_cc_form');

// proces platnosci
function transferuj_process_payment($purchase_data) {
    global $edd_options;

    // check there is a gateway name
    if (!isset($purchase_data['post_data']['edd-gateway']))
        return;

    // collect payment data
    $payment_data = array(
        'price' => $purchase_data['price'],
        'date' => $purchase_data['date'],
        'user_email' => $purchase_data['user_email'],
        'purchase_key' => $purchase_data['purchase_key'],
        'currency' => $edd_options['currency'],
        'downloads' => $purchase_data['downloads'],
        'user_info' => $purchase_data['user_info'],
        'cart_details' => $purchase_data['cart_details'],
        'status' => 'pending'
    );

    $errors = edd_get_errors();

    if ($errors) {
        // return the errors if any
        edd_send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['edd-gateway']);
    } else {

        $payment = edd_insert_payment($payment_data);

        // check the payment
        if (!$payment) {

            edd_send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['edd-gateway']);
        } else {

            $returnurl = add_query_arg('payment-confirmation', 'transferuj', get_permalink($edd_options['success_page']));

            $transferuj_address = 'https://secure.transferuj.pl';

           
            $merchant = $edd_options['transferuj_merchantid'];
            $secretpass = $edd_options['transferuj_secretpass'];
            $amount = $purchase_data['price'];
            $ordernumber = str_pad($payment, 4, 0, STR_PAD_LEFT);
           
            $splitpayment = '0';
            $crc = base64_encode($ordernumber);

            $md5 = md5($merchant . $amount . $crc . $secretpass);

            edd_empty_cart();

            echo '
				<form action="' . $transferuj_address . '" name="transferuj_payment_form" method="post">
				
				<input type="hidden" name="opis" value="Zamówienie nr: ' . $ordernumber . '" />
				<input type="hidden" name="id" value="' . $merchant . '" />
				<input type="hidden" name="nazwisko" value="' . $purchase_data['user_info']['first_name'] . ' ' . $purchase_data['user_info']['last_name'] . '" />
				<input type="hidden" name="email" value="' . $purchase_data['user_info']['email'] . '" />
				<input type="hidden" name="crc" value="' . $crc . '" />
				<input type="hidden" name="kwota" value="' . $amount . '" />
				<input type="hidden" name="kanal" value="' . $_POST['kanal'] . '" />
                                <input type="hidden" name="kanal" value="' . $_POST['terms_t'] . '" /> 
				<input type="hidden" name="pow_url" value="' . $returnurl . '" />
				<input type="hidden" name="pow_url_blad" value="' . $returnurl . '" />
				<input type="hidden" name="wyn_url" value="' . $returnurl . '" />
				
				<input type="hidden" name="md5sum" value="' . $md5 . '" />
				</form>		
				<b> Trwa przekierowanie...</b>	
                                		
				<script type="text/javascript" event="onload">
					document.transferuj_payment_form.submit();
				</script>';
            
        }
    }
}

add_action('edd_gateway_transferuj', 'transferuj_process_payment');

function transferuj_ipn() {
    global $edd_options;

    if ($_SERVER['REMOTE_ADDR'] == '195.149.229.109' && !empty($_POST)) {

        $id_sprzedawcy = $_POST['id'];
        $status_transakcji = $_POST['tr_status'];
        $id_transakcji = $_POST['tr_id'];
        $kwota_transakcji = $_POST['tr_amount'];
        $kwota_zaplacona = $_POST['tr_paid'];
        $blad = $_POST['tr_error'];
        $data_transakcji = $_POST['tr_date'];
        $opis_transakcji = $_POST['tr_desc'];
        $ciag_pomocniczy = $_POST['tr_crc'];
        $email_klienta = $_POST['tr_email'];
        $suma_kontrolna = $_POST['md5sum'];
        $blad = $_POST['tr_error'];
        $md5=md5($edd_options['transferuj_merchantid'].$id_transakcji.$kwota_transakcji.$ciag_pomocniczy.$edd_options['transferuj_secretpass']);
// sprawdzenie stanu transakcji
        if ($md5===$suma_kontrolna){

        if ($status_transakcji == 'TRUE' && $blad == 'none') {
            $order = base64_decode($ciag_pomocniczy);
            edd_insert_payment_note($order, 'Zapłacono przez Transferuj: ' . $id_transakcji);
            edd_update_payment_status($order, 'publish');
        } else {
            $order = base64_decode($ciag_pomocniczy);

            edd_insert_payment_note($order, 'Błąd transakcji: ' . $id_transakcji . 'Typ błędu: ' . $blad);
            edd_update_payment_status($order, 'failed');
        }
    }
     echo 'TRUE';
    }
   
}

add_action('init', 'transferuj_ipn');

function transferuj_add_settings($settings) {

    $transferuj_settings = array(
        array(
            'id' => 'transferuj_settings',
            'name' => '<strong>Ustawienia dla Transferuj.pl</strong>',
            'desc' => 'Skonfiguruj',
            'type' => 'header'
        ),
        array(
            'id' => 'transferuj_display_name',
            'name' => 'Wyświetlana nazwa',
            'desc' => 'Nazwa wyświetlana podczas wyboru metody płatności',
            'type' => 'text',
            'size' => 'regular',
            'default' => 'Quickpay',
        ),
        array(
            'id' => 'transferuj_merchantid',
            'name' => 'ID Sprzedawcy',
            'desc' => 'ID Sprzedawcy nadane podczas tejestraci w Transferuj.pl',
            'type' => 'text',
            'size' => 'regular'
        ),
        array(
            'id' => 'transferuj_secretpass',
            'name' => 'Kod bezpieczeństwa',
            'desc' => 'Kod znajduje się w Panelu Odbiorcy Płatności w zakładce Ustawienia->Powiadomienia',
            'type' => 'text',
            'size' => 'regular'
        ),
        array(
			'id' => 'transferuj_view',
			'name' => 'Widok dla kanałów płatności', 'transferuj_view',
			'desc' => 'Wybierz sposób i miejsce wyświetlania kanałów płatości',
			'type' => 'select',
			'options' => array( '0' => 'Ikony banków na stronie sklepu',
								'1'=>'Lista banków na stronie sklepu', 
								'2'=>'Przekierowanie na stronę Transferuj.pl'
								),
			'size' => 'regular'
		)
    );

    return array_merge($settings, $transferuj_settings);
}

add_filter('edd_settings_gateways', 'transferuj_add_settings');
 