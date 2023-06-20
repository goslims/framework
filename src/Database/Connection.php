<?php
namespace SLiMS\Database;

use Illuminate\Database\Capsule\Manager;

class Connection extends Manager
{
    public function __construct()
    {
        parent::__construct();
    }

    public function register(array $connections, string $defaultConnection)
    {
        $this->addConnection($connections[$defaultConnection], 'default');
        foreach($connections as $connection => $detail) {
            if ($connection === $defaultConnection) continue;
            $this->addConnection($detail, $connection);
        }
    }
}