<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', 'all');

        if (! in_array($status, ['all', 'active', 'inactive'], true)) {
            $status = 'all';
        }

        $products = Product::query()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', fn (Builder $query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $productStats = [
            'total' => Product::query()->count(),
            'active' => Product::query()->where('status', 'active')->count(),
            'low_stock' => Product::query()->where('stock', '>', 0)->where('stock', '<=', 5)->count(),
            'out_of_stock' => Product::query()->where('stock', 0)->count(),
        ];

        return view('products.index', compact('products', 'productStats', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedProduct($request);
        $image = $data['image'] ?? null;
        unset($data['image'], $data['remove_image']);

        if ($image instanceof UploadedFile) {
            $data['image'] = $this->storeImage($image);
        }

        Product::query()->create($data);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedProduct($request, $product);
        $image = $data['image'] ?? null;
        $removeImage = (bool) ($data['remove_image'] ?? false);
        unset($data['image'], $data['remove_image']);

        if ($image instanceof UploadedFile) {
            $this->deleteImage($this->imagePath($product));
            $data['image'] = $this->storeImage($image);
        } elseif ($removeImage) {
            $this->deleteImage($this->imagePath($product));
            $data['image'] = null;
        }

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->saleItems()->exists() || $product->orderItems()->exists() || $product->sales()->exists() || $product->orders()->exists()) {
            return redirect()->route('products.index')->withErrors([
                'product' => 'This product has sales or orders. Set it to inactive instead of deleting it.',
            ]);
        }

        $this->deleteImage($this->imagePath($product));
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /** @return array<string, mixed> */
    private function validatedProduct(Request $request, ?Product $product = null): array
    {
        $uniqueSku = Rule::unique('products', 'sku');

        if ($product !== null) {
            $uniqueSku->ignore($product);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'sku' => [
                'required',
                'string',
                'max:60',
                $uniqueSku,
            ],
            'category' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function storeImage(UploadedFile $image): string
    {
        $directory = public_path('uploads/products');
        File::ensureDirectoryExists($directory);
        $filename = $image->hashName();
        $image->move($directory, $filename);

        return 'uploads/products/'.$filename;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/products/')) {
            File::delete(public_path($path));
        }
    }

    private function imagePath(Product $product): ?string
    {
        $path = $product->getAttribute('image');

        return is_string($path) ? $path : null;
    }
}
