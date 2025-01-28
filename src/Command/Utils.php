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

  public static function convert(string $fileName, array $hierarchyNew) {
    $tags = [];
    foreach($hierarchyNew as $newTags){
      $tags = array_unique($tags+explode('|',$newTags));
    }
    $touched = false;
    $tags = array_merge(['Personen'], $tags);
    $xml = simplexml_load_file($fileName . '.xmp');
    $description = $xml->children('rdf', TRUE)->RDF->Description;
    $subject = Utils::ensure($description, 'http://purl.org/dc/elements/1.1/', 'subject');
    $bag = Utils::ensure($subject, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'Bag');
    $currentPersons = Utils::arrayFromXml($bag, 'rdf:li');
    foreach (array_diff($tags, $currentPersons) as $person) {
      $bag->addChild('li', $person);
    }
    $hierarchy = Utils::ensure($description, 'http://ns.adobe.com/lightroom/1.0/', 'hierarchicalSubject');
    $hierarchyBag = Utils::ensure($hierarchy, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'Bag');
    $currentHierarchyPersons = Utils::arrayFromXml($hierarchyBag, 'rdf:li');
    foreach (array_diff($hierarchyNew, $currentHierarchyPersons) as $person) {
      $hierarchyBag->addChild('li', $person);
      $touched = true;
    }
    if($touched) {
      $xml->asXML($fileName . '.xmp');
    }
  }

  /**
   * @param mixed $category
   * @param array $result
   *
   * @return array
   */
  public static function extractCategory(SimpleXMLElement $category): array {
    $content = (string) $category;
    $result = [];
    foreach($category->xpath('Category') as $child){
      $result = $result + self::extractCategory($child);
    };
    if(empty($result)){
      $result = [$content];
    } else {
      $result = array_map(function ($s) use ($content) {
        return "$content|$s";
      },$result);
    }
    return $result;
  }

  public static function extractRootCategory(string $categories): array {
    if (empty($categories)) {
      return [];
    }
    /* @var SimpleXMLElement $peopleXml */
    $peopleXml = new SimpleXMLElement($categories);
    $result = [];
    foreach ($peopleXml->xpath('/Categories/Category') as $category) {
      $result = Utils::extractCategory($category);
    }
    return $result;
  }

}
