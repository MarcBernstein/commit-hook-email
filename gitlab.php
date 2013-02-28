<?php

require 'vendor/autoload.php';

$gitlabToken = '';
$from = '';
$to = '';
$secret = '';

$config = __DIR__.'/config.php';
if (stream_resolve_include_path($config)) {
    include $config;
}

// check secret
if ($secret !== @$_GET['secret']) {
    exit;
}

// read hook request
$request = file_get_contents('php://input');
$json = json_decode($request);

if (empty($json->total_commits_count)) {
    exit;
}

$branch = substr($json->ref, strrpos($json->ref, "/") + 1);

// prepare subject
$subject = '[Git] ' . $json->repository->name . ' branch ' . $branch . ' updated';

$html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . "\r\n";

$html .= '<pre>' . "\r\n";
$html .= 'This is an automated email from GitLab. It was generated because a ref' . "\r\n";
$html .= 'change was pushed to the repository containing the project [' . $json->repository->name . '].' . "\r\n";
$html .= "\r\n";
$html .= 'The branch [' . $branch . '] has been updated with ' . $json->total_commits_count .' commit(s).' . "\r\n";
$html .= "\r\n";

$html .= '- Log -----------------------------------------------------------------' . "\r\n";

foreach ($json->commits as $commit) {
    $url = str_replace('commits', 'commit', $commit->url);

    $html .= 'Commit: <a href="' . $url . '">' . $commit->id . '</a>' . "\r\n";
    $html .= 'Author: ' . $commit->author->name . ' &lt;<a href="mailto:' . $commit->author->email . '">' . $commit->author->email .'</a>&gt;' . "\r\n";
    $html .= 'Date: ' . date("m/d/Y h:i:s A T", strtotime($commit->timestamp)) . "\r\n";
    $html .= "\r\n";
    $html .= '&nbsp;&nbsp;' . wordwrap($commit->message, 70, "<br>", true) . "\r\n";
    $html .= "\r\n" . '-----------------------------------------------------------------------' . "\r\n";
    $html .= "\r\n";
}


$html .= '<br><pre>' . var_dump($json) .'</pre>' . "\r\n";
$html .= '</pre>' . "\r\n";
$html .= '</body></html>'. "\r\n";

// send email
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
$headers .= 'From: ' . $from . "\r\n";
$headers .= 'Reply-To: ' . $from . "\r\n";

mail($to, $subject, $html, $headers);

?>

