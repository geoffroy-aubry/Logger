<?php

namespace GAubry\Logger;

use \Psr\Log\LogLevel;

class MinimalLogger extends AbstractLogger
{
    /**
     * Constructeur.
     *
     * @param string $iMinMsgLevel Seuil d'importance à partir duquel accepter de loguer un message.
     * @see \Psr\Log\LogLevel
     */
    public function __construct ($sMinMsgLevel = LogLevel::DEBUG)
    {
        parent::__construct($sMinMsgLevel);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log ($sMsgLevel, $sMessage, array $aContext = array())
    {
        $this->checkMsgLevel($sMsgLevel);
        if (self::$aIntLevels[$sMsgLevel] >= $this->iMinMsgLevel) {
            echo $this->interpolateContext($sMessage, $aContext) . PHP_EOL;
        }
    }
}
