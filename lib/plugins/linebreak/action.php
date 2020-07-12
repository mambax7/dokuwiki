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
require_once(DOKU_PLUGIN.'action.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class action_plugin_linebreak extends DokuWiki_Action_Plugin {

    var $marker_id = 0;
    var $linebreak_conversions = array();

    /**
     * return some info
     */
    function getInfo(){
      return array(
        'author' => 'Christopher Smith',
        'email'  => 'chris@jalakai.co.uk',
        'date'   => '2007-02-18',
        'name'   => 'Linebreak Plugin',
        'desc'   => 'Provide a line break for a new line in the raw wiki data',
        'url'    => 'http://wiki.splitbrain.org/plugin:linebreak',
      );
    }

    /**
     * plugin should use this method to register its handlers with the dokuwiki's event controller
     */
    function register(&$controller) {
      $controller->register_hook('PARSER_WIKITEXT_PREPROCESS', 'BEFORE',  $this, '_addspaces', NULL);
      $controller->register_hook('PARSER_HANDLER_DONE','BEFORE', $this, '_fixsecedit', NULL);
    }

    /**
     * add spaces before line breaks, required so syntax component pattern will match correctly
     * record the offset changes so we can fix up the section edit offsets later
     */
    function _addspaces(&$event, $param) {

      // preg pattern used to find line breaks which require spaces inserted before them
      $pattern = '/(?!< )(\n+|$)/';

      // marker to add at the start of the raw wiki data, it contains an id we use to access the location
      // of the additional spaces added into the file
      $marker = "~~LINEBREAK#{$this->marker_id}~~\n";
			
      // get the location (offset) of all the spaces to be added
      $linebreaks = array();
      preg_match_all($pattern, $event->data, $linebreaks, PREG_OFFSET_CAPTURE);

      for ($i=0; $i<count($linebreaks[0]); $i++) {
        $conversion[] = $linebreaks[0][$i][1];
      }

      // save details of the added spaces
      $this->linebreak_conversions[$this->marker_id] = $conversion;

      // add in the spaces
      $event->data = $marker.preg_replace($pattern,' $1',$event->data);

      // update the marker id so that the next use gets a fresh id.
      $this->marker_id++;
    }

    function _fixsecedit(&$event, $param) {

      // find our linebreak marker instruction and get the marker id
      $calls =& $event->data->calls;
      $marker = null;

      for ($i=0; $i < count($calls); $i++) {
        if ($calls[$i][0] == 'plugin' && $calls[$i][1][0] == 'linebreak' && isset($calls[$i][1][1]['marker'])) {
          $marker = $calls[$i][1][1]['marker'];
          break;
        }
      }

      if (is_null($marker)) return;

      // calculate the amount added to the start of the raw wiki data for our marker tag
      $this->base_delta = strlen("~~LINEBREAK#$marker~~\n");
      $this->current_marker = $marker;

      // iterate through the instruction list and set the file offset values (usually the $pos variable)
      // back to the values they would be if no spaces had been added by this plugin
      for ($i=0; $i < count($calls); $i++) {
        if ($calls[$i][0] == 'section_edit') {
          $calls[$i][1][0] = $this->_convert($calls[$i][1][0]);
          $calls[$i][1][1] = $this->_convert($calls[$i][1][1]);
          $calls[$i][2] = $this->_convert($calls[$i][2]);
        } else {
//          $calls[$i][2] = $this->_convert($calls[$i][2]);
				}
      }
    }

    /**
     *  convert modified raw wiki offset value ($pos) back to the unmodified value
     */
    function _convert($pos) {
      global $debug;

      // file start values will still be file start values - don't change them
      if ($pos <= 1) return $pos;

      $pos -= $this->base_delta;
      if ($pos < 0) return 0;

      // simplify access to the conversion data
      $conversion =& $this->linebreak_conversions[$this->current_marker];

      for ($i=0; $i<count($conversion); $i++) {
        // the conversion contains the original source position
        // we need to add in the modifications up to that point and compare against $pos
        // remembering we have already modified $pos for the marker string we added first
        if ($conversion[$i] + $i >= $pos) {
          return $pos - $i;
        }
      }

      // $i will be one more than the number of modifications made
      return $pos - $i -1;
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :