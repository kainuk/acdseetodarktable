<?php
// src/Command/CreateUserCommand.php
namespace AcdDarktable\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'showtags')]
class ShowTags extends Command {

  protected function configure(): void
  {
    $this->setDescription('Show the tags of an AcdSee file')
      ->addArgument('dir',InputArgument::OPTIONAL,'Directory to start the scanning','.')
      ->addOption('recursive','R', InputOption::VALUE_NONE, 'Process subdirectories')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output->writeln([
         'Executing ShowTags ......',
         'Starting in directory '.$input->getArgument('dir')
      ]
    );
    return Command::SUCCESS;
  }
}
