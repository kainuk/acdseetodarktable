<?php
/**
 * @author Klaas Eikelboom  <klaas.eikelboom@civicoop.org>
 * @date 18-Jun-2020
 * @license  AGPL-3.0
 */
namespace AcdDarktable;

use AcdDarktable\Command\ConvertTags;
use Symfony\Component\Console\Application as SymfonyApplication;
use AcdDarktable\Command\AddPerson;
use AcdDarktable\Command\ShowTags;

class Application extends SymfonyApplication
{
  public function __construct()
  {
    parent::__construct('ACDSee 2 Darktable', 'v1.0');
    $this->add(new ShowTags());
    $this->add(new AddPerson());
    $this->add(new ConvertTags());
  }
}
