<?php

namespace GAubry\Logger;

use \Psr\Log\LogLevel;

abstract class AbstractLogger extends \Psr\Log\AbstractLogger
{
    /**
     * Seuil d'importance à partir duquel accepter de loguer un message.
     * @var int
     */
    protected $_iMinMsgLevel;

    protected static $_aIntLevels = array(
        LogLevel::DEBUG => 0,
        LogLevel::INFO => 10,
        LogLevel::NOTICE => 20,
        LogLevel::WARNING => 30,
        LogLevel::ERROR => 40,
        LogLevel::CRITICAL => 50,
        LogLevel::ALERT => 60,
        LogLevel::EMERGENCY => 70
    );

    /**
     * Constructeur.
     *
     * @param string $iMinMsgLevel Seuil d'importance à partir duquel accepter de loguer un message.
     * @see \Psr\Log\LogLevel
    */
    protected function __construct ($sMinMsgLevel=LogLevel::DEBUG)
    {
        $this->_checkMsgLevel($sMinMsgLevel);
        $this->_iMinMsgLevel = self::$_aIntLevels[$sMinMsgLevel];
    }

    protected function _checkMsgLevel ($sMsgLevel)
    {
        if ( ! isset(self::$_aIntLevels[$sMsgLevel])) {
            $sErrorMsg = "Unkown level: '$sMsgLevel'! Level MUST be defined in \Psr\Log\LogLevel class.";
            throw new \InvalidArgumentException($sErrorMsg, 1);
        }
    }

    /**
     * Interpolates context values into the message placeholders.
     */
    protected function _interpolateContext ($sMessage, array $aContext)
    {
        // build a replacement array with braces around the context keys
        $aReplace = array();
        foreach ($aContext as $sKey => $mValue) {
            $sValue = (string)$mValue;
            $aReplace['{' . trim($sKey) . '}'] = $sValue;
        }

        // interpolate replacement values into the message and return
        return strtr($sMessage, $aReplace);
    }
}