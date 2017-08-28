<?php

/* Ad hoc functions to make the examples marginally prettier.*/
function isWebRequest()
{
    return isset($_SERVER['HTTP_USER_AGENT']);
}

function pageHeader($title)
{
    $ret = "<!doctype html>
    <html>
    <head>
    <title>" . $title . "</title>
    <link href='styles/style.css' rel='stylesheet' type='text/css' />
    </head>
    <body>\n";
    if ($_SERVER['PHP_SELF'] != "/index.php") {
        $ret .= "<p><a href='index.php'>Back</a></p>";
    }
    $ret .= "<header><h1>" . $title . "</h1></header>";
    
    // Start the session (for storing access tokens and things)
    if (!headers_sent()) {
        session_start();
    }
    
    return $ret;
}


function pageFooter($file = null)
{
    $ret = "";
    if ($file) {
        $ret .= "<h3>Code:</h3>";
        $ret .= "<pre class='code'>";
        $ret .= htmlspecialchars(file_get_contents($file));
        $ret .= "</pre>";
    }
    $ret .= "</html>";
    
    return $ret;
}

function missingApiKeyWarning()
{
    $ret = "
    <h3 class='warn'>
    Warning: You need to set a Simple API Access key from the
    <a href='http://developers.google.com/console'>Google API console</a>
    </h3>";
    
    return $ret;
}

function missingClientSecretsWarning()
{
    $ret = "
    <h3 class='warn'>
    Warning: You need to set Client ID, Client Secret and Redirect URI from the
    <a href='http://developers.google.com/console'>Google API console</a>
    </h3>";
    
    return $ret;
}

function missingServiceAccountDetailsWarning()
{
    $ret = "
    <h3 class='warn'>
    Warning: You need download your Service Account Credentials JSON from the
    <a href='http://developers.google.com/console'>Google API console</a>.
    </h3>
    <p>
    Once downloaded, move them into the root directory of this repository and
    rename them 'service-account-credentials.json'.
    </p>
    <p>
    In your application, you should set the GOOGLE_APPLICATION_CREDENTIALS environment variable
    as the path to this file, but in the context of this example we will do this for you.
        </p>";
    
    return $ret;
}

function missingOAuth2CredentialsWarning()
{
    $ret = "
    <h3 class='warn'>
    Warning: You need to set the location of your OAuth2 Client Credentials from the
    <a href='http://developers.google.com/console'>Google API console</a>.
    </h3>
    <p>
    Once downloaded, move them into the root directory of this repository and
    rename them 'oauth-credentials.json'.
    </p>";
    
    return $ret;
}

function checkServiceAccountCredentialsFile()
{
    // service account creds
    $application_creds = __DIR__ . '/../../../../credentials/service-account-credentials.json';
    
    return file_exists($application_creds) ? $application_creds : false;
}

function getOAuthCredentialsFile()
{
    // oauth2 creds
    $oauth_creds = __DIR__ . '/../../../../credentials/oauth-credentials.json';
    
    if (file_exists($oauth_creds)) {
        return $oauth_creds;
    }
    
    return false;
}

function setClientCredentialsFile($apiKey)
{
    $file = __DIR__ . '/../../tests/.apiKey';
    file_put_contents($file, $apiKey);
}


function getApiKey()
{
    return getenv("APIKEY");
}

function setApiKey($apiKey)
{
    setenv("APIKEY", $apiKey);
}

/**
* Dumps the contents of the environment variable GOOGLE_CREDENTIALS_BASE64 to
* a file.
*
* To setup Travis to run on your fork, read TRAVIS.md.
*/
putenv('GOOGLE_APPLICATION_CREDENTIALS='. __DIR__.'/../../../../credentials/service-account-credentials.json');
$cred = getenv('GOOGLE_CREDENTIALS_BASE64');
$fpath = getenv('GOOGLE_APPLICATION_CREDENTIALS');
if ($cred !== false && $fpath !== false) {
    file_put_contents($fpath, base64_decode($cred));
}
putenv('GOOGLE_CLIENT_CREDENTIALS='. __DIR__.'/../../../../credentials/oauth-credentials.json');
$cred = getenv('GOOGLE_CLIENT_CREDENTIALS_BASE64');
$fpath = getenv('GOOGLE_CLIENT_CREDENTIALS');
if ($cred !== false && $fpath !== false) {
    file_put_contents($fpath, base64_decode($cred));
}

function url(){
    return sprintf(
    '%s://%s%s',
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http',
    ($_SERVER['SERVER_NAME']),
    ($_SERVER['BASE_PATH'])
    );
}