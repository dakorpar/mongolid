<?php
namespace Mongolid\Connection;

use MongoDB\Client;

/**
 * Represents a single connection with the database.
 */
class Connection
{
    /**
     * The default database where mongolid will store the documents.
     *
     * @var string
     */
    public $defaultDatabase = 'mongolid';

    /**
     * MongoDB Client object that represents this connection.
     *
     * @var Client
     */
    protected $client;

    /**
     * Constructs a new Mongolid connection. It uses the same constructor
     * parameters as the original MongoDB\Client constructor.
     *
     * @see   http://php.net/manual/en/mongodb-driver-manager.construct.php
     *
     * @param string $server        the specified connection string
     * @param array  $options       the mongodb client options
     * @param array  $driverOptions the mongodb driver options when opening a connection
     */
    public function __construct(
        string $server = 'mongodb://localhost:27017',
        array $options = [],
        array $driverOptions = []
    ) {
        // In order to work with PHP arrays instead of with objects
        $driverOptions['typeMap'] = ['array' => 'array', 'document' => 'array'];

        $this->findDefaultDatabase($server);

        $this->client = new Client($server, $options, $driverOptions);
    }

    /**
     * Getter for Client instance.
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Getter for Manager instance.
     *
     * @return \MongoDB\Driver\Manager
     */
    public function getManager()
    {
        return $this->getClient()->getManager();
    }

    /**
     * Find and stores the default database in the connection string.
     *
     * @param string $connectionString mongoDB connection string
     */
    protected function findDefaultDatabase(string $connectionString)
    {
        preg_match('/\S+\/(\w*)/', $connectionString, $matches);

        if ($matches[1] ?? null) {
            $this->defaultDatabase = $matches[1];
        }
    }
}
