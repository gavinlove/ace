<?php

namespace DalaiLomo\ACE\Setup\Section;

use DalaiLomo\ACE\Config\ACEConfig;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface Section
{
    public static function create();

    public function getSectionName();

    public function setInputInterface(InputInterface $input);

    public function setOutputInterface(OutputInterface $output);

    public function setQuestionHelper(QuestionHelper $questionHelper);

    public function setConfig(ACEConfig $ACEConfig);

    public function __toString();

    public function doAction();
}
