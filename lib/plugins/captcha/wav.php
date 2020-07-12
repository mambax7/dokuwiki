<?php
/**
 * CAPTCHA antispam plugin - sound generator
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <gohr@cosmocode.de>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
define('NOSESSION',true);
define('DOKU_DISABLE_GZIP_OUTPUT', 1);
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/auth.php');
require_once(dirname(__FILE__).'/action.php');

$ID = $_REQUEST['id'];
$plugin = new action_plugin_captcha();
$rand = PMA_blowfish_decrypt($_REQUEST['secret'],auth_cookiesalt());
$code = strtolower($plugin->_generateCAPTCHA($plugin->_fixedIdent(),$rand));

// prepare an array of wavfiles
$lc = dirname(__FILE__).'/lang/'.$conf['lang'].'/audio/';
$en = dirname(__FILE__).'/lang/en/audio/';
$wavs = array();
for($i=0;$i<5;$i++){
    $file = $lc.$code{$i}.'.wav';
    if(!@file_exists($file)) $file = $en.$code{$i}.'.wav';
    $wavs[] = $file;
}

header('Content-type: audio/x-wav');
header('Content-Disposition: attachment;filename=captcha.wav');

echo joinwavs($wavs);


/**
 * Join multiple wav files
 *
 * All wave files need to have the same format and need to be uncompressed.
 * The headers of the last file will be used (with recalculated datasize
 * of course)
 *
 * @link http://ccrma.stanford.edu/CCRMA/Courses/422/projects/WaveFormat/
 * @link http://www.thescripts.com/forum/thread3770.html
 */
function joinwavs($wavs){
    $fields = join('/',array( 'H8ChunkID', 'VChunkSize', 'H8Format',
                              'H8Subchunk1ID', 'VSubchunk1Size',
                              'vAudioFormat', 'vNumChannels', 'VSampleRate',
                              'VByteRate', 'vBlockAlign', 'vBitsPerSample' ));

    $data = '';
    foreach($wavs as $wav){
        $fp     = fopen($wav,'rb');
        $header = fread($fp,36);
        $info   = unpack($fields,$header);

        // read optional extra stuff
        if($info['Subchunk1Size'] > 16){
            $header .= fread($fp,($info['Subchunk1Size']-16));
        }

        // read SubChunk2ID
        $header .= fread($fp,4);

        // read Subchunk2Size
        $size  = unpack('vsize',fread($fp, 4));
        $size  = $size['size'];

        // read data
        $data .= fread($fp,$size);
    }

    return $header.pack('V',strlen($data)).$data;
}

