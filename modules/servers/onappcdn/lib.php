<?php

/**
 * Create base tables structure
 */

function onappcdn_createTables() {
    global $_LANG, $whmcsmysql;

    define ("CREATE_TABLE_CDNCLIENTS",
"CREATE TABLE IF NOT EXISTS `tblonappcdnclients` (
  `service_id` int(11) NOT NULL,
  `onapp_user_id` int(11) NOT NULL,
  `password` text NOT NULL,
  `email` text NOT NULL,
  PRIMARY KEY (`service_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB;");


    if ( ! full_query( CREATE_TABLE_CDNCLIENTS, $whmcsmysql ) ) {
        return array(
            "error" => sprintf($_LANG["onappcdnerrtablecreate"], 'onappcdnclients')
        );
    };

    return;
}

/**
 * Load $_LANG from language file
 */
function load_language() {
    global $_LANG;

    $dir = dirname(__FILE__).'/lang/';

    if(! file_exists($dir))
       return;

    $dh = opendir ($dir);

    while (false !== $file2 = readdir ($dh)) {
        if (!is_dir ('' . 'lang/' . $file2) ) {
            $pieces = explode ('.', $file2);
            if ($pieces[1] == 'txt') {
                $arrayoflanguagefiles[] = $pieces[0];
                continue;
            }
            continue;
        }
    };

    closedir ($dh);

    $language = $_SESSION['Language'];

    if( ! in_array ($language, $arrayoflanguagefiles) ) {
        $language =  "English";
    }

    if( file_exists( dirname(__FILE__) . "/lang/$language.txt" ) ) {
        ob_start ();
        include dirname(__FILE__) . "/lang/$language.txt";
        $templang = ob_get_contents ();
        ob_end_clean ();
        eval ($templang);
    }
}

?>
