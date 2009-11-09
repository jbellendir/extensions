<?php
if ( !defined( 'MEDIAWIKI' ) ) die( 'Not an entry point.' );
ini_set( 'memory_limit', '64M' );

# Read the DB access info from wikid.conf
foreach( file( '/var/www/tools/wikid.conf' ) as $line ) {
	if ( preg_match( '|^\s*\$(wgDB.+?)\s*=\s*[\'"](.+?)["\']|m', $line, $m ) )
		$$m[1] = $m[2];
}

# Constants
define( 'WIKIA_VERSION', '1.0.1, 2009-08-26');
define( 'NS_EXTENSION',      1000 );
define( 'NS_CONFIG',         1004 );
define( 'NS_QUERY',          1006 );
define( 'NS_WORKFLOW',       1008 );
define( 'NS_PORTAL',         1010 );
define( 'NS_CREATE',         1014 );
define( 'NS_SYSOP',          1016 );
define( 'NS_MEMBER',         1018 );
define( 'NS_RECORD',         1020 );
define( 'NS_REPORT',         1022 );

# Namespaces
$wgExtraNamespaces[NS_EXTENSION]   = 'Extension';
$wgExtraNamespaces[NS_EXTENSION+1] = 'Extension_talk';
$wgExtraNamespaces[NS_CONFIG]      = 'Config';
$wgExtraNamespaces[NS_CONFIG+1]    = 'Config_talk';
$wgExtraNamespaces[NS_QUERY]       = 'Query';
$wgExtraNamespaces[NS_QUERY+1]     = 'Query_talk';
$wgExtraNamespaces[NS_WORKFLOW]    = 'Workflow';
$wgExtraNamespaces[NS_WORKFLOW+1]  = 'Workflow_talk';
$wgExtraNamespaces[NS_PORTAL]      = 'Portal';
$wgExtraNamespaces[NS_PORTAL+1]    = 'Portal_talk';
$wgExtraNamespaces[NS_CREATE]      = 'Create';
$wgExtraNamespaces[NS_CREATE+1]    = 'Create_talk';
$wgExtraNamespaces[NS_SYSOP]       = 'Admin';
$wgExtraNamespaces[NS_SYSOP+1]     = 'Admin_talk';
$wgExtraNamespaces[NS_MEMBER]      = 'Member';
$wgExtraNamespaces[NS_MEMBER+1]    = 'Member_talk';
$wgExtraNamespaces[NS_RECORD]      = 'Record';
$wgExtraNamespaces[NS_RECORD+1]    = 'Record_talk';

# Default globals defined before specific LocalSettings inclusion
$wgArticlePath            = '/$1';
$wgScriptPath             = '/wiki';
$wgUseDatabaseMessages    = true;
$wgAllowDisplayTitle      = true;
$wgAllowPageInfo          = true;
$wgDBmysql5               = false;
$wgTruncatedCommentLength = 50;
$wgVerifyMimeType         = false;
$wgUseSiteJs              = true;
$wgUseTeX                 = true;
$wgSVGConverter           = 'rsvg';
$wgRewriteRule            = 'Friendly'; # rewrite.pl URL transformation function name
$wgSiteDown               = false;
$wgEmergencyContact       = false;

# File upload settings
$wgEnableUploads          = true;
$wgAllowCopyUploads       = true;
$wgUploadPath             = '/files';
$wgFileExtensions         = array(
	'jpeg', 'jpg', 'png', 'gif', 'svg', 'swf',
	'pdf', 'xls', 'ods', 'odt', 'doc', 'docx', 'mm',
	'zip', '7z', 'gz', 'tgz', 't7z', 
	'avi', 'divx', 'mpeg', 'mpg', 'ogv', 'ogm', 'mp3', 'mp4', 'flv'
);
$wgGroupPermissions['sysop']['upload_by_url'] = true;

# Allow fallback to OD images
$wgUseSharedUploads       = true;
$wgSharedUploadDirectory  = '/var/www/wikis/od/files';
$wgSharedUploadPath       = 'http://www.organicdesign.co.nz/files';

# Global wikia configuration
$settings                 = '/var/www/wikis';
$domains                  = '/var/www/domains';
$extensions               = dirname( __FILE__ );

# Check if the request is from command line for running maintenance scripts
if ( $wgCommandLineMode ) {
	$admin = file_get_contents( "$IP/AdminSettings.php" );
	if ( preg_match( '/^\\s*\\$domain\\s*=\\s*[\'"](.+?)["\'];/m', $admin, $m ) ) $domain = $m[1];
	$root = "$domains/$domain";
}
else {
	$domain = ereg_replace( '^(www\\.|wiki\\.)', '', $_SERVER['SERVER_NAME'] );
	$root   = $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . "/$domain";
	$domain = $_SERVER['SERVER_NAME'];
}

# Add google analytics code
$wgExtensionFunctions[] = 'wfGoogleAnalytics';
$wgGoogleTrackingCodes = array();
function wfGoogleAnalytics() {
	global $wgOut, $wgGoogleTrackingCodes;
	foreach ( $wgGoogleTrackingCodes as $code ) $wgOut->addScript( '<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
		</script><script type="text/javascript">
		var pageTracker = _gat._getTracker("' . $code . '");
		pageTracker._trackPageview();</script>' );
}

# Include the LocalSettings file for the domain
$wgUploadDirectory = "$root$wgUploadPath";
include( "$root/LocalSettings.php" );

# Display a maintenance page if $wgSiteDown set (unless request is from command line)
if ( $wgSiteDown && !$wgCommandLineMode ) {
	while( @ob_end_clean() );
	$msg = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head><title>Down for maintenance</title></head>
	<body bgcolor="white"><table width="100%"><tr><td align="center">
	<img border="0" src="http://www.organicdesign.co.nz/files/9/9c/Cone.png" style="padding-top:100px"/><br>
	<div style="font-family:sans;font-weight:bold;color:#89a;font-size:16pt;padding-top:25px">
	' . $wgSitename . ' is temporarily down for maintenance,<br><br><small>please try again soon.</small>
	</div></td></tr></table></body></html>';
	if ( in_array('Content-Encoding: gzip', headers_list() ) ) $msg = gzencode( $msg );
	echo $msg;
	die;
}

# Post LocalSettings globals
$wgUploadDirectory  = $_SERVER['DOCUMENT_ROOT'] . "$wgUploadPath"; # allows wiki's settings to change images location
$wgLocalInterwiki   = $wgSitename;
if ( $wgEmergencyContact === false ) $wgEmergencyContact = $wgPasswordSender = 'admin@' . str_replace( 'www.', '', $domain );
$wgNoReplyAddress = "";

# Include a special page for listing current wikia and their domains
if ( ereg( 'organicdesign.co.nz', $domain ) ) include( 'extensions/SpecialWikiaInfo.php' );

# Map naked URL to different articles depending on domain
function domainRedirect( $list ) {
	if ( basename( $_SERVER['SCRIPT_FILENAME'] ) !== 'index.php' ) return;
	$d = $_SERVER['SERVER_NAME'];
	$t = $_REQUEST['title'];
	if ( empty( $t ) ) $t = ereg_replace( '^/', '', isset( $_SERVER['PATH_INFO'] ) ? $_SERVER['PATH_INFO'] : '' );
	if ( empty( $t ) || $t == 'Main_Page' )
		foreach ( $list as $regexp => $title )
			if ( ereg( $regexp, $d ) ) header( "Location: $wgServer/$title" ) && die;
}

# Load the messages
$wgExtensionFunctions[] = 'odLoadMessages';
function odLoadMessages() {
	#wfLoadExtensionMessages( '/var/www/extensions/wikia' );
}

# Block problem users, bots and requests
$wgExtensionFunctions[] = 'odLogActivity';
function odLogActivity() {
	global $wgUser, $wgShortName, $wgRequest;
	$user = $wgUser->getUserPage()->getText();
	$sesh = ereg( '_session=([0-9a-z]+)', isset( $_SERVER['HTTP_COOKIE'] ) ? $_SERVER['HTTP_COOKIE'] : '', $m ) ? $m[1] : '';
	if ( $sesh ) $user .= ":$sesh";
	if ( !$wgUser->isAnon() ) $user .= ':' . $_SERVER['REMOTE_ADDR'];
	$url = $_SERVER['REQUEST_URI'];
	if ( $wgRequest->wasPosted() ) {
		$post = array();
		foreach ( $wgRequest->getValues() as $k => $v ) {
			if ( strlen( $v ) > 10 ) $v = substr( $v, 0, 9 ) . '...';
			$v = urlencode( $v );
			$post[] = "$k=$v";
		}
		$post = join( ',', $post );
		$url = '/' . $wgRequest->getText( 'title' ) . " (POST:$post)";
	}
	$block = '';

	# IP/User based blocks
	$list = array(        # nslookup on ipaddresses;
		'148.243.232.98', # Bot attempting shell hacks
	);
	
	foreach( $list as $i ) if ( $block == '' and ereg( $i, $user ) ) $block .= '(ip-block)';

	# Session-based blocks
	if (
		$sesh == '2297d58013571cb3a6adddb9c5e3c36f'
		|| $sesh == '0bbf7493262a75e3258c8da11a303296'
	) $block .= '(sesh-block)';

	# Silently block requests
	if ( eregi( '(favicon|robots.txt|action=rawxxx)', $url ) ) return;

	# Write log entry
	$handle = fopen( "/var/www/activity.log", "a" );
	fwrite( $handle, date( 'Y-m-d H:i:s' ) . " ($wgShortName)($user)$block: $url\n" );
	#if ($block) { sleep(1); die; }
}
