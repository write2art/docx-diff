<?php
require_once './vendor/autoload.php';

function getTextFromContainerElements($container) {
    $text = '';
    foreach ($container->getElements() as $element) {
        switch (get_class($element)) {
            case 'PhpOffice\PhpWord\Element\Text':
                $change = $element->getTrackChange();
                if ($change && $change->getChangeType() !== 'DELETED' || !$change) {
                    $text .= $element->getText();
                }
                break;
            case 'PhpOffice\PhpWord\Element\TextBreak':
                $text .= "\n";
                break;
            case $element instanceOf \PhpOffice\PhpWord\Element\AbstractContainer:
                return getTextFromContainerElements($element);
                break;
            default:
                var_dump(get_class($element));
        }
    }
    return $text;
}

function getTextFromTable($table) {
    $text = [];
    foreach ($table->getRows() as $row) {
        foreach ($row->getCells() as $cell) {
            $text = array_merge($text, explode("\n", getTextFromContainerElements($cell)));
        }
    }
    return $text;
}

function extractText($file) {
    $phpWord = \PhpOffice\PhpWord\IOFactory::load($file);

    var_dump($phpWord);

    $text = [];
    foreach ($phpWord->getSections() as $section) {
        foreach ($section->getElements() as $element) {
            switch (get_class($element)) {
                case $element instanceOf \PhpOffice\PhpWord\Element\AbstractContainer:
                    $text = array_merge($text, explode("\n", getTextFromContainerElements($element)));
                    break;
                case 'PhpOffice\PhpWord\Element\TextBreak':
                    $text[] = '';
                    break;
                case 'PhpOffice\PhpWord\Element\Table':
                    $text = array_merge($text, getTextFromTable($element));
                    break;

                default:
                    var_dump(get_class($element));
            }
        }
    }
    return array_filter($text);
}

if (!isset($argv[1]) || !isset($argv[2])) {
    echo "Укажите 2 файла - исходник и изменённый - php index.php orig.docx diff.docx" . PHP_EOL;
    exit();
}

$orig = extractText($argv[1]);
$diff = extractText($argv[2]);

//var_dump($orig);
//var_dump($diff);

foreach ($orig as $keyOrig => $valueOrig) {
    foreach ($diff as $key => $value) {

        //var_dump(trim($valueOrig));
        //var_dump(trim($value));

        if (trim($valueOrig) === trim($value)) {
           $orig[$keyOrig] = null;
           $diff[$key] = null;
           break;
        }
    }
}

//print_r(array_filter($orig));
//print_r(array_filter($diff));