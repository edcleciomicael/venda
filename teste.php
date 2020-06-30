<?php
$xml_doc = new DomDocument;
$xml_doc->preserveWhiteSpace = false;
$xml_doc->formatOutput = true;
$xml_doc->load('menu.xml');

foreach ($xml_doc->getElementsByTagName('menuitem') as $node)
{
    $label = $node->getAttribute('label');
    echo $label . "\n";
    foreach ($node->childNodes as $subnode)
    {
        if ($subnode instanceof DOMElement)
        {
            if ($subnode->tagName == 'menu')
            {
                echo "  ** menu \n";
                if ($label == 'Common pages')
                {
                    $node = $xml_doc->createElement("menuitem");
                    $node->setAttribute('label', 'xxx');
    
                    $icon = $xml_doc->createElement("icon");
                    $action = $xml_doc->createElement("action");
                    $icon->nodeValue = 'fa:save';
                    $action->nodeValue = 'clicaction';
                    $subnode->appendChild($node);
                    $node->appendChild($icon);
                    $node->appendChild($action);
                }
            }
        }
    }
}

var_dump($xml_doc->save('x.xml'));