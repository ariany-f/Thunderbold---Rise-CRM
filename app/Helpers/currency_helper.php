<?php

/**
 * convert a number to currency forma
 * 
 * @param number $number
 * @param string $currency
 * @return number with currency symbol
 */
if (!function_exists('to_currency')) {

    function to_currency($number = 0, $currency = "", $no_of_decimals = 2) {
        $decimal_separator = get_setting("decimal_separator");
        $thousand_separator = get_setting("thousand_separator");
        $number = is_null($number) ? 0 : $number;

        if (get_setting("no_of_decimals") == "0") {
            $no_of_decimals = 0;
        }

        $negative_sign = "";
        if ($number < 0) {
            $number = $number * -1;
            $negative_sign = "-";
        }
        if (!$currency) {
            $currency = get_setting("currency_symbol");
        }

        $currency_position = get_setting("currency_position");
        if (!$currency_position) {
            $currency_position = "left";
        }

        if ($decimal_separator === ",") {
            if ($thousand_separator !== " ") {
                $thousand_separator = ".";
            }

            if ($currency_position === "right") {
                return $negative_sign . number_format($number, $no_of_decimals, ",", $thousand_separator) . $currency;
            } else {
                return $negative_sign . $currency . number_format($number, $no_of_decimals, ",", $thousand_separator);
            }
        } else {
            if ($thousand_separator !== " ") {
                $thousand_separator = ",";
            }

            if ($currency_position === "right") {
                return $negative_sign . number_format($number, $no_of_decimals, ".", $thousand_separator) . $currency;
            } else {
                return $negative_sign . $currency . number_format($number, $no_of_decimals, ".", $thousand_separator);
            }
        }
    }

}

/**
 * convert a number to quantity format
 * 
 * @param number $number
 * @return number
 */
if (!function_exists('to_decimal_format')) {

    function to_decimal_format($number = 0) {
        $decimal_separator = get_setting("decimal_separator");
        $number = is_null($number) ? 0 : $number;

        $decimal = 0;
        if (is_numeric($number) && floor($number) != $number) {
            $decimal = get_setting("no_of_decimals") == "0" ? 0 : 2;
        }
        if ($decimal_separator === ",") {
            return number_format($number, $decimal, ",", ".");
        } else {
            return number_format($number, $decimal, ".", ",");
        }
    }

}

/**
 * Remove currency formatting and convert a formatted currency string to a number
 * 
 * @param string $formatted_number
 * @param string $currency
 * @return float
 */
if (!function_exists('from_currency')) {
    function from_currency($formatted_number, $currency = "") {
        // Se não for passado um símbolo de moeda, pega o símbolo padrão das configurações
        if (!$currency) {
            $currency = get_setting("currency_symbol");
        }
        // Remove o símbolo da moeda e qualquer espaço ao redor
        $number = str_replace($currency, '', $formatted_number);
        $number = trim($number);
        // Obter separadores configurados
        $decimal_separator = get_setting("decimal_separator");
        $thousand_separator = get_setting("thousand_separator");
        // Remover separadores de milhar
        if ($thousand_separator) {
            $number = str_replace($thousand_separator, '', $number);
        }
        // Substituir o separador decimal configurado por um ponto
        if ($decimal_separator && $decimal_separator !== '.') {
            $number = str_replace($decimal_separator, '.', $number);
        }
        // Converter para float para operações matemáticas
        return (float)$number;
    }
}


/**
 * convert a currency value to data format
 *  
 * @param number $currency
 * @return number
 */
if (!function_exists('unformat_currency')) {

    function unformat_currency($currency = "") {
// remove everything except a digit "0-9", a comma ",", and a dot "."
        $new_money = preg_replace('/[^\d,-\.]/', '', $currency);
        $decimal_separator = get_setting("decimal_separator");
        if ($decimal_separator === ",") {
            $new_money = str_replace(".", "", $new_money);
            $new_money = str_replace(",", ".", $new_money);
        } else {
            $new_money = str_replace(",", "", $new_money);
        }
        return $new_money;
    }

}

/**
 * get array of international currency codes
 * 
 * @return array
 */
if (!function_exists('get_international_currency_code_list')) {

    function get_international_currency_code_list() {
        return array(
            "AED",
            "AFN",
            "ALL",
            "AMD",
            "ANG",
            "AOA",
            "ARS",
            "AUD",
            "AWG",
            "AZN",
            "BAM",
            "BBD",
            "BDT",
            "BGN",
            "BHD",
            "BIF",
            "BMD",
            "BND",
            "BOB",
            "BOV",
            "BRL",
            "BSD",
            "BTN",
            "BWP",
            "BYR",
            "BZD",
            "CAD",
            "CDF",
            "CHE",
            "CHF",
            "CHW",
            "CLF",
            "CLP",
            "CNY",
            "COP",
            "COU",
            "CRC",
            "CUC",
            "CUP",
            "CVE",
            "CZK",
            "DJF",
            "DKK",
            "DOP",
            "DZD",
            "EGP",
            "ERN",
            "ETB",
            "EUR",
            "FJD",
            "FKP",
            "GBP",
            "GEL",
            "GHS",
            "GIP",
            "GMD",
            "GNF",
            "GTQ",
            "GYD",
            "HKD",
            "HNL",
            "HRK",
            "HTG",
            "HUF",
            "IDR",
            "ILS",
            "INR",
            "IQD",
            "IRR",
            "ISK",
            "JMD",
            "JOD",
            "JPY",
            "KES",
            "KGS",
            "KHR",
            "KMF",
            "KPW",
            "KRW",
            "KWD",
            "KYD",
            "KZT",
            "LAK",
            "LBP",
            "LKR",
            "LRD",
            "LSL",
            "LYD",
            "MAD",
            "MDL",
            "MGA",
            "MKD",
            "MMK",
            "MNT",
            "MOP",
            "MRO",
            "MUR",
            "MVR",
            "MWK",
            "MXN",
            "MXV",
            "MYR",
            "MZN",
            "NAD",
            "NGN",
            "NIO",
            "NOK",
            "NPR",
            "NZD",
            "OMR",
            "PAB",
            "PEN",
            "PGK",
            "PHP",
            "PKR",
            "PLN",
            "PYG",
            "QAR",
            "RON",
            "RSD",
            "RUB",
            "RWF",
            "SAR",
            "SBD",
            "SCR",
            "SDG",
            "SEK",
            "SGD",
            "SHP",
            "SLL",
            "SOS",
            "SRD",
            "SSP",
            "STD",
            "SYP",
            "SZL",
            "THB",
            "TJS",
            "TMT",
            "TND",
            "TOP",
            "TRY",
            "TTD",
            "TWD",
            "TZS",
            "UAH",
            "UGX",
            "USD",
            "USN",
            "USS",
            "UYI",
            "UYU",
            "UZS",
            "VEF",
            "VND",
            "VUV",
            "WST",
            "XAF",
            "XAG",
            "XAU",
            "XBA",
            "XBB",
            "XBC",
            "XBD",
            "XCD",
            "XDR",
            "XFU",
            "XOF",
            "XPD",
            "XPF",
            "XPT",
            "XSU",
            "XTS",
            "XUA",
            "YER",
            "ZAR",
            "ZMW"
        );
    }

}


/**
 * get dropdown list fro international currency code
 * 
 * @return array
 */
if (!function_exists('get_international_currency_code_dropdown')) {

    function get_international_currency_code_dropdown() {
        $result = array();
        foreach (get_international_currency_code_list() as $value) {
            $result[$value] = $value;
        }
        return $result;
    }

}


/**
 * get ignor minor amount 
 * 
 * @return int
 */
if (!function_exists('ignor_minor_value')) {

    function ignor_minor_value($value) {
        if (abs($value) < 0.05) {
            $value = 0;
        }
        return $value;
    }

}

if (!function_exists('get_converted_amount')) {

    function get_converted_amount($currency = "", $value = 0) {
        if (!$currency) {
            //no currency given
            return $value;
        }

        $conversion_rate = get_setting("conversion_rate");
        $conversion_rate = @unserialize($conversion_rate);
        if (!($conversion_rate && is_array($conversion_rate) && count($conversion_rate))) {
            //no settings found
            return $value;
        }

        $conversion_rate_for_this_currency = get_array_value($conversion_rate, $currency);
        if (!$conversion_rate_for_this_currency) {
            //rate not found for this currency
            return $value;
        }

        //conversion rate found
        return ((1 / $conversion_rate_for_this_currency) * 1) * $value;
    }

}
