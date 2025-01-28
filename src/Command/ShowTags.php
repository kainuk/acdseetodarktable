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

class ShowTags extends Command {

  protected function configure(): void
  {
    $this->setName('showtags')
      ->setDescription('Show the tags of an AcdSee file')
      ->addArgument('dir',InputArgument::OPTIONAL,'Directory to start the scanning','.')
      ->addOption('filter','F', InputOption::VALUE_OPTIONAL, 'Filter')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output->writeln([
         'Executing ShowTags ......',
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
          $result = Utils::extractRootCategory((string) $categories);
          if($input->hasOption('filter')){
            $result = array_filter($result, function($v) use ($input) { return str_starts_with($v,$input->getOption('filter'));});
          }
          if(!empty($result)){
            $people = array_unique(array_merge($people,$result));
          }
        }
      }
    }
    $progressBar->finish();
    $output->writeln("");
    foreach($people as $person){
      $output->writeln($person);
    }
    return Command::SUCCESS;
  }


}
