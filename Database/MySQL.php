<?php
namespace Dizda\CloudBackupBundle\Database;

use Symfony\Component\Process\ProcessUtils;

/**
 * Class MySQL.
 *
 * @author  Jonathan Dizdarevic <dizda@dizda.fr>
 */
class MySQL extends BaseDatabase
{
    const DB_PATH = 'mysql';

    private $allDatabases;
    private $database;
    private $auth = '';
    private $restoreAuth = '';
    private $fileName;
    private $ignoreTables = '';

    /**
     * DB Auth.
     *
     * @param array  $params
     * @param string $basePath
     */
    public function __construct($params, $basePath, $restoreFolder)
    {
        parent::__construct($basePath, $restoreFolder);

        $params = $params['mysql'];
        $this->allDatabases = $params['all_databases'];
        $this->database     = $params['database'];
        $this->auth         = '';

        if ($this->allDatabases) {
            $this->database = '--all-databases';
            $this->fileName = 'all-databases.sql';
        } else {
            $this->fileName = $this->database.'.sql';
        }

        if (isset($params['ignore_tables'])) {
            foreach ($params['ignore_tables'] as $ignoreTable) {
                if ($this->allDatabases) {
                    if (false === strpos($ignoreTable, '.')) {
                        throw new \LogicException(
                            'When dumping all databases both database and table must be specified when ignoring table'
                        );
                    }
                    $this->ignoreTables .= sprintf('--ignore-table=%s ', $ignoreTable);
                } else {
                    $this->ignoreTables .= sprintf('--ignore-table=%s.%s ', $this->database, $ignoreTable);
                }
            }
        }

        /* if user is set, we add authentification */
        if ($params['db_user']) {
            $this->auth = sprintf('-u%s', $params['db_user']);
            $this->restoreAuth = $this->auth;

            if ($params['db_password']) {
                $this->auth = sprintf("--host=\"%s\" --port=\"%d\" --user=\"%s\" --password=\"%s\"", $params['db_host'], $params['db_port'], $params['db_user'], $params['db_password']);
                $this->restoreAuth = sprintf("--user=\"%s\" --password=\"%s\"", $params['db_user'], $params['db_password']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump()
    {
        $this->preparePath();
        $this->execute($this->getCommand());
    }

    /**
     * {@inheritdoc}
     */
    public function restore()
    {
        $this->execute($this->getRestoreCommand());
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommand()
    {
        return sprintf('mysqldump %s %s %s > %s',
            $this->auth,
            $this->database,
            $this->ignoreTables,
            ProcessUtils::escapeArgument($this->dataPath.$this->fileName)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getRestoreCommand()
    {
        return sprintf('mysql %s %s < %s/mysql/%s',
            $this->restoreAuth,
            $this->database,
            $this->restoreFolder,
            ProcessUtils::escapeArgument($this->fileName)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'MySQL';
    }
}
