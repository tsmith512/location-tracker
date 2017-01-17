<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Finder\Finder;

$console = new Application();

$console->register('location:update')
  ->setDefinition(array())
  ->setDescription("Run application database updates and migrations")
  ->addOption(
    'yes',
    'y',
    InputOption::VALUE_NONE,
    'Should force execution answering yes to the question.'
  )
  ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
    $rows = $app['db']->fetchAll('SELECT `value` FROM `variables` WHERE `key` = "db_update" LIMIT 1;', array());
    $row = reset($rows);

    // $version will count up as updates are applied
    $version = $row['value'];

    // Remember where we started so we can say "no updates needed"
    $start_version = $version;

    // Tell the user where we are
    $output->writeln("Current Update Level: $version");

    $finder = new Finder();
    $finder->files()->in(__DIR__ . "/../update")->name('*.sql')->sortByName();

    if (!$input->getOption('yes')) {
      $helper = $this->getHelper('question');
      $question = new ConfirmationQuestion('<question>This action is irreversable have you made a backup? [y/n]</question>', false);
      if (!$helper->ask($input, $output, $question)) {
        return;
      }
    }

    foreach( $finder as $file ){
      $file_version = $file->getBasename('.sql');

      // If we're already at or past this update script's version, skip it
      if ($file_version <= $version) {
        continue;
      }

      // Else, apply the update in question
      $output->writeln("<comment>Applying Update: $file_version</comment>");
      $content = $file->getContents();
      $stmt = $app['db']->prepare($content);
      $stmt->execute();
      $output->writeln("<info>Applied Update: $file_version</info>");

      // Keep a tally of where we are
      $version = $file_version;
    }

    // If we iterated over all updates and we're no further than we
    // started no updates were applied; explain to the user and exit.
    if ($version == $start_version) {
      $output->writeln('<comment>No updates were applied.</comment>');
      return;
    }

    // If we're still here, we did work; update the database and tell
    // the user where we are.
    $update_query = "UPDATE `variables` SET `value` = ? WHERE `key` = 'db_update';";
    $stmt = $app['db']->prepare($update_query);
    $stmt->execute(array($version));
    $output->writeln('<info>All updates applied</info>');
  });

return $console;
