<?php
/**
 * Author: Hoang Ngo
 */
if (!class_exists('IG_Form')) {
    class IG_Form
    {
        public static function open($args = array())
        {
            $default = array(
                'url' => '#',
                'method' => 'POST',
                'attributes' => array()
            );

            $p = wp_parse_args($args, $default);
            $attrs = self::build_attrs($p['attributes']);
            return sprintf('<form action="%s" method="%s" %s>', esc_attr($p['url']), esc_attr($p['method']), $attrs);
        }

        public static function close()
        {
            return '</form>';
        }

        public static function label($args = array())
        {
            $default = array(
                'for' => '',
                'text' => '',
                'attributes' => array()
            );

            $p = wp_parse_args($args, $default);

            $attrs = self::build_attrs($p['attributes']);

            return sprintf('<label for="%s" %s >%s</label>', esc_attr($p['for']), $attrs, esc_html($p['text']));
        }

        public static function hidden($args = array())
        {
            $default = array(
                'name' => '',
                'value' => '',
                'attributes' => array()
            );

            $p = wp_parse_args($args, $default);

            $attrs = self::build_attrs($p['attributes']);

            return sprintf('<input type="hidden" value="%s" name="%s" %s />', esc_attr($p['value']), esc_attr($p['name']), $attrs);
        }

        public static function text($args = array())
        {
            $default = array(
                'name' => '',
                'value' => '',
                'attributes' => array()
            );

            $p = wp_parse_args($args, $default);

            $attrs = self::build_attrs($p['attributes']);

            return sprintf('<input type="text" value="%s" name="%s" %s />', esc_attr($p['value']), esc_attr($p['name']), $attrs);
        }

        public static function password($args = array())
        {
            $default = array(
                'name' => '',
                'value' => '',
                'attributes' => array()
            );

            $p = wp_parse_args($args, $default);

            $attrs = self::build_attrs($p['attributes']);

            return sprintf('<input type="password" value="%s" name="%s" %s />', esc_attr($p['value']), esc_attr($p['name']), $attrs);
        }

        public static function text_area($args = array())
        {
            $default = array(
                'name' => '',
                'value' => '',
                'attributes' => array()
            );

            $p = wp_parse_args($args, $default);

            $attrs = self::build_attrs($p['attributes']);

            return sprintf('<textarea name="%s" %s >%s</textarea>', esc_attr($p['name']), $attrs, $p['value']);
        }

        public static function email($args = array())
        {
            $default = array(
                'name' => '',
                'value' => '',
                'attributes' => array()
            );

            $p = wp_parse_args($args, $default);

            $attrs = self::build_attrs($p['attributes']);

            return sprintf('<input type="email" value="%s" name="%s" %s />', esc_attr($p['value']), esc_attr($p['name']), $attrs);
        }

        public static function file($args = array())
        {
            $default = array(
                'name' => '',
                'attributes' => array()
            );

            $p = wp_parse_args($args, $default);

            $attrs = self::build_attrs($p['attributes']);

            return sprintf('<input type="file" name="%s" %s />', esc_attr($p['name']), $attrs);
        }

        public static function select($args = array())
        {
            $default = array(
                'name' => '',
                'data' => array(),
                'selected' => array(),
                'attributes' => array(),
                'nameless' => ''
            );

            $p = wp_parse_args($args, $default);

            if (!is_array($p['selected'])) {
                $p['selected'] = array($p['selected']);
            }

            $p['selected'] = array_filter($p['selected']);

            $attrs = self::build_attrs($p['attributes']);

            $html = sprintf('<select name="%s" %s>', $p['name'], $attrs);
            if ($p['nameless']) {
                $html .= sprintf('<option value="">%s</option>', $p['nameless']);
            }

            foreach ($p['data'] as $key => $val) {
                $checked = in_array($key, $p['selected']) ? 'selected="selected"' : null;
                $html .= sprintf('<option value="%s" %s >%s</option>', esc_attr($key), $checked, esc_html($val));
            }
            $html .= '</select>';
            return $html;
        }

        public static function radio($args = array())
        {
            $default = array(
                'name' => '',
                'value' => '',
                'checked' => false,
                'attributes' => array()
            );

            $p = wp_parse_args($args, $default);

            $attrs = self::build_attrs($p['attributes']);

            return sprintf('<input type="radio" name="%s" value="%s" %s %s>', esc_attr($p['name']), esc_attr($p['value']), $p['checked'] == true ? 'checked' : null, $attrs);
        }

        public static function checkbox($args = array())
        {
            $default = array(
                'name' => '',
                'value' => '',
                'checked' => false,
                'attributes' => array()
            );

            $p = wp_parse_args($args, $default);


            $attrs = self::build_attrs($p['attributes']);

            return sprintf('<input type="checkbox" name="%s" value="%s" %s %s>', esc_attr($p['name']), esc_attr($p['value']), $p['checked'] == true ? 'checked="checked"' : null, $attrs);
        }

        public static function number($args = array())
        {
            $default = array(
                'name' => '',
                'value' => '',
                'attributes' => array()
            );

            $p = wp_parse_args($args, $default);

            $attrs = self::build_attrs($p['attributes']);

            return sprintf('<input type="number" value="%s" name="%s" %s />', esc_attr($p['value']), esc_attr($p['name']), $attrs);
        }

        private static function build_attrs($data)
        {
            $attrs = '';
            foreach ($data as $key => $val) {
                $attrs .= sprintf(' %s="%s" ', esc_attr($key), esc_attr($val));
            }
            return $attrs;
        }

        public static function country_select($args = array())
        {
            $args['data'] = self::country();
            return self::select($args);
        }

        public static function country()
        {
            $countries = array(
                "AF" => "Afghanistan",
                "AL" => "Albania",
                "DZ" => "Algeria",
                "AS" => "American Samoa",
                "AD" => "Andorra",
                "AO" => "Angola",
                "AI" => "Anguilla",
                "AQ" => "Antarctica",
                "AG" => "Antigua and Barbuda",
                "AR" => "Argentina",
                "AM" => "Armenia",
                "AW" => "Aruba",
                "AU" => "Australia",
                "AT" => "Austria",
                "AZ" => "Azerbaijan",
                "BS" => "Bahamas",
                "BH" => "Bahrain",
                "BD" => "Bangladesh",
                "BB" => "Barbados",
                "BY" => "Belarus",
                "BE" => "Belgium",
                "BZ" => "Belize",
                "BJ" => "Benin",
                "BM" => "Bermuda",
                "BT" => "Bhutan",
                "BO" => "Bolivia",
                "BA" => "Bosnia and Herzegovina",
                "BW" => "Botswana",
                "BV" => "Bouvet Island",
                "BR" => "Brazil",
                "BQ" => "British Antarctic Territory",
                "IO" => "British Indian Ocean Territory",
                "VG" => "British Virgin Islands",
                "BN" => "Brunei",
                "BG" => "Bulgaria",
                "BF" => "Burkina Faso",
                "BI" => "Burundi",
                "KH" => "Cambodia",
                "CM" => "Cameroon",
                "CA" => "Canada",
                "CT" => "Canton and Enderbury Islands",
                "CV" => "Cape Verde",
                "KY" => "Cayman Islands",
                "CF" => "Central African Republic",
                "TD" => "Chad",
                "CL" => "Chile",
                "CN" => "China",
                "CX" => "Christmas Island",
                "CC" => "Cocos [Keeling] Islands",
                "CO" => "Colombia",
                "KM" => "Comoros",
                "CG" => "Congo - Brazzaville",
                "CD" => "Congo - Kinshasa",
                "CK" => "Cook Islands",
                "CR" => "Costa Rica",
                "HR" => "Croatia",
                "CU" => "Cuba",
                "CY" => "Cyprus",
                "CZ" => "Czech Republic",
                "CI" => "Côte d’Ivoire",
                "DK" => "Denmark",
                "DJ" => "Djibouti",
                "DM" => "Dominica",
                "DO" => "Dominican Republic",
                "NQ" => "Dronning Maud Land",
                "DD" => "East Germany",
                "EC" => "Ecuador",
                "EG" => "Egypt",
                "SV" => "El Salvador",
                "GQ" => "Equatorial Guinea",
                "ER" => "Eritrea",
                "EE" => "Estonia",
                "ET" => "Ethiopia",
                "FK" => "Falkland Islands",
                "FO" => "Faroe Islands",
                "FJ" => "Fiji",
                "FI" => "Finland",
                "FR" => "France",
                "GF" => "French Guiana",
                "PF" => "French Polynesia",
                "TF" => "French Southern Territories",
                "FQ" => "French Southern and Antarctic Territories",
                "GA" => "Gabon",
                "GM" => "Gambia",
                "GE" => "Georgia",
                "DE" => "Germany",
                "GH" => "Ghana",
                "GI" => "Gibraltar",
                "GR" => "Greece",
                "GL" => "Greenland",
                "GD" => "Grenada",
                "GP" => "Guadeloupe",
                "GU" => "Guam",
                "GT" => "Guatemala",
                "GG" => "Guernsey",
                "GN" => "Guinea",
                "GW" => "Guinea-Bissau",
                "GY" => "Guyana",
                "HT" => "Haiti",
                "HM" => "Heard Island and McDonald Islands",
                "HN" => "Honduras",
                "HK" => "Hong Kong SAR China",
                "HU" => "Hungary",
                "IS" => "Iceland",
                "IN" => "India",
                "ID" => "Indonesia",
                "IR" => "Iran",
                "IQ" => "Iraq",
                "IE" => "Ireland",
                "IM" => "Isle of Man",
                "IL" => "Israel",
                "IT" => "Italy",
                "JM" => "Jamaica",
                "JP" => "Japan",
                "JE" => "Jersey",
                "JT" => "Johnston Island",
                "JO" => "Jordan",
                "KZ" => "Kazakhstan",
                "KE" => "Kenya",
                "KI" => "Kiribati",
                "KW" => "Kuwait",
                "KG" => "Kyrgyzstan",
                "LA" => "Laos",
                "LV" => "Latvia",
                "LB" => "Lebanon",
                "LS" => "Lesotho",
                "LR" => "Liberia",
                "LY" => "Libya",
                "LI" => "Liechtenstein",
                "LT" => "Lithuania",
                "LU" => "Luxembourg",
                "MO" => "Macau SAR China",
                "MK" => "Macedonia",
                "MG" => "Madagascar",
                "MW" => "Malawi",
                "MY" => "Malaysia",
                "MV" => "Maldives",
                "ML" => "Mali",
                "MT" => "Malta",
                "MH" => "Marshall Islands",
                "MQ" => "Martinique",
                "MR" => "Mauritania",
                "MU" => "Mauritius",
                "YT" => "Mayotte",
                "FX" => "Metropolitan France",
                "MX" => "Mexico",
                "FM" => "Micronesia",
                "MI" => "Midway Islands",
                "MD" => "Moldova",
                "MC" => "Monaco",
                "MN" => "Mongolia",
                "ME" => "Montenegro",
                "MS" => "Montserrat",
                "MA" => "Morocco",
                "MZ" => "Mozambique",
                "MM" => "Myanmar [Burma]",
                "NA" => "Namibia",
                "NR" => "Nauru",
                "NP" => "Nepal",
                "NL" => "Netherlands",
                "AN" => "Netherlands Antilles",
                "NT" => "Neutral Zone",
                "NC" => "New Caledonia",
                "NZ" => "New Zealand",
                "NI" => "Nicaragua",
                "NE" => "Niger",
                "NG" => "Nigeria",
                "NU" => "Niue",
                "NF" => "Norfolk Island",
                "KP" => "North Korea",
                "VD" => "North Vietnam",
                "MP" => "Northern Mariana Islands",
                "NO" => "Norway",
                "OM" => "Oman",
                "PC" => "Pacific Islands Trust Territory",
                "PK" => "Pakistan",
                "PW" => "Palau",
                "PS" => "Palestinian Territories",
                "PA" => "Panama",
                "PZ" => "Panama Canal Zone",
                "PG" => "Papua New Guinea",
                "PY" => "Paraguay",
                "YD" => "Peoples Democratic Republic of Yemen",
                "PE" => "Peru",
                "PH" => "Philippines",
                "PN" => "Pitcairn Islands",
                "PL" => "Poland",
                "PT" => "Portugal",
                "PR" => "Puerto Rico",
                "QA" => "Qatar",
                "RO" => "Romania",
                "RU" => "Russia",
                "RW" => "Rwanda",
                "RE" => "Réunion",
                "BL" => "Saint Barthélemy",
                "SH" => "Saint Helena",
                "KN" => "Saint Kitts and Nevis",
                "LC" => "Saint Lucia",
                "MF" => "Saint Martin",
                "PM" => "Saint Pierre and Miquelon",
                "VC" => "Saint Vincent and the Grenadines",
                "WS" => "Samoa",
                "SM" => "San Marino",
                "SA" => "Saudi Arabia",
                "SN" => "Senegal",
                "RS" => "Serbia",
                "CS" => "Serbia and Montenegro",
                "SC" => "Seychelles",
                "SL" => "Sierra Leone",
                "SG" => "Singapore",
                "SK" => "Slovakia",
                "SI" => "Slovenia",
                "SB" => "Solomon Islands",
                "SO" => "Somalia",
                "ZA" => "South Africa",
                "GS" => "South Georgia and the South Sandwich Islands",
                "KR" => "South Korea",
                "ES" => "Spain",
                "LK" => "Sri Lanka",
                "SD" => "Sudan",
                "SR" => "Suriname",
                "SJ" => "Svalbard and Jan Mayen",
                "SZ" => "Swaziland",
                "SE" => "Sweden",
                "CH" => "Switzerland",
                "SY" => "Syria",
                "ST" => "São Tomé and Príncipe",
                "TW" => "Taiwan",
                "TJ" => "Tajikistan",
                "TZ" => "Tanzania",
                "TH" => "Thailand",
                "TL" => "Timor-Leste",
                "TG" => "Togo",
                "TK" => "Tokelau",
                "TO" => "Tonga",
                "TT" => "Trinidad and Tobago",
                "TN" => "Tunisia",
                "TR" => "Turkey",
                "TM" => "Turkmenistan",
                "TC" => "Turks and Caicos Islands",
                "TV" => "Tuvalu",
                "UM" => "U.S. Minor Outlying Islands",
                "PU" => "U.S. Miscellaneous Pacific Islands",
                "VI" => "U.S. Virgin Islands",
                "UG" => "Uganda",
                "UA" => "Ukraine",
                "SU" => "Union of Soviet Socialist Republics",
                "AE" => "United Arab Emirates",
                "GB" => "United Kingdom",
                "US" => "United States",
                "ZZ" => "Unknown or Invalid Region",
                "UY" => "Uruguay",
                "UZ" => "Uzbekistan",
                "VU" => "Vanuatu",
                "VA" => "Vatican City",
                "VE" => "Venezuela",
                "VN" => "Vietnam",
                "WK" => "Wake Island",
                "WF" => "Wallis and Futuna",
                "EH" => "Western Sahara",
                "YE" => "Yemen",
                "ZM" => "Zambia",
                "ZW" => "Zimbabwe",
                "AX" => "Åland Islands",
            );
            return $countries;
        }
    }
}