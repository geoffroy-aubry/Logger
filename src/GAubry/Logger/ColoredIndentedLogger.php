<?php

namespace GAubry\Logger;

use \Psr\Log\LogLevel;

class ColoredIndentedLogger extends AbstractLogger
{
    private $aRawColors;
    private $aColorsWithTag;

    /**
     * Valeur d'un niveau d'indentation.
     * @var string
     */
    private $sBaseIndentation;

    private $sIndentTag;
    private $sUnindentTag;
    private $sIndentTagLength;
    private $sUnindentTagLength;

    private $sResetColorSequence;
    private $sColorTagPrefix;

    /**
     * Niveau de l'indentation courante (commence à 0).
     * @var int
     */
    private $iIndentationLevel;

    /**
     * Constructeur.
     *
     * @param string $iMinMsgLevel Seuil d'importance à partir duquel accepter de loguer un message.
     * @throws \Psr\Log\InvalidArgumentException if calling this method with a level not defined in \Psr\Log\LogLevel
     * @see \Psr\Log\LogLevel
     */
    public function __construct (
        array $aColors,
        $sBaseIndentation,
        $sIndentTag = '+++',
        $sUnindentTag = '---',
        $sMinMsgLevel = LogLevel::DEBUG,
        $sResetColorSequence = "\033[0m",
        $sColorTagPrefix = 'C.'
    ) {
        parent::__construct($sMinMsgLevel);
        $this->sBaseIndentation = $sBaseIndentation;
        $this->sIndentTag = $sIndentTag;
        $this->sIndentTagLength = strlen($sIndentTag);
        $this->sUnindentTag = $sUnindentTag;
        $this->sUnindentTagLength = strlen($sUnindentTag);
        $this->iIndentationLevel = 0;
        $this->sResetColorSequence = $sResetColorSequence;
        $this->sColorTagPrefix = $sColorTagPrefix;
        $this->aRawColors = $aColors;
        $this->buildColorTags();
    }

    private function buildColorTags ()
    {
        $this->aColorsWithTag = array();
        foreach ($this->aRawColors as $sRawName => $sSequence) {
            $sName = '{' . $this->sColorTagPrefix . $sRawName . '}';
            $this->aColorsWithTag[$sName] = $sSequence;
        }
    }

    private function processLeadingIndentationTags ($sMessage)
    {
        $bTagFound = true;
        while ($bTagFound && strlen($sMessage) > 0) {
            if (substr($sMessage, 0, $this->sIndentTagLength) == $this->sIndentTag) {
                $this->iIndentationLevel++;
                $sMessage = substr($sMessage, $this->sIndentTagLength);
            } elseif (substr($sMessage, 0, $this->sUnindentTagLength) == $this->sUnindentTag) {
                $this->iIndentationLevel = max(0, $this->iIndentationLevel-1);
                $sMessage = substr($sMessage, $this->sUnindentTagLength);
            } else {
                $bTagFound = false;
            }
        }
        return $sMessage;
    }

    private function processTrailingIndentationTags ($sMessage)
    {
        $bTagFound = true;
        while ($bTagFound && strlen($sMessage) > 0) {
            if (substr($sMessage, -$this->sIndentTagLength) == $this->sIndentTag) {
                $this->iIndentationLevel++;
                $sMessage = substr($sMessage, 0, -$this->sIndentTagLength);
            } elseif (substr($sMessage, -$this->sUnindentTagLength) == $this->sUnindentTag) {
                $this->iIndentationLevel = max(0, $this->iIndentationLevel-1);
                $sMessage = substr($sMessage, 0, -$this->sUnindentTagLength);
            } else {
                $bTagFound = false;
            }
        }
        return $sMessage;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $sMessage
     * @param array $context
     * @param string $sColor
     * @return ColoredIndentedLogger_Interface $this
     * @throws \Psr\Log\InvalidArgumentException if calling this method with a level not defined in \Psr\Log\LogLevel
     */
    public function log ($sMsgLevel, $sMessage, array $aContext = array())
    {
        $this->checkMsgLevel($sMsgLevel);
        if (self::$aIntLevels[$sMsgLevel] >= $this->iMinMsgLevel) {
            $sMessage = $this->processLeadingIndentationTags($sMessage);
            $iCurrIndentationLvl = $this->iIndentationLevel;
            $sMessage = $this->processTrailingIndentationTags($sMessage);

            if (strlen($sMessage) > 0) {
                if (isset($this->aRawColors[$sMsgLevel]) || isset($aContext[$this->sColorTagPrefix . $sMsgLevel])) {
                    $sImplicitColor = '{' . $this->sColorTagPrefix . $sMsgLevel . '}';
                    $sMessage = $sImplicitColor . $sMessage;
                } else {
                    $iNbColorTags = preg_match_all('/{C.[A-Za-z0-9_.]+}/', $sMessage, $aMatches);
                    $sImplicitColor = '';
                }
                $sMessage = $this->interpolateContext($sMessage, $aContext);
                $sIndent = str_repeat($this->sBaseIndentation, $iCurrIndentationLvl);
                $sMessage = $sIndent . str_replace("\n", "\n$sIndent$sImplicitColor", $sMessage);
                $sMessage = strtr($sMessage, $this->aColorsWithTag);
                if ($sImplicitColor != ''
                    || (
                        $iNbColorTags > 0
                        && preg_match_all('/{C.[A-Za-z0-9_.]+}/', $sMessage, $aMatches) < $iNbColorTags
                    )
                ) {
                    $sMessage .= $this->sResetColorSequence;
                }

                echo $sMessage . PHP_EOL;
            }
        }
    }
}
