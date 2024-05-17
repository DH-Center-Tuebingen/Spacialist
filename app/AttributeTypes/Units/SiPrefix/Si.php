<?php

namespace App\AttributeTypes\Units\SiPrefix;

class Si {

    public static SiPrefix $YOTTA; 
    public static SiPrefix $ZETTA;
    public static SiPrefix $EXA;
    public static SiPrefix $PETA;
    public static SiPrefix $TERA;
    public static SiPrefix $GIGA;
    public static SiPrefix $MEGA;
    public static SiPrefix $KILO;
    public static SiPrefix $HECTO;
    public static SiPrefix $DECA;
    public static SiPrefix $DECI;
    public static SiPrefix $CENTI;
    public static SiPrefix $MILLI;
    public static SiPrefix $MICRO;
    public static SiPrefix $NANO;
    public static SiPrefix $PICO;
    public static SiPrefix $FEMTO;
    public static SiPrefix $ATTO;
    public static SiPrefix $ZEPTO;
    public static SiPrefix $YOCTO;

    public static function init(){
        self::$YOTTA = new SiPrefix('yotta', 'Y', 24);
        self::$ZETTA = new SiPrefix('zetta', 'Z', 21);
        self::$EXA = new SiPrefix('exa', 'E', 18);
        self::$PETA = new SiPrefix('peta', 'P', 15);
        self::$TERA = new SiPrefix('tera', 'T', 12);
        self::$GIGA = new SiPrefix('giga', 'G', 9);
        self::$MEGA = new SiPrefix('mega', 'M', 6);
        self::$KILO = new SiPrefix('kilo', 'k', 3);
        self::$HECTO = new SiPrefix('hecto', 'h', 2);
        self::$DECA = new SiPrefix('deca', 'da', 1);
        self::$DECI = new SiPrefix('deci', 'd', -1);
        self::$CENTI = new SiPrefix('centi', 'c', -2);
        self::$MILLI = new SiPrefix('milli', 'm', -3);
        self::$MICRO = new SiPrefix('micro', 'µ', -6);
        self::$NANO = new SiPrefix('nano', 'n', -9);
        self::$PICO = new SiPrefix('pico', 'p', -12);
        self::$FEMTO = new SiPrefix('femto', 'f', -15);
        self::$ATTO = new SiPrefix('atto', 'a', -18);
        self::$ZEPTO = new SiPrefix('zepto', 'z', -21);
        self::$YOCTO = new SiPrefix('yocto', 'y', -24);
    }
}

Si::init();