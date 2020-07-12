<?php
/**
 * Italian language for indexmenu plugin
 *
 * @license:    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author:     Samuele Tognini <samuele@netsons.org>
 */
$lang['checkupdate']	        =	"Controlla periodicamente la presenza di nuovi aggiornamenti.";
$lang['only_admins']	        =	"Disabilita l'uso di indexmenu agli utenti non amministratori.<br>Nota che una pagina modificata da un utente non amministratore perdera' qualsiasi indexmenu contenuto in essa.";
$lang['aclcache']	        =	"Ottimizza la cache di indexmenu per le acl (solo per i root namespaces) .<br>La scelta del metodo influisce solo sulla visualizzazione dei nodi nell'albero di indexmenu, non sulle autorizzazioni delle pagine.<ul><li>None: Standard. &Egrave il metodo pi&ugrave veloce e non crea ulteriori files di cache, ma i nodi con permesso negato potrebbero essere visualizzati da utenti non autorizzati o viceversa. Consigliato se non si nega l'accesso alle pagine tramite acl o non interessa come vengano visualizzate.<li>User: Per singola login. Metodo pi&ugrave lento e crea molti files di cache, ma nasconde sempre correttamente i nodi a cui l'utente non ha accesso. Consigliato se si nega l'accesso alle pagine a degli utenti in base alla loro login.<li>Groups: Usa una cache differente in base ai gruppi di appartenenza dell'utente. Buon compromesso fra i precedenti metodi, ma nel caso si neghi l'accesso ad un utente che appartiene ad un guppo con autorizzazione, questo potrebbe comunque visualizzare i nodi a lui negati. Consigliato se si impostano le acl unicamente in base ai gruppi di appartenenza.</ul>";
$lang['headpage']		=	"Headpage: La pagina da cui ottenere il titolo e il link del namespace.<br>Puo' essere uno qualsiasi di questi valori:<ul><li>La pagina globale start.<li>La pagina con il nome del namespace e che si trova al suo interno.<li>La pagina con il nome del namespace e che si trova allo stesso livello.<li>Il nome personalizzato di una pagina.<li>Una lista di nomi di pagine separati da virgola.</ul>";
$lang['hide_headpage']	        =	'Nascondi le pagine di tipo headpage.';
$lang['page_index']             =       'La pagina che sostituira&grave l\'indice di sokuwiki. Creala and inserisci la sintassi di indexmenu. Usa id#random se hai gia&grave una sidebar con indexmenu e l\'opzione navbar. Il mio suggerimento e&grave "{{indexmenu>..|js navbar nocookie id#random}}".';
$lang['empty_msg']		=	'Messaggio da visualizzare in caso di menu vuoto. Utilizzare la sinstassi Dokuwiki, non codice html.La variabile {{ns}} mostra il namespace richiesto.';
$lang['skip_index']	        =	'Namespaces da nascondere. I percorsi completi devono essere specificati in forma di percorsi di filesystem. Utilizzare le Espressioni Regolari. Esempio: /(sidebar|private\/dir)/';
$lang['skip_file']		=	'Files da nascondere. Le pagine sono controllate con estensione .txt. I percorsi completi devono essere specificati in forma di percorsi di filesystem. Utilizzare le Espressioni Regolari. Esempio: /(start|mydir\/start.txt)/';
$lang['show_sort']		=	'Mostra agli amministratori il numero di ordinamento di indexmenu come nota ad inizio pagina.';
$lang['themes_url']             =       'Http url da cui scaricare i temi.';
$lang['be_repo']                =       'Permetti a tutti di scaricare i temi dal tuo sito';