<?php

namespace Charcoal\Admin\Script\Object\Table;

use PDO;
use PDOStatement;

use Closure;
use Countable;
use Traversable;
use Exception;
use ReflectionMethod;
use RuntimeException;
use UnexpectedValueException;
use InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

// From 'charcoal-property'
use Charcoal\Property\IdProperty;
use Charcoal\Property\PropertyField;
use Charcoal\Property\PropertyInterface;

// From 'charcoal-app'
use Charcoal\App\Script\ArgScriptTrait;

// From 'charcoal-admin'
use Charcoal\Admin\AdminScript;

/**
 * Alter an object's primary key (SQL source).
 */
class AlterPrimaryKeyScript extends AdminScript
{
    use ArgScriptTrait;

    /**
     * The model to alter.
     *
     * @var ModelInterface|null
     */
    protected $targetModel;

    /**
     * The related models to update.
     *
     * @var ModelInterface[]|null
     */
    protected $relatedModels;

    /**
     * The related model properties to update.
     *
     * @var PropertyInterface[]|null
     */
    protected $relatedProperties;

    /**
     * The model's old primary key name.
     *
     * @var string|null
     */
    protected $oldPrimaryKey;

    /**
     * The model's current/new primary key name.
     *
     * @var string|null
     */
    protected $newPrimaryKey;

    /**
     * The function to generate a unique ID.
     *
     * @var callable|null
     */
    protected $idGenerator;

    /**
     * @return void
     */
    protected function init()
    {
        parent::init();

        $this->setDescription(
            'The <underline>object/table/alter-primary-key</underline> script replaces '.
            'the existing primary key with the new definition from the given model\'s metadata.'
        );
    }

    /**
     * Run the script.
     *
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        try {
            $this->start();
        } catch (Exception $e) {
            $this->climate()->error($e->getMessage());
        }

        return $response;
    }

    /**
     * Execute the prime directive.
     *
     * @return self
     */
    public function start()
    {
        $cli = $this->climate();

        $cli->br();
        $cli->bold()->underline()->out('Alter Model\'s Primary Key');
        $cli->br();

        if ($this->dryRun()) {
            $cli->shout('This command does not support --dry-run');
            $cli->br();
            $cli->whisper('Canceled Conversion');

            return $this;
        }

        $objType = $this->argOrInput('target_model');
        $this->setTargetModel($objType);

        $model  = $this->targetModel();
        $source = $model->source();
        $table  = $source->table();

        $cli->comment(sprintf('The "%s" table will be altered.', $table));
        $cli->br();
        $cli->shout('This process is destructive. A backup should be made before proceeding.');
        $cli->br();
        $cli->red()->flank('Analyse your tables before proceeding. Not all fields can or might be affected.', '!');
        $cli->br();

        $input = $cli->confirm('Continue?');
        if ($input->confirmed()) {
            $cli->info('Starting Conversion');
        } else {
            $cli->info('Canceled Conversion');

            return $this;
        }

        $cli->br();

        $dbh = $source->db();
        if (!$dbh) {
            $cli->error(
                'Could not instantiate a database connection.'
            );

            return $this;
        }

        if (!$source->tableExists()) {
            $cli->error(
                sprintf(
                    'The table "%s" does not exist. This script can only alter existing tables.',
                    $table
                )
            );

            return $this;
        }

        $oldKey = $this->oldPrimaryKey();
        $newKey = $this->newPrimaryKey();

        $dbh->query(
            strtr(
                'LOCK TABLES `%table` WRITE',
                [
                    '%table' => $table,
                ]
            )
        );

        $this->prepareProperties($oldKey, $newKey, $oldProp, $newProp);

        if ($newProp->mode() === $oldProp->mode()) {
            $cli->error(
                sprintf(
                    'The ID is already %s. Canceling conversion.',
                    $this->labelFromMode($newProp)
                )
            );
            $dbh->query('UNLOCK TABLES');

            return $this;
        }

        $newField = $this->propertyField($newProp);
        $oldField = $this->propertyField($oldProp);
        $oldField->setExtra('');

        if (!$this->quiet()) {
            $this->describeConversion($newProp, $oldProp);
        }

        $this->convertIdField($newProp, $newField, $oldProp, $oldField);

        $dbh->query('UNLOCK TABLES');

        if (!$this->quiet()) {
            $cli->br();
            $cli->info('Success!');
        }

        return $this;
    }



    // Alter Table
    // =========================================================================

    /**
     * Retrieve the old and new ID properties.
     *
     * @param  string          $oldKey  The previous key.
     * @param  string          $newKey  The new key.
     * @param  IdProperty|null $oldProp If provided, then it is filled with an instance of IdProperty.
     * @param  IdProperty|null $newProp If provided, then it is filled with an instance of IdProperty.
     * @throws RuntimeException If the $oldKey does not exist.
     * @return IdProperty[]
     */
    protected function prepareProperties($oldKey, $newKey, &$oldProp = null, &$newProp = null)
    {
        $model  = $this->targetModel();
        $source = $model->source();

        /**
         * Either:
         * - TRUE if the $oldKey exists in the model's datasource.
         * - FALSE if the $oldKey does NOT exist in the model's datasource.
         * - NULL if the $oldKey does NOT exist in the model's datasource and is different from $newKey.
         *
         * @var boolean|null
         */
        $oldKeyExists = null;

        if ($this->isPrimaryKeyDifferent()) {
            $newProp = $model->property($newKey)->setAllowNull(false);
            $oldProp = clone $newProp;
            $oldProp->setIdent($oldKey);
        } else {
            $oldKeyExists = false;

            $oldProp = $model->property($oldKey)->setAllowNull(false);
            $newProp = clone $oldProp;
            $newProp->setIdent($newKey);
        }

        $sql  = strtr(
            'SHOW COLUMNS FROM `%table`',
            [
                '%table' => $source->table(),
            ]
        );
        $cols = $source->db()->query($sql, PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            if ($col['Field'] !== $oldKey) {
                continue;
            }

            $oldKeyExists = true;

            if (!$this->quiet()) {
                $this->climate()->comment(
                    sprintf('Evaluating the current `%s` column.', $oldKey)
                );
            }

            if (preg_match('~\bINT\(?(?:$|\b)~i', $col['Type'])) {
                $oldProp->setMode(IdProperty::MODE_AUTO_INCREMENT);
            } elseif (preg_match('~(?:^|\b)(?:VAR)?CHAR\(13\)(?:$|\b)~i', $col['Type'])) {
                $oldProp->setMode(IdProperty::MODE_UNIQID);
            } elseif (preg_match('~(?:^|\b)(?:VAR)?CHAR\(36\)(?:$|\b)~i', $col['Type'])) {
                $oldProp->setMode(IdProperty::MODE_UUID);
            } else {
                $oldProp->setMode(IdProperty::MODE_CUSTOM);
            }

            break;
        }

        if (!$oldKeyExists) {
            throw new RuntimeException(
                sprintf(
                    'The model [%1$s] does not have the target field [%2$s]',
                    get_class($model),
                    $oldKey
                )
            );
        }

        return [
            'old' => $oldProp,
            'new' => $newProp,
        ];
    }

    /**
     * Retrieve a label for the ID's mode.
     *
     * @param  string|IdProperty $mode The mode or property to resolve.
     * @throws UnexpectedValueException If the ID mode is invalid.
     * @return string
     */
    protected function labelFromMode($mode)
    {
        if ($mode instanceof IdProperty) {
            $mode = $mode->mode();
        }

        switch ($mode) {
            case IdProperty::MODE_AUTO_INCREMENT:
                return 'auto-increment';

            case IdProperty::MODE_UNIQID:
                return 'uniqid()';

            case IdProperty::MODE_UUID:
                return 'RFC-4122 UUID';

            case IdProperty::MODE_CUSTOM:
                return 'custom';
        }

        throw new UnexpectedValueException(sprintf(
            'The ID mode was not recognized: %s',
            is_object($mode) ? get_class($mode) : gettype($mode)
        ));
    }

    /**
     * Retrieve a label for the property.
     *
     * @param  IdProperty $prop The new ID property to analyse.
     * @return string|null
     */
    protected function labelFromProp(IdProperty $prop)
    {
        $mode = $prop->mode();
        switch ($mode) {
            case IdProperty::MODE_AUTO_INCREMENT:
                return 'auto-increment ID';

            case IdProperty::MODE_CUSTOM:
                return 'custom ID';

            default:
                $label = $this->labelFromMode($mode);
                if ($label) {
                    return sprintf('auto-generated ID (%s)', $label);
                } else {
                    return 'auto-generated ID';
                }
        }

        return null;
    }

    /**
     * Describe what we are converting to.
     *
     * @param  IdProperty $newProp The new ID property to analyse.
     * @param  IdProperty $oldProp The previous ID property to analyse.
     * @return self
     */
    protected function describeConversion(IdProperty $newProp, IdProperty $oldProp = null)
    {
        if ($oldProp) {
            $new  = $this->labelFromProp($newProp);
            $old  = $this->labelFromProp($oldProp);
            $desc = sprintf('Converting to %s from %s.', $new, $old);
        } else {
            $new  = $this->labelFromProp($newProp);
            $desc = sprintf('Converting to %s.', $new);
        }

        $this->climate()->comment($desc);

        return $this;
    }

    /**
     * Retrieve the given property's field.
     *
     * @param  IdProperty $prop The property to retrieve the field from.
     * @return PropertyField
     */
    protected function propertyField(IdProperty $prop)
    {
        $fields = $prop->fields('');

        return reset($fields);
    }

    /**
     * Retrieve the target model's rows.
     *
     * @return array|Traversable
     */
    private function fetchTargetRows()
    {
        $model  = $this->targetModel();
        $source = $model->source();

        $sql = strtr(
            'SELECT %key FROM `%table`',
            [
                '%table' => $source->table(),
                '%key'   => $this->oldPrimaryKey(),
            ]
        );

        return $source->db()->query($sql, PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve the target model's old primary key name.
     *
     * @return boolean
     */
    private function isPrimaryKeyDifferent()
    {
        return $this->climate()->arguments->defined('old_key');
    }

    /**
     * Retrieve the target model's old primary key name.
     *
     * @return string|null
     */
    private function oldPrimaryKey()
    {
        if ($this->oldPrimaryKey === null) {
            if ($this->isPrimaryKeyDifferent()) {
                $oldKey = $this->climate()->arguments->get('old_key');
            } else {
                $oldKey = $this->targetModel()->key();
            }

            $this->oldPrimaryKey = $oldKey;
        }

        return $this->oldPrimaryKey;
    }

    /**
     * Retrieve the target model's current/new primary key name.
     *
     * @return string|null
     */
    private function newPrimaryKey()
    {
        if ($this->newPrimaryKey === null) {
            $model = $this->targetModel();

            if ($this->isPrimaryKeyDifferent()) {
                $newKey = $model->key();
            } else {
                $newKey = sprintf('%s_new', $model->key());
            }

            $this->newPrimaryKey = $newKey;
        }

        return $this->newPrimaryKey;
    }

    /**
     * Describe the given count.
     *
     * @param  array|Traversable $rows The target model's existing rows.
     * @throws InvalidArgumentException If the given argument is not iterable.
     * @return boolean
     */
    private function describeCount($rows = null)
    {
        if ($rows === null) {
            $rows = $this->fetchTargetRows();
        }

        if (!is_array($rows) && !($rows instanceof Traversable)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The rows must be iterable; received %s',
                    is_object($rows) ? get_class($rows) : gettype($rows)
                )
            );
        }

        $cli   = $this->climate();
        $model = $this->targetModel();

        if (is_array($rows) || $rows instanceof Countable) {
            $count = count($rows);
        } elseif ($rows instanceof PDOStatement) {
            $count = $rows->rowCount();
        } else {
            $count = iterator_count($rows);
        }

        if ($count === 0) {
            $cli->comment('The object table is empty.');
            $cli->comment(
                sprintf('Only changing `%s` column.', $model->key())
            );

            return false;
        } elseif ($count === 1) {
            if (!$this->quiet()) {
                $cli->comment('The object table has 1 row.');
            }
        } else {
            if (!$this->quiet()) {
                $cli->comment(
                    sprintf('The object table has %s rows.', $count)
                );
            }
        }

        return true;
    }

    /**
     * Insert the given field.
     *
     * @param  PropertyField $field The new ID field.
     * @param  IdProperty    $prop  The new ID property.
     * @return self
     */
    private function insertNewField(PropertyField $field, IdProperty $prop)
    {
        unset($prop);

        $model  = $this->targetModel();
        $source = $model->source();

        $extra = $field->extra();
        $field->setExtra('');

        // Don't alter table if column name already exists.
        $sql = strtr(
            'SHOW COLUMNS FROM `%table` LIKE "%key"',
            [
                '%table' => $source->table(),
                '%key'   => $field->ident(),
            ]
        );

        $res = $source->db()->query($sql);

        if ($res->fetch(1)) {
            // Column name already exists.
            return $this;
        }

        $sql = strtr(
            'ALTER TABLE `%table` ADD COLUMN %field FIRST',
            [
                '%table' => $source->table(),
                '%field' => $field->sql(),
            ]
        );
        $field->setExtra($extra);

        $source->db()->query($sql);

        return $this;
    }

    /**
     * Drop the primary key from the given field.
     *
     * @param  PropertyField $field The previous ID field.
     * @param  IdProperty    $prop  The previous ID property.
     * @return self
     */
    private function dropPrimaryKey(PropertyField $field, IdProperty $prop)
    {
        $keepId = $this->climate()->arguments->defined('keep_id');
        $model  = $this->targetModel();
        $source = $model->source();
        $dbh    = $source->db();
        $key    = $prop->ident();

        if ($keepId) {
            $field->setIdent(sprintf('%1$s_%2$s', $key, date('YmdHis')));
            $sql = strtr(
                'ALTER TABLE `%table` CHANGE COLUMN `%key` %field, DROP PRIMARY KEY',
                [
                    '%table' => $source->table(),
                    '%field' => $field->sql(),
                    '%key'   => $key,
                ]
            );
        } else {
            $sql = strtr(
                'ALTER TABLE `%table` MODIFY COLUMN %field, DROP PRIMARY KEY',
                [
                    '%table' => $source->table(),
                    '%field' => $field->sql(),
                ]
            );
        }

        $dbh->query($sql);

        return $this;
    }

    /**
     * Set the given field as the primary key.
     *
     * @param  PropertyField $field The new ID field.
     * @param  IdProperty    $prop  The new ID property.
     * @return self
     */
    private function applyPrimaryKey(PropertyField $field, IdProperty $prop)
    {
        unset($prop);

        $model  = $this->targetModel();
        $source = $model->source();

        $sql = strtr(
            'ALTER TABLE `%table` ADD PRIMARY KEY (`%key`)',
            [
                '%table' => $source->table(),
                '%key'   => $field->ident(),
            ]
        );
        $source->db()->query($sql);

        return $this;
    }

    /**
     * Rename the given field.
     *
     * @param  PropertyField $field The field to rename.
     * @param  string        $from  The original field key.
     * @param  string        $to    The new field key.
     * @return self
     */
    private function renameColumn(PropertyField $field, $from, $to)
    {
        $model  = $this->targetModel();
        $source = $model->source();

        $field->setIdent($to);
        $sql = strtr(
            'ALTER TABLE `%table` CHANGE COLUMN `%from` %field',
            [
                '%table' => $source->table(),
                '%field' => $field->sql(),
                '%from'  => $from,
                '%to'    => $to,
            ]
        );
        $source->db()->query($sql);

        return $this;
    }

    /**
     * Remove the given field.
     *
     * @param  PropertyField $field The field to remove.
     * @return self
     */
    private function removeColumn(PropertyField $field)
    {
        $source = $this->targetModel()->source();

        $sql = strtr(
            'ALTER TABLE `%table` DROP COLUMN `%key`',
            [
                '%table' => $source->table(),
                '%key'   => $field->ident(),
            ]
        );
        $source->db()->query($sql);

        return $this;
    }

    /**
     * Convert a primary key column from one format to another.
     *
     * @param  IdProperty    $newProp  The new ID property.
     * @param  PropertyField $newField The new ID field.
     * @param  IdProperty    $oldProp  The previous ID property.
     * @param  PropertyField $oldField The previous ID field.
     * @throws InvalidArgumentException If the new property does not implement the proper mode.
     * @return self
     */
    protected function convertIdField(
        IdProperty $newProp,
        PropertyField $newField,
        IdProperty $oldProp,
        PropertyField $oldField
    ) {
        $cli = $this->climate();

        $keepId = $cli->arguments->defined('keep_id');
        $model  = $this->targetModel();
        $source = $model->source();
        $table  = $source->table();
        $dbh    = $source->db();

        $newKey = $newProp->ident();
        $oldKey = $oldProp->ident();

        $this->insertNewField($newField, $newProp);

        $rows = $this->fetchTargetRows();
        if ($this->describeCount($rows)) {
            if (!$this->quiet()) {
                $cli->br();
                $progress = $cli->progress($rows->rowCount());
            }

            $mode = $newProp->mode();
            switch ($mode) {
                case IdProperty::MODE_AUTO_INCREMENT:
                    $pool = 0;
                    $ids  = function () use (&$pool) {
                        return ++$pool;
                    };
                    break;

                case IdProperty::MODE_CUSTOM:
                    $generator = $this->argOrInput('id_generator');
                    $this->setIdGenerator($generator);
                    $generator = $this->idGenerator();

                    $pool = [];
                    $ids  = function () use (&$pool, $model, $generator) {
                        $id = $generator();
                        while (in_array($id, $pool)) {
                            $id = $generator();
                        }

                        $pool[] = $id;

                        return $id;
                    };
                    break;

                default:
                    $pool = [];
                    $ids  = function () use (&$pool, $newProp) {
                        $id = $newProp->autoGenerate();
                        while (in_array($id, $pool)) {
                            $id = $newProp->autoGenerate();
                        }

                        $pool[] = $id;

                        return $id;
                    };
                    break;
            }

            foreach ($rows as $row) {
                $id  = $ids();
                $sql = strtr(
                    'UPDATE `%table` SET `%newKey` = :new WHERE `%oldKey` = :old',
                    [
                        '%table'  => $table,
                        '%newKey' => $newKey,
                        '%oldKey' => $oldKey,
                    ]
                );
                $source->dbQuery(
                    $sql,
                    [
                        'new' => $id,
                        'old' => $row[$oldKey],
                    ],
                    [
                        'new' => $newField->sqlPdoType(),
                        'old' => $oldField->sqlPdoType(),
                    ]
                );

                if (!$this->quiet()) {
                    $progress->advance();
                }
            }
        }

        $this->dropPrimaryKey($oldField, $oldProp);
        $this->applyPrimaryKey($newField, $newProp);

        /** @todo Alter related tables */
        $this->syncRelatedFields($newProp, $newField, $oldProp, $oldField);

        if (!$keepId) {
            $this->removeColumn($oldField);
        }

        if (!$this->isPrimaryKeyDifferent()) {
            $this->renameColumn($newField, $newKey, $oldKey);
        }

        return $this;
    }

    /**
     * Sync the new primary keys to related models.
     *
     * @param  IdProperty    $newProp  The new ID property.
     * @param  PropertyField $newField The new ID field.
     * @param  IdProperty    $oldProp  The previous ID property.
     * @param  PropertyField $oldField The previous ID field.
     * @throws InvalidArgumentException If the new property does not implement the proper mode.
     * @return self
     */
    protected function syncRelatedFields(
        IdProperty $newProp,
        PropertyField $newField,
        IdProperty $oldProp,
        PropertyField $oldField
    ) {
        unset($newProp, $oldProp, $oldField);

        $cli = $this->climate();
        if (!$this->quiet()) {
            $cli->br();
            $cli->comment('Syncing new IDs to related tables.');
        }

        $related = $cli->arguments->get('related_model');
        if (!$related) {
            $cli->br();
            $input = $cli->confirm('Are there any model(s) related to the target?');
            if (!$input->confirmed()) {
                return $this;
            }

            $related = $this->argOrInput('related_model');
        }
        $this->setRelatedModels($related);

        $target = $this->targetModel();
        $table  = $target->source()->table();
        foreach ($this->relatedModels() as $model) {
            $src = $model->source();
            $tbl = $src->table();
            $dbh = $src->db();

            $dbh->query(
                strtr(
                    'LOCK TABLES
                        `%relatedTable` AS a WRITE,
                        `%sourceTable` AS b WRITE',
                    [
                        '%relatedTable' => $tbl,
                        '%sourceTable'  => $table,
                    ]
                )
            );

            $sql = strtr(
                'UPDATE `%relatedTable` AS a '.
                    'JOIN `%sourceTable` AS b ON a.`%prop` = b.`%oldKey` '.
                    'SET a.`%prop` = b.`%newKey`',
                [
                    '%relatedTable' => $tbl,
                    '%prop'         => $this->relatedProperties[$model->objType()],
                    '%sourceTable'  => $table,
                    '%newKey'       => $newField->ident(),
                    '%oldKey'       => $target->key(),
                ]
            );
            $dbh->query($sql);

            $dbh->query('UNLOCK TABLES');
        }

        return $this;
    }



    // CLI Arguments
    // =========================================================================

    /**
     * Retrieve the script's supported arguments.
     *
     * @return array
     */
    public function defaultArguments()
    {
        static $arguments;

        if ($arguments === null) {
            $validateFieldName = function ($response) {
                return is_string($response) && strlen($response) > 0;
            };

            $validateCallback = function ($response) {
                return is_string($response) && (strpos($callable, '::') > 1 || function_exists($response));
            };

            $validateModel = function ($response) {
                if (strlen($response) === 0) {
                    return false;
                }

                try {
                    $this->modelFactory()->get($response);
                } catch (Exception $e) {
                    unset($e);

                    return false;
                }

                return true;
            };

            $validateModels = function ($response) {
                if (strlen($response) === 0) {
                    return false;
                }

                try {
                    $arr = $this->parseAsArray($response);
                    foreach ($arr as $model) {
                        $this->resolveRelatedModel($model);
                    }
                } catch (Exception $e) {
                    unset($e);

                    return false;
                }

                return true;
            };

            $arguments = [
                'keep_id'       => [
                    'longPrefix'  => 'keep-id',
                    'noValue'     => true,
                    'description' => 'Skip the deletion of the ID field to be replaced.',
                ],
                'id_generator'  => [
                    'longPrefix'   => 'id-generator',
                    'required'     => false,
                    'description'  => 'A function or a method on the model to generate an ID.',
                    'prompt'       => 'What function to generate a unique ID?',
                    'acceptValue'  => $validateCallback->bindTo($this),
                    'defaultValue' => null,
                ],
                'old_key'  => [
                    'longPrefix'   => 'old-key',
                    'required'     => false,
                    'description'  => 'The model\'s deprecated ID field to replace.',
                    'prompt'       => 'What is the model\'s deprecated primary key?',
                    'acceptValue'  => $validateFieldName->bindTo($this),
                    'defaultValue' => null,
                ],
                'target_model'  => [
                    'prefix'      => 'o',
                    'longPrefix'  => 'obj-type',
                    'required'    => true,
                    'description' => 'The object type to alter.',
                    'prompt'      => 'What model must be altered?',
                    'acceptValue' => $validateModel->bindTo($this),
                ],
                'related_model' => [
                    'prefix'      => 'r',
                    'longPrefix'  => 'related-obj-type',
                    'description' => 'Properties of related object types to synchronize (ObjType:propertyIdent,…).',
                    'prompt'      => 'List related models and properties (ObjType:propertyIdent,…):',
                    'acceptValue' => $validateModels->bindTo($this),
                ],
            ];

            $arguments = array_merge(parent::defaultArguments(), $arguments);
        }

        return $arguments;
    }

    /**
     * Retrieve the script's parent arguments.
     *
     * Useful for specialty classes extending this one that might not want
     * options for selecting specific objects.
     *
     * @return array
     */
    public function parentArguments()
    {
        return parent::defaultArguments();
    }

    /**
     * Set the ID generator.
     *
     * @param  mixed $callable A function or method.
     * @throws InvalidArgumentException If the given argument is not a callable function.
     * @return self
     */
    public function setIdGenerator($callable)
    {
        $this->idGenerator = $this->parseIdGenerator($callable);

        return $this;
    }

    /**
     * Parse and validate the given function is callable.
     *
     * @param  mixed $callable A function or method.
     * @throws InvalidArgumentException If the given argument is not a callable function.
     * @return callable
     */
    public function parseIdGenerator($callable)
    {
        if ($callable instanceof Closure) {
            return $callable;
        }

        $class    = null;
        $method   = null;
        $isMethod = false;
        $isModel  = false;
        $bail     = false;

        if (is_array($callable) && count($callable) === 2) {
            list($class, $func) = $callable;
            $isMethod = ($class && $func);
        } elseif (is_string($callable) && strpos($callable, '::') > 1) {
            list($class, $func) = explode('::', $callable);
            $isMethod = ($class && $func);
        }

        if ($isMethod) {
            $model   = $this->targetModel();
            $isModel = is_a($model, $class);

            $method = new ReflectionMethod($class, $func);
            if ($isModel && $method->isPublic()) {
                return $method->getClosure($model);
            } elseif ($method->isStatic() && $method->isPublic()) {
                return $callable;
            } else {
                $bail = true;
            }
        }

        if ($bail || !(is_string($callable) && function_exists($callable))) {
            throw new InvalidArgumentException(
                sprintf(
                    'The ID generator must be callable, received: %s',
                    is_object($callable)
                        ? get_class($callable)
                        : (is_string($callable)
                            ? $callable
                            : gettype($callable)
                        )
                )
            );
        }

        return $callable;
    }

    /**
     * Retrieve the ID generator.
     *
     * @throws RuntimeException If a function has not been defined.
     * @return callable
     */
    public function idGenerator()
    {
        if (!isset($this->idGenerator)) {
            throw new RuntimeException('A function to generate a unique ID must be provided.');
        }

        return $this->idGenerator;
    }

    /**
     * Set the model to alter.
     *
     * @param  string|ModelInterface $model An object model.
     * @throws InvalidArgumentException If the given argument is not a model.
     * @return self
     */
    public function setTargetModel($model)
    {
        if (is_string($model)) {
            $model = $this->modelFactory()->get($model);
        }

        if (!$model instanceof ModelInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'The model must be an instance of "%s"',
                    ModelInterface::class
                )
            );
        }

        $this->targetModel = $model;

        return $this;
    }

    /**
     * Retrieve the model to alter.
     *
     * @throws RuntimeException If a target model has not been defined.
     * @return ModelInterface
     */
    public function targetModel()
    {
        if (!isset($this->targetModel)) {
            throw new RuntimeException('A model must be targeted.');
        }

        return $this->targetModel;
    }

    /**
     * Set the related models to update.
     *
     * @param  string|array $models One or more object models.
     * @throws InvalidArgumentException If the given argument is not a model.
     * @return self
     */
    public function setRelatedModels($models)
    {
        $models = $this->parseAsArray($models);
        foreach ($models as $i => $model) {
            if (is_string($model)) {
                list($model, $prop) = $this->resolveRelatedModel($model);
                $models[$i]                                 = $model;
                $this->relatedProperties[$model->objType()] = $prop;
            } elseif ($model instanceof ModelInterface) {
                if (!isset($this->relatedProperties[$model->objType()])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'The related model [%s] requires a target property',
                            get_class($model)
                        )
                    );
                }
                $models[$i] = $model;
            } else {
                throw new InvalidArgumentException(
                    sprintf(
                        'A related model must be defined as "%s"',
                        'ObjType:propertyIdent'
                    )
                );
            }
        }

        $this->relatedModels = $models;

        return $this;
    }

    /**
     * Resolve the given related model.
     *
     * @param  string $pattern A 'model:property' identifier.
     * @throws InvalidArgumentException If the identifier is invalid.
     * @return array Returns an array containing a ModelInterface and a property identifier.
     */
    protected function resolveRelatedModel($pattern)
    {
        list($class, $prop) = array_pad($this->parseAsArray($pattern, ':'), 2, null);
        $model = $this->modelFactory()->get($class);

        if (!$prop) {
            throw new InvalidArgumentException(
                sprintf(
                    'The related model [%s] requires a target property',
                    get_class($model)
                )
            );
        }

        $metadata = $model->metadata();
        if (!$metadata->property($prop)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The related model [%1$s] does not have the target property [%2$s]',
                    $class,
                    (is_string($prop) ? $prop : gettype($prop))
                )
            );
        }

        return [$model, $prop];
    }

    /**
     * Retrieve the related models to update.
     *
     * @return ModelInterface[]|null
     */
    public function relatedModels()
    {
        return $this->relatedModels;
    }
}
