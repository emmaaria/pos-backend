<?php //5f6ff86b9e0fffa4d927d969a9c55ba8
/** @noinspection all */

namespace LaravelIdea\Helper\App\Models {

    use App\Models\AveragePurchasePrice;
    use App\Models\BkashTransaction;
    use App\Models\CardTransaction;
    use App\Models\CashBook;
    use App\Models\Category;
    use App\Models\Customer;
    use App\Models\CustomerLedger;
    use App\Models\Invoice;
    use App\Models\InvoiceItem;
    use App\Models\NagadTransaction;
    use App\Models\Product;
    use App\Models\Purchase;
    use App\Models\PurchaseItem;
    use App\Models\Supplier;
    use App\Models\SupplierLedger;
    use App\Models\Unit;
    use App\Models\User;
    use Illuminate\Contracts\Support\Arrayable;
    use Illuminate\Database\Query\Expression;
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Pagination\Paginator;
    use LaravelIdea\Helper\_BaseBuilder;
    use LaravelIdea\Helper\_BaseCollection;

    /**
     * @method AveragePurchasePrice|null getOrPut($key, $value)
     * @method AveragePurchasePrice|$this shift(int $count = 1)
     * @method AveragePurchasePrice|null firstOrFail($key = null, $operator = null, $value = null)
     * @method AveragePurchasePrice|$this pop(int $count = 1)
     * @method AveragePurchasePrice|null pull($key, $default = null)
     * @method AveragePurchasePrice|null last(callable $callback = null, $default = null)
     * @method AveragePurchasePrice|$this random(int|null $number = null)
     * @method AveragePurchasePrice|null sole($key = null, $operator = null, $value = null)
     * @method AveragePurchasePrice|null get($key, $default = null)
     * @method AveragePurchasePrice|null first(callable $callback = null, $default = null)
     * @method AveragePurchasePrice|null firstWhere(string $key, $operator = null, $value = null)
     * @method AveragePurchasePrice|null find($key, $default = null)
     * @method AveragePurchasePrice[] all()
     */
    class _IH_AveragePurchasePrice_C extends _BaseCollection {
        /**
         * @param int $size
         * @return AveragePurchasePrice[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_AveragePurchasePrice_QB whereId($value)
     * @method _IH_AveragePurchasePrice_QB whereProductId($value)
     * @method _IH_AveragePurchasePrice_QB wherePrice($value)
     * @method _IH_AveragePurchasePrice_QB whereCreatedAt($value)
     * @method _IH_AveragePurchasePrice_QB whereUpdatedAt($value)
     * @method AveragePurchasePrice baseSole(array|string $columns = ['*'])
     * @method AveragePurchasePrice create(array $attributes = [])
     * @method _IH_AveragePurchasePrice_C|AveragePurchasePrice[] cursor()
     * @method AveragePurchasePrice|null|_IH_AveragePurchasePrice_C|AveragePurchasePrice[] find($id, array $columns = ['*'])
     * @method _IH_AveragePurchasePrice_C|AveragePurchasePrice[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method AveragePurchasePrice|_IH_AveragePurchasePrice_C|AveragePurchasePrice[] findOrFail($id, array $columns = ['*'])
     * @method AveragePurchasePrice|_IH_AveragePurchasePrice_C|AveragePurchasePrice[] findOrNew($id, array $columns = ['*'])
     * @method AveragePurchasePrice first(array|string $columns = ['*'])
     * @method AveragePurchasePrice firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method AveragePurchasePrice firstOrCreate(array $attributes = [], array $values = [])
     * @method AveragePurchasePrice firstOrFail(array $columns = ['*'])
     * @method AveragePurchasePrice firstOrNew(array $attributes = [], array $values = [])
     * @method AveragePurchasePrice firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method AveragePurchasePrice forceCreate(array $attributes)
     * @method _IH_AveragePurchasePrice_C|AveragePurchasePrice[] fromQuery(string $query, array $bindings = [])
     * @method _IH_AveragePurchasePrice_C|AveragePurchasePrice[] get(array|string $columns = ['*'])
     * @method AveragePurchasePrice getModel()
     * @method AveragePurchasePrice[] getModels(array|string $columns = ['*'])
     * @method _IH_AveragePurchasePrice_C|AveragePurchasePrice[] hydrate(array $items)
     * @method AveragePurchasePrice make(array $attributes = [])
     * @method AveragePurchasePrice newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|AveragePurchasePrice[]|_IH_AveragePurchasePrice_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|AveragePurchasePrice[]|_IH_AveragePurchasePrice_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method AveragePurchasePrice sole(array|string $columns = ['*'])
     * @method AveragePurchasePrice updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_AveragePurchasePrice_QB extends _BaseBuilder {}

    /**
     * @method BkashTransaction|null getOrPut($key, $value)
     * @method BkashTransaction|$this shift(int $count = 1)
     * @method BkashTransaction|null firstOrFail($key = null, $operator = null, $value = null)
     * @method BkashTransaction|$this pop(int $count = 1)
     * @method BkashTransaction|null pull($key, $default = null)
     * @method BkashTransaction|null last(callable $callback = null, $default = null)
     * @method BkashTransaction|$this random(int|null $number = null)
     * @method BkashTransaction|null sole($key = null, $operator = null, $value = null)
     * @method BkashTransaction|null get($key, $default = null)
     * @method BkashTransaction|null first(callable $callback = null, $default = null)
     * @method BkashTransaction|null firstWhere(string $key, $operator = null, $value = null)
     * @method BkashTransaction|null find($key, $default = null)
     * @method BkashTransaction[] all()
     */
    class _IH_BkashTransaction_C extends _BaseCollection {
        /**
         * @param int $size
         * @return BkashTransaction[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_BkashTransaction_QB whereId($value)
     * @method _IH_BkashTransaction_QB whereTransactionId($value)
     * @method _IH_BkashTransaction_QB whereReferenceNo($value)
     * @method _IH_BkashTransaction_QB whereComment($value)
     * @method _IH_BkashTransaction_QB whereType($value)
     * @method _IH_BkashTransaction_QB whereWithdraw($value)
     * @method _IH_BkashTransaction_QB whereDeposit($value)
     * @method _IH_BkashTransaction_QB whereDate($value)
     * @method _IH_BkashTransaction_QB whereCreatedAt($value)
     * @method _IH_BkashTransaction_QB whereUpdatedAt($value)
     * @method BkashTransaction baseSole(array|string $columns = ['*'])
     * @method BkashTransaction create(array $attributes = [])
     * @method _IH_BkashTransaction_C|BkashTransaction[] cursor()
     * @method BkashTransaction|null|_IH_BkashTransaction_C|BkashTransaction[] find($id, array $columns = ['*'])
     * @method _IH_BkashTransaction_C|BkashTransaction[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method BkashTransaction|_IH_BkashTransaction_C|BkashTransaction[] findOrFail($id, array $columns = ['*'])
     * @method BkashTransaction|_IH_BkashTransaction_C|BkashTransaction[] findOrNew($id, array $columns = ['*'])
     * @method BkashTransaction first(array|string $columns = ['*'])
     * @method BkashTransaction firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method BkashTransaction firstOrCreate(array $attributes = [], array $values = [])
     * @method BkashTransaction firstOrFail(array $columns = ['*'])
     * @method BkashTransaction firstOrNew(array $attributes = [], array $values = [])
     * @method BkashTransaction firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method BkashTransaction forceCreate(array $attributes)
     * @method _IH_BkashTransaction_C|BkashTransaction[] fromQuery(string $query, array $bindings = [])
     * @method _IH_BkashTransaction_C|BkashTransaction[] get(array|string $columns = ['*'])
     * @method BkashTransaction getModel()
     * @method BkashTransaction[] getModels(array|string $columns = ['*'])
     * @method _IH_BkashTransaction_C|BkashTransaction[] hydrate(array $items)
     * @method BkashTransaction make(array $attributes = [])
     * @method BkashTransaction newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|BkashTransaction[]|_IH_BkashTransaction_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|BkashTransaction[]|_IH_BkashTransaction_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method BkashTransaction sole(array|string $columns = ['*'])
     * @method BkashTransaction updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_BkashTransaction_QB extends _BaseBuilder {}

    /**
     * @method CardTransaction|null getOrPut($key, $value)
     * @method CardTransaction|$this shift(int $count = 1)
     * @method CardTransaction|null firstOrFail($key = null, $operator = null, $value = null)
     * @method CardTransaction|$this pop(int $count = 1)
     * @method CardTransaction|null pull($key, $default = null)
     * @method CardTransaction|null last(callable $callback = null, $default = null)
     * @method CardTransaction|$this random(int|null $number = null)
     * @method CardTransaction|null sole($key = null, $operator = null, $value = null)
     * @method CardTransaction|null get($key, $default = null)
     * @method CardTransaction|null first(callable $callback = null, $default = null)
     * @method CardTransaction|null firstWhere(string $key, $operator = null, $value = null)
     * @method CardTransaction|null find($key, $default = null)
     * @method CardTransaction[] all()
     */
    class _IH_CardTransaction_C extends _BaseCollection {
        /**
         * @param int $size
         * @return CardTransaction[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_CardTransaction_QB whereId($value)
     * @method _IH_CardTransaction_QB whereTransactionId($value)
     * @method _IH_CardTransaction_QB whereReferenceNo($value)
     * @method _IH_CardTransaction_QB whereComment($value)
     * @method _IH_CardTransaction_QB whereType($value)
     * @method _IH_CardTransaction_QB whereWithdraw($value)
     * @method _IH_CardTransaction_QB whereDeposit($value)
     * @method _IH_CardTransaction_QB whereDate($value)
     * @method _IH_CardTransaction_QB whereCreatedAt($value)
     * @method _IH_CardTransaction_QB whereUpdatedAt($value)
     * @method CardTransaction baseSole(array|string $columns = ['*'])
     * @method CardTransaction create(array $attributes = [])
     * @method _IH_CardTransaction_C|CardTransaction[] cursor()
     * @method CardTransaction|null|_IH_CardTransaction_C|CardTransaction[] find($id, array $columns = ['*'])
     * @method _IH_CardTransaction_C|CardTransaction[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method CardTransaction|_IH_CardTransaction_C|CardTransaction[] findOrFail($id, array $columns = ['*'])
     * @method CardTransaction|_IH_CardTransaction_C|CardTransaction[] findOrNew($id, array $columns = ['*'])
     * @method CardTransaction first(array|string $columns = ['*'])
     * @method CardTransaction firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method CardTransaction firstOrCreate(array $attributes = [], array $values = [])
     * @method CardTransaction firstOrFail(array $columns = ['*'])
     * @method CardTransaction firstOrNew(array $attributes = [], array $values = [])
     * @method CardTransaction firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method CardTransaction forceCreate(array $attributes)
     * @method _IH_CardTransaction_C|CardTransaction[] fromQuery(string $query, array $bindings = [])
     * @method _IH_CardTransaction_C|CardTransaction[] get(array|string $columns = ['*'])
     * @method CardTransaction getModel()
     * @method CardTransaction[] getModels(array|string $columns = ['*'])
     * @method _IH_CardTransaction_C|CardTransaction[] hydrate(array $items)
     * @method CardTransaction make(array $attributes = [])
     * @method CardTransaction newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|CardTransaction[]|_IH_CardTransaction_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|CardTransaction[]|_IH_CardTransaction_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method CardTransaction sole(array|string $columns = ['*'])
     * @method CardTransaction updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_CardTransaction_QB extends _BaseBuilder {}

    /**
     * @method CashBook|null getOrPut($key, $value)
     * @method CashBook|$this shift(int $count = 1)
     * @method CashBook|null firstOrFail($key = null, $operator = null, $value = null)
     * @method CashBook|$this pop(int $count = 1)
     * @method CashBook|null pull($key, $default = null)
     * @method CashBook|null last(callable $callback = null, $default = null)
     * @method CashBook|$this random(int|null $number = null)
     * @method CashBook|null sole($key = null, $operator = null, $value = null)
     * @method CashBook|null get($key, $default = null)
     * @method CashBook|null first(callable $callback = null, $default = null)
     * @method CashBook|null firstWhere(string $key, $operator = null, $value = null)
     * @method CashBook|null find($key, $default = null)
     * @method CashBook[] all()
     */
    class _IH_CashBook_C extends _BaseCollection {
        /**
         * @param int $size
         * @return CashBook[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_CashBook_QB whereId($value)
     * @method _IH_CashBook_QB whereTransactionId($value)
     * @method _IH_CashBook_QB whereReferenceNo($value)
     * @method _IH_CashBook_QB whereComment($value)
     * @method _IH_CashBook_QB whereType($value)
     * @method _IH_CashBook_QB wherePayment($value)
     * @method _IH_CashBook_QB whereReceive($value)
     * @method _IH_CashBook_QB whereDate($value)
     * @method _IH_CashBook_QB whereCreatedAt($value)
     * @method _IH_CashBook_QB whereUpdatedAt($value)
     * @method CashBook baseSole(array|string $columns = ['*'])
     * @method CashBook create(array $attributes = [])
     * @method _IH_CashBook_C|CashBook[] cursor()
     * @method CashBook|null|_IH_CashBook_C|CashBook[] find($id, array $columns = ['*'])
     * @method _IH_CashBook_C|CashBook[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method CashBook|_IH_CashBook_C|CashBook[] findOrFail($id, array $columns = ['*'])
     * @method CashBook|_IH_CashBook_C|CashBook[] findOrNew($id, array $columns = ['*'])
     * @method CashBook first(array|string $columns = ['*'])
     * @method CashBook firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method CashBook firstOrCreate(array $attributes = [], array $values = [])
     * @method CashBook firstOrFail(array $columns = ['*'])
     * @method CashBook firstOrNew(array $attributes = [], array $values = [])
     * @method CashBook firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method CashBook forceCreate(array $attributes)
     * @method _IH_CashBook_C|CashBook[] fromQuery(string $query, array $bindings = [])
     * @method _IH_CashBook_C|CashBook[] get(array|string $columns = ['*'])
     * @method CashBook getModel()
     * @method CashBook[] getModels(array|string $columns = ['*'])
     * @method _IH_CashBook_C|CashBook[] hydrate(array $items)
     * @method CashBook make(array $attributes = [])
     * @method CashBook newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|CashBook[]|_IH_CashBook_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|CashBook[]|_IH_CashBook_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method CashBook sole(array|string $columns = ['*'])
     * @method CashBook updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_CashBook_QB extends _BaseBuilder {}

    /**
     * @method Category|null getOrPut($key, $value)
     * @method Category|$this shift(int $count = 1)
     * @method Category|null firstOrFail($key = null, $operator = null, $value = null)
     * @method Category|$this pop(int $count = 1)
     * @method Category|null pull($key, $default = null)
     * @method Category|null last(callable $callback = null, $default = null)
     * @method Category|$this random(int|null $number = null)
     * @method Category|null sole($key = null, $operator = null, $value = null)
     * @method Category|null get($key, $default = null)
     * @method Category|null first(callable $callback = null, $default = null)
     * @method Category|null firstWhere(string $key, $operator = null, $value = null)
     * @method Category|null find($key, $default = null)
     * @method Category[] all()
     */
    class _IH_Category_C extends _BaseCollection {
        /**
         * @param int $size
         * @return Category[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_Category_QB whereId($value)
     * @method _IH_Category_QB whereName($value)
     * @method _IH_Category_QB whereCreatedAt($value)
     * @method _IH_Category_QB whereUpdatedAt($value)
     * @method Category baseSole(array|string $columns = ['*'])
     * @method Category create(array $attributes = [])
     * @method _IH_Category_C|Category[] cursor()
     * @method Category|null|_IH_Category_C|Category[] find($id, array $columns = ['*'])
     * @method _IH_Category_C|Category[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method Category|_IH_Category_C|Category[] findOrFail($id, array $columns = ['*'])
     * @method Category|_IH_Category_C|Category[] findOrNew($id, array $columns = ['*'])
     * @method Category first(array|string $columns = ['*'])
     * @method Category firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method Category firstOrCreate(array $attributes = [], array $values = [])
     * @method Category firstOrFail(array $columns = ['*'])
     * @method Category firstOrNew(array $attributes = [], array $values = [])
     * @method Category firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method Category forceCreate(array $attributes)
     * @method _IH_Category_C|Category[] fromQuery(string $query, array $bindings = [])
     * @method _IH_Category_C|Category[] get(array|string $columns = ['*'])
     * @method Category getModel()
     * @method Category[] getModels(array|string $columns = ['*'])
     * @method _IH_Category_C|Category[] hydrate(array $items)
     * @method Category make(array $attributes = [])
     * @method Category newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|Category[]|_IH_Category_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|Category[]|_IH_Category_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Category sole(array|string $columns = ['*'])
     * @method Category updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_Category_QB extends _BaseBuilder {}

    /**
     * @method CustomerLedger|null getOrPut($key, $value)
     * @method CustomerLedger|$this shift(int $count = 1)
     * @method CustomerLedger|null firstOrFail($key = null, $operator = null, $value = null)
     * @method CustomerLedger|$this pop(int $count = 1)
     * @method CustomerLedger|null pull($key, $default = null)
     * @method CustomerLedger|null last(callable $callback = null, $default = null)
     * @method CustomerLedger|$this random(int|null $number = null)
     * @method CustomerLedger|null sole($key = null, $operator = null, $value = null)
     * @method CustomerLedger|null get($key, $default = null)
     * @method CustomerLedger|null first(callable $callback = null, $default = null)
     * @method CustomerLedger|null firstWhere(string $key, $operator = null, $value = null)
     * @method CustomerLedger|null find($key, $default = null)
     * @method CustomerLedger[] all()
     */
    class _IH_CustomerLedger_C extends _BaseCollection {
        /**
         * @param int $size
         * @return CustomerLedger[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_CustomerLedger_QB whereId($value)
     * @method _IH_CustomerLedger_QB whereCustomerId($value)
     * @method _IH_CustomerLedger_QB whereTransactionId($value)
     * @method _IH_CustomerLedger_QB whereReferenceNo($value)
     * @method _IH_CustomerLedger_QB whereType($value)
     * @method _IH_CustomerLedger_QB whereDue($value)
     * @method _IH_CustomerLedger_QB whereDeposit($value)
     * @method _IH_CustomerLedger_QB whereDate($value)
     * @method _IH_CustomerLedger_QB whereComment($value)
     * @method _IH_CustomerLedger_QB whereCreatedAt($value)
     * @method _IH_CustomerLedger_QB whereUpdatedAt($value)
     * @method CustomerLedger baseSole(array|string $columns = ['*'])
     * @method CustomerLedger create(array $attributes = [])
     * @method _IH_CustomerLedger_C|CustomerLedger[] cursor()
     * @method CustomerLedger|null|_IH_CustomerLedger_C|CustomerLedger[] find($id, array $columns = ['*'])
     * @method _IH_CustomerLedger_C|CustomerLedger[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method CustomerLedger|_IH_CustomerLedger_C|CustomerLedger[] findOrFail($id, array $columns = ['*'])
     * @method CustomerLedger|_IH_CustomerLedger_C|CustomerLedger[] findOrNew($id, array $columns = ['*'])
     * @method CustomerLedger first(array|string $columns = ['*'])
     * @method CustomerLedger firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method CustomerLedger firstOrCreate(array $attributes = [], array $values = [])
     * @method CustomerLedger firstOrFail(array $columns = ['*'])
     * @method CustomerLedger firstOrNew(array $attributes = [], array $values = [])
     * @method CustomerLedger firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method CustomerLedger forceCreate(array $attributes)
     * @method _IH_CustomerLedger_C|CustomerLedger[] fromQuery(string $query, array $bindings = [])
     * @method _IH_CustomerLedger_C|CustomerLedger[] get(array|string $columns = ['*'])
     * @method CustomerLedger getModel()
     * @method CustomerLedger[] getModels(array|string $columns = ['*'])
     * @method _IH_CustomerLedger_C|CustomerLedger[] hydrate(array $items)
     * @method CustomerLedger make(array $attributes = [])
     * @method CustomerLedger newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|CustomerLedger[]|_IH_CustomerLedger_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|CustomerLedger[]|_IH_CustomerLedger_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method CustomerLedger sole(array|string $columns = ['*'])
     * @method CustomerLedger updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_CustomerLedger_QB extends _BaseBuilder {}

    /**
     * @method Customer|null getOrPut($key, $value)
     * @method Customer|$this shift(int $count = 1)
     * @method Customer|null firstOrFail($key = null, $operator = null, $value = null)
     * @method Customer|$this pop(int $count = 1)
     * @method Customer|null pull($key, $default = null)
     * @method Customer|null last(callable $callback = null, $default = null)
     * @method Customer|$this random(int|null $number = null)
     * @method Customer|null sole($key = null, $operator = null, $value = null)
     * @method Customer|null get($key, $default = null)
     * @method Customer|null first(callable $callback = null, $default = null)
     * @method Customer|null firstWhere(string $key, $operator = null, $value = null)
     * @method Customer|null find($key, $default = null)
     * @method Customer[] all()
     */
    class _IH_Customer_C extends _BaseCollection {
        /**
         * @param int $size
         * @return Customer[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_Customer_QB whereId($value)
     * @method _IH_Customer_QB whereName($value)
     * @method _IH_Customer_QB whereMobile($value)
     * @method _IH_Customer_QB whereAddress($value)
     * @method _IH_Customer_QB whereCreatedAt($value)
     * @method _IH_Customer_QB whereUpdatedAt($value)
     * @method Customer baseSole(array|string $columns = ['*'])
     * @method Customer create(array $attributes = [])
     * @method _IH_Customer_C|Customer[] cursor()
     * @method Customer|null|_IH_Customer_C|Customer[] find($id, array $columns = ['*'])
     * @method _IH_Customer_C|Customer[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method Customer|_IH_Customer_C|Customer[] findOrFail($id, array $columns = ['*'])
     * @method Customer|_IH_Customer_C|Customer[] findOrNew($id, array $columns = ['*'])
     * @method Customer first(array|string $columns = ['*'])
     * @method Customer firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method Customer firstOrCreate(array $attributes = [], array $values = [])
     * @method Customer firstOrFail(array $columns = ['*'])
     * @method Customer firstOrNew(array $attributes = [], array $values = [])
     * @method Customer firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method Customer forceCreate(array $attributes)
     * @method _IH_Customer_C|Customer[] fromQuery(string $query, array $bindings = [])
     * @method _IH_Customer_C|Customer[] get(array|string $columns = ['*'])
     * @method Customer getModel()
     * @method Customer[] getModels(array|string $columns = ['*'])
     * @method _IH_Customer_C|Customer[] hydrate(array $items)
     * @method Customer make(array $attributes = [])
     * @method Customer newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|Customer[]|_IH_Customer_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|Customer[]|_IH_Customer_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Customer sole(array|string $columns = ['*'])
     * @method Customer updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_Customer_QB extends _BaseBuilder {}

    /**
     * @method InvoiceItem|null getOrPut($key, $value)
     * @method InvoiceItem|$this shift(int $count = 1)
     * @method InvoiceItem|null firstOrFail($key = null, $operator = null, $value = null)
     * @method InvoiceItem|$this pop(int $count = 1)
     * @method InvoiceItem|null pull($key, $default = null)
     * @method InvoiceItem|null last(callable $callback = null, $default = null)
     * @method InvoiceItem|$this random(int|null $number = null)
     * @method InvoiceItem|null sole($key = null, $operator = null, $value = null)
     * @method InvoiceItem|null get($key, $default = null)
     * @method InvoiceItem|null first(callable $callback = null, $default = null)
     * @method InvoiceItem|null firstWhere(string $key, $operator = null, $value = null)
     * @method InvoiceItem|null find($key, $default = null)
     * @method InvoiceItem[] all()
     */
    class _IH_InvoiceItem_C extends _BaseCollection {
        /**
         * @param int $size
         * @return InvoiceItem[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_InvoiceItem_QB whereId($value)
     * @method _IH_InvoiceItem_QB whereInvoiceId($value)
     * @method _IH_InvoiceItem_QB whereProductId($value)
     * @method _IH_InvoiceItem_QB wherePrice($value)
     * @method _IH_InvoiceItem_QB whereQuantity($value)
     * @method _IH_InvoiceItem_QB whereTotal($value)
     * @method _IH_InvoiceItem_QB whereDate($value)
     * @method _IH_InvoiceItem_QB whereCreatedAt($value)
     * @method _IH_InvoiceItem_QB whereUpdatedAt($value)
     * @method InvoiceItem baseSole(array|string $columns = ['*'])
     * @method InvoiceItem create(array $attributes = [])
     * @method _IH_InvoiceItem_C|InvoiceItem[] cursor()
     * @method InvoiceItem|null|_IH_InvoiceItem_C|InvoiceItem[] find($id, array $columns = ['*'])
     * @method _IH_InvoiceItem_C|InvoiceItem[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method InvoiceItem|_IH_InvoiceItem_C|InvoiceItem[] findOrFail($id, array $columns = ['*'])
     * @method InvoiceItem|_IH_InvoiceItem_C|InvoiceItem[] findOrNew($id, array $columns = ['*'])
     * @method InvoiceItem first(array|string $columns = ['*'])
     * @method InvoiceItem firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method InvoiceItem firstOrCreate(array $attributes = [], array $values = [])
     * @method InvoiceItem firstOrFail(array $columns = ['*'])
     * @method InvoiceItem firstOrNew(array $attributes = [], array $values = [])
     * @method InvoiceItem firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method InvoiceItem forceCreate(array $attributes)
     * @method _IH_InvoiceItem_C|InvoiceItem[] fromQuery(string $query, array $bindings = [])
     * @method _IH_InvoiceItem_C|InvoiceItem[] get(array|string $columns = ['*'])
     * @method InvoiceItem getModel()
     * @method InvoiceItem[] getModels(array|string $columns = ['*'])
     * @method _IH_InvoiceItem_C|InvoiceItem[] hydrate(array $items)
     * @method InvoiceItem make(array $attributes = [])
     * @method InvoiceItem newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|InvoiceItem[]|_IH_InvoiceItem_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|InvoiceItem[]|_IH_InvoiceItem_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method InvoiceItem sole(array|string $columns = ['*'])
     * @method InvoiceItem updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_InvoiceItem_QB extends _BaseBuilder {}

    /**
     * @method Invoice|null getOrPut($key, $value)
     * @method Invoice|$this shift(int $count = 1)
     * @method Invoice|null firstOrFail($key = null, $operator = null, $value = null)
     * @method Invoice|$this pop(int $count = 1)
     * @method Invoice|null pull($key, $default = null)
     * @method Invoice|null last(callable $callback = null, $default = null)
     * @method Invoice|$this random(int|null $number = null)
     * @method Invoice|null sole($key = null, $operator = null, $value = null)
     * @method Invoice|null get($key, $default = null)
     * @method Invoice|null first(callable $callback = null, $default = null)
     * @method Invoice|null firstWhere(string $key, $operator = null, $value = null)
     * @method Invoice|null find($key, $default = null)
     * @method Invoice[] all()
     */
    class _IH_Invoice_C extends _BaseCollection {
        /**
         * @param int $size
         * @return Invoice[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_Invoice_QB whereId($value)
     * @method _IH_Invoice_QB whereCustomerId($value)
     * @method _IH_Invoice_QB whereInvoiceId($value)
     * @method _IH_Invoice_QB whereTotal($value)
     * @method _IH_Invoice_QB whereComment($value)
     * @method _IH_Invoice_QB whereDate($value)
     * @method _IH_Invoice_QB whereDiscount($value)
     * @method _IH_Invoice_QB whereDiscountamount($value)
     * @method _IH_Invoice_QB whereDiscounttype($value)
     * @method _IH_Invoice_QB whereProfit($value)
     * @method _IH_Invoice_QB whereCreatedAt($value)
     * @method _IH_Invoice_QB whereUpdatedAt($value)
     * @method Invoice baseSole(array|string $columns = ['*'])
     * @method Invoice create(array $attributes = [])
     * @method _IH_Invoice_C|Invoice[] cursor()
     * @method Invoice|null|_IH_Invoice_C|Invoice[] find($id, array $columns = ['*'])
     * @method _IH_Invoice_C|Invoice[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method Invoice|_IH_Invoice_C|Invoice[] findOrFail($id, array $columns = ['*'])
     * @method Invoice|_IH_Invoice_C|Invoice[] findOrNew($id, array $columns = ['*'])
     * @method Invoice first(array|string $columns = ['*'])
     * @method Invoice firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method Invoice firstOrCreate(array $attributes = [], array $values = [])
     * @method Invoice firstOrFail(array $columns = ['*'])
     * @method Invoice firstOrNew(array $attributes = [], array $values = [])
     * @method Invoice firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method Invoice forceCreate(array $attributes)
     * @method _IH_Invoice_C|Invoice[] fromQuery(string $query, array $bindings = [])
     * @method _IH_Invoice_C|Invoice[] get(array|string $columns = ['*'])
     * @method Invoice getModel()
     * @method Invoice[] getModels(array|string $columns = ['*'])
     * @method _IH_Invoice_C|Invoice[] hydrate(array $items)
     * @method Invoice make(array $attributes = [])
     * @method Invoice newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|Invoice[]|_IH_Invoice_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|Invoice[]|_IH_Invoice_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Invoice sole(array|string $columns = ['*'])
     * @method Invoice updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_Invoice_QB extends _BaseBuilder {}

    /**
     * @method NagadTransaction|null getOrPut($key, $value)
     * @method NagadTransaction|$this shift(int $count = 1)
     * @method NagadTransaction|null firstOrFail($key = null, $operator = null, $value = null)
     * @method NagadTransaction|$this pop(int $count = 1)
     * @method NagadTransaction|null pull($key, $default = null)
     * @method NagadTransaction|null last(callable $callback = null, $default = null)
     * @method NagadTransaction|$this random(int|null $number = null)
     * @method NagadTransaction|null sole($key = null, $operator = null, $value = null)
     * @method NagadTransaction|null get($key, $default = null)
     * @method NagadTransaction|null first(callable $callback = null, $default = null)
     * @method NagadTransaction|null firstWhere(string $key, $operator = null, $value = null)
     * @method NagadTransaction|null find($key, $default = null)
     * @method NagadTransaction[] all()
     */
    class _IH_NagadTransaction_C extends _BaseCollection {
        /**
         * @param int $size
         * @return NagadTransaction[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_NagadTransaction_QB whereId($value)
     * @method _IH_NagadTransaction_QB whereTransactionId($value)
     * @method _IH_NagadTransaction_QB whereReferenceNo($value)
     * @method _IH_NagadTransaction_QB whereComment($value)
     * @method _IH_NagadTransaction_QB whereType($value)
     * @method _IH_NagadTransaction_QB whereWithdraw($value)
     * @method _IH_NagadTransaction_QB whereDeposit($value)
     * @method _IH_NagadTransaction_QB whereDate($value)
     * @method _IH_NagadTransaction_QB whereCreatedAt($value)
     * @method _IH_NagadTransaction_QB whereUpdatedAt($value)
     * @method NagadTransaction baseSole(array|string $columns = ['*'])
     * @method NagadTransaction create(array $attributes = [])
     * @method _IH_NagadTransaction_C|NagadTransaction[] cursor()
     * @method NagadTransaction|null|_IH_NagadTransaction_C|NagadTransaction[] find($id, array $columns = ['*'])
     * @method _IH_NagadTransaction_C|NagadTransaction[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method NagadTransaction|_IH_NagadTransaction_C|NagadTransaction[] findOrFail($id, array $columns = ['*'])
     * @method NagadTransaction|_IH_NagadTransaction_C|NagadTransaction[] findOrNew($id, array $columns = ['*'])
     * @method NagadTransaction first(array|string $columns = ['*'])
     * @method NagadTransaction firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method NagadTransaction firstOrCreate(array $attributes = [], array $values = [])
     * @method NagadTransaction firstOrFail(array $columns = ['*'])
     * @method NagadTransaction firstOrNew(array $attributes = [], array $values = [])
     * @method NagadTransaction firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method NagadTransaction forceCreate(array $attributes)
     * @method _IH_NagadTransaction_C|NagadTransaction[] fromQuery(string $query, array $bindings = [])
     * @method _IH_NagadTransaction_C|NagadTransaction[] get(array|string $columns = ['*'])
     * @method NagadTransaction getModel()
     * @method NagadTransaction[] getModels(array|string $columns = ['*'])
     * @method _IH_NagadTransaction_C|NagadTransaction[] hydrate(array $items)
     * @method NagadTransaction make(array $attributes = [])
     * @method NagadTransaction newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|NagadTransaction[]|_IH_NagadTransaction_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|NagadTransaction[]|_IH_NagadTransaction_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method NagadTransaction sole(array|string $columns = ['*'])
     * @method NagadTransaction updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_NagadTransaction_QB extends _BaseBuilder {}

    /**
     * @method Product|null getOrPut($key, $value)
     * @method Product|$this shift(int $count = 1)
     * @method Product|null firstOrFail($key = null, $operator = null, $value = null)
     * @method Product|$this pop(int $count = 1)
     * @method Product|null pull($key, $default = null)
     * @method Product|null last(callable $callback = null, $default = null)
     * @method Product|$this random(int|null $number = null)
     * @method Product|null sole($key = null, $operator = null, $value = null)
     * @method Product|null get($key, $default = null)
     * @method Product|null first(callable $callback = null, $default = null)
     * @method Product|null firstWhere(string $key, $operator = null, $value = null)
     * @method Product|null find($key, $default = null)
     * @method Product[] all()
     */
    class _IH_Product_C extends _BaseCollection {
        /**
         * @param int $size
         * @return Product[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_Product_QB whereId($value)
     * @method _IH_Product_QB whereName($value)
     * @method _IH_Product_QB whereProductId($value)
     * @method _IH_Product_QB whereCategory($value)
     * @method _IH_Product_QB whereUnit($value)
     * @method _IH_Product_QB wherePrice($value)
     * @method _IH_Product_QB wherePurchasePrice($value)
     * @method _IH_Product_QB whereWeight($value)
     * @method _IH_Product_QB whereCreatedAt($value)
     * @method _IH_Product_QB whereUpdatedAt($value)
     * @method Product baseSole(array|string $columns = ['*'])
     * @method Product create(array $attributes = [])
     * @method _IH_Product_C|Product[] cursor()
     * @method Product|null|_IH_Product_C|Product[] find($id, array $columns = ['*'])
     * @method _IH_Product_C|Product[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method Product|_IH_Product_C|Product[] findOrFail($id, array $columns = ['*'])
     * @method Product|_IH_Product_C|Product[] findOrNew($id, array $columns = ['*'])
     * @method Product first(array|string $columns = ['*'])
     * @method Product firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method Product firstOrCreate(array $attributes = [], array $values = [])
     * @method Product firstOrFail(array $columns = ['*'])
     * @method Product firstOrNew(array $attributes = [], array $values = [])
     * @method Product firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method Product forceCreate(array $attributes)
     * @method _IH_Product_C|Product[] fromQuery(string $query, array $bindings = [])
     * @method _IH_Product_C|Product[] get(array|string $columns = ['*'])
     * @method Product getModel()
     * @method Product[] getModels(array|string $columns = ['*'])
     * @method _IH_Product_C|Product[] hydrate(array $items)
     * @method Product make(array $attributes = [])
     * @method Product newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|Product[]|_IH_Product_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|Product[]|_IH_Product_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Product sole(array|string $columns = ['*'])
     * @method Product updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_Product_QB extends _BaseBuilder {}

    /**
     * @method PurchaseItem|null getOrPut($key, $value)
     * @method PurchaseItem|$this shift(int $count = 1)
     * @method PurchaseItem|null firstOrFail($key = null, $operator = null, $value = null)
     * @method PurchaseItem|$this pop(int $count = 1)
     * @method PurchaseItem|null pull($key, $default = null)
     * @method PurchaseItem|null last(callable $callback = null, $default = null)
     * @method PurchaseItem|$this random(int|null $number = null)
     * @method PurchaseItem|null sole($key = null, $operator = null, $value = null)
     * @method PurchaseItem|null get($key, $default = null)
     * @method PurchaseItem|null first(callable $callback = null, $default = null)
     * @method PurchaseItem|null firstWhere(string $key, $operator = null, $value = null)
     * @method PurchaseItem|null find($key, $default = null)
     * @method PurchaseItem[] all()
     */
    class _IH_PurchaseItem_C extends _BaseCollection {
        /**
         * @param int $size
         * @return PurchaseItem[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_PurchaseItem_QB whereId($value)
     * @method _IH_PurchaseItem_QB wherePurchaseId($value)
     * @method _IH_PurchaseItem_QB whereProductId($value)
     * @method _IH_PurchaseItem_QB wherePrice($value)
     * @method _IH_PurchaseItem_QB whereQuantity($value)
     * @method _IH_PurchaseItem_QB whereTotal($value)
     * @method _IH_PurchaseItem_QB whereDate($value)
     * @method _IH_PurchaseItem_QB whereCreatedAt($value)
     * @method _IH_PurchaseItem_QB whereUpdatedAt($value)
     * @method PurchaseItem baseSole(array|string $columns = ['*'])
     * @method PurchaseItem create(array $attributes = [])
     * @method _IH_PurchaseItem_C|PurchaseItem[] cursor()
     * @method PurchaseItem|null|_IH_PurchaseItem_C|PurchaseItem[] find($id, array $columns = ['*'])
     * @method _IH_PurchaseItem_C|PurchaseItem[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method PurchaseItem|_IH_PurchaseItem_C|PurchaseItem[] findOrFail($id, array $columns = ['*'])
     * @method PurchaseItem|_IH_PurchaseItem_C|PurchaseItem[] findOrNew($id, array $columns = ['*'])
     * @method PurchaseItem first(array|string $columns = ['*'])
     * @method PurchaseItem firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method PurchaseItem firstOrCreate(array $attributes = [], array $values = [])
     * @method PurchaseItem firstOrFail(array $columns = ['*'])
     * @method PurchaseItem firstOrNew(array $attributes = [], array $values = [])
     * @method PurchaseItem firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method PurchaseItem forceCreate(array $attributes)
     * @method _IH_PurchaseItem_C|PurchaseItem[] fromQuery(string $query, array $bindings = [])
     * @method _IH_PurchaseItem_C|PurchaseItem[] get(array|string $columns = ['*'])
     * @method PurchaseItem getModel()
     * @method PurchaseItem[] getModels(array|string $columns = ['*'])
     * @method _IH_PurchaseItem_C|PurchaseItem[] hydrate(array $items)
     * @method PurchaseItem make(array $attributes = [])
     * @method PurchaseItem newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|PurchaseItem[]|_IH_PurchaseItem_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|PurchaseItem[]|_IH_PurchaseItem_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method PurchaseItem sole(array|string $columns = ['*'])
     * @method PurchaseItem updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_PurchaseItem_QB extends _BaseBuilder {}

    /**
     * @method Purchase|null getOrPut($key, $value)
     * @method Purchase|$this shift(int $count = 1)
     * @method Purchase|null firstOrFail($key = null, $operator = null, $value = null)
     * @method Purchase|$this pop(int $count = 1)
     * @method Purchase|null pull($key, $default = null)
     * @method Purchase|null last(callable $callback = null, $default = null)
     * @method Purchase|$this random(int|null $number = null)
     * @method Purchase|null sole($key = null, $operator = null, $value = null)
     * @method Purchase|null get($key, $default = null)
     * @method Purchase|null first(callable $callback = null, $default = null)
     * @method Purchase|null firstWhere(string $key, $operator = null, $value = null)
     * @method Purchase|null find($key, $default = null)
     * @method Purchase[] all()
     */
    class _IH_Purchase_C extends _BaseCollection {
        /**
         * @param int $size
         * @return Purchase[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_Purchase_QB whereId($value)
     * @method _IH_Purchase_QB whereSupplierId($value)
     * @method _IH_Purchase_QB wherePurchaseId($value)
     * @method _IH_Purchase_QB whereAmount($value)
     * @method _IH_Purchase_QB wherePaid($value)
     * @method _IH_Purchase_QB whereComment($value)
     * @method _IH_Purchase_QB whereDate($value)
     * @method _IH_Purchase_QB whereCreatedAt($value)
     * @method _IH_Purchase_QB whereUpdatedAt($value)
     * @method Purchase baseSole(array|string $columns = ['*'])
     * @method Purchase create(array $attributes = [])
     * @method _IH_Purchase_C|Purchase[] cursor()
     * @method Purchase|null|_IH_Purchase_C|Purchase[] find($id, array $columns = ['*'])
     * @method _IH_Purchase_C|Purchase[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method Purchase|_IH_Purchase_C|Purchase[] findOrFail($id, array $columns = ['*'])
     * @method Purchase|_IH_Purchase_C|Purchase[] findOrNew($id, array $columns = ['*'])
     * @method Purchase first(array|string $columns = ['*'])
     * @method Purchase firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method Purchase firstOrCreate(array $attributes = [], array $values = [])
     * @method Purchase firstOrFail(array $columns = ['*'])
     * @method Purchase firstOrNew(array $attributes = [], array $values = [])
     * @method Purchase firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method Purchase forceCreate(array $attributes)
     * @method _IH_Purchase_C|Purchase[] fromQuery(string $query, array $bindings = [])
     * @method _IH_Purchase_C|Purchase[] get(array|string $columns = ['*'])
     * @method Purchase getModel()
     * @method Purchase[] getModels(array|string $columns = ['*'])
     * @method _IH_Purchase_C|Purchase[] hydrate(array $items)
     * @method Purchase make(array $attributes = [])
     * @method Purchase newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|Purchase[]|_IH_Purchase_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|Purchase[]|_IH_Purchase_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Purchase sole(array|string $columns = ['*'])
     * @method Purchase updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_Purchase_QB extends _BaseBuilder {}

    /**
     * @method SupplierLedger|null getOrPut($key, $value)
     * @method SupplierLedger|$this shift(int $count = 1)
     * @method SupplierLedger|null firstOrFail($key = null, $operator = null, $value = null)
     * @method SupplierLedger|$this pop(int $count = 1)
     * @method SupplierLedger|null pull($key, $default = null)
     * @method SupplierLedger|null last(callable $callback = null, $default = null)
     * @method SupplierLedger|$this random(int|null $number = null)
     * @method SupplierLedger|null sole($key = null, $operator = null, $value = null)
     * @method SupplierLedger|null get($key, $default = null)
     * @method SupplierLedger|null first(callable $callback = null, $default = null)
     * @method SupplierLedger|null firstWhere(string $key, $operator = null, $value = null)
     * @method SupplierLedger|null find($key, $default = null)
     * @method SupplierLedger[] all()
     */
    class _IH_SupplierLedger_C extends _BaseCollection {
        /**
         * @param int $size
         * @return SupplierLedger[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_SupplierLedger_QB whereId($value)
     * @method _IH_SupplierLedger_QB whereSupplierId($value)
     * @method _IH_SupplierLedger_QB whereTransactionId($value)
     * @method _IH_SupplierLedger_QB whereReferenceNo($value)
     * @method _IH_SupplierLedger_QB whereType($value)
     * @method _IH_SupplierLedger_QB whereDue($value)
     * @method _IH_SupplierLedger_QB whereDeposit($value)
     * @method _IH_SupplierLedger_QB whereDate($value)
     * @method _IH_SupplierLedger_QB whereComment($value)
     * @method _IH_SupplierLedger_QB whereCreatedAt($value)
     * @method _IH_SupplierLedger_QB whereUpdatedAt($value)
     * @method SupplierLedger baseSole(array|string $columns = ['*'])
     * @method SupplierLedger create(array $attributes = [])
     * @method _IH_SupplierLedger_C|SupplierLedger[] cursor()
     * @method SupplierLedger|null|_IH_SupplierLedger_C|SupplierLedger[] find($id, array $columns = ['*'])
     * @method _IH_SupplierLedger_C|SupplierLedger[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method SupplierLedger|_IH_SupplierLedger_C|SupplierLedger[] findOrFail($id, array $columns = ['*'])
     * @method SupplierLedger|_IH_SupplierLedger_C|SupplierLedger[] findOrNew($id, array $columns = ['*'])
     * @method SupplierLedger first(array|string $columns = ['*'])
     * @method SupplierLedger firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method SupplierLedger firstOrCreate(array $attributes = [], array $values = [])
     * @method SupplierLedger firstOrFail(array $columns = ['*'])
     * @method SupplierLedger firstOrNew(array $attributes = [], array $values = [])
     * @method SupplierLedger firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method SupplierLedger forceCreate(array $attributes)
     * @method _IH_SupplierLedger_C|SupplierLedger[] fromQuery(string $query, array $bindings = [])
     * @method _IH_SupplierLedger_C|SupplierLedger[] get(array|string $columns = ['*'])
     * @method SupplierLedger getModel()
     * @method SupplierLedger[] getModels(array|string $columns = ['*'])
     * @method _IH_SupplierLedger_C|SupplierLedger[] hydrate(array $items)
     * @method SupplierLedger make(array $attributes = [])
     * @method SupplierLedger newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|SupplierLedger[]|_IH_SupplierLedger_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|SupplierLedger[]|_IH_SupplierLedger_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method SupplierLedger sole(array|string $columns = ['*'])
     * @method SupplierLedger updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_SupplierLedger_QB extends _BaseBuilder {}

    /**
     * @method Supplier|null getOrPut($key, $value)
     * @method Supplier|$this shift(int $count = 1)
     * @method Supplier|null firstOrFail($key = null, $operator = null, $value = null)
     * @method Supplier|$this pop(int $count = 1)
     * @method Supplier|null pull($key, $default = null)
     * @method Supplier|null last(callable $callback = null, $default = null)
     * @method Supplier|$this random(int|null $number = null)
     * @method Supplier|null sole($key = null, $operator = null, $value = null)
     * @method Supplier|null get($key, $default = null)
     * @method Supplier|null first(callable $callback = null, $default = null)
     * @method Supplier|null firstWhere(string $key, $operator = null, $value = null)
     * @method Supplier|null find($key, $default = null)
     * @method Supplier[] all()
     */
    class _IH_Supplier_C extends _BaseCollection {
        /**
         * @param int $size
         * @return Supplier[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_Supplier_QB whereId($value)
     * @method _IH_Supplier_QB whereName($value)
     * @method _IH_Supplier_QB whereMobile($value)
     * @method _IH_Supplier_QB whereAddress($value)
     * @method _IH_Supplier_QB whereCreatedAt($value)
     * @method _IH_Supplier_QB whereUpdatedAt($value)
     * @method Supplier baseSole(array|string $columns = ['*'])
     * @method Supplier create(array $attributes = [])
     * @method _IH_Supplier_C|Supplier[] cursor()
     * @method Supplier|null|_IH_Supplier_C|Supplier[] find($id, array $columns = ['*'])
     * @method _IH_Supplier_C|Supplier[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method Supplier|_IH_Supplier_C|Supplier[] findOrFail($id, array $columns = ['*'])
     * @method Supplier|_IH_Supplier_C|Supplier[] findOrNew($id, array $columns = ['*'])
     * @method Supplier first(array|string $columns = ['*'])
     * @method Supplier firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method Supplier firstOrCreate(array $attributes = [], array $values = [])
     * @method Supplier firstOrFail(array $columns = ['*'])
     * @method Supplier firstOrNew(array $attributes = [], array $values = [])
     * @method Supplier firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method Supplier forceCreate(array $attributes)
     * @method _IH_Supplier_C|Supplier[] fromQuery(string $query, array $bindings = [])
     * @method _IH_Supplier_C|Supplier[] get(array|string $columns = ['*'])
     * @method Supplier getModel()
     * @method Supplier[] getModels(array|string $columns = ['*'])
     * @method _IH_Supplier_C|Supplier[] hydrate(array $items)
     * @method Supplier make(array $attributes = [])
     * @method Supplier newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|Supplier[]|_IH_Supplier_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|Supplier[]|_IH_Supplier_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Supplier sole(array|string $columns = ['*'])
     * @method Supplier updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_Supplier_QB extends _BaseBuilder {}

    /**
     * @method Unit|null getOrPut($key, $value)
     * @method Unit|$this shift(int $count = 1)
     * @method Unit|null firstOrFail($key = null, $operator = null, $value = null)
     * @method Unit|$this pop(int $count = 1)
     * @method Unit|null pull($key, $default = null)
     * @method Unit|null last(callable $callback = null, $default = null)
     * @method Unit|$this random(int|null $number = null)
     * @method Unit|null sole($key = null, $operator = null, $value = null)
     * @method Unit|null get($key, $default = null)
     * @method Unit|null first(callable $callback = null, $default = null)
     * @method Unit|null firstWhere(string $key, $operator = null, $value = null)
     * @method Unit|null find($key, $default = null)
     * @method Unit[] all()
     */
    class _IH_Unit_C extends _BaseCollection {
        /**
         * @param int $size
         * @return Unit[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_Unit_QB whereId($value)
     * @method _IH_Unit_QB whereName($value)
     * @method _IH_Unit_QB whereCreatedAt($value)
     * @method _IH_Unit_QB whereUpdatedAt($value)
     * @method Unit baseSole(array|string $columns = ['*'])
     * @method Unit create(array $attributes = [])
     * @method _IH_Unit_C|Unit[] cursor()
     * @method Unit|null|_IH_Unit_C|Unit[] find($id, array $columns = ['*'])
     * @method _IH_Unit_C|Unit[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method Unit|_IH_Unit_C|Unit[] findOrFail($id, array $columns = ['*'])
     * @method Unit|_IH_Unit_C|Unit[] findOrNew($id, array $columns = ['*'])
     * @method Unit first(array|string $columns = ['*'])
     * @method Unit firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method Unit firstOrCreate(array $attributes = [], array $values = [])
     * @method Unit firstOrFail(array $columns = ['*'])
     * @method Unit firstOrNew(array $attributes = [], array $values = [])
     * @method Unit firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method Unit forceCreate(array $attributes)
     * @method _IH_Unit_C|Unit[] fromQuery(string $query, array $bindings = [])
     * @method _IH_Unit_C|Unit[] get(array|string $columns = ['*'])
     * @method Unit getModel()
     * @method Unit[] getModels(array|string $columns = ['*'])
     * @method _IH_Unit_C|Unit[] hydrate(array $items)
     * @method Unit make(array $attributes = [])
     * @method Unit newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|Unit[]|_IH_Unit_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|Unit[]|_IH_Unit_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Unit sole(array|string $columns = ['*'])
     * @method Unit updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_Unit_QB extends _BaseBuilder {}

    /**
     * @method User|null getOrPut($key, $value)
     * @method User|$this shift(int $count = 1)
     * @method User|null firstOrFail($key = null, $operator = null, $value = null)
     * @method User|$this pop(int $count = 1)
     * @method User|null pull($key, $default = null)
     * @method User|null last(callable $callback = null, $default = null)
     * @method User|$this random(int|null $number = null)
     * @method User|null sole($key = null, $operator = null, $value = null)
     * @method User|null get($key, $default = null)
     * @method User|null first(callable $callback = null, $default = null)
     * @method User|null firstWhere(string $key, $operator = null, $value = null)
     * @method User|null find($key, $default = null)
     * @method User[] all()
     */
    class _IH_User_C extends _BaseCollection {
        /**
         * @param int $size
         * @return User[][]
         */
        public function chunk($size)
        {
            return [];
        }
    }

    /**
     * @method _IH_User_QB whereId($value)
     * @method _IH_User_QB whereName($value)
     * @method _IH_User_QB whereEmail($value)
     * @method _IH_User_QB whereEmailVerifiedAt($value)
     * @method _IH_User_QB wherePassword($value)
     * @method _IH_User_QB whereRememberToken($value)
     * @method _IH_User_QB whereCreatedAt($value)
     * @method _IH_User_QB whereUpdatedAt($value)
     * @method User baseSole(array|string $columns = ['*'])
     * @method User create(array $attributes = [])
     * @method _IH_User_C|User[] cursor()
     * @method User|null|_IH_User_C|User[] find($id, array $columns = ['*'])
     * @method _IH_User_C|User[] findMany(array|Arrayable $ids, array $columns = ['*'])
     * @method User|_IH_User_C|User[] findOrFail($id, array $columns = ['*'])
     * @method User|_IH_User_C|User[] findOrNew($id, array $columns = ['*'])
     * @method User first(array|string $columns = ['*'])
     * @method User firstOr(array|\Closure $columns = ['*'], \Closure $callback = null)
     * @method User firstOrCreate(array $attributes = [], array $values = [])
     * @method User firstOrFail(array $columns = ['*'])
     * @method User firstOrNew(array $attributes = [], array $values = [])
     * @method User firstWhere(array|\Closure|Expression|string $column, $operator = null, $value = null, string $boolean = 'and')
     * @method User forceCreate(array $attributes)
     * @method _IH_User_C|User[] fromQuery(string $query, array $bindings = [])
     * @method _IH_User_C|User[] get(array|string $columns = ['*'])
     * @method User getModel()
     * @method User[] getModels(array|string $columns = ['*'])
     * @method _IH_User_C|User[] hydrate(array $items)
     * @method User make(array $attributes = [])
     * @method User newModelInstance(array $attributes = [])
     * @method LengthAwarePaginator|User[]|_IH_User_C paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method Paginator|User[]|_IH_User_C simplePaginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method User sole(array|string $columns = ['*'])
     * @method User updateOrCreate(array $attributes, array $values = [])
     */
    class _IH_User_QB extends _BaseBuilder {}
}
