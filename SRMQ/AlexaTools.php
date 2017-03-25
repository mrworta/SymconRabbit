<?

 function checkWithAmazon($call) {
        try {
			    $token = $call->user->accessToken;
                $amazon = curl_init('https://api.amazon.com/user/profile');
                curl_setopt($amazon, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $token));
                curl_setopt($amazon, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($amazon); curl_close($amazon);
			    $profile = json_decode($result);								
				
				return $profile->user_id == $call->AmazonAccountID;

        } catch (Exception $e) {
                IPS_LogMessage("Security", "Amazon Account validation failed.");
        }
  }
  
  function completeAmazonProfile($call) {
        try {
			    $token = $call->user->accessToken;
                $amazon = curl_init('https://api.amazon.com/user/profile');
                curl_setopt($amazon, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $token));
                curl_setopt($amazon, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($amazon); curl_close($amazon);
			    $profile = json_decode($result);
				$call->AmazonAccountID  = $profile->user_id;
				$call->AmazonAccountEMAIL = $profile->email;
				
				return $call;

        } catch (Exception $e) {
                IPS_LogMessage("Security", "Amazon account validation failed.");
				return null;
        }
  }
  
  

?>
