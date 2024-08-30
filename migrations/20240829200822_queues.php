<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Queues extends AbstractMigration
{
    public function change(): void
    {
        $this->table('queue')
            ->addColumn('name', 'string')
            ->addColumn('short_name', 'string')
            ->addColumn('date_created', 'datetime')
            ->create();

        $this->table('queue_subscription')
            ->addColumn('queue_id', 'integer')
            ->addColumn('task_name', 'string')
            ->addColumn('controller', 'string')
            ->addColumn('date_created', 'datetime')
            ->addColumn('date_last_task', 'datetime')
            ->addColumn('deleted', 'boolean')
            ->addIndex('task_name')
            ->create();

        $this->table('queue_task')
            ->addColumn('subscription_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('message_id', 'string', ['null' => true])
            ->addColumn('date_created', 'datetime')
            ->addColumn('date_picked_up', 'datetime', ['null' => true])
            ->addColumn('date_completed', 'datetime', ['null' => true])
            ->addColumn('parameters', 'json')
            ->addColumn('status', 'json')
            ->addColumn('time_taken', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('error', 'text', ['null' => true])
            ->addColumn('priority', 'integer', ['default' => 3])
            ->addColumn('ttl', 'integer', ['null' => true])
            ->addForeignKey('subscription_id', 'queue_subscription', 'id')
            ->addIndex('message_id')
            ->addIndex('date_picked_up')
            ->create();
    }
}
