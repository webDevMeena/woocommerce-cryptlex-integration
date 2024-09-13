<?php
class CryptlexApi 
{
	private static $access_token;

	// Leave it as such unless you're using the self-hosted version of Cryptlex.
	private static $base_path = "https://api.cryptlex.com/v3";

    public static function SetAccessToken($access_token)
	{
		self::$access_token = $access_token;
	}

	public static function CreateUser($body)
	{
		$api_url = self::$base_path . "/users";

        // creating new user...
        $user = self::PostRequest($api_url, $body);
        return $user;
	}

	public static function GetUser($email)
	{
		
		$api_url = self::$base_path . "/users";
		$query['email'] = $email;
        // check whether user exists
        $users = self::GetRequest($api_url."?".http_build_query($query));
        if (count($users)) {
            // user already exists!
            return $users[0];
		} 
		// user not found
		return NULL;
	} 

	public static function CreateLicense($body)
	{
		$api_url = self::$base_path . "/licenses";
		
        // creating license...
        $license = self::PostRequest($api_url, $body);
		return $license;
	}

	public static function RenewLicense($productId, $metadataKey, $metadataValue)
	{
		$api_url = self::$base_path . "/licenses";
		
        // fetching existing license...
        $licenses = self::GetRequest($api_url."?productId=".$productId."&metadataKey=".$metadataKey."&metadataValue=".$metadataValue);
		if (count($licenses) == 0) {
            throw new Exception("License does not exist!");
        }
        $license = $licenses[0];
		// renewing existing license...
		$renewedLicense = self::PostRequest($api_url."/".$license->id."/renew", null);
        return $renewedLicense;
	}

	private static function GetRequest($url)
	{

		
		if (!self::$access_token)
		{
			throw new Exception("eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzY29wZSI6WyJhY2NvdW50OnJlYWQiLCJhY2NvdW50OndyaXRlIiwiYWN0aXZhdGlvbjpyZWFkIiwiYW5hbHl0aWNzOnJlYWQiLCJhdXRvbWF0ZWRFbWFpbDpyZWFkIiwiYXV0b21hdGVkRW1haWw6d3JpdGUiLCJhdXRvbWF0ZWRFbWFpbEV2ZW50TG9nOnJlYWQiLCJldmVudExvZzpyZWFkIiwiZmVhdHVyZUZsYWc6cmVhZCIsImZlYXR1cmVGbGFnOndyaXRlIiwiaW52b2ljZTpyZWFkIiwibGljZW5zZTpyZWFkIiwibGljZW5zZTp3cml0ZSIsImxpY2Vuc2VQb2xpY3k6cmVhZCIsImxpY2Vuc2VQb2xpY3k6d3JpdGUiLCJtYWludGVuYW5jZVBvbGljeTpyZWFkIiwibWFpbnRlbmFuY2VQb2xpY3k6d3JpdGUiLCJvcmdhbml6YXRpb246cmVhZCIsIm9yZ2FuaXphdGlvbjp3cml0ZSIsInBheW1lbnRNZXRob2Q6cmVhZCIsInBheW1lbnRNZXRob2Q6d3JpdGUiLCJwZXJzb25hbEFjY2Vzc1Rva2VuOndyaXRlIiwicHJvZHVjdDpyZWFkIiwicHJvZHVjdDp3cml0ZSIsInByb2R1Y3RWZXJzaW9uOnJlYWQiLCJwcm9kdWN0VmVyc2lvbjp3cml0ZSIsInJlbGVhc2U6cmVhZCIsInJlbGVhc2U6d3JpdGUiLCJyZXNlbGxlcjpyZWFkIiwicmVzZWxsZXI6d3JpdGUiLCJyb2xlOnJlYWQiLCJyb2xlOndyaXRlIiwic2VnbWVudDpyZWFkIiwic2VnbWVudDp3cml0ZSIsInNlbmRpbmdEb21haW46cmVhZCIsInNlbmRpbmdEb21haW46d3JpdGUiLCJ0YWc6cmVhZCIsInRhZzp3cml0ZSIsInRyaWFsQWN0aXZhdGlvbjpyZWFkIiwidHJpYWxBY3RpdmF0aW9uOndyaXRlIiwidHJpYWxQb2xpY3k6cmVhZCIsInRyaWFsUG9saWN5OndyaXRlIiwidXNlcjpyZWFkIiwidXNlcjp3cml0ZSIsIndlYmhvb2s6cmVhZCIsIndlYmhvb2s6d3JpdGUiLCJ3ZWJob29rRXZlbnRMb2c6cmVhZCJdLCJzdWIiOiIwMjc4MjcxMi0wMjJmLTQ1MzMtYjBmNC1lNjllNzU4MzM5NTYiLCJlbWFpbCI6ImNtLnNwYXJrbGUwMjRAZ21haWwuY29tIiwianRpIjoiYzVlZDNlOTItNWY5Yy00OGU1LTk0ZTUtOWJhZmU5NzNmMTgzIiwiaWF0IjoxNzExMTA0Mzk5LCJ0b2tlbl91c2FnZSI6InBlcnNvbmFsX2FjY2Vzc190b2tlbiIsInRlbmFudGlkIjoiNjhlOWFkODMtZjRhNy00ZTFlLWIxZmItNGQxYTI4ZTRiY2QzIiwiZXhwIjoxNzEzOTgzNDAwLCJhdWQiOiJodHRwczovL2FwaS5jcnlwdGxleC5jb20ifQ.iy4d5PAk2sNemqi9g8FIXhGoSbvXCYVEBo8ukOLwfs0U2gEEDYXnF_iSNxxgmmlhp06BTXtmgrFu8qHoxZizaO7_sQsgt2ySgSkYy3n6tOq8DmqYnvzuQMsmfiVe76cX1Dl52qrIV5FtLGA6J1vo10C-QVVemluswAUguiXhqvV6Cn0zP6hlohTPdf5UthmI8-U0m2qLq9qHhCqJ0U1QOsyP1wMWPC1jqqZznoptmF34LIo8XxcGNCPc9zj7yKi6jYMq5aYaZbqRJKUrjZ_F39xazibCsO0tisUoENJzQh4z39FbmoLMpNDB_LqVpNVYZHKjTIVlZkxMMukgHovs7g");
		}
		$headers = array("Authorization: Bearer ".self::$access_token, "Content-Type: application/json");
		
		$request = curl_init($url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_ENCODING, "");
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($request);
		$info = curl_getinfo($request);
		if($info["http_code"] != 200)
		{
			throw new Exception($response);
		}
		curl_close($request);
		//var_dump($response);
		return json_decode($response);
	}

	private static function PostRequest($url, $body)
	{
		if (!self::$access_token)
		{
			throw new Exception("You must set the access token.");
		}
		$headers = array("Authorization: Bearer ".self::$access_token, "Content-Type: application/json");

		$request = curl_init($url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_ENCODING, "");
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($body));
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($request);
		$info = curl_getinfo($request);
		if($info["http_code"] != 200 && $info["http_code"] != 201)
		{
			throw new Exception($response);
		}
		curl_close($request);
		//var_dump($response);
		return json_decode($response);;
	}

}
