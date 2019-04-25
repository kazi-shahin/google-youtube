<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/vendor/autoload.php'; // change path as needed


if(isset($_SERVER['HTTPS'])){
    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
}
else{
    $protocol = 'http';
}
$baseurl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$g_client_id = '';
$g_secret = '';
$g_access_token = '';
// $blogger_id = '';
// $g_refresh_token = '';

$google = new Google_Client();

$google->setApplicationName("New App");
$google->setClientId($g_client_id);
$google->setClientSecret($g_secret);
$google->setScopes(array('https://www.googleapis.com/auth/plus.stream.write','https://www.googleapis.com/auth/blogger','https://www.googleapis.com/auth/youtube','https://www.googleapis.com/auth/drive.readonly', 'https://www.googleapis.com/auth/youtube.upload'));
$google->setRedirectUri($baseurl.'?from=google');
$google->setAccessType('offline');
$google->setApprovalPrompt('force');

if ($g_access_token != '') {
	$google->setAccessToken($g_access_token);

	if($google->isAccessTokenExpired()) {

		if($g_refresh_token!=''){
			//$google->refreshToken($g_refresh_token);
		}

		$new_token=$google->getAccessToken();

	}
}
if($_GET['auth-status'] == 'success' &&  $_GET['auth-from'] == 'google'){
	echo 'Google successfully!';
}elseif($_GET['from']=='google'){

	$google->authenticate($_GET['code']);

		$access_token = $google->getAccessToken();

		if (isset($access_token)) {
			
		 	$refresh_token = json_decode($access_token)->refresh_token;

		 	$bloggerService = new Google_Service_Blogger($google);

		 	
		 	$info = $bloggerService->users;
		 	$self = $info->get('self');
		 	$id = $self->id;

		 	$blogs = $bloggerService->blogs;
		 	$blogs = $blogs->listByUser($id);
		 	$blog_id = $blogs[0]->id; 

		 	$arr = array('google_access_token' => $access_token, 'google_refresh_token' => $refresh_token, 'google' => '1', 'youtube' => '1');

		 	if ($blog_id) {
		 		$arr['google_blogger_id'] = $blog_id;
		 		$arr['blogger'] = '1';
		 	}

			header('Location: '.$baseurl.'?auth-status=success&auth-from=google');
		}
}else{
	$g_loginUrl = $google->createAuthUrl();
	header('Location: '.$g_loginUrl);
}
