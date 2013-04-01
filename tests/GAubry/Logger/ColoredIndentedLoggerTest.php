<?php

namespace GAubry\Logger\Tests;

use \GAubry\Logger\ColoredIndentedLogger;
use \Psr\Log\LogLevel;

class ColoredIndentedLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp ()
    {

    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
    }

    /**
     * @covers \GAubry\Logger\AbstractLogger::__construct
     * @covers \GAubry\Logger\AbstractLogger::_checkMsgLevel
     * @covers \GAubry\Logger\ColoredIndentedLogger::__construct
     */
    public function testConstruct_ThrowExceptionWhenBadMinMsgLevel ()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unkown level: 'xyz'! Level MUST be defined in \Psr\Log\LogLevel class."
        );
        $oLogger = new ColoredIndentedLogger(array(), ' ', '+', '-', 'xyz');
    }


    /**
     * @covers \GAubry\Logger\AbstractLogger::_checkMsgLevel
     * @covers \GAubry\Logger\ColoredIndentedLogger::log
     */
    public function testLog_ThrowExceptionWhenBadMinMsgLevel ()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unkown level: 'xyz'! Level MUST be defined in \Psr\Log\LogLevel class."
        );
        $oLogger = new ColoredIndentedLogger(array(), ' ', '+', '-', LogLevel::DEBUG);
        $oLogger->log('xyz', 'Message');
    }

    /**
     * @covers \GAubry\Logger\AbstractLogger::__construct
     * @covers \GAubry\Logger\ColoredIndentedLogger::__construct
     * @covers \GAubry\Logger\ColoredIndentedLogger::log
     * @dataProvider providerTestLog_MinMsgLevel
     */
    public function testLog_MinMsgLevel ($sMinMsgLevel, $sLevel, $sMessage, $sExpectedMessage)
    {
        $oLogger = new ColoredIndentedLogger(array(), ' ', '+', '-', $sMinMsgLevel);
        $sExpectedResult = $sExpectedMessage . (strlen($sExpectedMessage) > 0 ? PHP_EOL : '');
//         $this->expectOutputString($sExpectedResult);
        $this->expectOutputString(str_replace('\033', "\033", $sExpectedResult));
        $oLogger->log($sLevel, $sMessage);
    }

    public function providerTestLog_MinMsgLevel ()
    {
        return array(
            array(LogLevel::DEBUG, LogLevel::DEBUG, 'Message', 'Message'),
            array(LogLevel::DEBUG, LogLevel::ERROR, 'Message', 'Message'),
            array(LogLevel::DEBUG, LogLevel::EMERGENCY, 'Message', 'Message'),

            array(LogLevel::ERROR, LogLevel::DEBUG, 'Message', ''),
            array(LogLevel::ERROR, LogLevel::ERROR, 'Message', 'Message'),
            array(LogLevel::ERROR, LogLevel::EMERGENCY, 'Message', 'Message'),

            array(LogLevel::EMERGENCY, LogLevel::DEBUG, 'Message', ''),
            array(LogLevel::EMERGENCY, LogLevel::ERROR, 'Message', ''),
            array(LogLevel::EMERGENCY, LogLevel::EMERGENCY, 'Message', 'Message'),
        );
    }

    /**
     * @covers \GAubry\Logger\ColoredIndentedLogger::log
     * @dataProvider providerTestLog_WithLevelSpecificMethods
     */
    public function testLog_WithLevelSpecificMethods ($sLevel)
    {
        $sMessage = 'Message';
        $aContext = array('key' => 'value');
        $oMockLogger = $this->getMock(
            '\GAubry\Logger\ColoredIndentedLogger',
            array('log'),
            array(array(), ' ', '+', '-', LogLevel::DEBUG)
        );
        $oMockLogger->expects($this->once())->method('log')->with(
            $this->equalTo($sLevel),
            $this->equalTo($sMessage),
            $this->equalTo($aContext)
        );
        $oMockLogger->$sLevel($sMessage, $aContext);
    }

    public function providerTestLog_WithLevelSpecificMethods ()
    {
        return array(
            array(LogLevel::DEBUG),
            array(LogLevel::INFO),
            array(LogLevel::NOTICE),
            array(LogLevel::WARNING),
            array(LogLevel::ERROR),
            array(LogLevel::CRITICAL),
            array(LogLevel::ALERT),
            array(LogLevel::EMERGENCY),
        );
    }

    /**
     * @param unknown $sMessage
     * @param array $aContext
     * @param unknown $sExpectedMessage
     * @covers \GAubry\Logger\ColoredIndentedLogger::log
     * @covers \GAubry\Logger\AbstractLogger::_interpolateContext
     * @dataProvider providerTestLog_WithContext
     */
    public function testLog_WithContext ($sMessage, array $aContext, $sExpectedMessage)
    {
        $oLogger = new ColoredIndentedLogger(array(), ' ', '+', '-', LogLevel::DEBUG);
        $this->expectOutputString($sExpectedMessage . PHP_EOL);
        $oLogger->log(LogLevel::INFO, $sMessage, $aContext);
    }

    public function providerTestLog_WithContext ()
    {
        return array(
            array('', array(), ''),
            array('bla', array(), 'bla'),
            array('', array('param1' => 'toto'), ''),

            array('bla {param1}', array('param1' => 'toto'), 'bla toto'),
            array('bla {param1} bla', array('param1' => 'toto'), 'bla toto bla'),
            array('{param1} bla', array('param1' => 'toto'), 'toto bla'),

            array('bla {param12} bla', array('param1' => 'toto'), 'bla {param12} bla'),
            array('bla {param1} bla', array('param12' => 'toto'), 'bla {param1} bla'),

            array('bla {p1}{p1} bla', array('p1' => 'to'), 'bla toto bla'),
            array('{{p1}{p2}{p3}}', array('p1' => 'A', 'p2' => 'B', 'p3' => 'C'), '{ABC}'),
        );
    }

    /**
     * @param unknown $sMessage
     * @param unknown $sExpectedMessage
     * @covers \GAubry\Logger\ColoredIndentedLogger::log
     * @dataProvider providerTestLog_WithIndent
     */
    public function testLog_WithIndent (array $aMessages, $sExpectedMessage)
    {
        $oLogger = new ColoredIndentedLogger(array(), "  ", '+++', '---', LogLevel::DEBUG);
        $this->expectOutputString($sExpectedMessage);
        foreach ($aMessages as $sMessage) {
            $oLogger->log(LogLevel::INFO, $sMessage, array());
        }
    }

    public function providerTestLog_WithIndent ()
    {
        $N = PHP_EOL;
        return array(
            array(array(''), "$N"),
            array(array('bla'), "bla$N"),

            array(array('+++bla'), "  bla$N"),
            array(array('---bla'), "bla$N"),
            array(array('---'), ''),

            array(array('++++++bla'), "    bla$N"),
            array(array('+++---bla'), "bla$N"),
            array(array('---+++bla'), "  bla$N"),

            array(array('bla+++bla'), "bla+++bla$N"),
            array(array('bla---bla'), "bla---bla$N"),

            array(array('A+++', 'B'), "A$N  B$N"),
            array(array('A+++', '+++B'), "A$N    B$N"),
            array(array('A+++', '---B'), "A{$N}B$N"),

            array(array('A+++', 'B', '---C'), "A$N  B{$N}C$N"),
            array(array('A+++', 'B---', 'C'), "A$N  B{$N}C$N"),

            array(array('A+++', 'B', 'C---', 'D'), "A$N  B$N  C{$N}D$N"),
            array(array('A+++', 'B+++', '------', 'D'), "A$N  B{$N}D$N"),
            array(array('A+++', 'B+++', 'C', '---D'), "A$N  B$N    C$N  D$N"),
        );
    }

    /**
     * @covers \GAubry\Logger\ColoredIndentedLogger::log
     * @covers \GAubry\Logger\ColoredIndentedLogger::_buildColorTags
     * @dataProvider providerTestLog_WithColor
     */
    public function testLog_WithColor ($sLevelMsg, $sMessage, $sExpectedMessage)
    {
        $aColors = array(
            'emergency' => '[RED]',
            'title' => '[WHITE]',
            'ok' => '[GREEN]',
        );
        $oLogger = new ColoredIndentedLogger($aColors, "  ", '+++', '---', LogLevel::DEBUG, '[RESET]', 'C.');
        $this->expectOutputString($sExpectedMessage . PHP_EOL);
        $oLogger->log($sLevelMsg, $sMessage, array());
    }

    public function providerTestLog_WithColor ()
    {
        return array(
            array(LogLevel::INFO, '', ''),
            array(LogLevel::INFO, 'a', 'a'),
            array(LogLevel::EMERGENCY, 'a', '[RED]a[RESET]'),
            array(LogLevel::EMERGENCY, '+++a', '  [RED]a[RESET]'),
            array(LogLevel::INFO, 'result: {C.ok}OK', 'result: [GREEN]OK[RESET]'),
            array(LogLevel::INFO, '{C.title}result: {C.ok}OK', '[WHITE]result: [GREEN]OK[RESET]'),
            array(LogLevel::EMERGENCY, '{C.title}result: {C.ok}OK', '[RED][WHITE]result: [GREEN]OK[RESET]'),
        );
    }
}
