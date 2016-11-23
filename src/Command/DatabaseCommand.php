<?php

namespace WemsCA\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseCommand extends Command
{

    const INIT = 'init';

    /** @var \PDO */
    private $dbh;

    /** @var string */
    private $databasePath;

    /**
     * @param \PDO $dbh
     */
    public function setDb(\PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    /**
     * @param string $path
     */
    public function setDatabasePath($path)
    {
        $this->databasePath = $path;
    }

    protected function configure()
    {
        $this
            ->setName('app:db')
            ->setDescription('Sets up the application database')
            ->setHelp('--init to initialise the database (idempotent)')
            ->addOption(self::INIT);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasOption(self::INIT) && $input->getOption(self::INIT)) {
            $this->initialiseDatabaseSchema();
        }
    }

    private function initialiseDatabaseSchema()
    {
        if (!file_exists($this->databasePath)) {
            throw new \InvalidArgumentException('Cannot read database schema at ' . $this->databasePath);
        }

        $databaseSchema = file_get_contents($this->databasePath);

        $this->dbh->query($databaseSchema);
    }
}
