<?php
// config/captcha.php

#print_r(__DIR__);
return [
    'enabled' => true,
    'length' => 4,
    'type' => 'alnum', // alnum | chinese | math
    'width' => 80,
    'height' => 35,
    'font_size' => 16,
    'font_path' => __DIR__ . '/fonts/arial.ttf', // 请确保字体文件存在
    'chinese_font_path' => __DIR__ . '/fonts/SmileySans-Oblique.ttf', // 中文字体
    'noise' => true, // 干扰点
    'lines' => true, // 干扰线
    'distortion' => true, // 文字扭曲
    'session_key' => 'captcha_code',
	'dpi_scale' => 1.5, //DPI 缩放
	'secret_key'  =>'your_secret_key',
];