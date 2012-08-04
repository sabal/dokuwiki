<?php

function search_springnote(&$data, $base, $func, $opts, $dir, $lvl, $sort) {
    global $conf;

    // XXX: assumes $data is empty

    $xml = new XMLReader;
    $xml->open($conf['metadir'].'/_tree.xml');
    while ($xml->read()) {
        if ($xml->nodeType !== XMLReader::ELEMENT)
            continue;
        if ($xml->name !== 'page')
            continue;

        $item = array();
        $item['id'] = $xml->getAttribute('id');
        $item['ns'] = $xml->getAttribute('path');
        $item['title'] = $xml->getAttribute('title');
        if ($item['ns'] === '')
            $item['level'] = 1;
        else
            $item['level'] = 1 + sizeof(explode(':', $item['ns']));
        $item['type'] = 'f';

        $data[] = $item;
    }
}

// FIXME: use more robust parser

function _replace_entity($xhtml) {
    $trans = array_map('utf8_encode', array_flip(
        array_diff(get_html_translation_table(HTML_ENTITIES),
        get_html_translation_table(HTML_SPECIALCHARS))));
    return strtr($xhtml, $trans);
}


function extract_body($content) {
    $xml = new XMLReader;
    $xml->XML(_replace_entity($content));
    while ($xml->read() && $xml->name !== 'body');
    return $xml->readInnerXML();
}


function replace_body($xhtml, $body) {
    return strstr($xhtml, '<body>', true).'<body>'.
        $body.'</body>'.strrchr($xhtml, '</body>');
}


function render_metadata($filename, &$current, &$persistent) {
    if(!$persistent['date']['created']) {
        $persistent['date']['created'] = filectime($filename);
    }
    if(!isset($persistent['user'])) {
        $persistent['user'] = '';
    }
    if(!isset($persistent['creator'])) {
        $persistent['creator'] = '';
    }
    $current = $persistent;

    $xml = new XMLReader;
    $xml->XML(_replace_entity(file_get_contents($filename)));
    while ($xml->read() && $xml->name !== 'title');
    $current['title'] = $xml->readString();
}

?>
