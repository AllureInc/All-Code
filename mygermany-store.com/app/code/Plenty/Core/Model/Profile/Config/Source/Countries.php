<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Api\CountryInformationAcquirerInterface;

/**
 * Class Countries
 * @package Plenty\Core\Model\Config
 */
class Countries
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var CountryInformationAcquirerInterface
     */
    protected $_countryInformation;

    /**
     * Countries constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param CountryInformationAcquirerInterface $countryInformation
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CountryInformationAcquirerInterface $countryInformation
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_countryInformation = $countryInformation;
    }

    /**
     * @return array
     */
	protected function toOptionArray()
	{
		return [
            'DE'	=>	1,
            'AT'	=>	2,
            'BE'	=>	3,
            'CH'	=>	4,
            'CY'	=>	5,
            'CZ'	=>	6,
            'DK'	=>	7,
            'ES'	=>	8,
            'EE'	=>	9,
            'FR'	=>	10,
            'FI'	=>	11,
            'GB'	=>	12,
            'GR'	=>	13,
            'HU'	=>	14,
            'IT'	=>	15,
            'IE'	=>	16,
            'LU'	=>	17,
            'LV'	=>	18,
            'MT'	=>	19,
            'NO'	=>	20,
            'NL'	=>	21,
            'PT'	=>	22,
            'PL'	=>	23,
            'SE'	=>	24,
            'SG'	=>	25,
            'SK'	=>	26,
            'SI'	=>	27,
            'US'	=>	28,
            'AU'	=>	29,
            'CA'	=>	30,
            'CN'	=>	31,
            'JP'	=>	32,
            'LT'	=>	33,
            'LI'	=>	34,
            'MC'	=>	35,
            'MX'	=>	36,
            'IC'	=>	37,
            'IN'	=>	38,
            'BR'	=>	39,
            'RU'	=>	40,
            'RO'	=>	41,
            'EA'	=>	42,
            // 'EA'	=>	43,
            'BG'	=>	44,
            'XZ'	=>	45,
            'KG'	=>	46,
            'KZ'	=>	47,
            'BY'	=>	48,
            'UZ'	=>	49,
            'MA'	=>	50,
            'AM'	=>	51,
            'AL'	=>	52,
            'EG'	=>	53,
            'HR'	=>	54,
            'MV'	=>	55,
            'MY'	=>	56,
            'HK'	=>	57,
            'YE'	=>	58,
            'IL'	=>	59,
            'TW'	=>	60,
            'GP'	=>	61,
            'TH'	=>	62,
            'TR'	=>	63,
            // 'GR'	=>	64,
            // 'ES'	=>	65,
            'NZ'	=>	66,
            'AF'	=>	67,
            'AX'	=>	68,
            'DZ'	=>	69,
            'AS'	=>	70,
            'AD'	=>	71,
            'AO'	=>	72,
            'AI'	=>	73,
            'AQ'	=>	74,
            'AG'	=>	75,
            'AR'	=>	76,
            'AW'	=>	77,
            'AZ'	=>	78,
            'BS'	=>	79,
            'BH'	=>	80,
            'BD'	=>	81,
            'BB'	=>	82,
            'BZ'	=>	83,
            'BJ'	=>	84,
            'BM'	=>	85,
            'BT'	=>	86,
            'BO'	=>	87,
            'BA'	=>	88,
            'BW'	=>	89,
            'BV'	=>	90,
            'IO'	=>	91,
            'BN'	=>	92,
            'BF'	=>	93,
            'BI'	=>	94,
            'KH'	=>	95,
            'CM'	=>	96,
            'CV'	=>	97,
            'KY'	=>	98,
            'CF'	=>	99,
            'TD'	=>	100,
            'CL'	=>	101,
            'CX'	=>	102,
            'CC'	=>	103,
            'CO'	=>	104,
            'KM'	=>	105,
            'CG'	=>	106,
            'CD'	=>	107,
            'CK'	=>	108,
            'CR'	=>	109,
            'CI'	=>	110,
            'CU'	=>	112,
            'DJ'	=>	113,
            'DM'	=>	114,
            'DO'	=>	115,
            'EC'	=>	116,
            'SV'	=>	117,
            'GQ'	=>	118,
            'ER'	=>	119,
            'ET'	=>	120,
            'FK'	=>	121,
            'FO'	=>	122,
            'FJ'	=>	123,
            'GF'	=>	124,
            'PF'	=>	125,
            'TF'	=>	126,
            'GA'	=>	127,
            'GM'	=>	128,
            'GE'	=>	129,
            'GH'	=>	130,
            'GI'	=>	131,
            'GL'	=>	132,
            'GD'	=>	133,
            'GU'	=>	134,
            'GT'	=>	135,
            'GG'	=>	136,
            'GN'	=>	137,
            'GW'	=>	138,
            'GY'	=>	139,
            'HT'	=>	140,
            'HM'	=>	141,
            'VA'	=>	142,
            'HN'	=>	143,
            'IS'	=>	144,
            'ID'	=>	145,
            'IR'	=>	146,
            'IQ'	=>	147,
            'IM'	=>	148,
            'JM'	=>	149,
            'JE'	=>	150,
            'JO'	=>	151,
            'KE'	=>	152,
            'KI'	=>	153,
            'KP'	=>	154,
            'KR'	=>	155,
            'KW'	=>	156,
            'LA'	=>	158,
            'LB'	=>	159,
            'LS'	=>	160,
            'LR'	=>	161,
            'LY'	=>	162,
            'MO'	=>	163,
            'MK'	=>	164,
            'MG'	=>	165,
            'MW'	=>	166,
            'ML'	=>	168,
            'MH'	=>	169,
            'MQ'	=>	170,
            'MR'	=>	171,
            'MU'	=>	172,
            'YT'	=>	173,
            'FM'	=>	174,
            'MD'	=>	175,
            'MN'	=>	176,
            'ME'	=>	177,
            'MS'	=>	178,
            'MZ'	=>	179,
            'MM'	=>	180,
            'NA'	=>	181,
            'NR'	=>	182,
            'NP'	=>	183,
            'AN'	=>	184,
            'NC'	=>	185,
            'NI'	=>	186,
            'NE'	=>	187,
            'NG'	=>	188,
            'NU'	=>	189,
            'NF'	=>	190,
            'MP'	=>	191,
            'OM'	=>	192,
            'PK'	=>	193,
            'PW'	=>	194,
            'PS'	=>	195,
            'PA'	=>	196,
            'PG'	=>	197,
            'PY'	=>	198,
            'PE'	=>	199,
            'PH'	=>	200,
            'PN'	=>	201,
            'PR'	=>	202,
            'QA'	=>	203,
            'RE'	=>	204,
            'RW'	=>	205,
            'SH'	=>	206,
            'KN'	=>	207,
            'LC'	=>	208,
            'PM'	=>	209,
            'VC'	=>	210,
            'WS'	=>	211,
            'SM'	=>	212,
            'ST'	=>	213,
            'SA'	=>	214,
            'SN'	=>	215,
            'RS'	=>	216,
            'SC'	=>	217,
            'SL'	=>	218,
            'SB'	=>	219,
            'SO'	=>	220,
            'ZA'	=>	221,
            'GS'	=>	222,
            'LK'	=>	223,
            'SD'	=>	224,
            'SR'	=>	225,
            'SJ'	=>	226,
            'SZ'	=>	227,
            'SY'	=>	228,
            'TJ'	=>	229,
            'TZ'	=>	230,
            'TL'	=>	231,
            'TG'	=>	232,
            'TK'	=>	233,
            'TO'	=>	234,
            'TT'	=>	235,
            'TN'	=>	236,
            'TM'	=>	237,
            'TC'	=>	238,
            'TV'	=>	239,
            'UG'	=>	240,
            'UA'	=>	241,
            'UM'	=>	242,
            'UY'	=>	243,
            'VU'	=>	244,
            'VE'	=>	245,
            'VN'	=>	246,
            'VG'	=>	247,
            'VI'	=>	248,
            'WF'	=>	249,
            'EH'	=>	250,
            'ZM'	=>	252,
            'ZW'	=>	253,
            'AE'	=>	254,
            'CUW'	=>	258,
            'SXM'	=>	259,
            'BES'	=>	260
        ];
	}

    /**
     * @return array
     */
	public function getCountryToOptionArray()
    {
        return $this->toOptionArray();
    }

    public function getCountryNames()
    {
        return $this->toOptionArray();
    }

    public function getCountryIds()
    {
        return array_flip($this->toOptionArray());
    }

    /**
     * @param $storeCode
     * @return int|mixed
     */
    public function getCountryIdByStoreCode($storeCode)
    {
        $countryCode = substr($this->_scopeConfig
            ->getValue('general/locale/code', ScopeInterface::SCOPE_STORE, $storeCode),-2);

        return isset($this->toOptionArray()[strtoupper($countryCode)])
            ? $this->toOptionArray()[strtoupper($countryCode)]
            : 0;
    }

    /**
     * @param $storeCode
     * @return bool|string
     */
    public function getCountryLocaleByStoreCode($storeCode)
    {
        $countryLocaleCode = substr($this->_scopeConfig
            ->getValue('general/locale/code', ScopeInterface::SCOPE_STORE, $storeCode),0,2);

        return $countryLocaleCode;
    }

    /**
     * @param $countryCode
     * @return int|mixed
     */
	public function getCountryIdByCode($countryCode)
	{
	    if (is_numeric($countryCode)) {
            $countryCode = substr($this->_scopeConfig->getValue('general/locale/code', $countryCode),0,2);
        }

	    return isset($this->toOptionArray()[strtoupper($countryCode)])
            ? $this->toOptionArray()[strtoupper($countryCode)]
            : 0;
	}

    /**
     * @param $countryCode
     * @return int|mixed
     */
    public function getCountryIdByCountryCode($countryCode)
    {
        return isset($this->toOptionArray()[strtoupper($countryCode)]) ? $this->toOptionArray()[strtoupper($countryCode)] : 0;
    }

    /**
     * @param $countryId
     * @return false|int|string
     */
    public function getCountryCodeById($countryId)
    {
        return array_search($countryId, $this->toOptionArray());
    }
}

