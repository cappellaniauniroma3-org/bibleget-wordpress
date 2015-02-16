=== BibleGet I/O ===
Contributors: Lwangaman
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HDS7XQKGFHJ58
Tags: bible,shortcode,quote,citation
Requires at least: 3.0.1
Tested up to: 4.1.0
Stable tag: 2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Creates a shortcode [bibleget] for inserting Bible quotes in articles or pages using the BibleGet I/O Service endpoint http://query.bibleget.io

== Description ==

[ENGLISH] 
This plugin creates a shortcode to use in articles and pages, that will inject Bible citations into your article or page.
USAGE: [bibleget query="Matthew 1:1-10,12-15" versions="NVBSE,NABRE"]  

The Plugin also has a settings page “BibleGet I/O” under the “Settings” area in the Dashboard, where you can choose your stylistic preferences (font, font size, color, etc.)
so that the injected Bible quotes may fit into the style of your own blog / WordPress website.
After you have made your choices in the settings area, remember to click on “Save”!
You can also choose, on the same settings page, to edit the css file directly if you would like to have a finer control over the appearance.

[ESPAÑOL] 
Este plugin crea un shortcode para ser utilizado en artículos o páginas, que puede inyectar citas Bíblicas en el artículo o página.
UTILIZACIÓN: [bibleget query="Mateo 1,1-10.12-15" versions="NVBSE,CEI2008,LUZZI"]  

El Plugin también tiene una página de opciones "BibleGet I/O" bajo el área "Configuración" en el cuadro de instrumentos (Dashboard), donde se puede elegir sus preferencias estilísticas (fuente, tamaño de fuente, color, etc.)
en manera que las citas bíblicas inyectadas pueden encajar en el estilo de su propio blog / sitio web WordPress.
Después de haber elegido sus preferencias en el área de configuración, no olvide hacer clic en "Guardar"!
También puede elegir, en la misma página de configuración, editar la hoja de estilo css directamente si le gustaría tener un mayor control sobre la apariencia.

[FRANÇAIS] 
Ce plugin crée un shortcode pour être utilisé dans des articles ou pages, qui peut injecter des citations Bibliques dans l'article ou la page.
UTILISATION: [bibleget query="Mathieu 1,1-10.12-15" versions="NVBSE,CEI2008,LUZZI"]  

Le plugin a également une page de paramètres "BibleGet I/O" dans la zone "Paramètres" dans le tableau de bord (Dashboard), où vous pouvez choisir vos préférences stylistiques (police, taille de police, couleur, etc.)
de sorte que les citations bibliques injectées peuvent se intégrer dans le style de votre propre blog / site WordPress.
Après avoir fait vos choix dans la zone des paramètres, ne oubliez pas de cliquer sur "Enregistrer"!
Vous pouvez également choisir, sur la même page de paramètres, de modifier le fichier CSS directement si vous souhaitez avoir un contrôle plus fin sur l'apparence.

[ITALIANO] 
Questo plugin crea uno shortcode da utilizzare all’interno di articoli o pagine, che può iniettare citazioni Bibliche nell'articolo o nella pagina. 
UTILIZZO: [bibleget query="Matteo 1,1-10.12-15" versions="NVBSE,CEI2008,LUZZI"]  

Il Plugin offre anche una pagina di opzioni “BibleGet I/O” sotto “Impostazioni” nella Dashboard, che permette di cambiare la presentazione delle citazioni bibliche (carattere, grandezza del carattere, colore, ecc.)
in modo che le citazioni iniettate nelle pagine abbiano uno stile confacente al proprio blog / sito WordPress.
Una volta cambiate le opzioni, ricòrdati di cliccare sul pulsante “Salva le modifiche”!
Puoi anche modificare direttamente il foglio di stile sulla stessa pagina delle opzioni, se preferisci avere un controllo più accurato sulla presentazione.

[DEUTSCH] 
Das Plugin erzeugt eine shortcode die in Artikeln oder Seiten verwendet werden können, und welche die Bibelzitate in dem Artikel oder Seite injizieren können.
VERWENDUNG: [bibleget query="Matthäus 1,1-10.12-15" versions="NVBSE,CEI2008,LUZZI"]

Das Plugin hat auch eine Einstellungsseite “BibleGet I/O” im Bereich “Einstellungen” im Dashboard, in dem Sie Ihre stilistischen Präferenzen wählen (Schriftart, Schriftgröße, Farbe, etc.) 
so dass die injizierten Bibelzitaten kann in den Stil Ihrer eigenen Blog / Wordpress-Website passen.
Nachdem Sie Ihre Auswahl im Bereich Einstellungen vorgenommen haben, denken Sie daran, auf "Speichern" klicken!
Sie können auch festlegen, im Bereich Einstellungen, um die CSS-Datei direkt bearbeiten, wenn Sie möchten, um eine feinere Kontrolle über das Aussehen haben.

[BibleGet I/O](
http://www.bibleget.io/
 "BibleGet IO Website")

== Installation ==

1. Upload the `bibleget-io` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I formulate a Bible citation? =
[ENGLISH]
The query parameter must contain a citation that is formulated following the standard notation for Bible citations (see [Bible citation notation](http://en.wikipedia.org/wiki/Bible_citation "http://en.wikipedia.org/wiki/Bible_citation")):
   * “:”: is the chapter – verse separator. “15:5” means “chapter 15, verse 5”.

   * “-”: is the from – to separator, and it can be used in one of three ways:

      1. from chapter to chapter: “15-16″ means “from chapter 15 to chapter 16”.
      2. from chapter,verse to verse (of the same chapter): “15:1-5” means “chapter 15, from verse 1 to verse 5”.
      3. from chapter,verse to chapter,verse “15:1-16:5” means “from chapter 15,verse 1 to chapter 16,verse 5”.

   * “,”: is the separator between one verse and another. “15:5,7,9” means “chapter 15,verse 5 then verse 7 then verse 9”.

   * “;”: is the separator between one query and another. “15:5-7;16:3-9,11-13” means “chapter 15, verses 5 to 7; then chapter 16, verses 3 to 9 and verses 11 to 13”.

At least the first query (of a series of queries chained by a semi-colon) must indicate the name of the book upon which to make the request;
 the name of the book can be written in full in more than 20 different languages, or written using the abbreviated form.
 See the page [Lista di Abbreviazioni di Libri](http://www.bibleget.io/come-funziona/lista-abbreviazioni-libri/ "Lista di Abbreviazioni di Libri").
 When a query following a semi-colon does not indicate the book name, it is intended that the request be made upon the same book as the previous query.
 So “Gen1:7-9;4:4-5;Ex3:19” means “Genesis chapter 1, verses 7 to 9; then again Genesis chapter 4, verses 4 to 5; then Exodus chapter 3, verse 19”.

[ITALIANO]
Il parametro "query" deve contenere una citazione formulata seguendo la notazione standard per le citazioni della Sacra Scrittura: 

   * “,”: è il separatore capitolo – versetto. “15,5” significa “capitolo 15, versetto 5”.

   * “-”: è il separatore da – a, e può essere utilizzato in tre modi diversi:

      1. da capitolo a capitolo: “15-16″ significa “da capitolo 15 a capitolo 16”.
      2. da capitolo,versetto a versetto (dello stesso capitolo): “15,1-5” significa “capitolo 15, dal versetto 1 al versetto 5”.
      3. da capitolo,versetto a capitolo,versetto: “15,1-16,5” significa “dal capitolo 15,versetto 1 al capitolo 16,versetto 5”.

   * “.”: è il separatore tra versetto e versetto. “15,5.7.9” significa “capitolo 15,versetto 5 poi versetto 7 poi versetto 9”.

   * “;”: è il separatore tra una query e l’altra. “15,5-7;16,3-9.11-13” significa “capitolo 15, versetti 5 a 7; poi capitolo 16, versetti 3 a 9 e versetti 11 a 13”.

Almeno la prima query deve indicare il nome del libro sul quale effettuare la ricerca;
 il nome del libro si può indicare per intero oppure utilizzando la forma abbreviata
 come indicata alla pagina [Lista di Abbreviazioni di Libri](http://www.bibleget.io/come-funziona/lista-abbreviazioni-libri/ "Lista di Abbreviazioni di Libri").
 Quando le query successive non hanno indicazione di nome di libro,
 è sottinteso che la query viene effettuata sullo stesso libro indicato precedentemente.
 "Gen1,7-9;4,4-5;Es3,19" significa “Genesi capitolo 1, versetti 7 a 9; poi ancora Genesi capitolo 4, versetti 4 a 5; poi Esodo capitolo 3, versetto 19″.

== Screenshots ==

1. A Bible Quote produced from usage of the shortcode in an article (screenshot-1.png).
2. Options page - font and style settings (screenshot-2.png).
3. Options page - edit the CSS stylesheet directly (screenshot-3.png).
4. Options page - information from the BibleGet server about available versions and supported languages (screenshot-4.png).

== Changelog ==

= 2.3 =
* Bugfix for versions of PHP < 5.4 that don't support short array syntax

= 2.2 =
* Bugfix for jquery-ui dependencies on certain Wordpress installations

= 2.1 =
* Fix missing images that weren't included correctly in 2.0 release

= 2.0 =
* Major version release
* Use the new engine of the BibleGet I/O service, which supports multiple versions, dynamic indexes, multiple languages both western and eastern
* Store locally the index information for the versions, for local integrity checks on the queries
* Better and more complete local integrity checks on the queries, using the index information for the versions and supporting both western and eastern languages
* Better and more complete interface for the settings page

= 1.5 =
* Compatible with Wordpress 4.0 "Benny"
* Added local checks for the validity and integrity of the queries
* Corrected a bug that created an error on preg_match_all for versions of PHP < 5.4
* Use the new and definitive domain for the BibleGet I/O service http://query.bibleget.io

= 1.4 =
* Corrected a bug that created an error when the server has safe_mode or open_basedir set (such as some servers with shared hosting)

= 1.3 =
*

= 1.2 =
* 

= 1.1 =
* Corrected a bug that created an error when there is a space in the query

= 1.0 =
* Plugin created


== Upgrade Notice ==

= 2.3 =
This is a major release which uses the new and upgraded BibleGet I/O service engine. Must update. (plus Bugfixes)

= 2.2 =
This is a major release which uses the new and upgraded BibleGet I/O service engine. Must update.

= 2.1 =
This is a major release which uses the new and upgraded BibleGet I/O service engine. Must update.

= 2.0 =
This is a major release which uses the new and upgraded BibleGet I/O service engine. Must update.

= 1.5 =
Si prega aggiornare alla versione 1.5, compatibile con Wordpress 4.0.

= 1.4 =
Si prega effettuare l'upgrade alla versione 1.4 che corregge un paio di bug (errori con server che hanno il safe_mode attivato oppure la direttiva open_basedir settata).

= 1.3 =

= 1.2 =

= 1.1 =
Si prega effettuare l'upgrade alla versione 1.1 che corregge un paio di bug (errori con gli spazi bianchi).

= 1.0 =
Versione iniziale, non pienamente testato.