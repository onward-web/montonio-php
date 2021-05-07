<?php 

/**
 * In multistore solutions, adding a prefix to an order ID 
 * is a common way to distinguish between orders from different stores
 * in the Montonio Partner System.
 * 
 * This class provides methods to add and remove this prefix in a uniform way
 */
class MontonioOrderPrefixer 
{
    const SEPARATOR = '-';

    public static function addPrefix($prefix, $orderId) {
        if (!empty($prefix)) {
            return $prefix . self::SEPARATOR . $orderId;
        }
        return $orderId;
    }

    public static function removePrefix($orderId) {
        $tmp = explode(self::SEPARATOR, $orderId);
        return end($tmp);
    }
}