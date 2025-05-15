<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250519101233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users and vpn_configs tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');

        $this->addSql('
            CREATE TABLE users (
                id UUID NOT NULL DEFAULT uuid_generate_v4(),
                telegram_id BIGINT NOT NULL,
                username VARCHAR(255) DEFAULT NULL,
                first_name VARCHAR(255) DEFAULT NULL,
                last_name VARCHAR(255) DEFAULT NULL,
                balance DECIMAL(10,2) NOT NULL DEFAULT \'0.00\',
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9C38AFC1C ON users (telegram_id)');

        $this->addSql('
            CREATE TABLE vpn_configs (
                id UUID NOT NULL DEFAULT uuid_generate_v4(),
                user_id UUID NOT NULL,
                protocol VARCHAR(20) NOT NULL,
                status VARCHAR(20) NOT NULL,
                config_data TEXT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('CREATE INDEX IDX_8F5B3B6AA76ED395 ON vpn_configs (user_id)');

        $this->addSql('
            ALTER TABLE vpn_configs
            ADD CONSTRAINT FK_8F5B3B6AA76ED395
            FOREIGN KEY (user_id)
            REFERENCES users (id)
            ON DELETE CASCADE
            NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vpn_configs DROP CONSTRAINT FK_8F5B3B6AA76ED395');
        $this->addSql('DROP TABLE vpn_configs');
        $this->addSql('DROP TABLE users');
    }
}
