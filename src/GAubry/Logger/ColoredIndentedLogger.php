<?php

namespace GAubry\Logger;

use \Psr\Log\LogLevel;

class ColoredIndentedLogger extends AbstractLogger
{
    private $_aRawColors;
    private $_aColorsWithTag;

    /**
     * Valeur d'un niveau d'indentation.
     * @var string
     */
    private $_sBaseIndentation;

    private $_sIndentTag;
    private $_sUnindentTag;
    private $_sIndentTagLength;
    private $_sUnindentTagLength;

    private $_sResetColorSequence;
    private $_sColorTagPrefix;

    /**
     * Niveau de l'indentation courante (commence à 0).
     * @var int
     */
    private $_iIndentationLevel;

    /**
     * Constructeur.
     *
     * @param string $iMinMsgLevel Seuil d'importance à partir duquel accepter de loguer un message.
     * @see \Psr\Log\LogLevel
     */
    public function __construct (
        array $aColors,
        $sBaseIndentation,
        $sIndentTag='+++',
        $sUnindentTag='---',
        $sMinMsgLevel=LogLevel::DEBUG,
        $sResetColorSequence="\033[0m",
        $sColorTagPrefix='C.'
    ) {
        parent::__construct($sMinMsgLevel);
        $this->_sBaseIndentation = $sBaseIndentation;
        $this->_sIndentTag = $sIndentTag;
        $this->_sIndentTagLength = strlen($sIndentTag);
        $this->_sUnindentTag = $sUnindentTag;
        $this->_sUnindentTagLength = strlen($sUnindentTag);
        $this->_iIndentationLevel = 0;
        $this->_sResetColorSequence = $sResetColorSequence;
        $this->_sColorTagPrefix = $sColorTagPrefix;
        $this->_aRawColors = $aColors;
        $this->_buildColorTags();
    }

    private function _buildColorTags ()
    {
        $this->_aColorsWithTag = array();
        foreach ($this->_aRawColors as $sRawName => $sSequence) {
            $sName = '{' . $this->_sColorTagPrefix . $sRawName . '}';
            $this->_aColorsWithTag[$sName] = $sSequence;
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $sMessage
     * @param array $context
     * @param string $sColor
     * @return ColoredIndentedLogger_Interface $this
     */
    public function log ($sMsgLevel, $sMessage, array $aContext=array())
    {
        $this->_checkMsgLevel($sMsgLevel);
        if (self::$_aIntLevels[$sMsgLevel] >= $this->_iMinMsgLevel) {

            $bTagFound = true;
            while ($bTagFound) {
                if (substr($sMessage, 0, $this->_sIndentTagLength) == $this->_sIndentTag) {
                    $this->_iIndentationLevel++;
                    $sMessage = substr($sMessage, $this->_sIndentTagLength);
                } else if (substr($sMessage, 0, $this->_sUnindentTagLength) == $this->_sUnindentTag) {
                    $this->_iIndentationLevel = max(0, $this->_iIndentationLevel-1);
                    $sMessage = substr($sMessage, $this->_sUnindentTagLength);
                    if (strlen($sMessage) === 0) {
                        return;
                    }
                } else {
                    $bTagFound = false;
                }
            }

            $iCurrIndentationLvl = $this->_iIndentationLevel;

            $bTagFound = true;
            while ($bTagFound) {
                if (substr($sMessage, -$this->_sIndentTagLength) == $this->_sIndentTag) {
                    $this->_iIndentationLevel++;
                    $sMessage = substr($sMessage, 0, -$this->_sIndentTagLength);
                } else if (substr($sMessage, -$this->_sUnindentTagLength) == $this->_sUnindentTag) {
                    $this->_iIndentationLevel = max(0, $this->_iIndentationLevel-1);
                    $sMessage = substr($sMessage, 0, -$this->_sUnindentTagLength);
                } else {
                    $bTagFound = false;
                }
            }

            if (isset($this->_aRawColors[$sMsgLevel]) || isset($aContext[$this->_sColorTagPrefix . $sMsgLevel])) {
                $sImplicitColor = '{' . $this->_sColorTagPrefix . $sMsgLevel . '}';
                $sMessage = $sImplicitColor . $sMessage;
            } else {
                $iNbColorTags = preg_match_all('/{C.[A-Za-z0-9_.]+}/', $sMessage, $aMatches);
                $sImplicitColor = '';
            }
            $sMessage = $this->_interpolateContext($sMessage, $aContext);
            $sIndent = str_repeat($this->_sBaseIndentation, $iCurrIndentationLvl);
            $sMessage = $sIndent . str_replace("\n", "\n$sIndent$sImplicitColor", $sMessage);
            $sMessage = strtr($sMessage, $this->_aColorsWithTag);
            if (
                $sImplicitColor != ''
                || (
                    $iNbColorTags > 0
                    && preg_match_all('/{C.[A-Za-z0-9_.]+}/', $sMessage, $aMatches) < $iNbColorTags
                )
            ) {
                $sMessage .= $this->_sResetColorSequence;
            }

            echo $sMessage . PHP_EOL;
        }
    }
}
