<?php

$link = 'https://www.amazon.com/dp/B0B3BVWJ6Y?_encoding=UTF8&ref=ods_ucc_kindle_B0B3BVWJ6Y_rc_nd_ucc&th=1';
preg_match('/(?:[\/dp\/]|$)([A-Z0-9]{10})/', $link, $matches);

var_dump($matches);
