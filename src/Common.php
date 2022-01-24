<?php

/**
 * This file is used for all common functions
 */

use Pulse\Debug\Debugger;
use Pulse\Logs\Logger;
use Pulse\Router\RouteOptions;
use Pulse\View\View;


function view(string $name, array $data = [])
{
    $view = new View();
    $data = array_merge(['systemCore' => ENV::class], $data);
    return $view->Render($name, $data);
}

function view_cell(string $name, array $data = [])
{
    $view = new View();
    return $view->RenderCell($name, $data);
}


function secure_password(string $password)
{
    return password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
}

function encrypt($string)
{
    $iv = base64_decode(ENV::ENC_IV64);
    $ciphertext_raw = openssl_encrypt($string, ENV::ENC_CIPHER, ENV::ENC_KEY, OPENSSL_RAW_DATA, $iv);

    return base64_encode($ciphertext_raw);
}

function decrypt($string)
{
    $iv = base64_decode(ENV::ENC_IV64);

    $ciphertext_raw = base64_decode($string);
    $original_plaintext = openssl_decrypt($ciphertext_raw, ENV::ENC_CIPHER, ENV::ENC_KEY, OPENSSL_RAW_DATA, $iv);

    return $original_plaintext;
}

function redirect(string $location)
{
    if (RouteOptions::API()) {
        echo json_encode([
            'status' => false,
            'redirect' => $location
        ]);
    } else {
        header("location: $location");
    }
    exit();
}

function redirect404()
{
    header("HTTP/1.0 404 Not Found");
    $looger = new Logger("access");
    $looger->insert("bad route", []);

    if (RouteOptions::API()) {
        return [
            'status' => false,
            'msg' => 'page not found'
        ];
    } else return view('404page');
}

function redirect400($data)
{
    header("HTTP/1.0 400 Bad Request");
    $looger = new Logger("access");
    $looger->insert("bad request", $data);

    if (RouteOptions::API()) {
        return [
            'status' => false,
            'msg' => 'bad request'
        ];
    } else return view('404page');
}

function dateLeft(int $date)
{
    $result = [];
    $template = [
        [1, 'second'],
        [60, 'minute'],
        [60, 'hour'],
        [24, 'day'],
    ];
    $remaining = $date - time();
    $secondsScale = 24 * 60 * 60; //scale to day

    while (count($result) < 2 && count($template) > 0) {
        $unit = floor($remaining / $secondsScale); // get the value
        $remaining -= $unit * $secondsScale;

        $unit_scale = array_pop($template);
        $secondsScale /= $unit_scale[0];

        //check if greater than zero or 2
        if ($unit == 0) continue;
        $result[] = [$unit, $unit_scale[1] . (($unit == 1) ? '' : 's')];
        if ($unit > 2) break;
    }

    return implode(" ", array_map(
        function ($unit) {
            return implode(" ", $unit);
        },
        $result
    )) . ' left';
}

function debugger()
{
    Debugger::debug();
}
