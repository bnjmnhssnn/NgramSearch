<?php
namespace NgramSearch\CliCommand;

use NgramSearch\Ngrams;
use NgramSearch\Preparer;
use NgramSearch\Exception\AddToIndexException;
use NgramSearch\Exception\NgramSearchException;
use NgramSearch\Exception\IndexNameInUseException;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class Import extends Command
{
    protected function configure()
    {
        $this->setName('import')
            ->setDescription('Description text goes here')
            ->setHelp('Help text goes here');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $io = new SymfonyStyle($input, $output);
        $io->title('Create new ngram index from text file');

        $import_files = array_values(array_filter(
            scandir(realpath(__DIR__ . '/../../../import')),
            function($item) {
                return pathinfo($item, PATHINFO_EXTENSION) === 'txt';
            }
        ));
        if(empty($import_files)) {
            $io->error('No .txt files found in /import, exit console.');
            exit;
        }
        $import_files['x'] = 'cancel';

        $question = new ChoiceQuestion(
            'Choose a file to import:' . PHP_EOL,
            $import_files,
            array_keys($import_files)
        );
        $question->setErrorMessage('Ungültige Eingabe');
        $choice = $helper->ask($input, $output, $question);

        if($choice === 'x') {
            $output->writeln('Command canceled, exit console.');
            exit;
        }
        $output->writeln('');
        $question = new Question('Enter new ngram index name:' . PHP_EOL);
        $index_name = $helper->ask($input, $output, $question);

        $storage = get_storage_adapter();

        try {
            $storage->createIndex($index_name);
        } catch (IndexNameInUseException $e) {
            $io->error('Index already exists, exit console.');
            exit; 
        } catch (NgramSearchException $e)  {
            $io->error('Unknown error creating index, exit console.');
            exit;
        } 
        $import_fh = fopen(realpath(__DIR__ . '/../../../import/' . $import_files[$choice]), 'r');
        $successful = 0;
        $with_error = 0;
        while (!feof($import_fh)) {
            $line = fgets($import_fh);
            try {
                $storage->addToIndex($index_name, rtrim($line, "\n"));
            } catch (\Exception $e) {
                $with_error++;
                continue;     
            }
            $output->writeln($line);
            $successful++;
        }
        $io->success($successful . ' lines successfully imported.');
        if($with_error) {
            $io->error($with_error . ' errors.');    
        }
    }
}



















