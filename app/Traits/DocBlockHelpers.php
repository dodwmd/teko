<?php

namespace App\Traits;

/**
 * @method static \Illuminate\Database\Eloquent\Builder where($column, $operator = null, $value = null)
 * @method static \Illuminate\Support\Collection pluck($column, $key = null)
 * @method static bool exists()
 * @method static \Illuminate\Database\Eloquent\Model|static create(array $attributes = [])
 * @method static \Illuminate\Database\Eloquent\Model|static find($id, $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Model|static|null first($columns = ['*'])
 */
trait DocBlockHelpers
{
    // This trait exists only for PHPDoc purposes
    // It helps static analysis tools understand Eloquent's magic methods
}
