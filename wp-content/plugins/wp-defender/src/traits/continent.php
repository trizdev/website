<?php
/**
 * Continent helper.
 *
 * @package WP_Defender\Traits
 * @since 3.10.0
 */

namespace WP_Defender\Traits;

trait Continent {

	/**
	 * Copy the list from https://en.wikipedia.org/wiki/United_Nations_geoscheme.
	 * It's without specific places:
	 * -Asia/Pacific Region,
	 * -Antarctica,
	 * -Anonymous Proxy,
	 * -Satellite Provider,
	 * -Other Country,
	 * which are in the list of countries.
	 * Note: there must be strict compliance with Country trait.
	 *
	 * @return array
	 */
	protected function get_countries_with_continents(): array {
		return apply_filters(
			'wd_countries_with_continents',
			[
			'AF' => [
				'name' => __('Africa', 'wpdef'),
				'area' => [
					'AF1' => [
						'name' => __( 'Northern Africa', 'wpdef' ),
						'countries' => [
							'DZ' => 'Algeria',
							'EG' => 'Egypt',
							'LY' => 'Libyan Arab Jamahiriya',
							'MA' => 'Morocco',
							'SD' => 'Sudan',
							'TN' => 'Tunisia',
							'EH' => 'Western Sahara',
						],
					],
					'AF2' => [
						'name' => __( 'Eastern Africa', 'wpdef' ),
						'countries' => [
							'IO' => 'British Indian Ocean Territory',
							'BI' => 'Burundi',
							'KM' => 'Comoros',
							'DJ' => 'Djibouti',
							'ER' => 'Eritrea',
							'ET' => 'Ethiopia',
							'TF' => 'French Southern Territories',
							'KE' => 'Kenya',
							'MG' => 'Madagascar',
							'MW' => 'Malawi',
							'MU' => 'Mauritius',
							'YT' => 'Mayotte',
							'MZ' => 'Mozambique',
							'RE' => 'Reunion',
							'RW' => 'Rwanda',
							'SC' => 'Seychelles',
							'SO' => 'Somalia',
							'SS' => 'South Sudan',
							'UG' => 'Uganda',
							'TZ' => 'Tanzania, United Republic of',
							'ZM' => 'Zambia',
							'ZW' => 'Zimbabwe',
						]
					],
					'AF3' => [
						'name' => __( 'Western Africa', 'wpdef' ),
						'countries' => [
							'BJ' => 'Benin',
							'BF' => 'Burkina Faso',
							'CV' => 'Cape Verde',
							'CI' => "Cote d'Ivoire",
							'GM' => 'Gambia',
							'GH' => 'Ghana',
							'GN' => 'Guinea',
							'GW' => 'Guinea-Bissau',
							'LR' => 'Liberia',
							'ML' => 'Mali',
							'MR' => 'Mauritania',
							'NE' => 'Niger',
							'NG' => 'Nigeria',
							'SH' => 'Saint Helena',
							'SN' => 'Senegal',
							'SL' => 'Sierra Leone',
							'TG' => 'Togo',
						]
					],
					'AF4' => [
						'name' => __( 'Central Africa', 'wpdef' ),
						'countries' => [
							'AO' => 'Angola',
							'CM' => 'Cameroon',
							'CF' => 'Central African Republic',
							'TD' => 'Chad',
							'CG' => 'Congo',
							'CD' => 'Congo, The Democratic Republic of the',
							'GQ' => 'Equatorial Guinea',
							'GA' => 'Gabon',
							'ST' => 'Sao Tome and Principe',
						]
					],
					'AF5' => [
						'name' => __( 'Southern Africa', 'wpdef' ),
						'countries' => [
							'BW' => 'Botswana',
							'SZ' => 'Swaziland',
							'LS' => 'Lesotho',
							'NA' => 'Namibia',
							'ZA' => 'South Africa',
						]
					]
				],
			],
			/* Commented because there are no countries for Antarctica.
			'AN' => [
				'name' => __('Antarctica', 'wpdef'),
				'area' => [
					'AN1' => [
						'name' => __( 'West Antarctica', 'wpdef' ),
						'countries' => []
					],
					'AN2' => [
						'name' => __( 'East Antarctica', 'wpdef' ),
						'countries' => []
					],
				],
			],*/
			'AS' => [
				'name' => __('Asia', 'wpdef'),
				'area' => [
					'AS1' => [
						'name' => __( 'Central Asia', 'wpdef' ),
						'countries' => [
							'KZ' => 'Kazakhstan',
							'KG' => 'Kyrgyzstan',
							'TJ' => 'Tajikistan',
							'TM' => 'Turkmenistan',
							'UZ' => 'Uzbekistan',
						],
					],
					'AS2' => [
						'name' => __( 'Eastern Asia', 'wpdef' ),
						'countries' => [
							'CN' => 'China',
							'HK' => 'Hong Kong',
							'MO' => 'Macao',
							'JP' => 'Japan',
							'MN' => 'Mongolia',
							'KP' => "Korea, Democratic People's Republic of",
							'KR' => 'Korea, Republic of',
							'TW' => 'Taiwan',
						],
					],
					'AS3' => [
						'name' => __( 'South Asia', 'wpdef' ),
						'countries' => [
							'AF' => 'Afghanistan',
							'BD' => 'Bangladesh',
							'BT' => 'Bhutan',
							'IN' => 'India',
							'MV' => 'Maldives',
							'NP' => 'Nepal',
							'PK' => 'Pakistan',
							'LK' => 'Sri Lanka',
						],
					],
					'AS4' => [
						'name' => __( 'South-Eastern Asia', 'wpdef' ),
						'countries' => [
							'BN' => 'Brunei Darussalam',
							'KH' => 'Cambodia',
							'ID' => 'Indonesia',
							'LA' => "Lao People's Democratic Republic",
							'MY' => 'Malaysia',
							'MM' => 'Myanmar',
							'PH' => 'Philippines',
							'SG' => 'Singapore',
							'TH' => 'Thailand',
							'TL' => 'Timor-Leste',
							'VN' => 'Vietnam',
						],
					],
					'AS5' => [
						'name' => __( 'Western Asia', 'wpdef' ),
						'countries' => [
							'AM' => 'Armenia',
							'AZ' => 'Azerbaijan',
							'BH' => 'Bahrain',
							'CY' => 'Cyprus',
							'GE' => 'Georgia',
							'IR' => 'Iran, Islamic Republic of',
							'IQ' => 'Iraq',
							'IL' => 'Israel',
							'JO' => 'Jordan',
							'KW' => 'Kuwait',
							'LB' => 'Lebanon',
							'OM' => 'Oman',
							'QA' => 'Qatar',
							'SA' => 'Saudi Arabia',
							'PS' => 'Palestinian Territory',
							'SY' => 'Syrian Arab Republic',
							'TR' => 'Turkey',
							'AE' => 'United Arab Emirates',
							'YE' => 'Yemen',
						],
					],
				],
			],
			'EU' => [
				'name' => __('Europe', 'wpdef'),
				'area' => [
					'EU1' => [
						'name' => __( 'Eastern Europe', 'wpdef' ),
						'countries' => [
							'BY' => 'Belarus',
							'BG' => 'Bulgaria',
							'CZ' => 'Czech Republic',
							'HU' => 'Hungary',
							'PL' => 'Poland',
							'MD' => 'Moldova, Republic of',
							'RO' => 'Romania',
							'RU' => 'Russian Federation',
							'SK' => 'Slovakia',
							'UA' => 'Ukraine',
						],
					],
					'EU2' => [
						'name' => __( 'Northern Europe', 'wpdef' ),
						'countries' => [
							'AX' => 'Aland Islands',
							'DK' => 'Denmark',
							'EE' => 'Estonia',
							'FO' => 'Faroe Islands',
							'FI' => 'Finland',
							'IS' => 'Iceland',
							'IE' => 'Ireland',
							'IM' => 'Isle of Man',
							'LV' => 'Latvia',
							'LT' => 'Lithuania',
							'NO' => 'Norway',
							'SJ' => 'Svalbard and Jan Mayen',
							'SE' => 'Sweden',
							'GB' => 'United Kingdom',
							'GG' => 'Guernsey',
							'JE' => 'Jersey',
						],
					],
					'EU3' => [
						'name' => __( 'Southern Europe', 'wpdef' ),
						'countries' => [
							'AL' => 'Albania',
							'AD' => 'Andorra',
							'BA' => 'Bosnia and Herzegovina',
							'HR' => 'Croatia',
							'GI' => 'Gibraltar',
							'GR' => 'Greece',
							'VA' => 'Holy See (Vatican City State)',
							'IT' => 'Italy',
							'MT' => 'Malta',
							'ME' => 'Montenegro',
							'MK' => 'North Macedonia',
							'PT' => 'Portugal',
							'SM' => 'San Marino',
							'RS' => 'Serbia',
							'SI' => 'Slovenia',
							'ES' => 'Spain',
						],
					],
					'EU4' => [
						'name' => __( 'Western Europe', 'wpdef' ),
						'countries' => [
							'AT' => 'Austria',
							'BE' => 'Belgium',
							'FR' => 'France',
							'DE' => 'Germany',
							'LI' => 'Liechtenstein',
							'LU' => 'Luxembourg',
							'MC' => 'Monaco',
							'NL' => 'Netherlands',
							'CH' => 'Switzerland',
						],
					],
				],
			],
			'AM' => [
				'name' => __('America', 'wpdef'),
				'area' => [
					'AM1' => [
						'name' => __( 'Central America', 'wpdef' ),
						'countries' => [
							'BZ' => 'Belize',
							'CR' => 'Costa Rica',
							'SV' => 'El Salvador',
							'GT' => 'Guatemala',
							'HN' => 'Honduras',
							'MX' => 'Mexico',
							'NI' => 'Nicaragua',
							'PA' => 'Panama',
						],
					],
					'AM2' => [
						'name' => __( 'Caribbean', 'wpdef' ),
						'countries' => [
							'AI' => 'Anguilla',
							'AG' => 'Antigua and Barbuda',
							'AW' => 'Aruba',
							'BS' => 'Bahamas',
							'BB' => 'Barbados',
							'BQ' => 'Bonaire, Saint Eustatius and Saba',
							'VG' => 'Virgin Islands, British',
							'KY' => 'Cayman Islands',
							'CU' => 'Cuba',
							'CW' => 'Curacao',
							'DM' => 'Dominica',
							'DO' => 'Dominican Republic',
							'GD' => 'Grenada',
							'GP' => 'Guadeloupe',
							'HT' => 'Haiti',
							'JM' => 'Jamaica',
							'MQ' => 'Martinique',
							'MS' => 'Montserrat',
							'PR' => 'Puerto Rico',
							'BL' => 'Saint Barthelemy',
							'KN' => 'Saint Kitts and Nevis',
							'LC' => 'Saint Lucia',
							'MF' => 'Saint Martin',
							'VC' => 'Saint Vincent and the Grenadines',
							'SX' => 'Sint Maarten',
							'TT' => 'Trinidad and Tobago',
							'TC' => 'Turks and Caicos Islands',
							'VI' => 'Virgin Islands, U.S.',
						],
					],
					'AM3' => [
						'name' => __( 'North America', 'wpdef' ),
						'countries' => [
							'BM' => 'Bermuda',
							'CA' => 'Canada',
							'GL' => 'Greenland',
							'PM' => 'Saint Pierre and Miquelon',
							'US' => 'United States',
							'UM' => 'United States Minor Outlying Islands',
						],
					],
					'AM4' => [
						'name' => __( 'South America', 'wpdef' ),
						'countries' => [
							'AR' => 'Argentina',
							'BO' => 'Bolivia',
							'BV' => 'Bouvet Island',
							'BR' => 'Brazil',
							'CL' => 'Chile',
							'CO' => 'Colombia',
							'EC' => 'Ecuador',
							'FK' => 'Falkland Islands (Malvinas)',
							'GF' => 'French Guiana',
							'GY' => 'Guyana',
							'PY' => 'Paraguay',
							'PE' => 'Peru',
							'GS' => 'South Georgia and the South Sandwich Islands',
							'SR' => 'Suriname',
							'UY' => 'Uruguay',
							'VE' => 'Venezuela',
						],
					],
				],
			],
			'OC' => [
				'name' => __('Oceania', 'wpdef'),
				'area' => [
					'OC1' => [
						'name' => __( 'Australia and New Zealand', 'wpdef' ),
						'countries' => [
							'AU' => 'Australia',
							'NZ' => 'New Zealand',
							'CC' => 'Cocos (Keeling) Islands',
							'CX' => 'Christmas Island',
							'HM' => 'Heard Island and McDonald Islands',
						],
					],
					'OC2' => [
						'name' => __( 'Melanesia', 'wpdef' ),
						'countries' => [
							'FJ' => 'Fiji',
							'NC' => 'New Caledonia',
							'PG' => 'Papua New Guinea',
							'SB' => 'Solomon Islands',
							'VU' => 'Vanuatu',
						],
					],
					'OC3' => [
						'name' => __( 'Micronesia', 'wpdef' ),
						'countries' => [
							'FM' => 'Micronesia, Federated States of',
							'MH' => 'Marshall Islands',
							'MP' => 'Northern Mariana Islands',
							'GU' => 'Guam',
							'KI' => 'Kiribati',
							'NR' => 'Nauru',
							'PW' => 'Palau',
						],
					],
					'OC4' => [
						'name' => __( 'Polynesia', 'wpdef' ),
						'countries' => [
							'AS' => 'American Samoa',
							'CK' => 'Cook Islands',
							'PF' => 'French Polynesia',
							'NU' => 'Niue',
							'NF' => 'Norfolk Island',
							'PN' => 'Pitcairn',
							'WS' => 'Samoa',
							'TK' => 'Tokelau',
							'TO' => 'Tonga',
							'TV' => 'Tuvalu',
							'WF' => 'Wallis and Futuna',
						],
					],
				],
			],
		]
		);
	}
}