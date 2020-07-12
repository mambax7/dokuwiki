<?php
/**
 * divalign: allows you to align right, left, center, or justify
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jason Byrne <jbyrne@floridascale.com>
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_divalign extends DokuWiki_Syntax_Plugin {
 
    function getInfo(){
        return array(
            'author' => 'Jason Byrne',
            'email'  => 'jbyrne@floridascale.com',
            'date'   => '2008-03-29',
            'name'   => 'divalign',
            'desc'   => 'Add alignment',
            'url'    => 'http://www.dokuwiki.org/plugin:divalign',
        );
    }
 
    function getSort() { return 157; }
    function getType() { return 'container'; }
    function getAllowedTypes() { return array('container','substition','protected','disabled','formatting','paragraphs'); }
    function getPType(){ return 'block';}
 
    function connectTo($mode) {
        $this->Lexer->addEntryPattern(';;#(?=.*;;#)',$mode,'plugin_divalign');
        $this->Lexer->addEntryPattern('#;;(?=.*#;;)',$mode,'plugin_divalign');
        $this->Lexer->addEntryPattern(';#;(?=.*;#;)',$mode,'plugin_divalign');
        $this->Lexer->addEntryPattern('###(?=.*###)',$mode,'plugin_divalign');
    }
    function postConnect() {
        $this->Lexer->addExitPattern(';;#','plugin_divalign');
        $this->Lexer->addExitPattern('#;;','plugin_divalign');
        $this->Lexer->addExitPattern(';#;','plugin_divalign');
        $this->Lexer->addExitPattern('###','plugin_divalign');
    }
 
    function handle($match, $state, $pos, &$handler){
        switch ( $state ) {
          case DOKU_LEXER_ENTER:
            switch ( $match ) {
              case '#;;' : $align = 'left'; break;
              case ';;#' : $align = 'right'; break;
              case ';#;' : $align = 'center'; break;
              case '###' : $align = 'justify'; break;
              default    : $align = '';
            }
            return array($align,$state,$pos);
 
          case DOKU_LEXER_UNMATCHED:
            $handler->_addCall('cdata', array($match), $pos);
            break;          
        }
        return array('',$state,'');
    }
 
    function render($mode, &$renderer, $data) {
 
        if ($mode == 'xhtml'){
 
          list($align,$state,$pos) = $data;
          switch ($state) {
            case DOKU_LEXER_ENTER:
              if ($align) { $renderer->doc .= '<div style="text-align: '.$align.';margin:5px">'; }
              break;
 
            case DOKU_LEXER_EXIT : 
              $renderer->doc .= '</div>';
              break;
          }
          return true;
        } // end if ($mode == 'xhtml')
 
        return false;
    }
 
}
 
//Setup VIM: ex: et ts=4 enc=utf-8 :
?>