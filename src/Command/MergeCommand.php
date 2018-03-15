<?php

namespace Inimerge\Command;

use Inimerge\Exceptions\IniFileNotFoundException;
use Matomo\Ini\IniReader;
use Matomo\Ini\IniReadingException;
use Matomo\Ini\IniWriter;
use Matomo\Ini\IniWritingException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MergeCommand extends Command {

  /**
   * {@inheritdoc}
   *
   * @throws InvalidArgumentException
   */
  protected function configure(): void {
    $this
      ->setName('ini:merge')
      ->setDescription('Merges two INI files.')
      ->addArgument('srcfile', InputArgument::REQUIRED, 'The source INI file.')
      ->addArgument('dstfile', InputArgument::REQUIRED, 'The destination INI file.')
      ->setHelp('This command allows you read an INI file as a blueprint, and use it add missing keys to your active file.');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Symfony\Component\Console\Exception\RuntimeException
   * @throws InvalidArgumentException
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $srcfile = $input->getArgument('srcfile');
    $dstfile = $input->getArgument('dstfile');

    $ini_reader = new IniReader();
    $ini_writer = new IniWriter();

    if (!is_file($srcfile)) {
      throw new RuntimeException(sprintf('Source file %s is not a regular file.', $srcfile));
    }
    if (!is_file($dstfile)) {
      throw new RuntimeException(sprintf('Destination file %s is not a regular file.', $dstfile));
    }

    try {
      $src_ini = $ini_reader->readFile($srcfile);
    }
    catch (IniReadingException $e) {
      throw new RuntimeException(sprintf("Error reading source file %s.\n%s", $srcfile, $e->getMessage()));
    }

    try {
      $dst_ini = $ini_reader->readFile($dstfile);
    }
    catch (IniReadingException $e) {
      throw new RuntimeException(sprintf("Error reading destination file %s.\n%s", $dstfile, $e->getMessage()));
    }

    $merge = array_merge_recursive($src_ini, $dst_ini);

    $this->truncateDstFile($dstfile);

    try {
      $ini_writer->writeToFile($dstfile, $merge);
    }
    catch(IniWritingException $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  /**
   * @param $dstfile
   *
   * @throws RuntimeException
   */
  private function truncateDstFile($dstfile): void {
    $fh = fopen($dstfile, 'wb');

    if (!$fh) {
      throw new RuntimeException(sprintf('Error opening %s file for truncating.', $dstfile));
    }

    ftruncate($fh, 0);
    fclose($fh);
  }

}
