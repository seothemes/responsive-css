<?php
header( "Content-type: text/css; charset: UTF-8" );

$size = $_REQUEST['size'];

echo get_option( 'responsive-css-' . $size );
