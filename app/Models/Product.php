<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'ingram_part_number',
        'vendor_part_number',
        'sku',
        'name',
        'slug',
        'brand',
        'short_description',
        'description',
        'specifications',
        'faqs',
        'cost_price_usd',
        'cost_price_clp',
        'margin_percentage',
        'calculated_price_clp',
        'regular_price',
        'sale_price',
        'stock',
        'stock_status',
        'main_image',
        'gallery',
        'scraper_source',
        'scraper_status',
        'scraper_last_run',
        'is_featured',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'specifications' => 'array',
        'faqs' => 'array',
        'gallery' => 'array',
        'cost_price_usd' => 'float',
        'cost_price_clp' => 'float',
        'margin_percentage' => 'float',
        'calculated_price_clp' => 'float',
        'regular_price' => 'float',
        'sale_price' => 'float',
        'stock' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'scraper_last_run' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get effective margin percentage:
     * 1. Product-specific margin override
     * 2. Category margin
     * 3. Global store margin
     */
    public function getEffectiveMarginAttribute(): float
    {
        if (!is_null($this->margin_percentage) && $this->margin_percentage > 0) {
            return (float) $this->margin_percentage;
        }

        if ($this->category && !is_null($this->category->margin_percentage) && $this->category->margin_percentage > 0) {
            return (float) $this->category->margin_percentage;
        }

        $globalMargin = Setting::get('ingram_global_margin', 18.0);
        return (float) $globalMargin;
    }

    /**
     * Recompute calculated retail price based on cost in USD/CLP and effective margin
     */
    public function calculateRetailPrice(): float
    {
        $rate = (float) Setting::get('ingram_usd_exchange_rate', 965.0);
        $costClp = $this->cost_price_clp;

        if ($costClp <= 0 && $this->cost_price_usd > 0) {
            $costClp = $this->cost_price_usd * $rate;
        }

        $margin = $this->effective_margin;
        $price = $costClp * (1 + ($margin / 100));

        // Round to nearest 10 or 100 CLP for clean Chilean prices
        return round($price / 10) * 10;
    }

    public function getCurrentPriceAttribute(): float
    {
        if (!empty($this->sale_price) && $this->sale_price > 0 && $this->sale_price < $this->regular_price) {
            return (float) $this->sale_price;
        }
        return (float) ($this->regular_price > 0 ? $this->regular_price : $this->calculated_price_clp);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->current_price, 0, ',', '.') . ' CLP';
    }

    public function getTransferDiscountPercentageAttribute(): float
    {
        return (float) Setting::get('transfer_discount_percentage', 5.0);
    }

    public function getNormalPriceAttribute(): float
    {
        return (float) $this->current_price;
    }

    public function getFormattedNormalPriceAttribute(): string
    {
        return '$' . number_format($this->normal_price, 0, ',', '.') . ' CLP';
    }

    public function getTransferPriceAttribute(): float
    {
        $discount = $this->transfer_discount_percentage;
        $price = $this->normal_price * (1 - ($discount / 100));
        return round($price / 10) * 10;
    }

    public function getFormattedTransferPriceAttribute(): string
    {
        return '$' . number_format($this->transfer_price, 0, ',', '.') . ' CLP';
    }

    public function getTransferSavingsAttribute(): float
    {
        return max(0, $this->normal_price - $this->transfer_price);
    }

    public function getFormattedTransferSavingsAttribute(): string
    {
        return '$' . number_format($this->transfer_savings, 0, ',', '.') . ' CLP';
    }

    public function getImageUrlAttribute(): string
    {
        if (!empty($this->main_image)) {
            if (Str::startsWith($this->main_image, ['http://', 'https://'])) {
                return $this->main_image;
            }
            if (file_exists(public_path($this->main_image))) {
                return asset($this->main_image);
            }
        }
        return asset('images/placeholder-product.svg');
    }

    public function getGalleryImagesAttribute(): array
    {
        $images = [];
        if (!empty($this->main_image)) {
            $images[] = $this->image_url;
        }
        if (!empty($this->gallery) && is_array($this->gallery)) {
            foreach ($this->gallery as $img) {
                $formatted = Str::startsWith($img, ['http://', 'https://']) ? $img : asset($img);
                if (!empty($formatted) && !in_array($formatted, $images)) {
                    $images[] = $formatted;
                }
            }
        }
        return !empty($images) ? $images : [asset('images/placeholder-product.svg')];
    }

    public function getHasCustomImageAttribute(): bool
    {
        return !empty($this->main_image) && $this->scraper_status !== 'not_found';
    }

    /**
     * Generate Schema.org Product JSON-LD array
     */
    public function toJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $this->name,
            'image' => [$this->image_url],
            'description' => strip_tags($this->short_description ?: Str::limit($this->description, 200)),
            'sku' => $this->sku,
            'mpn' => $this->vendor_part_number ?: $this->sku,
            'brand' => [
                '@type' => 'Brand',
                'name' => $this->brand ?: 'INEXUS Chile'
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('product.show', $this->slug),
                'priceCurrency' => 'CLP',
                'price' => (string) round($this->current_price),
                'availability' => $this->stock > 0 
                    ? 'https://schema.org/InStock' 
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => 'INEXUS Chile'
                ]
            ]
        ];
    }

    /**
     * Generate Schema.org FAQPage JSON-LD array
     */
    public function toFaqJsonLd(): ?array
    {
        $faqs = $this->faqs;
        if (empty($faqs) || !is_array($faqs)) {
            // Default high-value e-commerce tech questions
            $faqs = [
                [
                    'question' => '¿El producto ' . $this->name . ' cuenta con garantía oficial?',
                    'answer' => 'Sí, todos nuestros productos son 100% originales con garantía oficial de marca y respaldo legal en Chile por 6 meses conforme a la Ley del Consumidor.'
                ],
                [
                    'question' => '¿Emite factura para empresas?',
                    'answer' => 'Sí, en el proceso de compra puedes seleccionar Boleta o Factura Electrónica ingresando el RUT, Razón Social y Giro de tu empresa.'
                ],
                [
                    'question' => '¿Cuáles son los tiempos de despacho?',
                    'answer' => 'Despachamos a todo Chile. En la Región Metropolitana entregamos en 24 a 48 horas hábiles, y en regiones entre 2 a 4 días hábiles vía Courier express.'
                ]
            ];
        }

        $entities = [];
        foreach ($faqs as $faq) {
            if (!empty($faq['question']) && !empty($faq['answer'])) {
                $entities[] = [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['answer']
                    ]
                ];
            }
        }

        if (empty($entities)) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities
        ];
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name . '-' . $product->sku);
            }
        });
    }
}
