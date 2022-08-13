<?php //b3db4bcaa84f956cd7e62c0561c753c7
/** @noinspection all */

namespace App\Models {

    use Database\Factories\UserFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphToMany;
    use Illuminate\Notifications\DatabaseNotification;
    use Illuminate\Support\Carbon;
    use LaravelIdea\Helper\App\Models\_IH_AveragePurchasePrice_C;
    use LaravelIdea\Helper\App\Models\_IH_AveragePurchasePrice_QB;
    use LaravelIdea\Helper\App\Models\_IH_BkashTransaction_C;
    use LaravelIdea\Helper\App\Models\_IH_BkashTransaction_QB;
    use LaravelIdea\Helper\App\Models\_IH_CardTransaction_C;
    use LaravelIdea\Helper\App\Models\_IH_CardTransaction_QB;
    use LaravelIdea\Helper\App\Models\_IH_CashBook_C;
    use LaravelIdea\Helper\App\Models\_IH_CashBook_QB;
    use LaravelIdea\Helper\App\Models\_IH_Category_C;
    use LaravelIdea\Helper\App\Models\_IH_Category_QB;
    use LaravelIdea\Helper\App\Models\_IH_CustomerLedger_C;
    use LaravelIdea\Helper\App\Models\_IH_CustomerLedger_QB;
    use LaravelIdea\Helper\App\Models\_IH_Customer_C;
    use LaravelIdea\Helper\App\Models\_IH_Customer_QB;
    use LaravelIdea\Helper\App\Models\_IH_InvoiceItem_C;
    use LaravelIdea\Helper\App\Models\_IH_InvoiceItem_QB;
    use LaravelIdea\Helper\App\Models\_IH_Invoice_C;
    use LaravelIdea\Helper\App\Models\_IH_Invoice_QB;
    use LaravelIdea\Helper\App\Models\_IH_NagadTransaction_C;
    use LaravelIdea\Helper\App\Models\_IH_NagadTransaction_QB;
    use LaravelIdea\Helper\App\Models\_IH_Product_C;
    use LaravelIdea\Helper\App\Models\_IH_Product_QB;
    use LaravelIdea\Helper\App\Models\_IH_PurchaseItem_C;
    use LaravelIdea\Helper\App\Models\_IH_PurchaseItem_QB;
    use LaravelIdea\Helper\App\Models\_IH_Purchase_C;
    use LaravelIdea\Helper\App\Models\_IH_Purchase_QB;
    use LaravelIdea\Helper\App\Models\_IH_SupplierLedger_C;
    use LaravelIdea\Helper\App\Models\_IH_SupplierLedger_QB;
    use LaravelIdea\Helper\App\Models\_IH_Supplier_C;
    use LaravelIdea\Helper\App\Models\_IH_Supplier_QB;
    use LaravelIdea\Helper\App\Models\_IH_Unit_C;
    use LaravelIdea\Helper\App\Models\_IH_Unit_QB;
    use LaravelIdea\Helper\App\Models\_IH_User_C;
    use LaravelIdea\Helper\App\Models\_IH_User_QB;
    use LaravelIdea\Helper\Illuminate\Notifications\_IH_DatabaseNotification_C;
    use LaravelIdea\Helper\Illuminate\Notifications\_IH_DatabaseNotification_QB;

    /**
     * @property int $id
     * @property string $product_id
     * @property string $price
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_AveragePurchasePrice_QB onWriteConnection()
     * @method _IH_AveragePurchasePrice_QB newQuery()
     * @method static _IH_AveragePurchasePrice_QB on(null|string $connection = null)
     * @method static _IH_AveragePurchasePrice_QB query()
     * @method static _IH_AveragePurchasePrice_QB with(array|string $relations)
     * @method _IH_AveragePurchasePrice_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_AveragePurchasePrice_C|AveragePurchasePrice[] all()
     * @ownLinks product_id,\App\Models\Product,id
     * @mixin _IH_AveragePurchasePrice_QB
     */
    class AveragePurchasePrice extends Model {}

    /**
     * @property int $id
     * @property string $transaction_id
     * @property string|null $reference_no
     * @property string|null $comment
     * @property string $type
     * @property string $withdraw
     * @property string $deposit
     * @property string $date
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_BkashTransaction_QB onWriteConnection()
     * @method _IH_BkashTransaction_QB newQuery()
     * @method static _IH_BkashTransaction_QB on(null|string $connection = null)
     * @method static _IH_BkashTransaction_QB query()
     * @method static _IH_BkashTransaction_QB with(array|string $relations)
     * @method _IH_BkashTransaction_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_BkashTransaction_C|BkashTransaction[] all()
     * @mixin _IH_BkashTransaction_QB
     */
    class BkashTransaction extends Model {}

    /**
     * @property int $id
     * @property string $transaction_id
     * @property string|null $reference_no
     * @property string|null $comment
     * @property string $type
     * @property string $withdraw
     * @property string $deposit
     * @property string $date
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_CardTransaction_QB onWriteConnection()
     * @method _IH_CardTransaction_QB newQuery()
     * @method static _IH_CardTransaction_QB on(null|string $connection = null)
     * @method static _IH_CardTransaction_QB query()
     * @method static _IH_CardTransaction_QB with(array|string $relations)
     * @method _IH_CardTransaction_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_CardTransaction_C|CardTransaction[] all()
     * @mixin _IH_CardTransaction_QB
     */
    class CardTransaction extends Model {}

    /**
     * @property int $id
     * @property string $transaction_id
     * @property string|null $reference_no
     * @property string|null $comment
     * @property string $type
     * @property string $payment
     * @property string $receive
     * @property string $date
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_CashBook_QB onWriteConnection()
     * @method _IH_CashBook_QB newQuery()
     * @method static _IH_CashBook_QB on(null|string $connection = null)
     * @method static _IH_CashBook_QB query()
     * @method static _IH_CashBook_QB with(array|string $relations)
     * @method _IH_CashBook_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_CashBook_C|CashBook[] all()
     * @mixin _IH_CashBook_QB
     */
    class CashBook extends Model {}

    /**
     * @property int $id
     * @property string $name
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_Category_QB onWriteConnection()
     * @method _IH_Category_QB newQuery()
     * @method static _IH_Category_QB on(null|string $connection = null)
     * @method static _IH_Category_QB query()
     * @method static _IH_Category_QB with(array|string $relations)
     * @method _IH_Category_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_Category_C|Category[] all()
     * @mixin _IH_Category_QB
     */
    class Category extends Model {}

    /**
     * @property int $id
     * @property string $name
     * @property string|null $mobile
     * @property string|null $address
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_Customer_QB onWriteConnection()
     * @method _IH_Customer_QB newQuery()
     * @method static _IH_Customer_QB on(null|string $connection = null)
     * @method static _IH_Customer_QB query()
     * @method static _IH_Customer_QB with(array|string $relations)
     * @method _IH_Customer_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_Customer_C|Customer[] all()
     * @foreignLinks id,\App\Models\CustomerLedger,customer_id|id,\App\Models\Invoice,customer_id
     * @mixin _IH_Customer_QB
     */
    class Customer extends Model {}

    /**
     * @property int $id
     * @property string $customer_id
     * @property string $transaction_id
     * @property string|null $reference_no
     * @property string $type
     * @property string $due
     * @property string $deposit
     * @property string $date
     * @property string $comment
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_CustomerLedger_QB onWriteConnection()
     * @method _IH_CustomerLedger_QB newQuery()
     * @method static _IH_CustomerLedger_QB on(null|string $connection = null)
     * @method static _IH_CustomerLedger_QB query()
     * @method static _IH_CustomerLedger_QB with(array|string $relations)
     * @method _IH_CustomerLedger_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_CustomerLedger_C|CustomerLedger[] all()
     * @ownLinks customer_id,\App\Models\Customer,id
     * @mixin _IH_CustomerLedger_QB
     */
    class CustomerLedger extends Model {}

    /**
     * @property int $id
     * @property string $customer_id
     * @property string $invoice_id
     * @property string $total
     * @property string|null $comment
     * @property string $date
     * @property string|null $discount
     * @property string|null $discountAmount
     * @property string|null $discountType
     * @property string $profit
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_Invoice_QB onWriteConnection()
     * @method _IH_Invoice_QB newQuery()
     * @method static _IH_Invoice_QB on(null|string $connection = null)
     * @method static _IH_Invoice_QB query()
     * @method static _IH_Invoice_QB with(array|string $relations)
     * @method _IH_Invoice_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_Invoice_C|Invoice[] all()
     * @ownLinks customer_id,\App\Models\Customer,id
     * @foreignLinks id,\App\Models\InvoiceItem,invoice_id
     * @mixin _IH_Invoice_QB
     */
    class Invoice extends Model {}

    /**
     * @property int $id
     * @property string $invoice_id
     * @property string $product_id
     * @property string $price
     * @property string $quantity
     * @property string $total
     * @property string $date
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_InvoiceItem_QB onWriteConnection()
     * @method _IH_InvoiceItem_QB newQuery()
     * @method static _IH_InvoiceItem_QB on(null|string $connection = null)
     * @method static _IH_InvoiceItem_QB query()
     * @method static _IH_InvoiceItem_QB with(array|string $relations)
     * @method _IH_InvoiceItem_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_InvoiceItem_C|InvoiceItem[] all()
     * @ownLinks invoice_id,\App\Models\Invoice,id|product_id,\App\Models\Product,id
     * @mixin _IH_InvoiceItem_QB
     */
    class InvoiceItem extends Model {}

    /**
     * @property int $id
     * @property string $transaction_id
     * @property string|null $reference_no
     * @property string|null $comment
     * @property string $type
     * @property string $withdraw
     * @property string $deposit
     * @property string $date
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_NagadTransaction_QB onWriteConnection()
     * @method _IH_NagadTransaction_QB newQuery()
     * @method static _IH_NagadTransaction_QB on(null|string $connection = null)
     * @method static _IH_NagadTransaction_QB query()
     * @method static _IH_NagadTransaction_QB with(array|string $relations)
     * @method _IH_NagadTransaction_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_NagadTransaction_C|NagadTransaction[] all()
     * @mixin _IH_NagadTransaction_QB
     */
    class NagadTransaction extends Model {}

    /**
     * @property int $id
     * @property string $name
     * @property string $product_id
     * @property string|null $category
     * @property string|null $unit
     * @property string|null $price
     * @property string|null $purchase_price
     * @property string $weight
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_Product_QB onWriteConnection()
     * @method _IH_Product_QB newQuery()
     * @method static _IH_Product_QB on(null|string $connection = null)
     * @method static _IH_Product_QB query()
     * @method static _IH_Product_QB with(array|string $relations)
     * @method _IH_Product_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_Product_C|Product[] all()
     * @foreignLinks id,\App\Models\PurchaseItem,product_id|id,\App\Models\InvoiceItem,product_id|id,\App\Models\AveragePurchasePrice,product_id
     * @mixin _IH_Product_QB
     */
    class Product extends Model {}

    /**
     * @property int $id
     * @property string $supplier_id
     * @property string $purchase_id
     * @property string $amount
     * @property string $paid
     * @property string|null $comment
     * @property string $date
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_Purchase_QB onWriteConnection()
     * @method _IH_Purchase_QB newQuery()
     * @method static _IH_Purchase_QB on(null|string $connection = null)
     * @method static _IH_Purchase_QB query()
     * @method static _IH_Purchase_QB with(array|string $relations)
     * @method _IH_Purchase_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_Purchase_C|Purchase[] all()
     * @ownLinks supplier_id,\App\Models\Supplier,id
     * @foreignLinks id,\App\Models\PurchaseItem,purchase_id
     * @mixin _IH_Purchase_QB
     */
    class Purchase extends Model {}

    /**
     * @property int $id
     * @property string $purchase_id
     * @property string $product_id
     * @property string $price
     * @property string $quantity
     * @property string $total
     * @property string $date
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_PurchaseItem_QB onWriteConnection()
     * @method _IH_PurchaseItem_QB newQuery()
     * @method static _IH_PurchaseItem_QB on(null|string $connection = null)
     * @method static _IH_PurchaseItem_QB query()
     * @method static _IH_PurchaseItem_QB with(array|string $relations)
     * @method _IH_PurchaseItem_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_PurchaseItem_C|PurchaseItem[] all()
     * @ownLinks purchase_id,\App\Models\Purchase,id|product_id,\App\Models\Product,id
     * @mixin _IH_PurchaseItem_QB
     */
    class PurchaseItem extends Model {}

    /**
     * @property int $id
     * @property string $name
     * @property string|null $mobile
     * @property string|null $address
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_Supplier_QB onWriteConnection()
     * @method _IH_Supplier_QB newQuery()
     * @method static _IH_Supplier_QB on(null|string $connection = null)
     * @method static _IH_Supplier_QB query()
     * @method static _IH_Supplier_QB with(array|string $relations)
     * @method _IH_Supplier_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_Supplier_C|Supplier[] all()
     * @foreignLinks id,\App\Models\SupplierLedger,supplier_id|id,\App\Models\Purchase,supplier_id
     * @mixin _IH_Supplier_QB
     */
    class Supplier extends Model {}

    /**
     * @property int $id
     * @property string $supplier_id
     * @property string $transaction_id
     * @property string|null $reference_no
     * @property string $type
     * @property string $due
     * @property string $deposit
     * @property string $date
     * @property string $comment
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_SupplierLedger_QB onWriteConnection()
     * @method _IH_SupplierLedger_QB newQuery()
     * @method static _IH_SupplierLedger_QB on(null|string $connection = null)
     * @method static _IH_SupplierLedger_QB query()
     * @method static _IH_SupplierLedger_QB with(array|string $relations)
     * @method _IH_SupplierLedger_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_SupplierLedger_C|SupplierLedger[] all()
     * @ownLinks supplier_id,\App\Models\Supplier,id
     * @mixin _IH_SupplierLedger_QB
     */
    class SupplierLedger extends Model {}

    /**
     * @property int $id
     * @property string $name
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static _IH_Unit_QB onWriteConnection()
     * @method _IH_Unit_QB newQuery()
     * @method static _IH_Unit_QB on(null|string $connection = null)
     * @method static _IH_Unit_QB query()
     * @method static _IH_Unit_QB with(array|string $relations)
     * @method _IH_Unit_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_Unit_C|Unit[] all()
     * @mixin _IH_Unit_QB
     */
    class Unit extends Model {}

    /**
     * @property int $id
     * @property string $name
     * @property string $email
     * @property Carbon|null $email_verified_at
     * @property string $password
     * @property string|null $remember_token
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @property _IH_DatabaseNotification_C|DatabaseNotification[] $notifications
     * @property-read int $notifications_count
     * @method MorphToMany|_IH_DatabaseNotification_QB notifications()
     * @property _IH_DatabaseNotification_C|DatabaseNotification[] $readNotifications
     * @property-read int $read_notifications_count
     * @method MorphToMany|_IH_DatabaseNotification_QB readNotifications()
     * @property _IH_DatabaseNotification_C|DatabaseNotification[] $unreadNotifications
     * @property-read int $unread_notifications_count
     * @method MorphToMany|_IH_DatabaseNotification_QB unreadNotifications()
     * @method static _IH_User_QB onWriteConnection()
     * @method _IH_User_QB newQuery()
     * @method static _IH_User_QB on(null|string $connection = null)
     * @method static _IH_User_QB query()
     * @method static _IH_User_QB with(array|string $relations)
     * @method _IH_User_QB newModelQuery()
     * @method false|int increment(string $column, float|int $amount = 1, array $extra = [])
     * @method false|int decrement(string $column, float|int $amount = 1, array $extra = [])
     * @method static _IH_User_C|User[] all()
     * @mixin _IH_User_QB
     * @method static UserFactory factory(...$parameters)
     */
    class User extends Model {}
}
