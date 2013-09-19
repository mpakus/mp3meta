# MP3 meta class
it is used by giving a full path of an MP3 file containing an ID3v2 (ID3) tag to
info().

An array is then returned containing all the frames in the tag.

%% Example of usage:
"<?php
"require 'mp3.php';
"$mp3 = new MP3();
"
"echo '<pre>';
"print_r( $mp3->info('./1.mp3') );
"echo '</pre>';

%% Author: Ibragimov "MpaK" Renat http://mrak7.com & de77 http://de77.com