<?php
// ---------------------------------------------------------------
// :: Doc Finder
// ----------------------------------------------------------------
//
// 	Short Description:
//         Ajax powered search and replace for the manager.
//
//   Version:
//         1.7
//
//   Created by:
// 	    Bogdan Günther (http://www.medianotions.de - bg@medianotions.de)
//
//
// ----------------------------------------------------------------
// :: Copyright & Licencing
// ----------------------------------------------------------------
//
//   GNU General Public License (GPL - http://www.gnu.org/copyleft/gpl.html)
//

$_lang['modulename'] = 'Doc Finder';
$_lang['reload'] = 'Neu laden';
$_lang['close'] = 'Schließen';
$_lang['close_button'] = '[+lang.modulename+] schließen';
$_lang['help_search'] = 'Sie können AND, OR und NOT in Ihrer Suche benutzen und ganze Wörter mittels zusätzlicher Leerzeichen suchen. Außerdem können Sie Reguläre Ausdrücke für die Suche benutzen, wenn sie dies in in den Allgemeinen Optionen aktivieren. Sie können ALL benutzen, um alle Ergebnisse zu erhalten – wenn sie beispielsweise Einträge in einem Datumsbereich suchen.';
$_lang['help_replace'] = 'Ersetze alle Vorkommen der Such-Zeichenkette mit der Ersetzen-Zeichenkette. Das Ersetzen kann nicht mit Logischen Verknüpfungen und Regulären Ausdrücken benutzt werden. Die Dokument ID wird vom Ersetzen ausgenommen. ';
$_lang['help_sortable_tables'] = 'Sortierbare Tabellen für eine bessere Geschwindigkeit insbesondere in älteren Versionen des Internet Explorers deaktivieren.';
$_lang['help_parent_ids'] = 'Geben Sie die Eltern IDs an. Sie können mehrere Eltern IDs kommasepariert angeben.';
$_lang['help_creation_date_range'] = 'Setzen Sie einen Datumsbereich für die Suche nach dem Erstelldatum. Es kann auch nur ein Anfangs- und ein Enddatum statt eines Datumsbereichs angegeben werden.';
$_lang['help_edited_date_range'] = 'Setzen Sie einen Datumsbereich für die Suche nach dem Bearbeitungsdatum. Es kann auch nur ein Anfangs- und ein Enddatum statt eines Datumsbereichs angegeben werden.';
$_lang['warning_charset'] = '<strong>Achtung:</strong> Doc Finder benötigt zwingend UTF-8 als MODX Zeichenkodierung um richtig zu funktionieren. Suchen und Ersetzen mit Sonderzeichen kann ansonsten zu unerwarteten Ergebnissen führen.';
$_lang['search'] = 'Suchen';
$_lang['replace'] = 'Ersetzen';
$_lang['search_for'] = 'Suchen nach';
$_lang['replace_with'] = 'Ersetzen durch';
$_lang['all_none'] = '[Alle / Keine]';
$_lang['search_replace'] = 'Suchen &amp; Ersetzen';
$_lang['general_options'] = 'Allgemeine Optionen';
$_lang['search_places'] = 'Such-Orte';
$_lang['search_options'] = 'Such-Optionen';
$_lang['case_sensitive'] = 'Groß-/Kleinschreibung beachten';
$_lang['regular_expression'] = 'Regulärer Ausdruck';
$_lang['sortable_tables'] = 'Sortierbare Tabellen';
$_lang['id'] = 'ID';
$_lang['pagetitle'] = 'Titel';
$_lang['longtitle'] = 'Langer Titel';
$_lang['name'] = 'Name';
$_lang['description'] = 'Beschreibung';
$_lang['alias'] = 'Alias';
$_lang['introtext'] = 'Zusammenfassung';
$_lang['menutitle'] = 'Menütitel';
$_lang['content'] = 'Inhalt';
$_lang['tvs'] = 'TVs';
$_lang['other'] = 'Alle anderen Felder';
$_lang['created_on'] ='Erstellt am';
$_lang['edited_on'] ='Bearbeitet am';
$_lang['found_in'] ='Gefunden in';
$_lang['functions'] ='Funktionen';
$_lang['edit'] ='Bearbeiten';
$_lang['info'] ='Info';
$_lang['preview'] ='Vorschau';
$_lang['created_between'] = 'Erstellt zwischen';
$_lang['edited_between'] = 'Bearbeitet zwischen';
$_lang['clear_date'] = 'Datum löschen';
$_lang['searching'] = 'Suchen …';
$_lang['search_empty'] = 'Die Such-Zeichenkette ist leer';
$_lang['no_results'] = 'Es wurden keine Ergebnisse gefunden';
$_lang['documents'] = 'Dokumente';
$_lang['templates'] = 'Templates';
$_lang['tvs'] = 'TVs';
$_lang['chunks'] = 'Chunks';
$_lang['snippets'] = 'Snippets';
$_lang['plugins'] = 'Plugins';
$_lang['modules'] = 'Module';
$_lang['type'] = 'Typ';
$_lang['combined_view'] = 'Alle Elemente';
$_lang['search_history_filled'] = 'Such-Historie …';
$_lang['search_history_empty'] = 'Such-Historie (leer)';
$_lang['replace_history_filled'] = 'Ersetzen-Historie …';
$_lang['replace_history_empty'] = 'Ersetzen-Historie (leer)';
$_lang['document_options'] = 'Dokument-Optionen';
$_lang['document_parents'] = 'Eltern-Dokumente';
$_lang['search_in'] = 'Suchen in';
$_lang['resource_options'] = 'Ressourcen-Optionen';
$_lang['date_options'] = 'Datums-Optionen';
$_lang['all'] = 'Alle';
$_lang['form_no_results'] = '<span id="results_info">Keine</span> Ergebnisse';
$_lang['form_search_for'] = '<span id="results_string">für &bdquo;<strong id="search_string"></strong>&ldquo;</span>';
$_lang['form_replace_with'] = '<span id="replace_info">ersetzt durch &bdquo;<strong id="replace_string"></strong>&ldquo;</span>';
$_lang['form_time'] = '<span id="time_info">(<span id="time">&ndash;</span> Sekunden)</span>';
$_lang['cancel_search'] = 'Suche abbrechen';
$_lang['number_of_entries'] = 'Anzahl der Ergebnisse';
