<?php
require "wx_config.php";	// require weixin and database config file

$wechatObj = new wechatCallbackAPI();

if(!empty($_GET['echostr']))
{
	$wechatObj->valid();
}
else
{
	$wechatObj->responseMsg();
}
exit;

class wechatCallbackAPI
{
	// identity authentication
	public function valid()
	{
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature())
		{
 			echo $echoStr;
        }
 		exit();
    }

	// check signature for weixin server
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$tmpArr = array(TOKEN, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		
		if($tmpStr == $signature)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	// reply message to user
    public function responseMsg()
	{
	    	$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
	
			if (!empty($postStr))
			{
				$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
				$fromUsername = $postObj->FromUserName;		// sender
				$toUsername = $postObj->ToUserName;		// receiver
				$MsgType = $postObj->MsgType;	// message type
				$MsgId = $postObj->MsgId;	// message id
				$time = time();		// response time

				switch($MsgType)
				{
				case 'text':
					$content = trim($postObj->Content);		// message content
					if(!empty($content) && $content == '1024')
					{
			            $imageTpl = "<xml>
									<ToUserName><![CDATA[%s]]></ToUserName>
									<FromUserName><![CDATA[%s]]></FromUserName>
									<CreateTime>%s</CreateTime>
									<MsgType><![CDATA[image]]></MsgType>
									<Image>
									<MediaId><![CDATA[%s]]></MediaId>
									</Image>
									</xml>";
						$mediaId = $this->select_randomize_image();	// get randomize image media id from databse
	                	$resultStr = sprintf($imageTpl, $fromUsername, $toUsername, $time, $mediaId);
						echo $resultStr;
					}
					break;
				case 'event':
					$Event = $postObj->Event;
					if($Event == 'subscribe')
					{
						$textTpl = "<xml>
									<ToUserName><![CDATA[%s]]></ToUserName>
									<FromUserName><![CDATA[%s]]></FromUserName>
									<CreateTime>%s</CreateTime>
									<MsgType><![CDATA[text]]></MsgType>
									<Content><![CDATA[%s]]></Content>
									</xml>";
						$content = "感谢您关注IDF实验室公共微信！";	// define subscription message
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $content);
						echo $resultStr;
					}
				default:
					echo "";
				}
	        }else {
	        	echo "";
	        	exit;
	        }
    }

	// select randomize image from media id database
	private function select_randomize_image()
	{
		$conn = mysql_connect(MySQL_Host, MySQL_User, MySQL_Password);
		$selected_db = mysql_select_db(MySQL_DB, $conn);

		// calcute count of media id
		$img_sql = "SELECT COUNT(*) FROM MediaID WHERE media_id IS NOT NULL";
		$row = mysql_fetch_array(mysql_query($img_sql));

		// select randomize media id from database
		$img_sql = "SELECT media_id FROM MediaID LIMIT " . (string)(rand(0, (int)($row[0]))) . ",1";
		$row = mysql_fetch_array(mysql_query($img_sql));

		mysql_close($conn);

		return $row['media_id'];
	}
}
?>
