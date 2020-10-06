<?php
/**
 * DokuWiki Plugin footer (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Original: from Plugin headerfooter, author  Li Zheng <lzpublic@qq.com>
 * Modified by Juergen  H-J-Schuemmer@Web.de
 * Only the footer component is supported in this plugin because the header functionality breaks the section edit mode
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_footer extends DokuWiki_Action_Plugin {
    public function register(Doku_Event_Handler $controller) {

       $controller->register_hook('PARSER_WIKITEXT_PREPROCESS', 'AFTER', $this, 'handle_parser_wikitext_preprocess');
   
    }
    public function handle_parser_wikitext_preprocess(Doku_Event &$event, $param) {
        global $INFO;
        global $ID;
        global $conf;
        
        //what does this mean???
        if ($INFO['id'] != '') return; // Jede Seite wird zweimal ausgeführt. Wenn die ID leer ist, ist es der echte Text, andernfalls ist es das Menü.

        //helper array needed for parsePageTemplate
        //so that replacement like shown here is possible: https://www.dokuwiki.org/namespace_templates#replacement_patterns
        $data = array(
            'id'        => $ID, // the id of the page to be created
            'tpl'       => '',  // the text used as template
        );

		// Auslesen der Konfiguration für das Präfix der Vorlage-Dateien:
		$pre_nsp = $this->getConf('prefix_namespace');
		if ($pre_nsp != '') {
			$pre_nsp = '/'.$pre_nsp.'_';
		} else {
			$pre_nsp = '/_';    // Defaultwert 1 Unterstrich für Namespace
		};		
		$pre_sub = $this->getConf('prefix_subnamespace');
		if ($pre_sub != '') {
			$pre_sub = '/'.$pre_sub.'_';
		} else {
			$pre_sub = '/__';   // Defaultwert 2 Unterstriche für Sub-Namespace
		};
		
        $footerpath = '';
		$templatename = 'footer.txt';    // Name der Vorlage
        $path = dirname(wikiFN($ID));
        if (@file_exists($path.$pre_nsp.$templatename)) {
            $footerpath = $path.$pre_nsp.$templatename;
        } else {
            // search upper namespaces for templates
            $len = strlen(rtrim($conf['datadir'], '/'));
            while (strlen($path) >= $len) {
                if (@file_exists($path.$pre_sub.$templatename)) {
                    $footerpath = $path.$pre_sub.$templatename;
                    break;
                }
                $path = substr($path, 0, strrpos($path, '/'));
            }
        }

        if (!empty($footerpath)) {
            $footer = file_get_contents($footerpath);
            if ($footer !== false) {
                $data['tpl'] = cleanText($footer);
                $footer = parsePageTemplate($data);

                if ($this->getConf('separation') == 'paragraph') { 
					// Wenn Absätze zum Teilen verwendet werden
                    $footer = rtrim($footer, " \r\n\\") . "\n\n";
                }
                $event->data .= $footer;
            }
        }
    }
}