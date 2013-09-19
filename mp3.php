<?php

/**
*
* MP3 class to read meta information from file.
*
* @author Ibragimov "MpaK" Renat <info@mrak7.com> <http://mrak7.com> & de77 <http://de77.com>
* @license MIT <http://en.wikipedia.org/wiki/MIT_License>
* 
* p.s. The 1st author was de77 from <http://de77.com/php/php-class-how-to-read-id3v2-tags-from-mp3-files> but 
* I took his, modified it, fixed some errors and merged with ID3 first version of mp3 metas.
*/
class MP3{

  // ID3 v2.3-4 frame tags
  protected $tags = array(
    'TALB' => 'album',
    'TCON' => 'genre',
    'TENC' => 'encoder',
    'TIT2' => 'title',
    'TPE1' => 'artist',
    'TPE2' => 'ensemble',
    'TYER' => 'year',
    'TCOM' => 'composer',
    'TCOP' => 'copyright',
    'TRCK' => 'track',
    'WXXX' => 'url',
    'COMM' => 'comment',
    'TDRC' => 'year',
    'APIC' => 'cover',
    'TXXX' => 'user_text',
    'PRIV' => 'private',
  );  
  protected $genres   = array('Blues', 'Classic Rock', 'Country', 'Dance', 'Disco', 'Funk', 'Grunge', 'Hip-Hop', 'Jazz', 'Metal', 'New Age', 'Oldies', 'Other', 'Pop', 'R&B', 'Rap', 'Reggae', 'Rock', 'Techno', 'Industrial', 'Alternative', 'Ska', 'Death Metal', 'Pranks', 'Soundtrack', 'Euro-Techno', 'Ambient', 'Trip-Hop', 'Vocal', 'Jazz+Funk', 'Fusion', 'Trance', 'Classical', 'Instrumental', 'Acid', 'House', 'Game', 'Sound Clip', 'Gospel', 'Noise', 'AlternRock', 'Bass', 'Soul', 'Punk', 'Space', 'Meditative', 'Instrumental Pop', 'Instrumental Rock', 'Ethnic', 'Gothic', 'Darkwave', 'Techno-Industrial', 'Electronic', 'Pop-Folk', 'Eurodance', 'Dream', 'Southern Rock', 'Comedy', 'Cult', 'Gangsta', 'Top 40', 'Christian Rap', 'Pop/Funk', 'Jungle', 'Native American', 'Cabaret', 'New Wave', 'Psychadelic', 'Rave', 'Showtunes', 'Trailer', 'Lo-Fi', 'Tribal', 'Acid Punk', 'Acid Jazz', 'Polka', 'Retro', 'Musical', 'Rock & Roll', 'Hard Rock', 'Folk', 'Folk-Rock', 'National Folk', 'Swing', 'Fast Fusion', 'Bebob', 'Latin', 'Revival', 'Celtic', 'Bluegrass', 'Avantgarde', 'Gothic Rock', 'Progressive Rock', 'Psychedelic Rock', 'Symphonic Rock', 'Slow Rock', 'Big Band', 'Chorus', 'Easy Listening', 'Acoustic', 'Humour', 'Speech', 'Chanson', 'Opera', 'Chamber Music', 'Sonata', 'Symphony', 'Booty Bass', 'Primus', 'Porn Groove', 'Satire', 'Slow Jam', 'Club', 'Tango', 'Samba', 'Folklore', 'Ballad', 'Power Ballad', 'Rhythmic Soul', 'Freestyle', 'Duet', 'Punk Rock', 'Drum Solo', 'Acapella', 'Euro-House', 'Dance Hall', 'Goa', 'Drum & Bass', 'Club-House', 'Hardcore', 'Terror', 'Indie', 'BritPop', 'Negerpunk', 'Polsk Punk', 'Beat', 'Christian Gangsta', 'Heavy Metal',  'Black Metal', 'Crossover', 'Contemporary C', 'Christian Rock',  'Merengue', 'Salsa',  'Thrash Metal',  'Anime',  'JPop', 'SynthPop');
  // place to keep ID3 v1 info
  protected $id3_info = array();

  /**
   * Get MP3's ID3 info 
   * 
   * @param $file_name string mp3 file name
   * @return mixed
   */
  protected function read_id3( $file_name ){
      $f   = fopen( $file_name, 'rb' );
      fseek( $f, -128, SEEK_END );
      $tag = fread( $f, 3 );
      if($tag == 'TAG')
          $this->id3_info = array(
            'title'   => iconv('WINDOWS-1251', 'UTF-8', fread($f, 30)),
            'artist'  => iconv('WINDOWS-1251', 'UTF-8', fread($f, 30)),
            'album'   => iconv('WINDOWS-1251', 'UTF-8', fread($f, 30)),
            'year'    => fread($f, 4),
            'comment' => iconv('WINDOWS-1251', 'UTF-8', fread($f, 30)),
            'genre'   => $this->genres[ ord( fread($f, 2) ) ],
          );
      else return FALSE;  

      fclose( $f );
      return $this->id3_info;
  }

     
  /**
   * Decode value 
   *
   * @param $value string  value
   * @param $tag   string  tag name
   * @param $flag  integer encoding type information
   * @return mixed
   */
  protected function decode_value($value, $tag, $flag){
    // if ($type == 'COMM') $tag = substr($tag, 0, 3) . substr($tag, 10);
    if ($tag == 'APIC' ) return $value;
    switch (ord($flag)){
      case 0: //ISO-8859-1
        return iconv('UTF-8', 'ISO-8859-1', $value);
      case 1: //UTF-16 BOM
        return iconv('UTF-16LE', 'UTF-8', $value);
      case 2: //UTF-16BE
        return iconv('UTF-16BE', 'UTF-8', $value);
      case 3: //UTF-8
        return $value;
    }
    return false;
  }
  
  /**
   * Read mp3 file and get music information
   *
   * @param  $file string file name
   * @return array
   */
  protected function read_id3v2( $file_name ){
    if( !file_exists($file_name) && !is_readable($file_name) ) return FALSE;

    $f      = fopen($file_name, 'rb');
    $header = fread($f, 10);
    $header = @unpack("a3signature/c1version_major/c1version_minor/c1flags/Nsize", $header);

    if (!$header['signature'] == 'ID3' or $header['version_major'] != 4 ){
      $this->error = 'This file does not contain ID3 v2.4 tag';
      fclose($f);
      return FALSE;   
    }

    $this->id3v2_info = array();
    // for ($i=0; $i<22; $i++){
    while( !feof($f) ){
      $tag  = rtrim( fread($f, 4) );      
      $size = fread($f, 4);
      $size = @unpack('N', $size);
      $size = $size[1]-1;
      
      if( !isset($this->tags[$tag]) ) break;
      
      $flag  = fread($f, 2);
      $flag  = fread($f, 1);
      $value = fread($f, $size);

      if( isset($this->tags[$tag]) ){
        $value = $this->decode_value($value, $tag, $flag);
        if( empty($this->tags[$tag]) || empty($tag) ) continue;
        $this->id3v2_info[$this->tags[$tag]] = $value;        
      }

    }    
    fclose($f);
    return $this->id3v2_info;
  } 

  /**
   * Returns mp3 tags info
   *
   * @param $file_name string mp3 file name
   * @return array
   */
  public function info( $file_name ){
    $info     = $this->read_id3v2( $file_name );
    $old_info = $this->read_id3( $file_name );

    $fields = array('title', 'artist', 'album', 'year', 'genre');
    foreach( $fields as $field ) if( empty($info[$field]) ) $info[$field] = $old_info[$field];
    
    return $info;
  }
}