<?php
$arUrlRewrite = [
    [
		"CONDITION" => "#^/brand/([0-9a-zA-Z_-]+)/((index\\.php)?(\\?.*)?)?\$#",
		"RULE" => "ELEMENT_CODE=\$1",
		"ID" => "",
		"PATH" => "/brand/detail.php",
	],
    [
        'CONDITION' => '#^/customer/shares/#',
        'RULE' => '',
        'ID' => 'bitrix:news',
        'PATH' => '/customer/shares/index.php',
	],
    [
        'CONDITION' => '#^/services/articles/#',
        'RULE'      => '',
        'ID'        => 'bitrix:news',
        'PATH'      => '/services/articles/index.php',
    ],
    [
        'CONDITION' => '#^/company/news/#',
        'RULE'      => '',
        'ID'        => 'bitrix:news',
        'PATH'      => '/company/news/index.php',
	],
	[
        'CONDITION' => '#^/personal/#',
        'RULE'      => '',
        'ID'        => 'fourpaws:personal',
        'PATH'      => '/personal/index.php',
    ],
    [
        'CONDITION' => '#^/sale/order/#',
        'RULE'      => '',
        'ID'        => 'fourpaws:order',
        'PATH'      => '/sale/order/index.php',
    ],
    [
		"CONDITION" => "#^/brands/#",
		"RULE" => "",
		"ID" => "fourpaws:brands",
		"PATH" => "/brands/index.php",
	],
	[
        'CONDITION' => '##',
        'RULE'      => '',
        'ID'        => '',
        'PATH'      => '/symfony_router.php',
    ],
];
