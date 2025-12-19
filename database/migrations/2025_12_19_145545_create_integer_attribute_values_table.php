<?php

use App\Migration\AttributeMigration;
use Illuminate\Database\Schema\Blueprint;

return new class extends AttributeMigration
{
    protected function getAttributeType(): string
    {
        return 'integer';
    }

    protected function defineValueColumn(Blueprint $table): void
    {
          $table->integer('value');
    }
};
