<?php
/**
 * 返回数组
 */
return [
    'wechat' => [
        'debug' => true,
        'app_id' => 'wx36f7184a24f8e31a',
        'secret' => '811ad15d5051bfa91301bead426f430a',
        'token' => 'weixin',
        'log' => [
            'level' => 'debug',
            'file' => '/tmp/easywechat.log',
        ],
		'oauth' => [
			'scopes'   => ['snsapi_userinfo'],
			'callback' => 'http://gq.herozw.com#/index',
		]
    ]
];