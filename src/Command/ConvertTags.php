<?php
// src/Command/CreateUserCommand.php
namespace AcdDarktable\Command;

use SimpleXMLElement;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ConvertTags extends Command {

  protected function configure(): void
  {
    $this->setName('converttags')
      ->setDescription('Show the tags of an AcdSee file')
      ->addArgument('dir',InputArgument::OPTIONAL,'Directory to start the scanning','.')
      ->addOption('recursive','R', InputOption::VALUE_NONE, 'Process subdirectories')
    ;
  }
  protected function extractPeople(string $categories, OutputInterface $output) : array {
    if(empty($categories)){
      return [];
    }
    $result = [];
    /* @var SimpleXMLElement $peopleXml */
    $peopleXml = new SimpleXMLElement($categories);
    foreach($peopleXml->xpath('/Categories/Category') as $category){
      $categoryContent = (string) $category;
      if($categoryContent=='People'){
        foreach($category->xpath('Category') as $person){
          $result[(string) $person]=(string) $person;
        }
      }
    }
    return $result;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output->writeln([
         'Executing ConvertTags ......',
         'Starting in directory '.$input->getArgument('dir')
      ]
    );
    $people = [];
    $finder = new Finder();
    $finder->files()->in($input->getArgument('dir'))->name(['*.RW2','*.NEF']);
    $output->writeln('Found '.$finder->count().' files');
    $progressBar = new ProgressBar($output, $finder->count());
    foreach ($finder as $file) {
      $progressBar->advance();
      $acdSeeXmpFileName = $file->getPath() .'/'.$file->getFilenameWithoutExtension().'.xmp';
      if(is_file($acdSeeXmpFileName)){
        $acdSeeXmpString = file_get_contents($acdSeeXmpFileName);
        $acdSeeXmp =  new SimpleXMLElement($acdSeeXmpString);
        $acdSeeXmp->registerXPathNamespace('acdsee', 'http://ns.acdsee.com/iptc/1.0/');
        //$output->writeln($acdSeeXmpFileName);
        foreach($acdSeeXmp->xpath('//acdsee:categories') as $categories) {
          $people = $this->extractPeople((string) $categories, $output);
          Utils::addPerson($file->getRealPath(), $people);
        }
      }
    }
    $progressBar->finish();
    $output->writeln("\nDone");
    return Command::SUCCESS;
  }
}
