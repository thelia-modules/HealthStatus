<?php

namespace HealthStatus\Service;


use Propel\Runtime\Propel;

class DatabaseConfig
{
    public function getDatabaseConfig(): array
    {
        $mysqlCon = Propel::getConnection();

        $mySQLVersion = $mysqlCon->query("SELECT @@version")->fetchColumn();
        $driver = $mysqlCon->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $databaseName = $mysqlCon->query("SELECT DATABASE()")->fetchColumn();
        $host = $mysqlCon->query("SELECT @@hostname")->fetchColumn();
        $user = $mysqlCon->query("SELECT CURRENT_USER()")->fetchColumn();
        $clientVersion = $mysqlCon->getAttribute(\PDO::ATTR_CLIENT_VERSION);
        $charset = $mysqlCon->query("SELECT @@character_set_database")->fetchColumn();
        $collation = $mysqlCon->query("SELECT @@collation_database")->fetchColumn();
        $maxAllowedPacket = $mysqlCon->query("SHOW VARIABLES LIKE 'max_allowed_packet'")->fetchColumn(1);
        $maxConnections = $mysqlCon->query("SHOW VARIABLES LIKE 'max_connections'")->fetchColumn(1);

        $databaseConfig = [
            'mysql' => [
                'label' => 'Server Version',
                'value' => $mySQLVersion,
            ],
            'driver' => [
                'label' => 'Driver',
                'value' => $driver,
            ],
            'client_version' => [
                'label' => 'Client Version',
                'value' => $clientVersion,
            ],
            'host' => [
                'label' => 'Host',
                'value' => $host,
            ],
            'user' => [
                'label' => 'User',
                'value' => $user,
            ],
            'database' => [
                'label' => 'Database Name',
                'value' => $databaseName,
            ],
            'charset' => [
                'label' => 'Charset',
                'value' => $charset,
            ],
            'collation' => [
                'label' => 'Collation',
                'value' => $collation,
            ],
            'max_allowed_packet' => [
                'label' => 'Max Allowed Packet',
                'value' => $maxAllowedPacket,
            ],
            'max_connections' => [
                'label' => 'Max Connections',
                'value' => $maxConnections,
            ],
        ];

        return $databaseConfig;
    }
}
