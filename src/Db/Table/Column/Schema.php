<?php

namespace Solar\Db\Table\Column;

use Solar\Object\Property\ColumnMapper;
use Solar\Object\Property\Visibility;
use Solar\String\Convention;

/**
 * @method string getColumnDefault()
 * @method string getColumnKey()
 * @method string getColumnName()
 * @method string getColumnType()
 * @method int getCharMaxLen()
 * @method string getDataType()
 * @method string getExtra()
 * @method string getIndexName()
 * @method string getIsNullable()
 * @method int getNonUnique()
 * @method int getNumericPrecision()
 * @method int getNumericScale()
 * @method int getSeqInIndex()
 * @method string getTableName()
 * @method string getTableSchema()
 */
class Schema extends ColumnMapper
{
    const COLUMN_ALIAS = [
        'CHARACTER_MAXIMUM_LENGTH' => 'charMaxLen'
    ];

    const COLUMN_CONVENTION = Convention::UPPER_UNDERSCORE;

    const COLUMN_VISIBILITY = Visibility::PROTECTED;

    const MAGIC_GETTERS = true;

    const PROPERTY_CONVENTION = Convention::LOWER_CAMEL_CASE;

    const VALUE_BAD_DATA_TYPE = 'bad_data_type';

    const VALUE_BAD_PRECISION = 'bad_precision';

    const VALUE_GOOD = 'good';

    const VALUE_REQUIRED = 'value_required';

    const VALUE_TOO_HIGH = 'too_height';

    const VALUE_TOO_LONG = 'too_long';

    const VALUE_TOO_LOW = 'too_low';

    const VALUE_TOO_SHORT = 'too_short';

    protected ?string $columnDefault = null;

    protected ?string $columnKey = null;

    protected string $columnName;

    protected string $columnType;

    protected ?int $charMaxLen = null;

    protected string $dataType;

    protected string $extra;

    protected ?string $indexName = null;

    protected string $isNullable;

    protected ?string $numericPrecision = null;

    protected ?int $nonUnique = null;

    protected ?int $numericScale = null;

    protected ?int $seqInIndex = null;

    protected string $tableName;

    protected string $tableSchema;

    public function __construct(array $columns)
    {
        $this->importColumns($columns);
    }

    /**
     * @param $value
     * @return string
     */
    public function getParamType($value = null): string
    {
        switch ($this->dataType)
        {
            case 'bigint':
            case 'int':
            case 'mediumint':
            case 'smallint':
            case 'tinyint':

                return $value <= PHP_INT_MAX ? 'i' : 's';

            case 'blob':
            case 'longblob':
            case 'mediumblob':
            case 'tinyblob':

                return 'b';

            case 'decimal':
            case 'double':
            case 'float':
            case 'numeric':

                return 'f';

            default:

                return 's';

        }
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return empty($this->columnDefault);
    }

    /**
     * @return bool
     */
    public function isSigned() : bool
    {
        return !$this->isUnsigned();
    }

    /**
     * @return bool
     */
    public function isUnsigned() : bool
    {
        return strpos($this->columnType, 'unsigned') !== false;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function testValue($value): string
    {
        $methods = [
            'testValueRequired',
            'testValueDataType',
            'testValueLength',
            'testValuePrecision',
            'testValueRange'
        ];

        foreach ($methods as $method)
        {
            $result = call_user_func(array($this, $method), $value);

            if ($result !== static::VALUE_GOOD)
                return $result;
        }

        return static::VALUE_GOOD;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function testValueDataType($value): string
    {
        switch ($this->dataType)
        {
            case 'bigint':
            case 'int':
            case 'mediumint':
            case 'smallint':
            case 'tinyint':

                if (!ctype_digit((string) $value))
                    return self::VALUE_BAD_DATA_TYPE;

                break;

            case 'bit':

                if (!preg_match('/^[0-1]+$/', $value))
                    return self::VALUE_BAD_DATA_TYPE;

                break;

            case 'date':

                if (!preg_match('/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])?$/', $value))
                    return self::VALUE_BAD_DATA_TYPE;

                break;

            case 'datetime':
            case 'timestamp':

                if (!preg_match('/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])(?:( [0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/', $value))
                    return self::VALUE_BAD_DATA_TYPE;

                break;

            case 'decimal':
            case 'double':
            case 'float':
            case 'numeric':

                if (is_numeric($value))
                    return self::VALUE_BAD_DATA_TYPE;

                break;

            case 'json':

                if (json_encode(json_decode($value)) !== $value)
                    return self::VALUE_BAD_DATA_TYPE;

                break;

            case 'time':

                if (!preg_match('/^([0-2][0-9]):([0-5][0-9]):([0-5][0-9])$/', $value))
                    return self::VALUE_BAD_DATA_TYPE;

                break;

            case 'year':

                if ($value !== '0000' && ($value >= 1901 && $value <= 2155))
                    return self::VALUE_BAD_DATA_TYPE;

                break;
        }

        return self::VALUE_GOOD;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function testValueLength($value): string
    {
        if (empty($this->charMaxLen) || strlen($value) <= $this->charMaxLen)
            return self::VALUE_GOOD;

        return self::VALUE_TOO_LONG;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function testValuePrecision($value): string
    {
        if (!$this->numericPrecision)
            return self::VALUE_GOOD;

        $pos = strpos($value, '.');

        $whole = $pos ? substr($value, 0, $pos) : $value;

        if (!$this->numericScale && strlen($whole) <= $this->numericPrecision)
            return self::VALUE_GOOD;

        $decimal = $pos ? substr($value, $pos + 1) : '';

        if (strlen($whole) <= ($this->numericPrecision - $this->numericScale) && strlen($decimal) <= $this->numericScale)
            return self::VALUE_GOOD;

        return self::VALUE_BAD_PRECISION;
    }

    /**
     * @param $value
     * @return string
     */
    public function testValueRange($value): string
    {
        switch ($this->dataType)
        {
            case 'tinyint':

                if ($this->isUnsigned() && $value < 0 || $this->isSigned() && $value < -0x80)
                    return self::VALUE_TOO_LONG;

                if ($this->isUnsigned() && $value > 0x7F || $this->isSigned() && $value > 0xFF)
                    return self::VALUE_TOO_HIGH;

                break;

            case 'smallint':

                if ($this->isUnsigned() && $value < 0 || $this->isSigned() && $value < -0x8000)
                    return self::VALUE_TOO_LONG;

                if ($this->isUnsigned() && $value > 0x7FFF || $this->isSigned() && $value > 0xFFFF)
                    return self::VALUE_TOO_HIGH;

                break;

            case 'mediumint':

                if ($this->isUnsigned() && $value < 0 || $this->isSigned() && $value < -0x800000)
                    return self::VALUE_TOO_LONG;

                if ($this->isUnsigned() && $value > 0x7FFFFF || $this->isSigned() && $value > 0xFFFFFF)
                    return self::VALUE_TOO_HIGH;

                break;

            case 'int':

                if ($this->isUnsigned() && $value < 0 || $this->isSigned() && $value < -0x80000000)
                    return self::VALUE_TOO_LONG;

                if ($this->isUnsigned() && $value > 0x7FFFFFFF || $this->isSigned() && $value > 0xFFFFFFFF)
                    return self::VALUE_TOO_HIGH;

                break;

            case 'bigint':

                if ($this->isUnsigned() && $value < 0 || $this->isSigned() && $value < -0x8000000000)
                    return self::VALUE_TOO_LONG;

                if ($this->isUnsigned() && $value > 0x7FFFFFFFFF || $this->isSigned() && $value > 0xFFFFFFFFFF)
                    return self::VALUE_TOO_HIGH;

                break;
        }

        return self::VALUE_GOOD;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function testValueRequired($value): string
    {
        if (empty($value) && empty($this->columnDefault))
            return self::VALUE_REQUIRED;

        return self::VALUE_GOOD;
    }
}