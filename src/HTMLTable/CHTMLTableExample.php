<?php

namespace Anax\HTMLTable;

class CHTMLTableExample extends \Guer\HTMLTable\CHTMLTable
{
    /**
     * Constructor
     *
     */
    public function __construct($data)
    {

        parent::__construct(
            [
                'caption'   => 'En tabell genererad av CHTMLTable'
            ],
            $data,
            [
                'column1' => [
                    'title' => 'Table Header 1',
                ],
                'column2' => [
                ],
                'column4' => [
                    'title'     => 'Table Header 4',
                    'function'  => function ($link) {
                        return '<a href="'. $link . '">' . "Google" . '</a>';
                    }
                ],
                'column6' => [
                    'title'     => 'Table Header 6',
                    'function'  => function ($isPresent) {
                        return empty($isPresent) ? 'Not present' :  'Present';
                    }
                ],
                'tablefoot1' => [
                    'type'      => 'footer',
                    'colspan'   => '2',
                    'value'     => 'Footer Cell 1',
                ],
                'tablefoot2' => [
                    'type'      => 'footer',
                    'colspan'   => 2,
                    'function'  => function () {
                        return '<a href="https://www.google.se"><i class="fa fa-external-link" aria-hidden="true"></i></a>';
                    }
                ],
            ]
        );
    }
}
