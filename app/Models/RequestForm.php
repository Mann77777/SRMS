<?php

namespace App\Models;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;

class RequestForm
{
    private $requestFormsPath;
    private $query;

    public function __construct()
    {
        $this->requestFormsPath = public_path('data/request-forms.json');
        $this->query = $this->getRequestForms();
    }

    /**
     * Get all request forms
     *
     * @return Collection
     */
    public static function all()
    {
        $instance = new self();
        return $instance->query;
    }

    /**
     * Filter request forms
     *
     * @param string $column
     * @param mixed $value
     * @return self
     */
    public static function where($column, $value)
    {
        $instance = new self();
        $instance->query = $instance->query->where($column, $value);
        return $instance;
    }

    /**
     * Read request forms from JSON file
     *
     * @return Collection
     */
    private function getRequestForms()
    {
        if (!File::exists($this->requestFormsPath)) {
            return collect([]);
        }

        $jsonContent = File::get($this->requestFormsPath);
        return collect(json_decode($jsonContent, true) ?? []);
    }

    /**
     * Select specific columns
     *
     * @param array $columns
     * @return self
     */
    public function select(...$columns)
    {
        $this->query = $this->query->map(function ($item) use ($columns) {
            return collect($item)->only($columns)->all();
        });
        return $this;
    }

    /**
     * Get the results
     *
     * @param array $columns
     * @return Collection
     */
    public function get($columns = ['*'])
    {
        // If '*' is passed or no specific columns are requested, return the entire query
        if ($columns === ['*'] || empty($columns)) {
            return $this->query;
        }

        // Otherwise, select only the specified columns
        return $this->query->map(function ($item) use ($columns) {
            return collect($item)->only($columns)->all();
        });
    }

    /**
     * Find a request form by ID
     *
     * @param int $id
     * @return array|null
     */
    public static function find($id)
    {
        $instance = new self();
        return $instance->query->firstWhere('id', $id);
    }

    /**
     * Find a request form or fail
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public static function findOrFail($id)
    {
        $form = self::find($id);
        if (!$form) {
            throw new \Exception("Request form with ID {$id} not found");
        }
        return $form;
    }
}