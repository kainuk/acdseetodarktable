<?php

namespace AcdDarktable\Command;

use SimpleXMLElement;
use Symfony\Component\Console\Output\OutputInterface;

class Utils {

  /**
   * @param \SimpleXMLElement|bool|null $xml
   * @param $ns
   * @param $name
   *
   * @return \SimpleXMLElement|null
   */
  public static function ensure(SimpleXMLElement|bool|null $xml, $ns, $name): ?SimpleXMLElement {
    $subject = $xml->children($ns)->{"$name"};
    if (!$subject) {
      $subject = $xml->addChild($name, NULL, $ns);
    }
    return $subject;
  }

  public static function arrayFromXml(SimpleXMLElement $xml, string $name) {
    $result = [];
    foreach ($xml->xpath($name) as $item) {
      $result [] = (string) $item;
    };
    return $result;
  }

  public static function addPerson(string $fileName, array $persons) {
    $hierarchyPersons = array_map(
      function ($s) {
        return "Personen|$s";
      },
      $persons
    );
    $touched = false;
    $persons = array_merge(['Personen'], $persons);
    $xml = simplexml_load_file($fileName . '.xmp');
    $description = $xml->children('rdf', TRUE)->RDF->Description;
    $subject = Utils::ensure($description, 'http://purl.org/dc/elements/1.1/', 'subject');
    $bag = Utils::ensure($subject, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'Bag');
    $currentPersons = Utils::arrayFromXml($bag, 'rdf:li');
    foreach (array_diff($persons, $currentPersons) as $person) {
      $bag->addChild('li', $person);
    }
    $hierarchy = Utils::ensure($description, 'http://ns.adobe.com/lightroom/1.0/', 'hierarchicalSubject');
    $hierarchyBag = Utils::ensure($hierarchy, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'Bag');
    $currentHierarchyPersons = Utils::arrayFromXml($hierarchyBag, 'rdf:li');
    foreach (array_diff($hierarchyPersons, $currentHierarchyPersons) as $person) {
      $hierarchyBag->addChild('li', $person);
      $touched = true;
    }
    if($touched) {
      $xml->asXML($fileName . '.xmp');
    }
  }

}
