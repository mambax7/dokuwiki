<?php
/**
 * Plugin Linebreak: Inserts a line break
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_linebreak extends DokuWiki_Syntax_Plugin {

    var $no_quotes = array(300, '(?<!^|\n)\n(?!\n)');
    var $in_quotes = array(100, '(?<!^|\n)\n(?!\n|>)');
#    var $no_quotes = array(300, '(?<!\n)\n(?!\n)');
#    var $in_quotes = array(100, '(?<!\n)\n(?!\n|>)');
    var $ptn_pageon = '~~LINEBREAK~~';
    var $ptn_pageoff = '~~NOLINEBREAK~~';
 
    function getInfo(){
      return array(
        'author' => 'Christopher Smith',
        'email'  => 'chris@jalakai.co.uk',
        'date'   => '2007-01-20',
        'name'   => 'Linebreak Plugin',
        'desc'   => 'Provide a line break for a new line in the raw wiki data',
        'url'    => 'http://wiki.splitbrain.org/plugin:linebreak',
      );
    }
 
    function getType() { return 'substition'; }
    function getSort() { return ($this->getConf('in_quotes') ? $this->in_quotes[0] : $this->no_quotes[0]); }
    function connectTo($mode) {

       $ptn = $this->getConf('in_quotes') ?  $this->in_quotes[1] : $this->no_quotes[1];
       $this->Lexer->addSpecialPattern($ptn,$mode,'plugin_linebreak');

       $this->Lexer->addSpecialPattern($this->ptn_pageon,$mode,'plugin_linebreak');
       $this->Lexer->addSpecialPattern($this->ptn_pageoff,$mode,'plugin_linebreak');

       $this->Lexer->addSpecialPattern('~~LINEBREAK#.+~~\n',$mode,'plugin_linebreak');
    }

    function handle($match, $state, $pos, &$handler){ 

      if (substr($match, 0, 12) == '~~LINEBREAK#') {
        $marker = substr($match, 12,-3);
        return array('marker' => $marker);
      }

      if (!isset($handler->status['plugin_linebreak'])) {
        $handler->status['plugin_linebreak'] = $this->getConf('automatic');
      }

      if ($match == "\n") return array($handler->status['plugin_linebreak']);

      if ($match == $this->ptn_pageon) {
        $handler->status['plugin_linebreak'] = true;
      } else if ($match == $this->ptn_pageoff) {
        $handler->status['plugin_linebreak'] = false;
      }

      return array(false);
    }

    function render($mode, &$renderer, $data) {

      if($mode == 'xhtml'){
          if ($data[0]) $renderer->doc .= "<br />";
          return true;
      }
      return false;
    }

}