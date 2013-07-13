<?php

/*
 * The function gets the file for the site language and adds marks to each
 * string in each array. Saves the changes to the language file
 *
 */

function add_identifier_to_lanugage_file() {
    global $_TABLES, $_CONF;
    require_once '/../../public_html/lib-common.php';
    require_once ($_CONF['path_system'] . 'lib-database.php');

    $file = file($_CONF['path_language'] . $_CONF['language'] . '.php');

    //the new content of the file is saved here
    $output = "";
    //keeps track of the current LANG array while looping throught the file lines
    $current_array = "";
    $db_entries = array();
    $current_object = array();
    foreach ($file as $line_num => $line) {


        $pivot = strpos($line, '=>');


        if ((strpos($line, "LANG") !== false || strpos($line, "MESSAGE")) && strpos($line, "= array") !== false) {
            $current_array = substr($line, 0, strpos($line, " ="));
            $current_array = str_replace(" ", "", $current_array);
            $current_array = str_replace("'", "", $current_array);
        }

        //this will check if the line is a key - value pair
        if ($pivot !== false) {
            //part 1 is the key, part 2 is the value
            $part1 = substr($line, 0, $pivot);
            $part2 = substr($line, $pivot + 2);

            if (strlen($part2) > 5) {
                if (strpos($line, "_-start_") == false) {
                    //extract the actual key from part1
                    $array_index = substr($part1, 1, -1);
                    $array_index = str_replace("'", "", $array_index);
                    $array_index = str_replace(" ", "", $array_index);

                    /* additional data, it is faster to add it here
                     * then to search for it during runtime
                     */
                    $meta_data = "||array__" . str_replace("$", "", $current_array) . "index__" . $array_index . "||";

                    //create a object for the array element
                    $current_object['line'] = substr($part2, 1, strlen($part2) - 3);
                    //this will host the html and php tags
                    $current_object['tags'] = array();
                    $current_object['line'] = remove_tags($current_object['line'], $current_object['tags']);
                    //encode tags for saving to db
                    $current_object['tags'] = json_encode($current_object['tags']);
                    $current_object['array'] = str_replace("$", "", $current_array);
                    $current_object['index'] = $array_index;


                    array_push($db_entries, $current_object);


                    $needle1 = "'";
                    $needle2 = "\""; //in a few places language files have " instead of '



                    $replace1 = "'_-start_" . $meta_data;
                    $replace2 = "\"_-start_" . $meta_data;
                    $replace3 = "_-end_'";
                    $replace4 = "_-end_\"";

                    $end_needle1 = "'\n";
                    $end_needle2 = "\"\n";

                    $end_replace1 = "_-end_'\n";
                    $end_replace2 = "_-end_\"\n";


                    /* the last array element does not have a ',' to be identified by
                     * so I identify it with the fact that in the closing bracket is in the next line
                     */

                    if (strpos($part2, "'") == 1)
                        $part2 = str_replace_limit($needle1, $replace1, $part2, $count, 1);
                    else
                        $part2 = str_replace_limit($needle2, $replace2, $part2, $count, 1);

                    if (strpos($part2, "_-start_") !== false) {
                        if (strpos($part2, "'") == 1)
                            $part2 = str_lreplace($needle1, $replace3, $part2);
                        else
                            $part2 = str_lreplace($needle2, $replace4, $part2);
                    }


                    $line = "";
                    //reasemble the line
                    $line = $part1 . "=>" . $part2;
                }
            }
        }
        $output .=$line;
    }

    //save the edited arrays to the database
    foreach ($db_entries as $key => $value) {
        $value['line'] = mysql_real_escape_string($value['line']);

        $value['tags'] = mysql_real_escape_string($value['tags']);

        DB_query("INSERT INTO {$_TABLES['originals']} (`id`, `language`, `plugin_name`, `language_array`, `array_index`, `string`, `tags`)
		VALUES ('', '{$_CONF['language']}', 'core', '{$value['array']}', '{$value['index']}' , '{$value['line']}', '{$value['tags']}' ) ");
    }

    //save the new file content
    file_put_contents($_CONF['path_language'] . $_CONF['language'] . '.php', $output);
}

/**
 * @param string $line the array element from which the tags are removed
 * @param array  &$tags after the tags are removed they are keept here for later assembly
 */
function remove_tags($line, &$tags) {

    $tags['html'] = array();
    $tags['vars'] = array();

    $variables = array("%s", "%t", "%d", "%n", "%i", "%t", "%s", "\$_DB_mysqldump_path", "\$_CONF['backup_path']", "\$_CONF['commentspeedlimit']", "\$_CONF['site_admin_url']", "\$_CONF['site_name']", "\$_CONF['site_url']", "\$_CONF['speedlimit']", "\$_USER['username']", "\$failures", "\$from", "\$fromemail", "\$qid", "\$shortmsg", "\$successes", "\$topic", "\$type");

    foreach ($variables as $key => $value) {
        while (strpos($line, $value) !== false) {

            array_push($tags['vars'], $value);
            $line = str_replace($value, "VAR", $line);
        }
    }

    remove_standard($line, "<", ">", $tags['html']);


    while (strpos($line, "TAG") !== false) {
        $line = str_replace("TAG", "<tag>", $line);
    }

    while (strpos($line, "VAR") !== false) {
        $line = str_replace("VAR", "<var>", $line);
    }
    return $line;
}

/**
 * @param $line string the string from which tags are to be removed
 * @param $key_begin string the begining of the tag
 * @param $key_end string the end of the tag
 * @param $ar
 */
function remove_standard(&$line, $key_begin, $key_end, &$array) {
    while (strpos($line, $key_begin) !== false && strpos($line, $key_end) !== false) {

        $begin = strpos($line, $key_begin);
        $length = strpos($line, $key_end) + 1 - $begin;

        $tag = substr($line, $begin, $length);
        $line = str_replace($tag, "TAG", $line);

        array_push($array, $tag);
    }
}

function str_lreplace($search, $replace, $subject) {
    $pos = strrpos($subject, $search);

    if ($pos !== false) {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}

/*
 * the function will upon uninstall of the plugin remove all changes made to the language file
 */

function remove_identifier_to_lanugage_file() {
    global $_CONF;
    require_once $_CONF['path'] . "/public_html/lib-common.php";

    $file = file($_CONF['path_language'] . $_CONF['language'] . '.php');
    $output = "";
    $current_array = "";

    foreach ($file as $line_num => $line) {
        $pivot = strpos($line, '=>');
        $part = substr($line, $pivot + 2);

        if ($pivot !== false && strlen($part) > 5) {

            $begin = strpos($line, "||");
            if ($begin !== false)
                $end = strpos($line, "||", $begin + 3) + 2;
            else
                $end = false;

            if ($begin !== false && $end !== false) {
                $substring = substr($line, $begin, ($end - $begin));

                $line = str_replace($substring, "", $line);
                $line = str_replace("_-start_", "", $line);
                $line = str_replace("_-end_", "", $line);
            }
        }
        $output .=$line;
    }
    file_put_contents($_CONF['path_language'] . $_CONF['language'] . '.php', $output);
}

//Following code is taken from stackoverflow, provided by user bfrohs - http://stackoverflow.com/users/526741/bfrohs
/**
 * Checks if $string is a valid integer. Integers provided as strings (e.g. '2' vs 2)
 * are also supported.
 * @param mixed $string
 * @return bool Returns boolean TRUE if string is a valid integer, or FALSE if it is not 
 */
function valid_integer($string) {
    // 1. Cast as string (in case integer is provided)
    // 1. Convert the string to an integer and back to a string
    // 2. Check if identical (note: 'identical', NOT just 'equal')
    // Note: TRUE, FALSE, and NULL $string values all return FALSE
    $string = strval($string);
    return ($string === strval(intval($string)));
}

/**
 * Replace $limit occurences of the search string with the replacement string
 * @param mixed $search The value being searched for, otherwise known as the needle. An
 * array may be used to designate multiple needles.
 * @param mixed $replace The replacement value that replaces found search values. An
 * array may be used to designate multiple replacements.
 * @param mixed $subject The string or array being searched and replaced on, otherwise
 * known as the haystack. If subject is an array, then the search and replace is
 * performed with every entry of subject, and the return value is an array as well. 
 * @param string $count If passed, this will be set to the number of replacements
 * performed.
 * @param int $limit The maximum possible replacements for each pattern in each subject
 * string. Defaults to -1 (no limit).
 * @return string This function returns a string with the replaced values.
 */
function str_replace_limit(
$search, $replace, $subject, &$count, $limit = -1
) {

    // Set some defaults
    $count = 0;

    // Invalid $limit provided. Throw a warning.
    if (!valid_integer($limit)) {
        $backtrace = debug_backtrace();
        trigger_error('Invalid $limit `' . $limit . '` provided to ' . __function__ . '() in ' .
                '`' . $backtrace[0]['file'] . '` on line ' . $backtrace[0]['line'] . '. Expecting an ' .
                'integer', E_USER_WARNING);
        return $subject;
    }

    // Invalid $limit provided. Throw a warning.
    if ($limit < -1) {
        $backtrace = debug_backtrace();
        trigger_error('Invalid $limit `' . $limit . '` provided to ' . __function__ . '() in ' .
                '`' . $backtrace[0]['file'] . '` on line ' . $backtrace[0]['line'] . '. Expecting -1 or ' .
                'a positive integer', E_USER_WARNING);
        return $subject;
    }

    // No replacements necessary. Throw a notice as this was most likely not the intended
    // use. And, if it was (e.g. part of a loop, setting $limit dynamically), it can be
    // worked around by simply checking to see if $limit===0, and if it does, skip the
    // function call (and set $count to 0, if applicable).
    if ($limit === 0) {
        $backtrace = debug_backtrace();
        trigger_error('Invalid $limit `' . $limit . '` provided to ' . __function__ . '() in ' .
                '`' . $backtrace[0]['file'] . '` on line ' . $backtrace[0]['line'] . '. Expecting -1 or ' .
                'a positive integer', E_USER_NOTICE);
        return $subject;
    }

    // Use str_replace() whenever possible (for performance reasons)
    if ($limit === -1) {
        return str_replace($search, $replace, $subject, $count);
    }

    if (is_array($subject)) {

        // Loop through $subject values and call this function for each one.
        foreach ($subject as $key => $this_subject) {

            // Skip values that are arrays (to match str_replace()).
            if (!is_array($this_subject)) {

                // Call this function again for
                $this_function = __FUNCTION__;
                $subject[$key] = $this_function(
                        $search, $replace, $this_subject, $this_count, $limit
                );

                // Adjust $count
                $count += $this_count;

                // Adjust $limit, if not -1
                if ($limit != -1) {
                    $limit -= $this_count;
                }

                // Reached $limit, return $subject
                if ($limit === 0) {
                    return $subject;
                }
            }
        }

        return $subject;
    } elseif (is_array($search)) {
        // Only treat $replace as an array if $search is also an array (to match str_replace())
        // Clear keys of $search (to match str_replace()).
        $search = array_values($search);

        // Clear keys of $replace, if applicable (to match str_replace()).
        if (is_array($replace)) {
            $replace = array_values($replace);
        }

        // Loop through $search array.
        foreach ($search as $key => $this_search) {

            // Don't support multi-dimensional arrays (to match str_replace()).
            $this_search = strval($this_search);

            // If $replace is an array, use the value of $replace[$key] as the replacement. If
            // $replace[$key] doesn't exist, just an empty string (to match str_replace()).
            if (is_array($replace)) {
                if (array_key_exists($key, $replace)) {
                    $this_replace = strval($replace[$key]);
                } else {
                    $this_replace = '';
                }
            } else {
                $this_replace = strval($replace);
            }

            // Call this function again for
            $this_function = __FUNCTION__;
            $subject = $this_function(
                    $this_search, $this_replace, $subject, $this_count, $limit
            );

            // Adjust $count
            $count += $this_count;

            // Adjust $limit, if not -1
            if ($limit != -1) {
                $limit -= $this_count;
            }

            // Reached $limit, return $subject
            if ($limit === 0) {
                return $subject;
            }
        }

        return $subject;
    } else {
        $search = strval($search);
        $replace = strval($replace);

        // Get position of first $search
        $pos = strpos($subject, $search);

        // Return $subject if $search cannot be found
        if ($pos === false) {
            return $subject;
        }

        // Get length of $search, to make proper replacement later on
        $search_len = strlen($search);

        // Loop until $search can no longer be found, or $limit is reached
        for ($i = 0; (($i < $limit) || ($limit === -1)); $i++) {

            // Replace 
            $subject = substr_replace($subject, $replace, $pos, $search_len);

            // Increase $count
            $count++;

            // Get location of next $search
            $pos = strpos($subject, $search);

            // Break out of loop if $needle
            if ($pos === false) {
                break;
            }
        }

        // Return new $subject
        return $subject;
    }
}

?>