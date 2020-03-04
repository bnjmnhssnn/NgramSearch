<?php
namespace NgramSearch\CliCommand;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class Setup extends Command
{
    protected function configure()
    {
        $this->setName('setup')
            ->setDescription('Description text goes here')
            ->setHelp('Help text goes here');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $io = new SymfonyStyle($input, $output);
        $io->title('Setup your NgramSearch App');

        $question = new Question('Enter base url:' . PHP_EOL, 'http://myapp.com');
        $base_url = $helper->ask($input, $output, $question);

        $storage_choices = [
            0 => 'Filesystem',
            1 => 'Sqlite',
            'x' => 'cancel'
        ];
        $question = new ChoiceQuestion(
            'Choose a storage type:' . PHP_EOL,
            $storage_choices,
            array_keys($storage_choices)
        );
        $question->setErrorMessage('Ungültige Eingabe');
        $storage_choice = $helper->ask($input, $output, $question);

        if($storage_choice === 'x') {
            $output->writeln('Command canceled, exit console.');
            exit;
        }
        switch($storage_choice) {
            case 0:
                $storage_type = 'Filesystem';
                $storage_path = realpath(__DIR__ . '/../../../storage/filesystem');
                break;
            case 1:
                $storage_type = 'Sqlite';
                $storage_path = realpath(__DIR__ . '/../../../storage/sqlite');
        }
        if(!file_put_contents(
            realpath(__DIR__ . '/../..') . '/env.php', 
            self::get_env_file($base_url, $storage_type, $storage_path)
        )) {
            $io->error('Could not write src/env.php file, exit console.');  
            exit; 
        }
        $io->success('Config file src/env.php successfully generated, exit console.');  
   
    }

    protected static function get_env_file($base_url, $storage_type, $storage_path)
    {
        return <<<EOT
<?php
use Monolog\Logger;

define('API_BASE_URL', '$base_url');

define('STORAGE_TYPE', '$storage_type');
define('STORAGE_PATH', '$storage_path');

// Logger Config -->
define('LOGROTATE_MAX_FILES', 7);
// Mögliche Log Levels: DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY
define('LOG_LEVEL', Logger::DEBUG);
EOT;
    }
}



















