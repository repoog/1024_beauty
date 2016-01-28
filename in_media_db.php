<?php
require "wx_config.php";	// require weixin and database config file

$wechatObj = new wechatCallbackAPI();
if($_SERVER["REMOTE_ADDR"] == "127.0.0.1")
{
	$wechatObj->upload_temp_image();
}
exit;

class wechatCallbackAPI
{
	// identity authentication
	private function get_access_token()
	{
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . AppID . "&secret=" . AppSecret; 
		$data = json_decode(file_get_contents($url), true);  
		if($data['access_token'])
		{
			return $data['access_token'];  
		}
		else
		{  
			exit();  
		}
	}

	// upload temporary image
	public function upload_temp_image()
	{
		$url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $this->get_access_token() . "&type=image";
		$imgAry = scandir("images");	// scan downloaded images path
		$countImg = count($imgAry);
		$conn = mysql_connect(MySQL_Host, MySQL_User, MySQL_Password);
		if (!$conn)
		{
			die("Could not connect to MySQL.");
		}
		$selected_db = mysql_select_db(MySQL_DB, $conn);
		if (!$selected_db)
		{
			die("Could not connect to Database.");
		}
		mysql_query("DELETE FROM MediaID");
		$img_sql = "INSERT INTO MediaID(id_name, media_id) VALUES";

		for ($i=2; $i<$countImg; $i++)
		{
			// get media id of uploaded image
			$imgName = $imgAry[$i];
			$imgPath = "images/" . $imgName;
			$json = array("media" => new \CURLFile(realpath($imgPath)));
			$ret = $this->curl_post($url, $json);
			$raw = json_decode($ret);

			// store media id to database
			$img_sql .= "('$imgName','$raw->media_id'),";
		}
		$img_sql = substr($img_sql, 0, strlen($img_sql) - 1);
		mysql_query($img_sql);
		mysql_close($conn);
	}

	private function curl_post($url, $data = null)  
	{
		$curl = curl_init(); 
		curl_setopt($curl, CURLOPT_URL, $url); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data))
		{
			curl_setopt($curl, CURLOPT_POST, 1); 
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data); 
		} 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		$output = curl_exec($curl); 
		curl_close($curl); 
		return $output; 
	}
}
?>
