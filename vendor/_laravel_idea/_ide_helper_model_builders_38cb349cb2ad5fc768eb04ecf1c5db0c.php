<?php //62427dcaeaa7c4451babfe76d1aa7b59
/** @noinspection all */

namespace LaravelIdea\Helper\Skycoder\InvoiceNumberGenerator\Models {

    use Illuminate\Contracts\Support\Arrayable;
    use Illuminate\Database\Query\Expression;
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Pagination\Paginator;
    use LaravelIdea\Helper\_BaseBuilder;
    use LaravelIdea\Helper\_BaseCollection;
    use Skycoder\InvoiceNumberGenerator\Models\InvoiceNumber;

    /**
     * @method InvoiceNumber|null getOrPut($key, $value)
     * @method InvoiceNumber|$this shift(int $count = 1)
     * @method InvoiceNumber|null firstOrFail($key = null, $operator = null, $value = null)
     * @method InvoiceNumber|$this pop(int $count = 1)
     * @method InvoiceNumber|null pull($key, $default = null)
     * @method InvoiceNumber|null last(callable $callback = null, $default = null)
     * @method InvoiceNumber|$this random(int|null $number = null)
     * @method InvoiceNumber|null sole($key = null, $operator = null, $value = null)
     * @method InvoiceNumber|null get($key, $default = null)
     * @method InvoiceNumber|null first(callable $callback = null, $default = null)
     * @method InvoiceNumber|null firstWhere(string $key, $operator = null, $value = null)
     * @method InvoiceNumber|null find($key, $default = null)
     * @method InvoiceNumber[] all()
     */
    class _IH_InvoiceNumber_C extends _BaseCollection {
        /**
         * @param int $size
         * @return InvoiceNumber[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method InvoiceNumber baseSole(array|string $columns = ['*'])
     * @method InvoiceNumber create(array $attributes = [])
     * @method _IH_InvoiceNumber_C|InvoiceNumber[] cursor()
     * @method InvoiceNumber|null|_IH_InvoiceNumber_C|InvoiceNumber[] find($id, array $columns = ['*'])
     * @method _IH_InvoiceNumber_C|InvoiceNumber[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method InvoiceNumber|_IH_InvoiceNumber_C|InvoiceNumber[] findOrFail($id, array $columns = ['*'])
     * @method InvoiceNumber|_IH_InvoiceNumber_C|InvoiceNumber[] findOrNew($id, array $columns = ['*'])
     * @method InvoiceNumber first(array|string $columns = ['*'])
     * @method InvoiceNumber firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method InvoiceNumber firstOrCreate(array $attributes = [], array $values = [])
     * @method InvoiceNumber firstOrFail(array $columns = ['*'])
     * @method InvoiceNumber firstOrNew(array $attributes = [], array $values = [])
     * @method InvoiceNumber firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method InvoiceNumber forceCreate(array $attributes)
     * @method _IH_InvoiceNumber_C|InvoiceNumber[] fromQuery(string $query, array $bindings = [])
     * @method _IH_InvoiceNumber_C|InvoiceNumber[] get(array|string $columns = ['*'])
     * @method InvoiceNumber getModel()
     * @method InvoiceNumber[] getModels(array|string $columns = ['*'])
     * @method _IH_InvoiceNumber_C|InvoiceNumber[] hydrate(array $items)
     * @method InvoiceNumber make(array $attributes = [])
     * @method InvoiceNumber newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|InvoiceNumber[]|_IH_InvoiceNumber_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|InvoiceNumber[]|_IH_InvoiceNumber_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method InvoiceNumber sole(array|string $columns = ['*'])
     * @method InvoiceNumber updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_InvoiceNumber_QB extends _BaseBuilder {}
}
