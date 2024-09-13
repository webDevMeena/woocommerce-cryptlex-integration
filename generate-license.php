<?php

require_once('../wp-load.php');
require('CryptlexApi.php');

// pass this secret as query param in the url e.g. https://yourserver.com/generate-license.php?cryptlex_secret=SOME_RANDOM_STRING
$CRYPTLEX_SECRET = "ZCDIUXV7SPHAGEK5QWC18NM4RJ932X";
// access token must have following permissions (scope): license:write, user:read, user:write
$PERSONAL_ACCESS_TOKEN = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzY29wZSI6WyJhY2NvdW50OnJlYWQiLCJhY2NvdW50OndyaXRlIiwiYWN0aXZhdGlvbjpyZWFkIiwiYW5hbHl0aWNzOnJlYWQiLCJhdXRvbWF0ZWRFbWFpbDpyZWFkIiwiYXV0b21hdGVkRW1haWw6d3JpdGUiLCJhdXRvbWF0ZWRFbWFpbEV2ZW50TG9nOnJlYWQiLCJldmVudExvZzpyZWFkIiwiZmVhdHVyZUZsYWc6cmVhZCIsImZlYXR1cmVGbGFnOndyaXRlIiwiaW52b2ljZTpyZWFkIiwibGljZW5zZTpyZWFkIiwibGljZW5zZTp3cml0ZSIsImxpY2Vuc2VQb2xpY3k6cmVhZCIsImxpY2Vuc2VQb2xpY3k6d3JpdGUiLCJtYWludGVuYW5jZVBvbGljeTpyZWFkIiwibWFpbnRlbmFuY2VQb2xpY3k6d3JpdGUiLCJvcmdhbml6YXRpb246cmVhZCIsIm9yZ2FuaXphdGlvbjp3cml0ZSIsInBheW1lbnRNZXRob2Q6cmVhZCIsInBheW1lbnRNZXRob2Q6d3JpdGUiLCJwZXJzb25hbEFjY2Vzc1Rva2VuOndyaXRlIiwicHJvZHVjdDpyZWFkIiwicHJvZHVjdDp3cml0ZSIsInByb2R1Y3RWZXJzaW9uOnJlYWQiLCJwcm9kdWN0VmVyc2lvbjp3cml0ZSIsInJlbGVhc2U6cmVhZCIsInJlbGVhc2U6d3JpdGUiLCJyZXNlbGxlcjpyZWFkIiwicmVzZWxsZXI6d3JpdGUiLCJyb2xlOnJlYWQiLCJyb2xlOndyaXRlIiwic2VnbWVudDpyZWFkIiwic2VnbWVudDp3cml0ZSIsInNlbmRpbmdEb21haW46cmVhZCIsInNlbmRpbmdEb21haW46d3JpdGUiLCJ0YWc6cmVhZCIsInRhZzp3cml0ZSIsInRyaWFsQWN0aXZhdGlvbjpyZWFkIiwidHJpYWxBY3RpdmF0aW9uOndyaXRlIiwidHJpYWxQb2xpY3k6cmVhZCIsInRyaWFsUG9saWN5OndyaXRlIiwidXNlcjpyZWFkIiwidXNlcjp3cml0ZSIsIndlYmhvb2s6cmVhZCIsIndlYmhvb2s6d3JpdGUiLCJ3ZWJob29rRXZlbnRMb2c6cmVhZCJdLCJzdWIiOiIyYTY1ZWM0My0xYWJjLTQxMDAtOWJjYi1hMGU4NDcyNGVjNGUiLCJlbWFpbCI6InJtLnNwYXJrbGUwMjNAZ21haWwuY29tIiwianRpIjoiYmM4ZjFlMTYtNjExYy00MWIzLWJkOTYtMGJjNDRjYTRkNjA3IiwiaWF0IjoxNzI1NDQyODA3LCJ0b2tlbl91c2FnZSI6InBlcnNvbmFsX2FjY2Vzc190b2tlbiIsInRlbmFudGlkIjoiYzI2ZThiZTItNjMwNC00YTU4LWE3ZTAtNDQxOTY1YjQ5NGZjIiwiZXhwIjoxNzY3MTE5NDAwLCJhdWQiOiJodHRwczovL2FwaS5jcnlwdGxleC5jb20ifQ.S2ST9MU5TPW1shFNaQa2Vm91yYZ4-pSDTwdyNOLsR9JGn7X6yH3NDtyo82gW91Osgs5SeBudvR_rHxtNbpO5_u3y_vCbic0gnLSAegVGMTkMjn64YRkad6efum2u7ut9TpMFYr8uGLQBHQq-FGIvYgTeVZ5KjGcIngJEshP6oK5bJZNHHkISqvGr4JEz-SUyMaI7N8_8J_M-63ArGr2kOT29gcquY1QzfKNiwRpwys9vrAMclJvfuuCZTHBTXhEUiMQi0P0itLeC5c3CT1xqxZkgGLiok8TZy70MRI-rDDdFmU_8F6WAGrSxmq6SI8-8kFO6m0G9Vx2459gLACaKrg";

// utility functions
function IsNullOrEmptyString($str){
    return (!isset($str) || trim($str) === '');
}

function ForbiddenRequest() {
    http_response_code(403);
    $message['error'] = 'You are not authorized to perform this action!';
    echo json_encode($message);
}

function BadRequest($error) {
    http_response_code(400);
    $message['error'] = $error;
    echo json_encode($message);
}

function VerifySecret($secret) {
    global $CRYPTLEX_SECRET;
    if($secret == $CRYPTLEX_SECRET) {
        return true;
    }
    return false;
}
function getCryptlexProductId($product_id) {
    return get_post_meta($product_id, '_cryptlex_product_id', true);
}

    function getProductNameFromOrder($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return null; 
        }
        $items = $order->get_items();
        $product_names = [];
        
        foreach ($items as $item) {
            $product_names[] = $item->get_name();
        }
        
        return implode(', ', $product_names);
    }

    
    function parseStripePostData($payload) {
    
        if (!isset($payload['data']['object']['metadata']['customer_email'])) {
            BadRequest('customer_email is missing...!');
            return NULL;
        }
    
        // if (!isset($payload['data']['object']['metadata']['quantity'])) {
        //     BadRequest('quantity is missing!');
        //     return NULL;
        // }
    
        if (!isset($payload['data']['object']['metadata']['customer_name'])) {
            BadRequest('customer_name is missing!');
            return NULL;
        }
    
        if (!isset($payload['data']['object']['metadata']['order_id'])) {
            BadRequest('order_id is missing!');
            return NULL;
        }
    
        $postBody['customer_email'] = $payload['data']['object']['metadata']['customer_email'];
        $postBody['quantity'] = 1;
        $postBody['customer_name'] = $payload['data']['object']['metadata']['customer_name'];
        $postBody['reference'] = $payload['data']['object']['metadata']['order_id'];
    
        return $postBody;
    }
    
    
    try {
        // Verify the secret
        if(!VerifySecret($_GET['cryptlex_secret'])) {
            return ForbiddenRequest();
        }
    
        // Set Cryptlex access token
        CryptlexApi::SetAccessToken($PERSONAL_ACCESS_TOKEN);
    
        // Product ID
       // $product_id = "8bf8e8e0-7696-4ec6-8ed8-9d6f3693b8ed";

        $payload = json_decode(file_get_contents("php://input"), true);
		
        // Parse Stripe post data
        $postBody = parseStripePostData($payload);

        if($postBody == NULL) {
            return;
        }
    
        $customer_email = $postBody['customer_email'];
        $first_name = explode(' ', $postBody['customer_name'])[0] ?? '';
        $last_name = explode(' ', $postBody['customer_name'])[1] ?? '';
        $quantity = $postBody['quantity'];
        $order_id = $postBody['reference'];
        $product_name = getProductNameFromOrder($order_id);

        $order = wc_get_order($order_id);
        $items = $order->get_items();

        // Initialize an array to store license information
        $licenses_info = [];

        foreach ($items as $item) {
            $product_id = $item->get_product_id();
            $cryptlex_product_id = getCryptlexProductId($product_id);

            if (!$cryptlex_product_id) {
                BadRequest('Cryptlex product ID not found for product ID ' . $product_id);
                continue;
            }

            // Check if user exists
            $user_exists = false;
            $user = CryptlexApi::GetUser($customer_email);

            if ($user == NULL) {
                // Create new user
                $user_body["email"] = $customer_email;
                $user_body["firstName"] = $first_name;
                $user_body["lastName"] = $last_name;
                $user_body["company"] = $last_name; // Adjust this field as needed
                $user_body["password"] = substr(md5(uniqid()), 0, 8); // Generate a random 8 character password
                $user_body["role"] = "user";
                $user = CryptlexApi::CreateUser($user_body);
            } else {
                $user_exists = true;
            }

            // Create license
            $license_body["allowedActivations"] = (int)$quantity;
            $license_body["productId"] = $cryptlex_product_id; // Use dynamic Cryptlex product ID
            $license_body["userId"] = $user->id;
            $metadata["key"] = "order_id";
            $metadata["value"] = $order_id;
            $metadata["visible"] = false;
            $license_body["metadata"] = array($metadata);

            $license = CryptlexApi::CreateLicense($license_body);

            // Store license info for email
            $licenses_info[] = [
                'product_name' => $item->get_name(),
                'license_key' => $license->key,
            ];
        }

        // After loop, send a single email
        if (!empty($licenses_info)) {
            $to = $customer_email;
            $subject = 'Your License Key(s) from [YUMAWORKS]'; // Use your company name
            $message = "Dear Customer,\n\n";
            $message .= "Thank you for your recent purchase from Our Site [Yumaworks]! Below, you will find your license key(s):\n\n";

            foreach ($licenses_info as $info) {
                $message .= "Product: " . $info['product_name'] . "\n";
                $message .= "License Key: " . $info['license_key'] . "\n\n";
            }

            $message .= "**Order Details:**\n";
            $message .= "Customer Email: " . $customer_email . "\n";
            $message .= "Customer Name: " . $first_name . " " . $last_name . "\n";
            $message .= "Order ID: " . $order_id . "\n";
            $message .= "Please keep this information secure, as it will be required for product activation and support.\n\n";
            $message .= "If you have any questions or need further assistance, feel free to reach out to our support team at [support@yumaworks.com].\n\n";
            $message .= "Thank you once again for choosing [Yumaworks]!\n\n";
            $message .= "Best regards,\n";
            $message .= "The [Yumaworks] Team";
            
            $headers = 'From: Yumaworks <noreply@' . $_SERVER['SERVER_NAME'] . '>' . "\r\n";
            // Use an external email service or uncomment the following for WordPress `wp_mail`
            if (wp_mail($to, $subject, $message, $headers)) {
                echo 'License Email has been sent successfully..';
            } else {
                // Handle email sending error (log, notify admin)
                error_log('Error sending license key email to ' . $customer_email);
            }
        } 
    
    } catch(Exception $e) {
        http_response_code(500);
        echo 'message: ' .$e->getMessage();
    }