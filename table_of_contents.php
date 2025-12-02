<?php

/**
 * Функция generateTableOfContents генерирует оглавление из заголовков h2 и h3 в HTML-контенте
 * 
 * Функция анализирует HTML-контент, находит все заголовки h2 и h3,
 * создает для них якорные ссылки и формирует структурированное оглавление
 * @param string $content Исходный HTML-контент с заголовками
 * @return array Массив с двумя элементами: 'headers' (оглавление) и 'text' (модифицированный контент)
 * Источник: https://kompaskreditov.ru/
 */

function generateTableOfContents($content)
{
    $toc = '';
    $h2_counter = 0;
    $h3_counter = 0;
    $toc_items = [];

    $pattern = '/<h(2|3)([^>]*)>(.*?)<\/h\1>/is';

    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $tag = $match[1];
        $attrs = $match[2];
        $text = trim(strip_tags($match[3]));

        if ($tag == '2') {
            $h2_counter++;
            $h3_counter = 0;
            $anchor = 'h2_' . $h2_counter;

            // Добавляем пункт в оглавление
            $toc_items[] = '<li><a href="#' . $anchor . '">' . htmlspecialchars($text) . '</a>';

            // Заменяем оригинальный h2 в контенте, добавляя id
            $replacement = '<h2 id="' . $anchor . '"' . $attrs . '>' . $text . '</h2>';
            $content = str_replace($match[0], $replacement, $content);
        } elseif ($tag == '3') {
            $h3_counter++;
            $anchor = 'h3_' . $h2_counter . '_' . $h3_counter;

            // Создаем вложенный список для h3
            if (!isset($toc_items[$h2_counter - 1]) || strpos($toc_items[$h2_counter - 1], '<ol>') === false) {
                $toc_items[$h2_counter - 1] .= '<ol>';
            }

            $toc_items[$h2_counter - 1] .= '<li><a href="#' . $anchor . '">' . htmlspecialchars($text) . '</a></li>';

            // Заменяем оригинальный h3 в контенте, добавляя id
            $replacement = '<h3 id="' . $anchor . '"' . $attrs . '>' . $text . '</h3>';
            $content = str_replace($match[0], $replacement, $content);
        }
    }

    // Закрываем теги <ol> и <li> где нужно
    foreach ($toc_items as &$item) {
        if (strpos($item, '<ol>') !== false) {
            $item .= '</ol></li>';
        } else {
            $item .= '</li>';
        }
    }

    $toc = '<h2>Содержание</h2><ol>' . implode("\n", $toc_items) . '</ol>';

    return [
        'headers' => $toc,
        'text' => $content,
    ];
}

?>
