<?php
/**
 * Italian language for indexmenu plugin
 *
 * @license:    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author:     Samuele Tognini <samuele@netsons.org>
 */
$lang['menu']  = "Utility di Indexmenu"; 
$lang['fetch'] = 'Mostra'; 
$lang['install'] = 'Installa';
$lang['delete'] = 'Elimina'; 
$lang['check'] = 'Controlla'; 
$lang['no_repos'] = 'Non &egrave stato configurato nessun indirizzo web per il repository dei temi.';
$lang['disabled'] = 'Disabilitato'; 
$lang['conn_err'] = 'Errore di connessione'; 
$lang['local_themes'] = 'Temi installati';
$lang['dir_err'] = 'Impossibile creare la directory temporanea per scaricare il tema';
$lang['down_err'] = 'Impossibile scaricare il tema';
$lang['zip_err'] = 'Errore durante la creazione o scompattazione dell\' archivio';
$lang['install_ok'] = "tema installato correttamente. Il nuovo tema &egrave accessibile dalla toolbar degli strumenti quando crei una pagina oppure tramite l' opzione js#nome_tema di indexmenu.";
$lang['install_no'] = 'Errore durante l\' upload. Puoi comunque provare ad inviarmi manualmente il tema da <a href="http://samuele.netsons.org/dokuwiki/lib/plugins/indexmenu/upload/">qui</a>.';
$lang['delete_ok'] = 'Rimozione del tema avvenuta con successo';
$lang['delete_no'] = "Errore durante la rimozione del tema.";
$lang['upload'] = 'Condividi';
$lang['checkupdates'] = 'Aggiornamenti plugin';
$lang['noupdates'] = "Indexmenu non ha bisogno di essere aggiornato. Hai gi&agrave l'ultima release: <br><br>".nl2br(@preg_replace('/\n\n.*$/s','',@io_readFile(DOKU_PLUGIN.'indexmenu/changelog')));
$lang['infos'] = 'Puoi creare il tuo tema seguendo le istruzioni nella pagina del <a href="http://wiki.splitbrain.org/plugin:indexmenu#theme_tutorial">Tutorial dei temi</a>.<br> Poi potreste rendere felice pi&ugrave gente :-) inviandolo nel repository pubblico di indexemnu tramite il pulsante "condividi" sotto il tema della lista.';
$lang['showsort']="Ordinamento in indexmenu: ";
