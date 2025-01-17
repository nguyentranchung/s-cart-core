<?php
#S-Cart/Core/Front/Models/ShopStoreBlockContent.php
namespace SCart\Core\Front\Models;

use Illuminate\Database\Eloquent\Model;

class ShopStoreBlockContent extends Model
{
    public $timestamps = false;
    public $table = SC_DB_PREFIX.'shop_store_block';
    protected $guarded = [];
    private static $getLayout = null;
    protected $connection = SC_CONNECTION;

    public static function getLayout()
    {
        if (self::$getLayout === null) {
            self::$getLayout = self::where('status', 1)
                ->where('store_id', config('app.storeId'))
                ->orderBy('sort', 'asc')
                ->get()
                ->groupBy('position');
        }
        return self::$getLayout;
    }
}
