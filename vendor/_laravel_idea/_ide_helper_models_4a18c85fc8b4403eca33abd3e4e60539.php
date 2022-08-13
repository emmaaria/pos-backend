<?php //1923afd15fb3304cac0fdba207bf90b7
/** @noinspection all */

namespace Skycoder\InvoiceNumberGenerator\Models {

    use Illuminate\Database\Eloquent\Model;
    use LaravelIdea\Helper\Skycoder\InvoiceNumberGenerator\Models\_IH_InvoiceNumber_C;
    use LaravelIdea\Helper\Skycoder\InvoiceNumberGenerator\Models\_IH_InvoiceNumber_QB;

    /**
     * @method static _IH_InvoiceNumber_QB onWriteConnection()
     * @method _IH_InvoiceNumber_QB newQuery()
     * @method static _IH_InvoiceNumber_QB on(null|string $connection = null)
     * @method static _IH_InvoiceNumber_QB query()
     * @method static _IH_InvoiceNumber_QB with(array|string $relations)
     * @method _IH_InvoiceNumber_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_InvoiceNumber_C|InvoiceNumber[] all()
     * @mixin _IH_InvoiceNumber_QB
     */
    class InvoiceNumber extends Model {}
}
