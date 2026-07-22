<?php

namespace App\Helpers;

class WhatsappCountryCodes
{
    /**
     * Curated list of countries for the WhatsApp link form. Sorted so the
     * Spanish-speaking world plus the largest HidenCloud markets sit near the
     * top of the dropdown, with the long tail in alphabetical order after.
     *
     * Each entry: dial = E.164 country code (with leading +), name = English
     * label, flag = unicode flag emoji.
     */
    public static function list(): array
    {
        return [
            ['dial' => '+34',  'name' => 'Spain',          'flag' => '🇪🇸'],
            ['dial' => '+1',   'name' => 'United States / Canada', 'flag' => '🇺🇸'],
            ['dial' => '+44',  'name' => 'United Kingdom', 'flag' => '🇬🇧'],
            ['dial' => '+33',  'name' => 'France',         'flag' => '🇫🇷'],
            ['dial' => '+49',  'name' => 'Germany',        'flag' => '🇩🇪'],
            ['dial' => '+39',  'name' => 'Italy',          'flag' => '🇮🇹'],
            ['dial' => '+351', 'name' => 'Portugal',       'flag' => '🇵🇹'],
            ['dial' => '+31',  'name' => 'Netherlands',    'flag' => '🇳🇱'],
            ['dial' => '+52',  'name' => 'Mexico',         'flag' => '🇲🇽'],
            ['dial' => '+54',  'name' => 'Argentina',      'flag' => '🇦🇷'],
            ['dial' => '+56',  'name' => 'Chile',          'flag' => '🇨🇱'],
            ['dial' => '+57',  'name' => 'Colombia',       'flag' => '🇨🇴'],
            ['dial' => '+58',  'name' => 'Venezuela',      'flag' => '🇻🇪'],
            ['dial' => '+51',  'name' => 'Peru',           'flag' => '🇵🇪'],
            ['dial' => '+593', 'name' => 'Ecuador',        'flag' => '🇪🇨'],
            ['dial' => '+591', 'name' => 'Bolivia',        'flag' => '🇧🇴'],
            ['dial' => '+598', 'name' => 'Uruguay',        'flag' => '🇺🇾'],
            ['dial' => '+595', 'name' => 'Paraguay',       'flag' => '🇵🇾'],
            ['dial' => '+502', 'name' => 'Guatemala',      'flag' => '🇬🇹'],
            ['dial' => '+503', 'name' => 'El Salvador',    'flag' => '🇸🇻'],
            ['dial' => '+504', 'name' => 'Honduras',       'flag' => '🇭🇳'],
            ['dial' => '+505', 'name' => 'Nicaragua',      'flag' => '🇳🇮'],
            ['dial' => '+506', 'name' => 'Costa Rica',     'flag' => '🇨🇷'],
            ['dial' => '+507', 'name' => 'Panama',         'flag' => '🇵🇦'],
            ['dial' => '+53',  'name' => 'Cuba',           'flag' => '🇨🇺'],
            ['dial' => '+1809','name' => 'Dominican Republic', 'flag' => '🇩🇴'],
            ['dial' => '+1787','name' => 'Puerto Rico',    'flag' => '🇵🇷'],
            ['dial' => '+55',  'name' => 'Brazil',         'flag' => '🇧🇷'],
            ['dial' => '+61',  'name' => 'Australia',      'flag' => '🇦🇺'],
            ['dial' => '+64',  'name' => 'New Zealand',    'flag' => '🇳🇿'],
            ['dial' => '+27',  'name' => 'South Africa',   'flag' => '🇿🇦'],
            ['dial' => '+30',  'name' => 'Greece',         'flag' => '🇬🇷'],
            ['dial' => '+32',  'name' => 'Belgium',        'flag' => '🇧🇪'],
            ['dial' => '+36',  'name' => 'Hungary',        'flag' => '🇭🇺'],
            ['dial' => '+40',  'name' => 'Romania',        'flag' => '🇷🇴'],
            ['dial' => '+41',  'name' => 'Switzerland',    'flag' => '🇨🇭'],
            ['dial' => '+43',  'name' => 'Austria',        'flag' => '🇦🇹'],
            ['dial' => '+45',  'name' => 'Denmark',        'flag' => '🇩🇰'],
            ['dial' => '+46',  'name' => 'Sweden',         'flag' => '🇸🇪'],
            ['dial' => '+47',  'name' => 'Norway',         'flag' => '🇳🇴'],
            ['dial' => '+48',  'name' => 'Poland',         'flag' => '🇵🇱'],
            ['dial' => '+90',  'name' => 'Turkey',         'flag' => '🇹🇷'],
            ['dial' => '+91',  'name' => 'India',          'flag' => '🇮🇳'],
            ['dial' => '+62',  'name' => 'Indonesia',      'flag' => '🇮🇩'],
            ['dial' => '+63',  'name' => 'Philippines',    'flag' => '🇵🇭'],
            ['dial' => '+65',  'name' => 'Singapore',      'flag' => '🇸🇬'],
            ['dial' => '+66',  'name' => 'Thailand',       'flag' => '🇹🇭'],
            ['dial' => '+81',  'name' => 'Japan',          'flag' => '🇯🇵'],
            ['dial' => '+82',  'name' => 'South Korea',    'flag' => '🇰🇷'],
            ['dial' => '+84',  'name' => 'Vietnam',        'flag' => '🇻🇳'],
            ['dial' => '+86',  'name' => 'China',          'flag' => '🇨🇳'],
            ['dial' => '+212', 'name' => 'Morocco',        'flag' => '🇲🇦'],
            ['dial' => '+213', 'name' => 'Algeria',        'flag' => '🇩🇿'],
            ['dial' => '+216', 'name' => 'Tunisia',        'flag' => '🇹🇳'],
            ['dial' => '+20',  'name' => 'Egypt',          'flag' => '🇪🇬'],
            ['dial' => '+234', 'name' => 'Nigeria',        'flag' => '🇳🇬'],
            ['dial' => '+254', 'name' => 'Kenya',          'flag' => '🇰🇪'],
            ['dial' => '+972', 'name' => 'Israel',         'flag' => '🇮🇱'],
            ['dial' => '+971', 'name' => 'United Arab Emirates', 'flag' => '🇦🇪'],
            ['dial' => '+966', 'name' => 'Saudi Arabia',   'flag' => '🇸🇦'],
            ['dial' => '+98',  'name' => 'Iran',           'flag' => '🇮🇷'],
            ['dial' => '+92',  'name' => 'Pakistan',       'flag' => '🇵🇰'],
            ['dial' => '+880', 'name' => 'Bangladesh',     'flag' => '🇧🇩'],
            ['dial' => '+7',   'name' => 'Russia',         'flag' => '🇷🇺'],
            ['dial' => '+380', 'name' => 'Ukraine',        'flag' => '🇺🇦'],
            ['dial' => '+420', 'name' => 'Czech Republic', 'flag' => '🇨🇿'],
            ['dial' => '+421', 'name' => 'Slovakia',       'flag' => '🇸🇰'],
            ['dial' => '+353', 'name' => 'Ireland',        'flag' => '🇮🇪'],
            ['dial' => '+358', 'name' => 'Finland',        'flag' => '🇫🇮'],
            ['dial' => '+372', 'name' => 'Estonia',        'flag' => '🇪🇪'],
            ['dial' => '+371', 'name' => 'Latvia',         'flag' => '🇱🇻'],
            ['dial' => '+370', 'name' => 'Lithuania',      'flag' => '🇱🇹'],
            ['dial' => '+359', 'name' => 'Bulgaria',       'flag' => '🇧🇬'],
            ['dial' => '+385', 'name' => 'Croatia',        'flag' => '🇭🇷'],
            ['dial' => '+386', 'name' => 'Slovenia',       'flag' => '🇸🇮'],
            ['dial' => '+381', 'name' => 'Serbia',         'flag' => '🇷🇸'],
            ['dial' => '+852', 'name' => 'Hong Kong',      'flag' => '🇭🇰'],
            ['dial' => '+886', 'name' => 'Taiwan',         'flag' => '🇹🇼'],
        ];
    }
}
