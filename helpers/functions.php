<?php
// *************************************************************************
// *                                                                       *
// * DEPRIXA PRO -  Integrated Web Shipping System                         *
// * Copyright (c) JAOMWEB. All Rights Reserved                            *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * Email: support@jaom.info                                              *
// * Website: http://www.jaom.info                                         *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * This software is furnished under a license and may be used and copied *
// * only  in  accordance  with  the  terms  of such  license and with the *
// * inclusion of the above copyright notice.                              *
// * If you Purchased from Codecanyon, Please read the full License from   *
// * here- http://codecanyon.net/licenses/standard                         *
// *                                                                       *
// *************************************************************************


function cdp_cleanOutx($text)
{
  $text =  strtr($text, array('\r\n' => "", '\r' => "", '\n' => ""));
  $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
  $text = str_replace('<br>', '<br />', $text);
  return stripslashes($text);
}


/**
     * validate track()
     */
  function cdp_validateTrack($value)
  {

      $valid_uname = "/^[A-Z-a-z0-9_-]{4,55}$/"; 
        if (!preg_match($valid_uname, $value))
            return 2;
      
  }   


function cdp_email_users_notificationsx($array)
{

  $email = "";
  $contador = 0;

  while ($contador < count($array)) {

    $email .= $array[$contador] . ",";
    $contador++;
  }

  $email = substr($email, 0, -1);

  return $email;
}



function cdb_m_format($amount)
{


  $db = new Conexion;


  if ($currency_decimal_digits == 'true') {
    $dec_digit = 2;
  } else {
    $dec_digit = 0;
  }

  if ($currency_symbol_position == 's') {
    $retval =
      number_format($amount, $dec_digit, $curr_point, $curr_sep) . ' ' . $currency_code;
  } else {
    $retval =
      $currency_code .
      ' ' .
      number_format($amount, $dec_digit, $curr_point, $curr_sep);
  }

  return $retval;
}


function cdb__forma($amount)
{

  $db = new Conexion;

  if ($curr_symbol == '') {
    $currency_code = $curr_money;
  } else {
    $currency_code = $curr_symbol;
  }

  $currency_decimal_digits = $curr_decimal;
  $currency_symbol_position = $curr_currency;

  if ($currency_decimal_digits == 'true') {
    $dec_digit = 2;
  } else {
    $dec_digit = 0;
  }

  $retval =  number_format($amount, $dec_digit, $curr_point, $curr_sep);

  return $retval;
}


/**
 * getSize()
 * 
 * @param mixed $size
 * @param integer $precision
 * @param bool $long_name
 * @param bool $real_size
 * @return
 */
function getSizex($size, $precision = 2, $long_name = false, $real_size = true)
{
  if ($size == 0) {
    return '-/-';
  } else {
    $base = $real_size ? 1024 : 1000;
    $pos = 0;
    while ($size > $base) {
      $size /= $base;
      $pos++;
    }
    $prefix = _getSizePrefix($pos);
    $size_name = $long_name ? $prefix . "bytes" : $prefix[0] . 'B';
    return round($size, $precision) . ' ' . ucfirst($size_name);
  }
}


/**
 * _getSizePrefix()
 * 
 * @param mixed $pos
 * @return
 */
function _getSizePrefixx($pos)
{
  switch ($pos) {
    case 00:
      return "";
    case 01:
      return "kilo";

    case 02:
      return "mega";
    case 03:
      return "giga";
    default:
      return "?-";
  }
}


function obtenerNombreMes($numeroMes) {
    // Array con los nombres de los meses en español
    $meses = array(
        1 => "Jan", 
        2 => "Feb", 
        3 => "Mar", 
        4 => "Apr", 
        5 => "may", 
        6 => "Jun", 
        7 => "Jul", 
        8 => "Aug", 
        9 => "Sept", 
        10 => "Oct", 
        11 => "Nov", 
        12 => "Dec"
    );

    // Verificar si el número de mes está dentro del rango válido
    if ($numeroMes >= 1 && $numeroMes <= 12) {
        return $meses[$numeroMes];
    } else {
        return "Invalid month";
    }
}



function cdp_round_outx($valor)
{
  $float_redondeado = round($valor * 100) / 100;
  return $float_redondeado;
}


// *************************************************************************
// * CBM (Cubic Meter) Calculation Functions                              *
// *************************************************************************

/**
 * Calculate CBM (Cubic Meter) from dimensions
 * 
 * @param float $length Length of package
 * @param float $width Width of package
 * @param float $height Height of package
 * @param string $unit Unit of measurement ('cm', 'inch', 'm')
 * @return float CBM value rounded to 4 decimal places
 */
function cdp_calculateCBM($length, $width, $height, $unit = 'cm')
{
    $length = floatval($length);
    $width = floatval($width);
    $height = floatval($height);
    
    // Return 0 if any dimension is 0
    if ($length == 0 || $width == 0 || $height == 0) {
        return 0;
    }
    
    if ($unit === 'cm') {
        // Convert cm³ to m³ (divide by 1,000,000)
        $cbm = ($length * $width * $height) / 1000000;
    } elseif ($unit === 'inch') {
        // Convert inch³ to m³ (divide by 61,023.7)
        $cbm = ($length * $width * $height) / 61023.7;
    } else {
        // Already in meters
        $cbm = $length * $width * $height;
    }
    
    return round($cbm, 4);
}

/**
 * Calculate charge based on CBM
 * 
 * @param float $cbm Cubic meter value
 * @param float $rate_per_cbm Rate per cubic meter
 * @param float $min_charge Minimum charge (optional)
 * @return float Calculated charge
 */
function cdp_calculateCBMCharge($cbm, $rate_per_cbm, $min_charge = 0)
{
    $charge = floatval($cbm) * floatval($rate_per_cbm);
    return max($charge, $min_charge);
}

/**
 * Determine which charge to use (weight-based or CBM-based)
 * 
 * @param float $actual_weight Actual weight
 * @param float $volumetric_weight Volumetric weight
 * @param float $cbm Cubic meter value
 * @param float $weight_rate Rate per weight unit
 * @param float $cbm_rate Rate per cubic meter
 * @param string $priority Priority method ('weight', 'cbm', 'higher')
 * @return array ['charge' => float, 'method' => string, 'cbm' => float]
 */
function cdp_getChargeableWeight($actual_weight, $volumetric_weight, $cbm, $weight_rate, $cbm_rate, $priority = 'higher')
{
    $actual_weight = floatval($actual_weight);
    $volumetric_weight = floatval($volumetric_weight);
    $cbm = floatval($cbm);
    $weight_rate = floatval($weight_rate);
    $cbm_rate = floatval($cbm_rate);
    
    // Calculate both charges
    $chargeable_weight = max($actual_weight, $volumetric_weight);
    $weight_charge = $chargeable_weight * $weight_rate;
    $cbm_charge = $cbm * $cbm_rate;
    
    // Determine which to use based on priority
    if ($priority === 'cbm') {
        return [
            'charge' => $cbm_charge,
            'method' => 'CBM',
            'cbm' => $cbm,
            'weight_charge' => $weight_charge,
            'cbm_charge' => $cbm_charge
        ];
    } elseif ($priority === 'weight') {
        return [
            'charge' => $weight_charge,
            'method' => 'Weight',
            'cbm' => $cbm,
            'weight_charge' => $weight_charge,
            'cbm_charge' => $cbm_charge
        ];
    } else {
        // Use higher charge
        if ($cbm_charge > $weight_charge) {
            return [
                'charge' => $cbm_charge,
                'method' => 'CBM',
                'cbm' => $cbm,
                'weight_charge' => $weight_charge,
                'cbm_charge' => $cbm_charge
            ];
        } else {
            return [
                'charge' => $weight_charge,
                'method' => 'Weight',
                'cbm' => $cbm,
                'weight_charge' => $weight_charge,
                'cbm_charge' => $cbm_charge
            ];
        }
    }
}

/**
 * Get CBM pricing tier based on CBM value
 * 
 * @param float $cbm Cubic meter value
 * @return array|null Pricing tier data or null if not found
 */
function cdp_getCBMPricingTier($cbm)
{
    $db = new Conexion;
    $cbm = floatval($cbm);
    
    $db->cdp_query("
        SELECT * FROM cdb_cbm_pricing_tiers 
        WHERE active = 1 
        AND min_cbm <= :cbm 
        AND (max_cbm >= :cbm OR max_cbm = 0)
        ORDER BY rate_per_cbm ASC 
        LIMIT 1
    ");
    
    $db->bind(':cbm', $cbm);
    
    $tier = $db->cdp_registro();
    
    return $tier ? $tier : null;
}

/**
 * Calculate CBM utilization percentage for containers
 * 
 * @param float $used_cbm Used CBM
 * @param float $max_cbm Maximum CBM capacity
 * @return float Percentage (0-100)
 */
function cdp_calculateCBMUtilization($used_cbm, $max_cbm)
{
    $used_cbm = floatval($used_cbm);
    $max_cbm = floatval($max_cbm);
    
    if ($max_cbm == 0) {
        return 0;
    }
    
    $percentage = ($used_cbm / $max_cbm) * 100;
    return round(min($percentage, 100), 2);
}

/**
 * Format CBM for display
 * 
 * @param float $cbm CBM value
 * @param int $decimals Number of decimal places
 * @return string Formatted CBM with unit
 */
function cdp_formatCBM($cbm, $decimals = 4)
{
    return number_format(floatval($cbm), $decimals, '.', ',') . ' m³';
}

/**
 * Get standard container CBM capacities
 * 
 * @return array Container types and their CBM capacities
 */
function cdp_getStandardContainerCBM()
{
    return [
        '20ft' => 33.00,
        '40ft' => 67.00,
        '40ft_hc' => 76.00,
        '45ft_hc' => 86.00
    ];
}

/**
 * Update locker CBM usage
 * 
 * @param int $locker_id Locker ID
 * @return bool Success status
 */
function cdp_updateLockerCBMUsage($locker_id)
{
    $db = new Conexion;
    
    // Calculate total CBM currently in this locker
    $db->cdp_query("
        SELECT COALESCE(SUM(o.total_cbm), 0) as total_cbm
        FROM cdb_customers_packages o
        WHERE o.locker_id = :locker_id
        AND o.status_courier NOT IN (7, 8)
    ");
    
    $db->bind(':locker_id', $locker_id);
    $result = $db->cdp_registro();
    
    $current_cbm = $result ? $result->total_cbm : 0;
    
    // Update locker
    $db->cdp_query("
        UPDATE cdb_address_locker 
        SET current_cbm_used = :current_cbm 
        WHERE id = :locker_id
    ");
    
    $db->bind(':current_cbm', $current_cbm);
    $db->bind(':locker_id', $locker_id);
    
    return $db->cdp_execute();
}
