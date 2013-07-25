<?php
///config
	//file name
	$user_file_name = "data/winner.txt";
	$icon_file_name = "images/icon.png";
	$avatar_file_name = "images/avatar.jpg";

	//secret twitter info
	$key = "";
	$secret = "";
	$token = "";
	$token_secret = "";
/// end config

	//setup twitter api
	require_once("codebird.php");
	\Codebird\Codebird::setConsumerKey($key, $secret); // static, see 'Using multiple Codebird instances'
	$cb = \Codebird\Codebird::getInstance();

	//get the saved username and his/her follower count
	$data = file_get_contents($user_file_name);
	$parts = explode(':', $data);
	$highest_username = $parts[0];
	$highest_followers = $parts[1];

	session_start();
	if(! isset($_SESSION['oauth_token']) && $_GET['login'] == 'true'){ //if
	    // get the request token
	    $reply = $cb->oauth_requestToken(array(
	        'oauth_callback' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
	    ));
	    // store the token
	    $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
	    $_SESSION['oauth_token'] = $reply->oauth_token;
	    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
	    $_SESSION['oauth_verify'] = true;
	    // redirect to auth website
	    $auth_url = $cb->oauth_authorize();
	    header('Location: ' . $auth_url);
	    die();
	}elseif(isset($_GET['oauth_verifier']) && isset($_SESSION['oauth_verify'])){
	    // verify the token
	    $cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	    unset($_SESSION['oauth_verify']);
	    // get the access token
	    $reply = $cb->oauth_accessToken(array(
	        'oauth_verifier' => $_GET['oauth_verifier']
	    ));
	    // store the token (which is different from the request token!)
	    $_SESSION['oauth_token'] = $reply->oauth_token;
    	$_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;

    	//get the logged in user's username
		$username = $reply->screen_name;

		//switch to the general API, not the logged in user
		$cb->setToken($token, $token_secret);

		//get the logged in user's profile data. only for the follower count
		$info = $cb->users_show(array('screen_name' => $username));

		//get the logged in user's follower count
		$followers = $info->followers_count;

		$result = ''; //whether or not the user won. only used to show modal, not define any logic
		$delta = 0; //how many followers the user needs if he or she isn't the highest user
		if($followers > $highest_followers){ //the logged in user has more followers than the previously highest account
			$result = 'win'; //winner winner chicken dinner)
			file_put_contents($user_file_name, $username . ":" . $followers); //store the winner's username and follower count to retrieve on next page load
			$avatar = $info->profile_image_url; //get the url of the winner's twitter avatar
			file_put_contents($icon_file_name, file_get_contents($avatar)); //save the avatar as an icon. even the favicon is this guy/gal's face
			//get the bigger sized avatar. more about that here: https://dev.twitter.com/docs/user-profile-images-and-banners
			$avatar_big = str_replace("_normal", '', $avatar);
			//save new avatar image for next page load
			file_put_contents($avatar_file_name, file_get_contents($avatar_big));
		}else{ //the logged in user has less followers than the previously highest account
			$result = 'fail'; //There's always next year, bud
			$delta = $highest_followers - $followers; //calculate how many followers the failure needs to get. step it up, man
		}
		header('Location: ' . $auth_url . "?result=$result&delta=$delta"); //send the user back to this same page but with their results
		die();
	}
// assign access token on each page load
$cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
?>
<!DOCTYPE html>
<html lang="en">
<html>
	<head>
	    <meta charset="utf-8" />
	   	<title>Twitter Narcissus</title>
	    <link rel="icon" type="image/png" href="images/icon.png">
	    <meta name="description" content="An exercise in narcissism. Showcases the twitter user with the most followers who has signed in." />
	    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	    <link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<!-- thanks for checking out the source!

			you're missing out on the PHP, get it here: github.com/milesokeefe

			shoot me an email while you're at it: miles.okeefe@gmail.com
			-->
		<div id="wrapper">
			<div id="feature">
				<h1><span id="narc" class="narcissus"><a class="no-color" href="https://twitter.com/<?php echo $highest_username?>">@<?php echo $highest_username?></a></span> is better than you</h1>
				<h2>with <span class="followers"><?php echo $highest_followers?></span> followers</h2>
			</div>
			<img id="background" src="images/avatar.jpg"/>
			<div id="signin" <?php if(isset($_SESSION['oauth_token'])){ echo 'hidden'; /* don't show the log in form if the user has already logged in*/}?>>
				This site showcases the twitter user with the most followers who has signed in.<br><br>
				If you have more than <span class="followers"><?php echo $highest_followers?></span> followers, sign in to take <span class="narcissus">@<?php echo $highest_username?></span>'s spot.
				<a href="/?login=true"><img id="signin-btn" src="images/signin.png"></a>
			</div>
			<div id="credit">
				made by <a href="https://twitter.com/_milesokeefe">@_milesokeefe</a>
			</div>
			<div id="modal-success" class="modal <?php if($_GET['result'] == 'win') echo 'show'?>">
				You have the most twitter followers of anyone who has logged in on this site.<br> Congrats.
				<a href="/"><div class="confirm">Rad :)</div></a>
			</div>
			<div id="modal-failure" class="modal <?php if($_GET['result'] == 'fail') echo 'show';?>">
				You need <span id="delta"><?php echo $_GET['delta']?></span> more followers
				<a href="/"><div class="confirm">Okay :(</div></a>
			</div>
		</div>
	</body>
</html>