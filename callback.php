<?php
    require 'config.php';
    require 'vendor/autoload.php';

    use GuzzleHttp\Client;
   // use GuzzleHttp\Exception\RequestException;
   // use GuzzleHttp\Psr7\Request;

    $userName = strtoupper($_GET['userName']);
    $content_id = $_GET['contentId'];
    $contentTitle = addslashes($_GET['title']);
    $user_content = strtoupper($_GET['title']);
    $contentDescription = addslashes($_GET['description']);
    $contentImage = $_GET['image'];
    $streamUrl = $_GET['stream_url'];
    $streamHls = $_GET['hls'];
    $phone_number = $_GET['phoneNumber'];
    $app_section = $_GET['appSection'];
    $premium_amount = $_GET['premiumAmount'];
    $content_duration = strtoupper($_GET['duration']);
    $data = file_get_contents('php://input');

    $json = json_decode($data);

    $fp = fopen('response.json', 'w');
    fwrite($fp, $json->response->reference); //keys/properties inside response object
    fclose($fp);

    //response values
    $merchantRequestID = $json->Body->stkCallback->MerchantRequestID;
    $checkoutRequestID = $json->Body->stkCallback->CheckoutRequestID;
    $resultCode = $json->Body->stkCallback->ResultCode;
    $resultDesc = $json->Body->stkCallback->ResultDesc;
    $amount = $json->Body->stkCallback->CallbackMetadata->Item[0]->Value;
    $mpesaReceiptNumber = $json->Body->stkCallback->CallbackMetadata->Item[1]->Value;
    $transactionDate = $json->Body->stkCallback->CallbackMetadata->Item[3]->Value;
    $phoneNumber = $json->Body->stkCallback->CallbackMetadata->Item[4]->Value;

    $status = "1";
    // $date_reg = date('Y-m-d H:i:s');

    $sql = mysqli_query($con, "INSERT INTO mpesa_responses (merchantRequestid, checkoutRequestid, resultCode, resultDesc, amount, mpesaReceiptNumber, transactionDate, phoneNumber, content_id, premium_amount, app_section)
    VALUES('$merchantRequestID', '$checkoutRequestID', '$resultCode', '$resultDesc', '$amount', '$mpesaReceiptNumber', '$transactionDate', '$phoneNumber', '$content_id', '$premium_amount', '$app_section')");

    if($resultCode == "0"){
        $sql_subscribed_users = mysqli_query($con, "INSERT INTO movies_subscribed_users (content_id, content_title, content_description, content_image, stream_url, stream_hls, phone_number, app_section, premium_amount, status) VALUES( '$content_id', '$contentTitle', '$contentDescription', '$contentImage', '$streamUrl', '$streamHls', '$phone_number', '$app_section', '$premium_amount', '1')");

        $client = new GuzzleHttp\Client();
                $response = $client->post(
                    'https://api.mojasms.dev/sendsms',
                    [
                        'headers' => [
                            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiOTFkZWM0ZmI4YzBlZGYyNWViOGY4NDIzZmRjZmE0MmM3MTNjYmEyMTdjNzlhMTlkYTAyZGFjNTgxNTg0M2MzYmVjN2ViZDNmZTdlYjViMjQiLCJpYXQiOjE2MjcxNjQxNDIuOTA1MDQ4LCJuYmYiOjE2MjcxNjQxNDIuOTA1MDU1LCJleHAiOjE2NTg3MDAxNDIuNzk2ODE1LCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.ipOlP0YU6Ftw2tsB1w1wU-sK6X-8Y4b_jSlq2niR5w3LYHQzcdppy92ScT7qJ0m9qskADRbqg-mUTzQzCXQ_xXZrCVW8_pdlO-Sc0KgBXxrGYWk5IgTpLwnLxBdsNJQn6ntAmTWJHzh8YvX_8RhuBHscIBTFo4ikzrfJagaR_uAYo91IFQVCchNFVD0Qj9kZ-sppOnNSTR4I7aNR8Hgj0sVRDu2garKrUTwV2MJMpAIrfYdh_3DSvtfSrkHQn9AK_F3RBd_v9BXpz8ykuAQwSD13knL-f7f5lklQuIz7jcv6JRNPR5qmcA-et6Uj2gKL_XzwQ73n4qzOEKDk8NQDcmfrnSmhOmdiUxO9pooYy_7qKCTdvSdXhgH2K_L2XPVilULGF_n4l8WEKPnMv2wei39WzW9VW2V8PTgZlKwvu-2OdY06dZWHQF1hA8CLLBgaM22JadWeQLVv6JAFgcfFIfVmqiyF9wYf7um1-zfPFvZNQBREUQglbuYLC84NQ3-TM0JxXZhKdsFGahTYc0N5acfRLj6eipRWb-CbX6vhLYq_dIv_U2UUIaJVdev61aIcMNPr7BxPiuCuoBbAX0A03NirkqrNOP6tioVpkGuDAnAXZmXk5ogt1RbmJExabuqgDZaA4E_nWjRKmnGflb1Ji8FIPLESO9kaMTdww8T52vY',
                            'Accept' => 'application/json',
                        ],
                        'json' => [
                            'from' => 'MediaMaxOTP',
                            'phone' => $phone_number,
                            'message' => "Hello ".$userName.","."\n"."Thank you for purchasing ".$user_content." package valid for ".$content_duration.". Enjoy watching!.",
                            'webhook_url' => 'https://k24plus.co.ke/app/mojagatesms.php?phone='.$request->phone_number,
                        ],
                    ]
                );
                $body = $response->getBody();


    }else if($resultCode == "1032"){
        $sql_subscribed_users = mysqli_query($con, "INSERT INTO movies_subscribed_users (content_id, content_title, content_description, content_image, stream_url, stream_hls, phone_number, app_section, premium_amount, status) VALUES( '$content_id', '$contentTitle', '$contentDescription', '$contentImage', '$streamUrl', '$streamHls', '$phone_number', '$app_section', '$premium_amount', '0')");
    }

?>