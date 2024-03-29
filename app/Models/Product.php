<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class Product extends Model
{
    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    public static $typeMap = [
        self::TYPE_NORMAL => '普通商品',
        self::TYPE_CROWDFUNDING => '众筹商品',
    ];

    protected $fillable = [
        'title','long_title','description','image','on_sale','rating','sold_count','review_count','price','type',
    ];

    protected $casts = [
        'on_sale' => 'boolean',
    ];

    public function properties()
    {
        return $this->hasMany(ProductProperty::class);
    }

    public function crowdfunding()
    {
        return $this->hasOne(CrowdfundingProduct::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // 与商品 SKU 关联
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    public function getImageUrlAttribute()
    {
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($this->attributes['image'],['http://','https://'])){
            return $this->attributes['image'];
        }

        return Storage::disk('public')->url($this->attributes['image']);
    }

    public function getGroupedPropertiesAttribute()
    {
        return $this->properties->groupBy('name')->map(function ($properties){
            return $properties->pluck('value')->all();
        });
    }

    public function toESArray()
    {
        $arr = Arr::only($this->toArray(), [
            'id',
            'type',
            'title',
            'category_id',
            'long_title',
            'on_sale',
            'rating',
            'sold_count',
            'review_count',
            'price',
        ]);

        $arr['category'] = $this->category ? explode(' - ', $this->category->full_name) : '';
        $arr['path'] = $this->category ? $this->category->path : '';
        $arr['description'] = strip_tags($this->description);
        $arr['skus'] = $this->skus->map(function (ProductSku $sku){
            return Arr::only($sku->toArray(), ['title', 'description', 'price']);
        });

        $arr['properties'] = $this->properties->map(function (ProductProperty $property){
            return Arr::only($property->toArray(), ['name', 'value']);
        });

        return $arr;
    }

    public function scopeByIds($query, $ids)
    {
        return $query->whereIn('id', $ids)->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(',', $ids)));
    }
}
