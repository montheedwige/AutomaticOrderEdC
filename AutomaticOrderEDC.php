<?php
/**
 * Plugin Name: AutomaticOrderEDC
 * Plugin URI: http://www.mywebsite.com/my-first-plugin
 * Description: The very first plugin that I have ever created.
 * Version: 1.1
 * Author: Edwige
 * Author URI: http://www.mywebsite.com
 */
 // Get an instance of the WC_Order object

add_action('woocommerce_thankyou', 'edc_order', 10, 1);
function edc_order( $order_id ) {
    if ( ! $order_id )
        return;
        

    // Allow code execution only once
    if( ! get_post_meta( $order_id, '_thankyou_action_done', true ) ) {

        // Get an instance of the WC_Order object
        $order = wc_get_order( $order_id );

        // Get the order key
        $order_key = $order->get_order_key();

        // Get the order number
        $order_key = $order->get_order_number();

        if($order->is_paid())
            $paid = __('yes');
        else
            $paid = __('no');

        // Loop through order items
        foreach ( $order->get_items() as $item_id => $item ) {

            // Get the product object
            $product = $item->get_product();

            // Get the product Id
            $product_id = $product->get_id();

            // Get the product name
            $product_id = $item->get_name();
        }


//code to create xml code
        $xml = new DOMDocument("1.0","UTF-8");
        $orderdetails=$xml->createElement('orderdetails');
        $xml->appendChild($orderdetails);
	        $customerdetails=$xml->createElement('customerdetails');
	        $orderdetails->appendChild($customerdetails);
	        	$email=$xml->createElement('email', 'joel.mbondi@hitem.fr');
	        	$customerdetails->appendChild($email);
	        	//tt333c1t67646281e84r959580cct977
	        	$apikey=$xml->createElement('apikey', 'tt333c1t67646281e84r959580cct977');
	        	$customerdetails->appendChild($apikey);

	        	$output=$xml->createElement('output', 'advanced');
	        	$customerdetails->appendChild($output);
	        //Receiver
	        $receiver=$xml->createElement('receiver');
	        $orderdetails->appendChild($receiver);
	        	$name=$xml->createElement('name',$order->shipping_last_name.' '.$order->shipping_first_name);
	        	$receiver->appendChild($name);

            //Condition pour les pays de livraison edc

              //Tableau des pays pris en compte par EDC
              $search_array = array(0 =>'Debut', 1 => 'NL',
               2 => 'BE', 3 => 'DE',4 => 'GB',
               5 => 'FR', 6 => 'LU' , 7 => 'AT' , 8 =>'PT', 9 =>'ES',
               10 => 'CH', 11 =>'SE', 12 =>'IT', 13 =>'AD',
               14 => 'AR', 15 =>'AW', 16 => 'BA',
               17 => 'BR', 18 =>'BG', 19 =>'CA', 20 =>'HR',
               21 => 'CY', 22 =>'CZ', 23 =>'DK', 24 =>'EE',
               25 => 'GR', 26 => 'HK', 27 =>'HU', 28 =>'IS',
               29 => 'IE', 30 =>'IL', 31 =>'JP', 32 =>'MT',
               33 => 'MX', 34 =>'MC', 35 =>'MA', 36 =>'AN',
               37 => 'NZ', 38 =>'NO', 39 =>'PL', 40 =>'RO',
               41 => 'SM' , 42 => 'SG', 43 =>'SK', 44 =>'ZA',
               45 => 'SR', 46 => 'TW' , 47 => 'TH', 48 => 'TR' ,
               49 => 'UA', 50 => 'AE', 51 => 'US');
               //fin Tableau des pays pris en compte par EDC

               //Recherche si le pays du client est dans le Tableau

             $key = array_search($order->shipping_country, $search_array);

             //Si oui?
              if ($key!= NULL) {
                  echo ( $order->billing_country);
                  $country=$xml->createElement('country',$key);
      	        	$receiver->appendChild($country);
                  $street=$xml->createElement('street',$order->billing_street);
      	        	$receiver->appendChild($street);
      	        	$house_nr=$xml->createElement('house_nr',$order->billing_address_1);
      	        	$receiver->appendChild($house_nr);
      	        	$postalcode=$xml->createElement('postalcode',$order->billing_postcode);
      	        	$receiver->appendChild($postalcode);
      	        	$city=$xml->createElement('city',$order->shipping_city);
      	        	$receiver->appendChild($city);
              } else{
                //Si non, on livre chez hofy
                $country=$xml->createElement('country','5');
                $receiver->appendChild($country);
                $street=$xml->createElement('street','Rue Michelet ');
                $receiver->appendChild($street);
                $house_nr=$xml->createElement('house_nr','22');
                $receiver->appendChild($house_nr);
                $postalcode=$xml->createElement('postalcode','42100');
                $receiver->appendChild($postalcode);
                $city=$xml->createElement('city','Saint-Etienne');
                $receiver->appendChild($city);
              }

	        	$phone=$xml->createElement('phone',$order->billing_phone);
	        	$receiver->appendChild($phone);

	        //Products
	        $product=$xml->createElement('product');
	        $orderdetails->appendChild($product);

          //IDs des différents produits EDC
	       	foreach ( $order->get_items() as $item_id => $item ) {

            // Get the product object
            $produit = $item->get_product();

            // Get the product Id
            $produit_id = $produit->get_sku();

            // Get the product name
            //$produit_id = $item->get_name();

	        	$artnr=$xml->createElement('artnr',$produit_id);
	        	$product->appendChild($artnr);
        }

        //echo $orderdetails;

        $order->update_meta_data( '_thankyou_action_done', true );
        $order->save();
        //$n="<xmp>" .$xml->saveXML(). "</xmp>";

        $curl = curl_init();
        $d = $xml->saveXML();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://www.one-dc.com/en/ao/',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => 'data='.$d,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //$n="<xmp>" .$xml->saveXML(). "</xmp>";
        //echo $response;
        //var_dump ($order->billing_country);
        //var_dump($key) ;
        //var_dump($result);
        //var_dump('data='.$d);



    }
}


 ?>
