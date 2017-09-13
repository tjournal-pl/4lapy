<?

namespace FourPaws\Console\Command;

use FourPaws\Migrator\Factory;
use FourPaws\Migrator\Installer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateData
 *
 * @package FourPaws\Console\Command
 *
 * Миграция данных со старого сайта из консоли
 */
class MigrateData extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    const ARG_MIGRATE_LIST = 'migrate-list';
    
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setLogger(new Logger('Migrator', [new StreamHandler(STDOUT, Logger::DEBUG)]));
        
        $migratorInstaller = new Installer($this->logger);
        if (!$migratorInstaller->isInstalled()) {
            $this->logger->warning('Migrator tables is not installed. Installing...');
            $migratorInstaller->doInstall();
        }
    }
    
    protected function configure()
    {
        $this->setName('migrate')
             ->setDescription('Migrate data via rest')
             ->addArgument(self::ARG_MIGRATE_LIST,
                           InputArgument::IS_ARRAY,
                           'Migration type, one or more of this: users, news, articles, shops, sale')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force migrate (disable time period check)');
    }
    
    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->log(LogLevel::INFO, 'Migration start');
        
        foreach ($input->getArgument(self::ARG_MIGRATE_LIST) as $type) {
            $client = (new Factory())->getClient($type);
            $client->save();
        }
        
        $this->logResult();
        
        return null;
    }
    
    /**
     * @param        $level
     * @param string $message
     * @param array  $context
     */
    protected function log($level, $message = '', array $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
    
    /**
     * Log final result of migration
     */
    protected function logResult()
    {
        /**
         * @todo log a migration result
         */
        $this->log(LogLevel::INFO, 'Data migration done');
    }
}