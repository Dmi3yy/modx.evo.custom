<?php
// ---------------------------------------------------------------
// :: Doc Finder
// ----------------------------------------------------------------
//
// 	Short Description:
//         Ajax powered search and replace for the manager.
//
//   Version:
//         1.6.1
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
//

function printResultTabs($search, $searchOptions, $theme, $searchPlacesArray)
{
    // if the search string is empty put message and return
    if (!$search['string']) {
        $output = '<p class="empty">[+lang.search_empty+].</p>

        <script type="text/javascript">
            // refresh results head info
            $("results_info").setText("No");
            $("results_string").setStyles("display: none;");
            $("time_info").setStyles("display: none;");
            $("replace_info").setStyles("display: none;");
        </script>';

        return $output;
    }

    // build tabs
    $output = '
        <div class="tab-pane" id="tabPaneResults">
        <script type="text/javascript">var tpResults = null;
            tpResults = new WebFXTabPane($("tabPaneResults"));
        </script>';

    $resultsTotal = 0;

    // loop through tabs
    foreach ($searchPlacesArray as $tab) {
        $id = $tab['id'];
        $name = $tab['name'];

        // skip non active search places
        if ($searchOptions['search_place_' . $id] != 'checked="checked"') continue;

        // get results
        $results[$id] = printResultTables($id, $search, $searchOptions, $theme, false, $searchPlacesArray);

        // print tab only if there are hits
        if ($results[$id]['hits']) {
            $output .= '
            <!-- Tab: ' . $name . ' -->
                <div class="tab-page" id="tab' . $id . '">
                    <h2 class="tab">' . $name . ' (<span id="hits' . $id . '"></span>)</h2>' .
                $results[$id]['html'] . '
                    <script type="text/javascript">
                        tpResults.addTabPage($("tab' . $id . '"));
                        $("hits' . $id . '").setText("' . $results[$id]['hits'] . '");
                    </script>
                </div>';
            $resultsTotal += $results[$id]['hits'];
        }
    }

    if ($resultsTotal == 0) {
        $output .= '<p class="empty">[+lang.no_results+].</p>';
    } else { // print combined view

        // set ID and name
        $id = "CombinedView";
        $name = "[+lang.combined_view+]";

        // get results
        $results[$id] = printResultTables($id, $search, $searchOptions, $theme, $results, $searchPlacesArray);

        $output .= '
        <!-- Tab: Combined View -->
        <div class="tab-page" id="tab' . $id . '">
            <h2 class="tab">' . $name . ' (<span id="hits' . $id . '"></span>)</h2>

            ' . $results[$id]['html'] . '

            <script type="text/javascript">
                tpResults.addTabPage($("tab' . $id . '"));
                $("hits' . $id . '").setText("' . $resultsTotal . '");
            </script>
        </div>';
    }

    $output .= '</div>';

    // adapt results headline
    $output .= '<script type="text/javascript">
        $("results_string").setStyles("display: inline;");
        $("results_info").setText("' . $resultsTotal . '");
    </script>
    ';

    // adapt results headline if there was a string replace
    if ($searchOptions['replace_mode']) {
        $output .= '// refresh results head info
            <script type="text/javascript">
                $("replace_string").setText("' . $searchOptions['replace'] . '");
                $("replace_info").setStyles("display: inline;");
                if ($counter > 0) {
                    // refresh doc tree in the left frame
                    if (parent.tree) parent.tree.location.reload();
                }
            </script>';
    } else {
        $output .= '
        <script type = "text/javascript">
            // show replace info
            $("replace_info") . setStyles("display: none;");
        </script >';
    }
    return $output;
}


function printResultTables($area, $search, $searchOptions, $theme, $results, $searchPlacesArray)
{
    global $modx;

    // set sortable class if sortable tables are enabled
    if ($searchOptions['sortable_tables']) {
        $sortableClass = "sortable";
    };

    $output = '';
    switch ($area) {
        // print Documents and TV values results table
        case "DocAndTVV":

            // get results
            $searchResultsArray = getDocAndTVVResults($search, $searchOptions);
            // print table top
            if ($searchResultsArray) {
                $output .= '
                <table class="DocAndTVV" id="' . $area . '">
                    <thead>
                    <tr>
                        <th class="no align_right ' . $sortableClass . '" axis="number">#</th>
                        <th class="type">[+lang.type+]</th>
                        <th class="id align_right ' . $sortableClass . '" axis="number">[+lang.id+]</th>
                        <th class="title ' . $sortableClass . '" axis="string">[+lang.pagetitle+]</th>
                        <th class="title ' . $sortableClass . '" axis="string">[+lang.longtitle+]</th>
                        <th class="title ' . $sortableClass . '" axis="date">[+lang.created_on+]</th>
                        <th class="title ' . $sortableClass . '" axis="date">[+lang.edited_on+]</th>';
                if ($search['string'] != "ALL") {
                    $output .= '
                        <th class="found_in ' . $sortableClass . '" axis="string">[+lang.found_in+]</th>';
                }
                $output .= '
                        <th class="functions">[+lang.functions+]</th>
                    </tr>
                    </thead>

                    <tbody>';
            }

            // print results
            $counter = 0;
            if ($searchResultsArray) {
                foreach ($searchResultsArray as $searchResults) {
                    // set URLs
                    $urlEdit = "index.php?a=" . $searchPlacesArray[$area]['edit'] . "&amp;id=" . $searchResults['id'];
                    $urlInfo = "index.php?a=" . $searchPlacesArray[$area]['info'] . "&amp;id=" . $searchResults['id'];
                    $urlOpen = $modx->makeUrl(intval($searchResults['id']));
                    $pagetitle_class = "";

                    // set CSS classes
                    if ($searchResults['hidemenu']) {
                        $pagetitle_class .= " hidemenu";
                    };
                    if ($searchResults['published']) {
                        $pagetitle_class .= " published";
                    };

                    // check document type
                    if ($modx->getDocumentChildren($searchResults['id'])) {
                        $iconName = "folder.gif";
                    } else {
                        $iconName = "page.gif";
                    }

                    // output result
                    $output .= '
                    <tr>
                        <td class="no align_right">' . ($counter + 1) . '</td>
                        <td class="type">
                            <img src="media/style/[+options.theme+]/images/tree/' . $iconName . '" alt=""/>
                        </td>
                        <td class="id align_right">' . $searchResults['id'] . '</td>
                        <td class="pagetitle' . $pagetitle_class . '">
                            <strong><a href="' . $urlEdit . '" title="[+lang.edit+]">' . $searchResults['pagetitle'] . '</a></strong>
                            <small>' . printDocumentBranch($searchResults['parentsArray']) . '</small>
                        </td>
                        <td class="longtitle">' . $searchResults['longtitle'] . '</td>
                        <td class="createdon">' . date("d/m/Y", $searchResults['createdon']) . '</td>
                        <td class="editedon">' . date("d/m/Y", $searchResults['editedon']) . '</td>';
                    if ($search['string'] != "ALL") {
                        $output .= '
                        <td class="found_in">' . substr($searchResults['found_in'], 2) . '</td>';
                    }
                    $output .= '
                        <td class="functions">
                            <a href="' . $urlEdit . '" title="[+lang.edit+]"><img src="media/style/[+options.theme+]/images/icons/save.png" alt="[+lang.edit+]"/></a>
                            <a href="' . $urlInfo . '" title="[+lang.info+]"><img src="media/style/[+options.theme+]/images/tree/page-html.gif" alt="[+lang.info+]"/></a>
                            <a href="' . $urlOpen . '" title="[+lang.preview+]" target="_blank"><img src="media/style/[+options.theme+]/images/icons/page_white_magnify.png" alt="[+lang.preview+]"/></a>
                        </td>
                    </tr>';

                    $counter++;
                }
            } else {
                $output .= '<p class="empty">[+lang.no_results+].</p>';
            }

            // print table bottom
            if ($searchResultsArray) {
                $output .= '
                </tbody>
                </table>';

                if ($searchOptions['sortable_tables']) {
                    $output .= '
                    <script type="text/javascript">
                        window.addEvent("domready", function () {
                            // activate sortable table
                            var DocAndTVV_Table = new sortableTable("' . $area . '");
                        });
                    </script>';
                }
            }
            break;

        // print Template results table
        case "Templates":
        case "TVs":
        case "Chunks":
        case "Snippets":
        case "Plugins":
        case "Modules":

            // get results
            $searchResultsArray = getResourcesResults($area, $search, $searchOptions);

            // print table top
            if ($searchResultsArray) {
                $output .= '
                <table class="Resources" id="' . $area . '_table">
                <thead>
                <tr>
                    <th class="no align_right ' . $sortableClass . '" axis="number">#</th>
                    <th class="align_right id ' . $sortableClass . '" axis="number">[+lang.id+]</th>
                    <th class="name ' . $sortableClass . '" axis="string">[+lang.name+]</th>
                    <th class="description ' . $sortableClass . '" axis="string">[+lang.description+]</th>';
                if ($search['string'] != "ALL") {
                    $output .= '
                    <th class="found_in ' . $sortableClass . '" axis="string">[+lang.found_in+]</th>';
                }
                $output .= '
                    <th class="functions">[+lang.functions+]</th>
                </tr>
                </thead>

                <tbody>';
            }

            // print results
            $counter = 0;
            if ($searchResultsArray) {
                foreach ($searchResultsArray as $searchResults) {
                    // set URLs
                    $urlEdit = "index.php?a=" . $searchPlacesArray[$area]['edit'] . "&amp;id=" . $searchResults['id'];

                    // take care of different name IDs
                    if ($searchResults['templatename']) {
                        $searchResults['name'] = $searchResults['templatename'];
                    }

                    // output result
                    $output .= '
                    <tr>
                        <td class="no align_right">' . ($counter + 1) . '</td>
                        <td class="id align_right">' . $searchResults['id'] . '</td>
                        <td class="name"><a href="' . $urlEdit . '" title="[+lang.edit+]">' . $searchResults['name'] . '</a></td>
                        <td class="description">' . $searchResults['description'] . '</td>';
                    if ($search['string'] != "ALL") {
                        $output .= '
                        <td class="found_in">' . substr($searchResults['found_in'], 2) . '</td>';
                    }
                    $output .= '
                        <td class="functions">
                            <a href="' . $urlEdit . '" title="[+lang.edit+]"><img src="media/style/[+options.theme+]/images/icons/save.png" alt="[+lang.edit+]"/></a>
                        </td>
                    </tr>';

                    $counter++;
                }
            } else {
                $output .= '<p class="empty">[+lang.no_results+].</p>';
            }

            // print table bottom
            if ($searchResultsArray) {
                $output .= '
                </tbody>
                </table>';

                if ($searchOptions['sortable_tables']) {
                    $output .= '
                    <script type="text/javascript">
                        window.addEvent("domready", function () {
                            // activate sortable table
                            var Resources_Table = new sortableTable("' . $area . '_table");
                        });
                    </script>';
                }
            }
            break;

        // print Combined View
        case "CombinedView":

            // print table top
            $output .= '
            <table class="Resources" id="' . $area . '">
                <thead>
                <tr>
                    <th class="no align_right ' . $sortableClass . '" axis="number">#</th>
                    <th class="align_right id ' . $sortableClass . '" axis="number">[+lang.id+]</th>
                    <th class="name ' . $sortableClass . '" axis="string">[+lang.type+]</th>
                    <th class="name ' . $sortableClass . '" axis="string">[+lang.pagetitle+] / [+lang.name+]</th>
                    <th class="description ' . $sortableClass . '" axis="string">[+lang.longtitle+] / [+lang.description+]</th>
                    <th class="title ' . $sortableClass . '" axis="date">[+lang.created_on+]</th>
                    <th class="title ' . $sortableClass . '" axis="date">[+lang.edited_on+]</th>';
            if ($search['string'] != "ALL") {
                $output .= '
                    <th class="found_in ' . $sortableClass . '" axis="string">[+lang.found_in+]</th>';
            }
            $output .= '
                    <th class="functions">[+lang.functions+]</th>
                </tr>
                </thead>

                <tbody>';

            // loop through search places
            $counter = 0;
            foreach ($searchPlacesArray as $section) {
                // ger search results
                $searchResultsArray = $results[$section['id']]['array'];

                if ($searchResultsArray) {
                    foreach ($searchResultsArray as $searchResults) {
                        // set values for the documents
                        if ($section['id'] == "DocAndTVV") {
                            // set URLs
                            $urlEdit = "index.php?a=" . $searchPlacesArray[$area]['edit'] . "&amp;id=" . $searchResults['id'];
                            $urlInfo = "index.php?a=" . $searchPlacesArray[$area]['info'] . "&amp;id=" . $searchResults['id'];
                            $urlOpen = $modx->makeUrl(intval($searchResults['id']));

                            // set values
                            $name = $searchResults['pagetitle'];
                            $description = $searchResults['longtitle'];
                            $createdon = date("d-m-Y", $searchResults['createdon']);
                            $editedon = date("d-m-Y", $searchResults['editedon']);
                        } else { // set values for the resources
                            // set URLs
                            $urlEdit = "index.php?a=" . $searchPlacesArray[$area]['edit'] . "&amp;id=" . $searchResults['id'];

                            // set values
                            if ($searchResults['templatename']) $searchResults['name'] = $searchResults['templatename'];
                            $name = $searchResults['name'];
                            $description = $searchResults['description'];
                            if ($searchResults['createdon']) $createdon = date("d-m-Y", $searchResults['createdon']);
                            else $createdon = "&ndash;";
                            if ($searchResults['editedon']) $editedon = date("d-m-Y", $searchResults['editedon']);
                            else $editedon = "&ndash;";
                        }

                        // set common values
                        $type = substr($section['name'], 0, strlen($section['name']));

                        // take care of different name IDs
                        if ($searchResults['templatename']) $searchResults['name'] = $searchResults['templatename'];

                        // output result

                        $output .= '
                        <tr>
                            <td class="no align_right">' . ($counter + 1) . '</td>
                            <td class="id align_right">' . $searchResults['id'] . '</td>
                            <td class="type">' . $type . '</td>
                            <td class="name"><a href="' . $urlEdit . '" title="[+lang.edit+]">' . $name . '</a></td>
                            <td class="description">' . $description . '</td>
                            <td class="createdon">' . $createdon . '</td>
                            <td class="editedon">' . $editedon . '</td>';
                        if ($search['string'] != "ALL") {
                            $output .= '<td class="found_in">' . substr($searchResults['found_in'], 2) . '</td>';
                        }
                        $output .= '
                            <td class="functions">
                                <a href="' . $urlEdit . '" title="[+lang.edit+]"><img src="media/style/[+options.theme+]/images/icons/save.png" alt="[+lang.edit+]"/></a>';
                        if ($section['id'] == "DocAndTVV") {
                            $output .= '
                                <a href="' . $urlInfo . '" title="[+lang.info+]"><img src="media/style' . $theme . '/images/tree/page-html.gif" alt="[+lang.info+]"/></a>
                                <a href="' . $urlOpen . '" title="[+lang.preview+]" target="_blank"><img src="media/style' . $theme . '/images/icons/page_white_magnify.png" alt="[+lang.preview+]"/></a>';
                        }
                        $output .= '
                            </td>
                        </tr>';

                        $pagetitle_class = "";
                        $counter++;
                    }
                }
            }

            // print table bottom
            $output .= '
                </tbody>
                </table>';

            if ($searchOptions['sortable_tables']) {
                $output .= '<script type="text/javascript">
                window.addEvent("domready", function () {
                    // activate sortable table
                    var CombinedView_Table = new sortableTable("' . $area . '");
                });
            </script>';
            }
            break;
    }

    // save search hits
    $results['hits'] = $counter;

    // save HTML output
    $results['html'] = $output;

    // save results arrays
    $results['array'] = $searchResultsArray;

    return $results;
}

function getDocAndTVVResults($search, $searchOptions)
{
    global $modx;

    // set SQL data selection
    $sqlSelection = "id, pagetitle, longtitle, published, hidemenu, description, alias, introtext, menutitle, content, createdon, editedon";

    // set search fields
    $searchFieldArray = explode(", ", $sqlSelection);

    // search in all defined fields
    $dbTable = $modx->getFullTableName("site_content");
    foreach ($searchFieldArray as $searchField) {
        // Filter: Check where to search in and where to skip
        if (!$searchOptions['id'] and $searchField == "id") continue;
        if (!$searchOptions['pagetitle'] and $searchField == "pagetitle") continue;
        if (!$searchOptions['longtitle'] and $searchField == "longtitle") continue;
        if (!$searchOptions['description'] and $searchField == "description") continue;
        if (!$searchOptions['alias'] and $searchField == "alias") continue;
        if (!$searchOptions['introtext'] and $searchField == "introtext") continue;
        if (!$searchOptions['menutitle'] and $searchField == "menutitle") continue;
        if (!$searchOptions['content'] and $searchField == "content") continue;

        // Filter: createdon and editedon are not searched directly
        if ($searchField == "createdon" or $searchField == "editedon") continue;

        // get SQL WHERE else continue
        if ($search['string'] != "ALL") {
            $sqlWhere = getSqlWhere($search['string'], $searchField, $searchOptions);
            if (!$sqlWhere) continue;
        }

        // complete SQL query
        $sql = "SELECT $sqlSelection FROM $dbTable $sqlWhere";

        // query DB via MODx DB API
        $result = $modx->db->query($sql);

        // get rows
        while ($row = $modx->fetchRow($result)) {
            // check created date range
            if ($row['createdon'] < $searchOptions['createdon_start_time'] or $row['createdon'] > $searchOptions['createdon_end_time']) continue;

            // check edited date range
            if ($row['editedon'] < $searchOptions['editedon_start_time'] or $row['editedon'] > $searchOptions['editedon_end_time']) continue;

            $id = $row['id'];

            // checkParents
            $parents = getAllParents($id);
            if (checkparents($search['parentsArray'], $parents)) {
                // save results in our results array
                $searchResultsArray[$id]['id'] = $id;
                $searchResultsArray[$id]['pagetitle'] = $row['pagetitle'];
                $searchResultsArray[$id]['longtitle'] = $row['longtitle'];
                $searchResultsArray[$id]['createdon'] = $row['createdon'];
                $searchResultsArray[$id]['editedon'] = $row['editedon'];
                $searchResultsArray[$id]['published'] = $row['published'];
                $searchResultsArray[$id]['hidemenu'] = $row['hidemenu'];
                $searchResultsArray[$id]['found_in'] .= ", " . ucfirst($searchField);
                $searchResultsArray[$id]['parentsArray'] = $parents;

                // replace
                if ($searchOptions['replace_mode'] and $searchField != "id") replace($search['string'], $searchOptions['replace'], $id, $searchField, $row[$searchField], $dbTable);
            }
        }

        // jump to next search array key
        next($search);
    }

    // search in TVs if required
    if ($searchOptions['tvs'] and $search['string'] != "ALL") {
        $tableTV_Names = $modx->getFullTableName("site_tmplvars");
        $tableTV_Content = $modx->getFullTableName("site_tmplvar_contentvalues");

        // get SQL WHERE
        $sqlWhere = getSqlWhere($search['string'], 'value', $searchOptions);

        // complete SQL query
        $sqlTVNames = "SELECT id, name FROM $tableTV_Names";
        $sqlTVContent = "SELECT contentid, value, tmplvarid, id FROM $tableTV_Content $sqlWhere";

        // query DB via MODx DB API
        $resultTVNames = $modx->db->query($sqlTVNames);
        $resultTVContent = $modx->db->query($sqlTVContent);

        // get rows TV names
        while ($row = $modx->fetchRow($resultTVNames)) {
            $id = $row['id'];
            $name = $row['name'];

            $TV_Names[$id]['id'] = $id;
            $TV_Names[$id]['name'] = $name;
        }

        // get rows TV content
        while ($row = $modx->fetchRow($resultTVContent)) {
            $id = $row['contentid'];

            // checkParents
            $parents = getAllParents($id);
            if (checkparents($search['parentsArray'], $parents)) {
                $TV_varID = $row['tmplvarid'];
                $TV_ID = $row['id'];
                $document = $modx->getDocument($id);

                // check created date range
                if ($document['createdon'] < $searchOptions['createdon_start_time'] or $document['createdon'] > $searchOptions['createdon_end_time']) continue;

                // check edited date range
                if ($document['editedon'] < $searchOptions['editedon_start_time'] or $document['editedon'] > $searchOptions['editedon_end_time']) continue;

                // save results in our results array
                $searchResultsArray[$id]['id'] = $id;
                $searchResultsArray[$id]['pagetitle'] = $document['pagetitle'];
                $searchResultsArray[$id]['longtitle'] = $document['longtitle'];
                $searchResultsArray[$id]['published'] = $document['published'];
                $searchResultsArray[$id]['hidemenu'] = $document['hidemenu'];
                $searchResultsArray[$id]['createdon'] = $document['createdon'];
                $searchResultsArray[$id]['editedon'] = $document['editedon'];
                $searchResultsArray[$id]['found_in'] .= ", tv_" . $TV_Names[$TV_varID]['name'];

                // replace
                if ($searchOptions['replace_mode'] and $searchField != "id") replace($search['string'], $searchOptions['replace'], $TV_ID, 'value', $row['value'], $tableTV_Content);
            }
        }
    }

    return $searchResultsArray;
}

function getResourcesResults($area, $search, $searchOptions)
{
    global $modx;

    // determine DB tables
    $dbShortTableArray['Templates'] = "site_templates";
    $dbShortTableArray['TVs'] = "site_tmplvars";
    $dbShortTableArray['Chunks'] = "site_htmlsnippets";
    $dbShortTableArray['Snippets'] = "site_snippets";
    $dbShortTableArray['Plugins'] = "site_plugins";
    $dbShortTableArray['Modules'] = "site_modules";
    $dbShortTable = $dbShortTableArray[$area];

    // determine SQL data selection
    $sqlSelectionArray['Templates'] = "id, templatename, description, content";
    $sqlSelectionArray['TVs'] = "id, name, description, type, elements, display, display_params, default_text";
    $sqlSelectionArray['Chunks'] = "id, name, description, snippet";
    $sqlSelectionArray['Snippets'] = "id, name, description, snippet, properties, moduleguid";
    $sqlSelectionArray['Plugins'] = "id, name, description, plugincode, properties, moduleguid";
    $sqlSelectionArray['Modules'] = "id, name, description, modulecode, properties, guid, resourcefile";
    $sqlSelection = $sqlSelectionArray[$area];

    // set search fields
    $searchFieldArray = explode(", ", $sqlSelection);

    // search in all defined fields
    $dbTable = $modx->getFullTableName($dbShortTable);
    foreach ($searchFieldArray as $searchField) {
        // Filter: Check where to search in and where to skip
        if (!$searchOptions['resources_id'] and $searchField == "id") continue;
        if (!$searchOptions['resources_name'] and ($searchField == "name" or $searchField == "templatename")) continue;
        if (!$searchOptions['resources_description'] and $searchField == "description") continue;
        if (!$searchOptions['resources_other'] and $searchField != "id" and $searchField != "name" and $searchField != "templatename" and $searchField != "description") continue;

        // get SQL WHERE else continue
        if ($search['string'] != "ALL") {
            $sqlWhere = getSqlWhere($search['string'], $searchField, $searchOptions);
            if (!$sqlWhere) continue;
        }

        // complete SQL query
        $sql = "SELECT $sqlSelection FROM $dbTable $sqlWhere";

        // query DB via MODx DB API
        $result = $modx->db->query($sql);

        // get rows
        while ($row = $modx->fetchRow($result)) {
            $id = $row['id'];

            // save results in our results array
            foreach ($searchFieldArray as $searchFieldStore) {
                $searchResultsArray[$id][$searchFieldStore] = $row[$searchFieldStore];
            }
            $searchResultsArray[$id]['found_in'] .= ", " . ucfirst($searchField);

            // replace
            if ($searchOptions['replace_mode'] and $searchField != "id") replace($search['string'], $searchOptions['replace'], $id, $searchField, $row[$searchField], $dbTable);

            // jump to next row array key
            next($row);
        }
    }

    return $searchResultsArray;
}

function replace($searchString, $replaceString, $id, $searchFieldName, $searchFieldString, $table)
{
    global $modx, $searchOptions;

    // take care of case _in_sensitive searches and do the PHP string replace
    if ($searchOptions['case_sensitive']) $searchFieldStringReplaced = str_replace($searchString, $replaceString, $searchFieldString);
    else $searchFieldStringReplaced = str_ireplace($searchString, $replaceString, $searchFieldString);

    $searchFieldStringReplaced = mysql_real_escape_string($searchFieldStringReplaced);

    // update Database
    $replaceResult = $modx->db->update($searchFieldName . ' = "' . $searchFieldStringReplaced . '"', $table, 'id = "' . $id . '"');

    return $replaceResult;
}

function getSqlWhere($searchString, $searchField, $searchOptions)
{
    // prepare SQL Query
    $sqlWhere = mysql_real_escape_string($searchString);

    // take care of ID search
    if ($searchField == 'id' and is_numeric($searchString)) return 'WHERE ' . $searchField . '=' . $searchString;
    else if ($searchField == 'id') return false;

    // take care of regular expressions
    if ($searchOptions['regular_expression']) return 'WHERE ' . $searchField . ' REGEXP "' . $sqlWhere . '"';

    // take care of case sensitive search
    if ($searchOptions['case_sensitive']) $caseSensitiveSQL = "BINARY";
    else $caseSensitiveSQL = "";

    // take care of AND NOT
    $sqlWhere = str_replace(" AND NOT ", " NOT ", $sqlWhere);

    // put together the SQL
    $sqlWhere = ' ' . $caseSensitiveSQL . ' ' . $searchField . ' LIKE "%' . $sqlWhere . '%"';

    // take care of ANDs
    $sqlWhere = str_replace(' AND ', '%" AND $searchField LIKE "%', $sqlWhere);
    // take care of ANDs some possible problems with AND
    $sqlWhere = str_replace('"%AND ', '"%', $sqlWhere);
    $sqlWhere = str_replace('"% AND ', '"%', $sqlWhere);
    $sqlWhere = str_replace(' AND%"', '%"', $sqlWhere);
    $sqlWhere = str_replace(' AND %"', '%"', $sqlWhere);

    // take care of ORs
    $sqlWhere = str_replace(' OR ', '%" OR $searchField LIKE "%', $sqlWhere);
    // take care of ANDs some possible problems with OR
    $sqlWhere = str_replace('"%OR ', '"%', $sqlWhere);
    $sqlWhere = str_replace('"% OR ', '"%', $sqlWhere);
    $sqlWhere = str_replace(' OR%"', '%"', $sqlWhere);
    $sqlWhere = str_replace(' OR %"', '%"', $sqlWhere);

    // take care of NOTs
    $sqlWhere = str_replace(' NOT ', '%" AND NOT $searchField LIKE "%', $sqlWhere);

    // take care of the results limit
    if ($searchOptions['entries_50']) {
        $limit = ' LIMIT 50';
    } else if ($searchOptions['entries_100']) {
        $limit = ' LIMIT 100';
    }
    $sqlWhere .= $limit;

    return 'WHERE ' . $sqlWhere;
}

function checkparents($searchParentsArray, $parents)
{
    // return if there are no parents
    if (!$searchParentsArray) return true;

    // check if the result document is within the searched parents and if so return true
    foreach ($searchParentsArray as $searchParent) if (isset($parents[$searchParent])) return true;

    // otherwise ...
    return false;
}

function getAllParents($id)
{
    global $modx;

    // go up document tree
    $counter = 1;
    while ($document = $modx->getParent($id)) {
        $id = $document['id'];
        $parents[$id] = $id;
    }

    // don't forget the root parent
    $parents[0] = 0;

    return $parents;
}

function printDocumentBranch($parentsArray)
{
    global $modx;

    // print Site
    $output = '
    &gt; <a href="index.php?a=2">' . $modx->config['site_name'] . ' (0)</a>';

    if (is_array($parentsArray)) {
        // reverse branch array create output
        $parentsArray = array_reverse($parentsArray);

        foreach ($parentsArray as $id) {
            $document = $modx->getDocument($id);
            $urlEdit = "index.php?a=27&amp;id=" . $id;

            // print parents
            if ($id > 0) {
                $output .= '
                &gt; <a href="' . $urlEdit . '" title="[+lang.edit+]">' . $document['pagetitle'] . ' (' . $id . ')</a>';
            }
        }
    }

    return $output;
}

function printHistory($type)
{
    // print select box start
    $output = array('<select id="' . $type . '_history" onchange="$(\'' . $type . 'string\').value=$(\'' . $type . '_history\').value;triggerSubmitButtons();">');

    if (isset($_SESSION[$type . '_history'])) {
        // get and unique history
        $historyArray = explode(";", $_SESSION[$type . '_history']);
        $historyArray = array_unique($historyArray);

        // build new history
        $newhistoryString = '';
        foreach ($historyArray as $entry) {
            if ($entry == "") {
                continue;
            };
            $newhistoryString .= ";" . $entry;
        }

        // save new history string
        $_SESSION[$type . '_history'] = $newhistoryString;

        // output
        $output[] = '<option value="">[+lang.' . $type . '_history_filled+]</option>';

        foreach ($historyArray as $entry) {
            $entry = htmlentities($entry, false, "UTF-8");
            if ($entry == "") {
                continue;
            };
            $output[] = '<option value="' . $entry . '">' . $entry . '</option>';
        }
    } else {
        $output[] = '<option value="">[+lang.' . $type . '_history_empty+]</option>';
    }

    // print select box end
    $output[] = '</select>';
    return implode("\n", $output);
}

?>