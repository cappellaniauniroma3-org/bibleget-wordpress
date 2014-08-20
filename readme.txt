=== BibleGet IO ===
Contributors: Lwangaman
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HDS7XQKGFHJ58
Tags: bible,shortcode,quote
Requires at least: 3.0.1
Tested up to: 3.9.2
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enables a shortcode [bibleget] for inserting a Bible quote in an article or a page, using the BibleGet IO Service at http://www.bibleget.de.

== Description ==

This plugin lets you use a shortcode in articles and pages, that lets you make Bible citations.

Questo plugin permette di utilizzare uno shortcode all’interno di articoli o pagine, 
che permette facilmente di effettuare citazioni della Sacra Scrittura. 
L’utilizzo dello shortcode è il seguente:

[bibleget query="[inserire query qui]"]  

Per esempio: [bibleget query="Genesi 1,1-10"] oppure [bibleget query="Gen1,1-10"] (con o senza spazio tra libro e capitolo)

La versione della Bibbia che viene restituita è CEI 2008. 
Con il tempo forse si potranno aggiungere altre versioni e magari altre lingue, 
e si potrà indicare la versione desiderata con un’altra opzione nello shortcode. 
Ma per ora, c’è solo la versione CEI 2008.

La query va formulata seguendo lo standard italiano per le citazioni della Sacra Scrittura, 
con regole simili alla notazione standard inglese (si veda http://en.wikipedia.org/wiki/Bible_citation):


   * “,”: è il separatore capitolo – versetto. “15,5″ significa “capitolo 15, versetto 5″.

   * “-”: è il separatore da – a, che può essere:

      1. da capitolo a capitolo: “15-16″ significa “da capitolo 15 a capitolo 16″.
      2. da capitolo,versetto a versetto (dello stesso capitolo): “15,1-5″ significa “capitolo 15, dal versetto 1 al versetto 5″.
      3. da capitolo,versetto a capitolo,versetto: “15,1-16,5″ significa “dal capitolo 15,versetto 1 al capitolo 16,versetto 5″.

   * “.”: è il separatore tra versetto e versetto. “15,5.7.9″ significa “capitolo 15,versetto 5 poi versetto 7 poi versetto 9″.

   * “;”: è il separatore tra una query e l’altra. “15,5-7;16,3-9.11-13″ significa “capitolo 15, versetti 5 a 7; poi capitolo 16, versetti 3 a 9 e versetti 11 a 13″

Almeno la prima query deve indicare il nome del libro sul quale effettuare la ricerca;
 il nome del libro si può indicare per intero oppure utilizzando la forma abbreviata
 come indicata alla pagina [Lista di Abbreviazioni di Libri](http://www.bibleget.de/come-funziona/lista-abbreviazioni-libri/ "Lista di Abbreviazioni di Libri").
 Quando le query successive non hanno indicazione di nome di libro,
 è sottinteso che la query viene effettuata sullo stesso libro indicato precedentemente.
 "Gen1,7-9;4,4-5;Es3,19" significa “Genesi capitolo 1, versetti 7 a 9; poi ancora Genesi capitolo 4, versetti 4 a 5; poi Esodo capitolo 3, versetto 19″.

Il Plugin offre anche una pagina di opzioni “BibleGet IO Settings” sotto “Impostazioni” nella Dashboard,
che permettono di cambiare la presentazione delle citazioni bibliche
(grandezza dei caratteri, colori, fonts, etc.)
in modo che sia più confacente allo stile del proprio blog / sito WordPress.
Una volta cambiate le opzioni, ricordati di cliccare sul pulsante “Salva le modifiche”!

[BibleGet IO](
http://www.bibleget.de/
 "BibleGet IO Website")

== Installation ==

1. Upload the `bibleget-io` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Which version of the Bible am I getting? =

For the time being, the only version is the Italian CEI 2008. Over time more version in more languages will be available.

= How come my query doesn't work? I'm using the common notation for Bible citations. =

Make sure you are using the italian standard notation and not the english notation.

== Screenshots ==

1. Screenshot of the options page (screenshot-1.png).
2. Screenshot of the resulting biblical quote from use of the shortcode in an article (screenshot-2.png).

== Changelog ==

= 1.1 =
* Corretto bug quando c'era uno spazio nella query
* Corretto bug quando il server è in safe_mode

= 1.0 =
* Plugin created


== Upgrade Notice ==

= 1.1 =
Si prega effettuare l'upgrade alla versione 1.1 che corregge un paio di bug.

