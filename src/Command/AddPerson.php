<?php

namespace AcdDarktable\Command;
use SimpleXMLElement;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddPerson extends Command {

  protected function configure(): void
  {
    $this->setName('addperson')
      ->setDescription('Add a person to a Darktable XML file')
      ->addArgument('file',InputArgument::REQUIRED,'File to process, without xmp extension')
      ->addArgument('person',InputArgument::REQUIRED, 'Name of the person that must be added')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
     $persons = explode(',',$input->getArgument('person'));
     Utils::convert($input->getArgument('file'), $persons);
     return Command::SUCCESS;
  }

}
