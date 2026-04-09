<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'price',
        'category_id',
        'image_url',
        'stock',
        'sku',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'uuid' => 'string',
        'category_id' => 'integer',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot do model
     */
    protected static function boot(): void
    {
        parent::boot();

        // Gera UUID e slug automaticamente ao criar
        static::creating(function (Product $product) {
            if (empty($product->uuid)) {
                $product->uuid = (string) Str::uuid();
            }
            
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            
            // Gera SKU automaticamente se não fornecido
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });

        // Atualiza slug ao editar o nome
        static::updating(function (Product $product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope para buscar apenas produtos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope para buscar produtos em estoque
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope para buscar por categoria
     */
    public function scopeByCategory($query, string $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope para buscar por range de preço
     */
    public function scopeByPriceRange($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Verifica se o produto está disponível
     * Produto disponível = ativo (is_active) E tem estoque (stock > 0)
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    /**
     * Decrementa o estoque
     */
    public function decrementStock(int $quantity = 1): bool
    {
        if ($this->stock < $quantity) {
            return false;
        }

        $this->decrement('stock', $quantity);
        return true;
    }

    /**
     * Incrementa o estoque
     */
    public function incrementStock(int $quantity = 1): bool
    {
        $this->increment('stock', $quantity);
        return true;
    }
}
