<?php
// https://api.github.com/users/<username>
// https://api.github.com/repos/<username>/<repository>
// https://api.github.com/user/<id>
// https://api.github.com/repositories/<id>

function exit_with_error($message)
{
    header("content-type: application/json; charset=utf-8");
    exit(json_encode(["error" => $message]));
}

$parent_dir = dirname($_SERVER["SCRIPT_NAME"]);
$remaining_path = substr($_SERVER["REQUEST_URI"], strlen($parent_dir));

if (!preg_match("/^\/(user|repo)\/\d{1,15}$/", $remaining_path))
    exit_with_error("provide a valid github id");

$remaining_path = str_replace("repo", "repositories", $remaining_path);

$url = "https://api.github.com$remaining_path";
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["user-agent: redirect to github profile"]);

$response = curl_exec($ch);

if (curl_errno($ch))
    exit_with_error("curl request failed");

curl_close($ch);

$json = json_decode($response);

if ($json === null)
    exit_with_error("failed to decode json");

if (!isset($json->html_url))
    exit_with_error("no user found for provided id");

header("location: " . $json->html_url, true, 301);
exit();
