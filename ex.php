<?php

require 'mp3.php';

$mp3 = new MP3();

echo '<pre>';
print_r( $mp3->info('./1.mp3') );
echo '</pre>';
