<?php
/**
 * Cloud Plugin: shows a cloud of the most frequently used words
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <wikidesign@gmail.com>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_cloud extends DokuWiki_Syntax_Plugin {

    function getInfo() {
        return array(
                'author' => 'Gina Häußge, Michael Klier, Esther Brunner',
                'email'  => 'dokuwiki@chimeric.de',
                'date'   => '2008-07-10',
                'name'   => 'Cloud Plugin',
                'desc'   => 'displays the most used words in a word cloud',
                'url'    => 'http://wiki.splitbrain.org/plugin:cloud',
                );
    }

    function getType() { return 'substition'; }
    function getPType() { return 'block'; }
    function getSort() { return 98; }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{\w*?cloud>.*?}}', $mode, 'plugin_cloud');
    }

    function handle($match, $state, $pos, &$handler) {
    global $ID;

        $match = substr($match, 2, -2); // strip markup
    if (substr($match, 0, 3) == 'tag') {
      $match = substr($match, 9);
      $type = 'tag';
    }
    else {
      $match = substr($match, 6);
      $type = 'word';
    }

    list($ns, $num) = explode('#', $match, 2);

    if (($ns == '*') || ($ns == ':')) $ns = '';
    elseif ($ns == '.') $ns = getNS($ID);
    else $ns = cleanID($ns);

        if (!is_numeric($num)) $num = 20; /*edit jayjay*/

        return array($type, $ns, $num);
    }            

    function render($mode, &$renderer, $data) {
        global $conf;

        list($type, $ns, $num) = $data;

        if ($mode == 'xhtml') {

            if ($type == 'tag') { // we need the tag helper plugin
                if (plugin_isdisabled('tag') || (!$tag = plugin_load('helper', 'tag'))) {
                    msg('The Tag Plugin must be installed to display tag clouds.', -1);
                    return false;
                }
                $cloud = $this->_getTagCloud($ns, $num, $min, $max, $tag);
            } else {
                $cloud = $this->_getWordCloud($ns, $num, $min, $max);
            }
            if (!is_array($cloud) || empty($cloud)) return false;
            $delta = ($max-$min)/16;

            // prevent caching to ensure the included pages are always fresh
            $renderer->info['cache'] = false;

            // and render the cloud
            $renderer->doc .= '<div id="cloud">'.DOKU_LF;
            foreach ($cloud as $word => $size) {
                if ($size < $min+round($delta)) $class = 'cloud1';
                elseif ($size < $min+round(2*$delta)) $class = 'cloud2';
                elseif ($size < $min+round(4*$delta)) $class = 'cloud3';
                elseif ($size < $min+round(8*$delta)) $class = 'cloud4';
                else $class = 'cloud5';

                if ($type == 'tag') {
                    $id = $word;
                    resolve_pageID($tag->namespace, $id, $exists);
                    if($exists) {
                        $link = wl($id);
                    } else {
                        $link = wl($id, array('do'=>'showtag', 'tag'=>noNS($id)));
                    }
                    $title = $id;
                    $class .= ($exists ? '_tag1' : '_tag2');
                } else {
                    if($conf['userewrite'] == 2) {
                        if ($ns == '')
						$link = wl($word, array('do'=>'search', 'id'=>$word));
						else
						$link = wl($word, array('do'=>'search', 'id'=>"$word@$ns"));
                        $title = $size;
                    } else {
                        if ($ns == '')
						$link = wl($word, 'do=search');
						else
						$link = wl("$word@$ns", 'do=search');
                        $title = $size;
                    }
                }
                $renderer->doc .= DOKU_TAB.'<a href="'.$link.'" class="'.$class.'"'.
                    ' title="'.$title.'">'.$word.'</a>'.DOKU_LF;
            }
            $renderer->doc .= '</div>'.DOKU_LF;
            return true;
        }
        return false;
    }

    /**
     * Returns the sorted word cloud array
     */
    function _getWordCloud($ns, $num, &$min, &$max){
        global $conf;

        // load stopwords
        $swfile   = DOKU_INC.'inc/lang/'.$conf['lang'].'/stopwords.txt';
        if (@file_exists($swfile)) $stopwords = file($swfile);
        else $stopwords = array();

        // load extra local stopwords
        $swfile = DOKU_CONF.'stopwords.txt';
        if (@file_exists($swfile)) $stopwords = array_merge($stopwords, file($swfile));

        $cloud = array();

        if (@file_exists($conf['indexdir'].'/page.idx')) { // new word-lenght based index
            require_once(DOKU_INC.'inc/indexer.php');
			$page_idx = idx_getIndex('page','');

            $n = 2; // minimum word length
            $lengths = idx_indexLengths($n);
            foreach ($lengths as $len) {
                $idx      = idx_getIndex('i', $len);
                $word_idx = idx_getIndex('w', $len);

                $this->_addWordsToCloud($cloud, $ns, $idx, $word_idx, $page_idx, $stopwords);
            }

        }

        if (empty($cloud)) return $cloud;
		return $this->_sortCloud($cloud, $num, $min, $max);
    }

    /**
     * Adds all words in given index as $word => $freq to $cloud array
     */
    function _addWordsToCloud(&$cloud, $ns, $idx, $word_idx, $page_idx, &$stopwords){
        $wcount = count($word_idx);

        // collect the frequency of the words
        for ($i = 0; $i < $wcount; $i++) {
            $key = trim($word_idx[$i]);
            if (!is_int(array_search("$key\n", $stopwords))) {
                $value = explode(':', $idx[$i]);
                if (!trim($value[0])) continue;
				if ($ns == '')
                $cloud[$key] = count($value);
		        else
		        {
		          foreach ($value as $v)
					{
		            list($p,$c) = explode('*', $v);
		            if (substr($page_idx[$p], 0, strlen($ns)) == $ns)
		              $cloud[$key]++;
					}
		        }
			}
        }
    }

    /**
     * Returns the sorted tag cloud array
     */
    function _getTagCloud($ns, $num, &$min, &$max, &$tag){
        $cloud = array();
        foreach ($tag->topic_idx as $key => $value) {
            if (!is_array($value) || empty($value) || (!trim($value[0]))) continue;
			if ($ns == '')
            $cloud[$key] = count($value);
			else
			{
				foreach ($value as $idx){
				  if (substr($tag->page_idx[$idx], 0, strlen($ns)) == $ns)
					$cloud[$key]++;
				}
			}
		}

        if (empty($cloud)) return $cloud;
		return $this->_sortCloud($cloud, $num, $min, $max);
    }

    /**
     * Sorts and slices the cloud
     */
    function _sortCloud($cloud, $num, &$min, &$max) {

        // sort by frequency, then alphabetically
        arsort($cloud);
        $cloud = array_chunk($cloud, $num, true);
        $max = current($cloud[0]);
        $min = end($cloud[0]);
        ksort($cloud[0]);

        return $cloud[0];
    }
}
// vim:ts=4:sw=4:et:enc=utf-8: 
