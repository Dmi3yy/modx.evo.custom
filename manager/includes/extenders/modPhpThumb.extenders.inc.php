<?php
/**
 * phpthumb extenders
 * Date: 07.10.14
 * Time: 15:45
 */

if (class_exists('modPhpThumb')) {
    return true;
}

if (!include_once(MODX_BASE_PATH . 'assets/libs/PHPThumb/modPhpThumb.class.php')) {
    return false;
}else{
    return true;
}