<?php

namespace DalaiLomo\ACE\Setup\Section\LogViewerSection;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
use DalaiLomo\ACE\Setup\Section\AbstractSection;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class LogFileSection extends AbstractSection
{
    // TODO make blacklist configurable
    private $blacklist = [
        'exception'
    ];

    /**
     * @var string
     */
    private $logFilePath;

    /**
     * @var array
     */
    private $parsedJson;

    public function __construct($logFilePath)
    {
        $this->logFilePath = $logFilePath;
        $this->parsedJson = json_decode(file_get_contents($this->logFilePath), true);
    }

    public function getSectionName()
    {
        return $this->decorateLogFileName($this->logFilePath);
    }

    public function doAction()
    {
        $output = '';

        foreach (reset($this->parsedJson) as $chunkName => $chunk) {
            foreach($chunk as $command) {
                foreach ($command as $commandName => $chunkStreams) {
                    $output .= CommandOutputHelper::oldSchoolSeparator();
                    $output .= $chunkName . ' : ' . $commandName . ' >> STDOUT'.PHP_EOL;
                    $output .= CommandOutputHelper::oldSchoolSeparator();
                    $output .= isset($chunkStreams['stdout']) ? $chunkStreams['stdout'] : '';
                    $output .= CommandOutputHelper::ninjaSeparator();

                    $output .= CommandOutputHelper::oldSchoolSeparator();
                    $output .= $chunkName . ' : ' . $commandName . ' >> STDERR'.PHP_EOL;
                    $output .= CommandOutputHelper::oldSchoolSeparator();
                    $output .= isset($chunkStreams['stderr']) ? $chunkStreams['stderr'] : '';
                    $output .= CommandOutputHelper::ninjaSeparator();
                }

                $output .= CommandOutputHelper::ninjaSeparator();
                $output .= CommandOutputHelper::ninjaSeparator();
                $output .= CommandOutputHelper::ninjaSeparator();
            }
        }

        $process = new Process(sprintf('echo "%s" | less', $output));

        try {
            $process->setTty(true);
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }

        $this->output->writeln(CommandOutputHelper::clearOutput());
    }

    private function decorateLogFileName($logFile)
    {
        $logTokens = explode('/', $logFile);
        $logLastToken = end($logTokens);
        $logTimestamp = explode('.', $logLastToken)[0];

        $output = array_keys($this->parsedJson)[0] . ' @ ' . date(\DateTime::ISO8601, $logTimestamp);

        return $this->flagFileNameIfBlacklistedContentIsFound($output, $logFile);
    }

    // TODO: change the way to do this
    private function flagFileNameIfBlacklistedContentIsFound($output, $logFile)
    {
        $fileContents = file_get_contents($logFile);

        foreach ($this->blacklist as $blacklistElement) {
            if (strpos(strtolower($fileContents), strtolower($blacklistElement))) {
                $output .= ' *' . $blacklistElement;
            }
        }

        return $output;
    }

}
