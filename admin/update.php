<?php

require_once('../inc/init.php');
require_once(DOKU_INC.'inc/common.php');
require_once(DOKU_INC.'inc/pageutils.php');
require_once(DOKU_INC.'inc/search.php');
require_once(DOKU_INC.'inc/indexer.php');
session_write_close();

$CLEAR = @$_GET["clear"];

#------------------------------------------------------------------------------
# Action

if($CLEAR) _clearindex(); 
_update();

header("location: index.php");


#------------------------------------------------------------------------------

function _update(){
    global $conf;
    $data = array();
    echo "Searching pages... ";
    search($data,$conf['datadir'],'search_allpages',array());
    echo count($data)." pages found.\n";

    foreach($data as $val){
        _index($val['id']);
    }
}

function _index($id){
    global $CLEAR;

    // if not cleared only update changed and new files
    if(!$CLEAR){
      $last = @filemtime(metaFN($id,'.indexed'));
      if($last > @filemtime(wikiFN($id))) return;
    }

    _lock();
    echo "$id... ";
    idx_addPage($id);
    io_saveFile(metaFN($id,'.indexed'),' ');
    echo "done.\n";
    _unlock();
}

/**
 * lock the indexer system
 */
function _lock(){
    global $conf;
    $lock = $conf['lockdir'].'/_indexer.lock';
    $said = false;
    while(!@mkdir($lock, $conf['dmode'])){
        if(time()-@filemtime($lock) > 60*5){
            // looks like a stale lock - remove it
            @rmdir($lock);
        }else{
            if($said){
                echo ".";
            }else{
                echo "Waiting for lockfile (max. 5 min)";
                $said = true;
            }
            sleep(15);
        }
    }
    if($conf['dperm']) chmod($lock, $conf['dperm']);
    if($said) print "\n";
}

/**
 * unlock the indexer sytem
 */
function _unlock(){
    global $conf;
    $lock = $conf['lockdir'].'/_indexer.lock';
    @rmdir($lock);
}

/**
 * Clear all index files
 */
function _clearindex(){
    global $conf;
    _lock();
    echo "Clearing index... ";
    io_saveFile($conf['cachedir'].'/word.idx','');
    io_saveFile($conf['cachedir'].'/page.idx','');
    io_saveFile($conf['cachedir'].'/index.idx','');
    echo "done.\n";
    _unlock();
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
