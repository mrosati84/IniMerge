<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Application;
use Inimerge\Command\MergeCommand;

$application = new Application();

$application->setName('IniMerge - Merge INI files');

try {
  $application->add(new MergeCommand());
} catch (LogicException $e) {
  printf("Error setting console command.\n%s", $e->getMessage());
  exit(1);
}

try {
  $application->run();
} catch (Exception $e) {
  printf("Error running inimerge.\n%s", $e->getMessage());
  exit(1);
}
