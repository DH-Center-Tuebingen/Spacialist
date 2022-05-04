<?php

namespace App;

use App\Exceptions\CannotChangeColumnException;
use App\Exceptions\CannotDeleteTableException;
use App\Exceptions\ColumnAlreadyExistsException;
use App\Exceptions\ColumnDoesNotExistException;
use App\Exceptions\MissingKeyException;
use App\Exceptions\TableAlreadyExistsException;
use App\Exceptions\TableDoesNotExistException;
use Exception;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

class MigrationHelper
{
    private $association;

    private const coreTables = [
        'users',
    ];

    private const allowsChange = [
        'bigInteger', 'binary', 'boolean', 'char', 'date', 'dateTime', 'dateTimeTz', 'decimal', 'integer', 'json', 'jsonb', 'longText', 'mediumText', 'smallInteger', 'string', 'text', 'time', 'unsignedBigInteger', 'unsignedInteger', 'unsignedSmallInteger', 'uuid',
    ];

    function __construct($associatedPlugin) {
        if(empty($associatedPlugin)) {
            throw new Exception("Associated Plugin parameter must not be empty.");
        }

        if(!Plugin::where('name', $associatedPlugin)->exists()) {
            throw new Exception("No plugin with the name $associatedPlugin is installed.");
        }

        $this->association = $associatedPlugin;
    }

    private static function isCoreTable(string $tbl) : bool {
        return in_array($tbl, self::coreTables);
    }

    private static function canBeChanged(string $columnType) : bool {
        return in_array($columnType, self::allowsChange);
    }

    private static function createFromType(Blueprint $table, string $columnName, MigrationOption $options) : ColumnDefinition {
        $addedColumn = null;
        switch($options->getType()) {
            case 'integer':
                $addedColumn = $table->integer($columnName);
                if($options->isUnsigned()) {
                    $addedColumn->unsigned();
                }
                break;
            case 'boolean':
                $addedColumn = $table->boolean($columnName);
                break;
            case 'text':
                $addedColumn = $table->text($columnName);
                break;
            case 'varchar':
            case 'string':
                $addedColumn = $table->string($columnName, $options->getStrLength());
                break;
            case 'jsonb':
                $addedColumn = $table->jsonb($columnName);
                break;
            case 'float':
                $addedColumn = $table->float($columnName, $options->getPrecision(), $options->getScale(), $options->isUnsigned());
                break;
            case 'double':
                $addedColumn = $table->double($columnName, $options->getPrecision(), $options->getScale(), $options->isUnsigned());
                break;
            case 'date':
                $addedColumn = $table->date($columnName);
                break;
            case 'timestamp':
                if($options->withTz()) {
                    $addedColumn = $table->timestampTz($columnName);
                } else {
                    $addedColumn = $table->timestamp($columnName);
                }
                break;
        }

        return $addedColumn;
    }

    private static function addModifiers(ColumnDefinition $column, MigrationOption $options) : void {
        if($options->isNullable()) {
            $column->nullable();
        }
        if($options->isUnique()) {
            $column->unique();
        }
        if($options->hasDefault()) {
            $column->default($options->getDefault());
        }
        if($options->hasComment()) {
            $column->comment($options->getComment());
        }
    }

    private static function addColumn(Blueprint $table, string $columnName, MigrationOption $options) : void {
        $addedColumn = null;
        if($options->isPrimary()) {
            $addedColumn = $table->increments($columnName);
        } else {
            $addedColumn = self::createFromType($table, $columnName, $options);
        }
        self::addModifiers($addedColumn, $options);
    }

    private static function applyConstraint(Blueprint $table, string $columnName, array $options) {
        if($options['type'] == 'foreign') {
                        
                    }

        if($options['type'] == 'foreign') {
            if(!array_key_exists('references', $options)) {
                throw new MissingKeyException("Missing key in array. Key 'references' not found in input array.");
            }
            if(!array_key_exists('on', $options)) {
                throw new MissingKeyException("Missing key in array. Key 'on' not found in input array.");
            }
            $constraint = $table->foreign($columnName)
                ->references($options['references'])
                ->on($options['on']);
            if(array_key_exists('on_delete', $options)) {
                $constraint = $constraint->onDelete($options['on_delete']);
            }
            if(array_key_exists('on_update', $options)) {
                $constraint = $constraint->onUpdate($options['on_update']);
            }
        }
    }

    public function requireExtension($extension) {
        Schema::getConnection()->statement('CREATE EXTENSION IF NOT EXISTS ' . $extension);
    }

    public function extendTable(String $tbl, string $column, array $options, array $constraints = [], bool $strictMode = true) : void {
        activity()->disableLogging();

        if(!Schema::hasTable($tbl)) {
            activity()->enableLogging();
            throw new TableDoesNotExistException("Table $tbl does not exist. Unable to extend it with $column.");
        }

        if(Schema::hasColumn($tbl, $column)) {
            activity()->enableLogging();
            if(!$strictMode) {
                return;
            }

            throw new ColumnAlreadyExistsException("Column $column already exists in table $tbl. Unable to extend it.");
        }

        $opts = new MigrationOption($options);

        Schema::table($tbl, function(Blueprint $table) use ($column, $opts, $constraints) {
            self::addColumn($table, $column, $opts);

            foreach($constraints as $constraint) {
                self::applyConstraint($table, $column, $constraint);
            }
        });


        activity()->enableLogging();
    }

    public function createTable(string $tbl, array $columns, array $options, bool $strictMode = true) : void {
        activity()->disableLogging();

        if(Schema::hasTable($tbl)) {
            activity()->enableLogging();
            if(!$strictMode) {
                return;
            }
            throw new TableAlreadyExistsException("Table $tbl already exists. Unable to create it.");
        }

        Schema::create($tbl, function(Blueprint $table) use ($columns, $options) {
            // options [primary: 'id', timestamps: true]
            $constraints = [];
            foreach($columns as $col) {
                if(!array_key_exists('name', $col)) {
                    // Error occurred, drop table to reset state
                    $table->drop();
                    activity()->enableLogging();
                    throw new MissingKeyException("Missing key in array. Key 'name' not found in input array.");
                }

                if(!array_key_exists('options', $col)) {
                    // Error occurred, drop table to reset state
                    $table->drop();
                    activity()->enableLogging();
                    throw new MissingKeyException("Missing key in array. Key 'options' not found in input array.");
                }
                $columnOptions = $col['options'];
                if(array_key_exists('constraints', $col)) {
                    $constraints[$col['name']] = $col['constraints'];
                }
                if(!empty($options['primary']) && $options['primary'] == $col['name']) {
                    $columnOptions['primary'] = true;
                }
                $migrationOptions = new MigrationOption($columnOptions);

                self::addColumn($table, $col['name'], $migrationOptions);
            }

            if(!empty($options['timestamps'])) {
                $table->timestamps();
            }

            foreach($constraints as $column => $columnConstraints) {
                foreach($columnConstraints as $conDef) {
                    try {
                        self::applyConstraint($table, $column, $conDef);
                    } catch(MissingKeyException $e) {
                        // Error occurred, drop table to reset state
                        $table->drop();
                        activity()->enableLogging();
                    }
                }
            }
        });

        activity()->enableLogging();
    }

    public function dropTable(string $tbl, bool $strictMode = true) : void {
        activity()->disableLogging();

        if(!Schema::hasTable($tbl)) {
            activity()->enableLogging();
            if(!$strictMode) {
                return;
            }
            throw new TableDoesNotExistException("Table $tbl does not exist. Unable to drop it.");
        }

        if(self::isCoreTable($tbl)) {
            activity()->enableLogging();
            throw new CannotDeleteTableException("Table $tbl is a Spacialist core table. Unable to drop it.");
        }

        Schema::drop($tbl);

        activity()->enableLogging();
    }

    public function createTableWithFn(string $tbl, $fn, bool $strictMode = true) : void {
        activity()->disableLogging();

        if(Schema::hasTable($tbl)) {
            activity()->enableLogging();
            if(!$strictMode) {
                return;
            }
            throw new TableAlreadyExistsException("Table $tbl already exists. Unable to create it.");
        }

        Schema::create($tbl, $fn);

        activity()->enableLogging();
    }

    public function renameTable(string $currentName, string $newName, bool $strictMode = true) : void {
        activity()->disableLogging();

        if(Schema::hasTable($newName)) {
            activity()->enableLogging();
            if(!$strictMode) {
                return;
            }
            throw new TableAlreadyExistsException("Table $newName already exists. Unable to rename $currentName.");
        }

        if(!Schema::hasTable($currentName)) {
            activity()->enableLogging();
            throw new TableDoesNotExistException("Table $currentName does not exist. Unable to rename it to $newName");
        }

        Schema::rename($currentName, $newName);

        activity()->enableLogging();
    }

    public function renameColumn(string $tbl, string $currentName, string $newName, bool $strictMode = true) : void {
        activity()->disableLogging();

        if(!Schema::hasTable($tbl)) {
            activity()->enableLogging();
            throw new TableDoesNotExistException("Table $tbl does not exist. Unable to rename one of it's columns.");
        }

        if(!Schema::hasColumn($tbl, $currentName)) {
            activity()->enableLogging();
            if(!$strictMode) {
                return;
            }
            throw new ColumnDoesNotExistException("Column $currentName does not exist in table $tbl. Unable to rename it.");
        }

        if(Schema::hasColumn($tbl, $newName)) {
            activity()->enableLogging();
            if(!$strictMode) {
                return;
            }
            throw new ColumnAlreadyExistsException("Column $newName already exist in table $tbl. Unable to rename $currentName to it.");
        }

        Schema::table($tbl, function (Blueprint $table) use ($currentName, $newName) {
            $table->renameColumn($currentName, $newName);
        });

        activity()->enableLogging();
    }

    public function dropColumn(string $tbl, string $column, bool $strictMode = true) : void {
        activity()->disableLogging();

        if(!Schema::hasTable($tbl)) {
            activity()->enableLogging();
            throw new TableDoesNotExistException("Table $tbl does not exist. Unable to drop it's columns.");
        }

        if(!Schema::hasColumn($tbl, $column)) {
            activity()->enableLogging();
            if(!$strictMode) {
                return;
            }
            throw new ColumnDoesNotExistException("Column $column does not exist in table $tbl. Unable to drop it.");
        }

        Schema::table($tbl, function (Blueprint $table) use ($column) {
            $table->dropColumn($column);
        });

        activity()->enableLogging();
    }

    public function changeColumn(string $tbl, string $column, array $options) : void {
        activity()->disableLogging();

        if(!Schema::hasTable($tbl)) {
            activity()->enableLogging();
            throw new TableDoesNotExistException("Table $tbl does not exist. Unable to change it's columns.");
        }

        if(!Schema::hasColumn($tbl, $column)) {
            activity()->enableLogging();
            throw new ColumnDoesNotExistException("Column $column does not exist in table $tbl. Unable to change it.");
        }

        $opts = new MigrationOption($options);

        if(!self::canBeChanged($opts->getType()) || self::isCoreTable($tbl)) {
            activity()->enableLogging();
            throw new CannotChangeColumnException("Column $column can not be changed.");
        }

        Schema::table($tbl, function (Blueprint $table) use ($column, $opts) {
            $changedColumn = null;
            $changedColumn = self::createFromType($table, $column, $opts);
            self::addModifiers($changedColumn, $opts);
            $changedColumn->change();
        });

        activity()->enableLogging();
    }
}