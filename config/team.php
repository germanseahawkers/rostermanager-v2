<?php

declare(strict_types=1);

return [
    'name' => getenv('TEAM_NAME') ?: 'Seattle Seahawks',
    'nickname' => getenv('TEAM_NICKNAME') ?: 'Seahawks',
    'city' => getenv('TEAM_CITY') ?: 'Seattle',
    'slug' => getenv('TEAM_SLUG') ?: 'seahawks',
    'logo_path' => getenv('TEAM_LOGO_PATH') ?: '',
    'tagline' => getenv('TEAM_TAGLINE') ?: 'Build your own 53-man roster before cutdown day.',
    'colors' => [
        'primary' => getenv('TEAM_COLOR_PRIMARY') ?: '#0b2545',
        'secondary' => getenv('TEAM_COLOR_SECONDARY') ?: '#7ac143',
        'surface' => getenv('TEAM_COLOR_SURFACE') ?: '#eef3f8',
        'surface_alt' => getenv('TEAM_COLOR_SURFACE_ALT') ?: '#d7e4f0',
        'text' => getenv('TEAM_COLOR_TEXT') ?: '#f7fbff',
        'ink' => getenv('TEAM_COLOR_INK') ?: '#142033',
        'muted' => getenv('TEAM_COLOR_MUTED') ?: '#60708a',
        'line' => getenv('TEAM_COLOR_LINE') ?: '#b5c5d6',
    ],
    'simulator' => [
        'roster_limit' => (int) (getenv('SIMULATOR_ROSTER_LIMIT') ?: 53),
        'position_groups' => [
            ['key' => 'QB', 'section' => 'offense', 'label_de' => 'Quarterback', 'label_en' => 'Quarterback', 'aliases' => ['QB']],
            ['key' => 'RB', 'section' => 'offense', 'label_de' => 'Running Back', 'label_en' => 'Running Back', 'aliases' => ['RB', 'FB']],
            ['key' => 'WR', 'section' => 'offense', 'label_de' => 'Wide Receiver', 'label_en' => 'Wide Receiver', 'aliases' => ['WR']],
            ['key' => 'TE', 'section' => 'offense', 'label_de' => 'Tight End', 'label_en' => 'Tight End', 'aliases' => ['TE']],
            ['key' => 'OL', 'section' => 'offense', 'label_de' => 'Offensive Line', 'label_en' => 'Offensive Line', 'aliases' => ['OL', 'OT', 'OG', 'C', 'G', 'T']],
            ['key' => 'DL', 'section' => 'defense', 'label_de' => 'Defensive Line', 'label_en' => 'Defensive Line', 'aliases' => ['DL', 'DE', 'DT', 'NT']],
            ['key' => 'LB', 'section' => 'defense', 'label_de' => 'Linebacker', 'label_en' => 'Linebacker', 'aliases' => ['LB', 'ILB', 'OLB', 'EDGE']],
            ['key' => 'CB', 'section' => 'defense', 'label_de' => 'Cornerback', 'label_en' => 'Cornerback', 'aliases' => ['CB']],
            ['key' => 'S', 'section' => 'defense', 'label_de' => 'Safety', 'label_en' => 'Safety', 'aliases' => ['S', 'FS', 'SS', 'DB']],
            ['key' => 'K', 'section' => 'special_teams', 'label_de' => 'Kicker', 'label_en' => 'Kicker', 'aliases' => ['K']],
            ['key' => 'P', 'section' => 'special_teams', 'label_de' => 'Punter', 'label_en' => 'Punter', 'aliases' => ['P']],
            ['key' => 'LS', 'section' => 'special_teams', 'label_de' => 'Long Snapper', 'label_en' => 'Long Snapper', 'aliases' => ['LS']],
        ],
    ],
];
