<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Uom;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()   { return view('master.products.index', ['products' => Product::with('defaultUom')->orderBy('name')->paginate(25)]); }
    public function create()  { return view('master.products.create', ['uoms' => Uom::where('is_active', true)->orderBy('code')->get()]); }
    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:products', 'commodity_type' => 'nullable|string|max:50', 'default_uom_id' => 'nullable|exists:uoms,id', 'status' => 'required|in:Auth Pending,Authorized,Do Not Use']);
        Product::create($data);
        return redirect()->route('master.products.index')->with('success', 'Product created.');
    }
    public function show(Product $product)  { return view('master.products.show', ['product' => $product->load('defaultUom')]); }
    public function edit(Product $product)  { return view('master.products.edit', ['product' => $product, 'uoms' => Uom::where('is_active', true)->orderBy('code')->get()]); }
    public function update(Request $request, Product $product)
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:products,name,'.$product->id, 'commodity_type' => 'nullable|string|max:50', 'default_uom_id' => 'nullable|exists:uoms,id', 'status' => 'required|in:Auth Pending,Authorized,Do Not Use']);
        $product->update($data);
        return redirect()->route('master.products.index')->with('success', 'Product updated.');
    }
    public function destroy(Product $product) { $product->delete(); return redirect()->route('master.products.index')->with('success', 'Deleted.'); }
}
