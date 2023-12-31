<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Selling;
use Illuminate\Http\Request;
use App\Models\SellingDetail;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaksi\SellingStoreRequest;

class SellingController extends Controller
{
    public function index(Request $request)
    {
        $data = Selling::with('customer', 'driver')
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('order', 'desc'))
            ->paginate($request->get('per_page', 10));
        $title = 'Data Penjualan';
        $route = 'selling';
        return view('pages.backoffice.selling.index', compact('data', 'title', 'route'));
    }

    public function create(Selling $selling)
    {
        $data['customer']   = $selling->getCustomer();
        $data['driver']     = $selling->getDriver();
        $data['vehicle']    = $selling->getVehicle();
        $data['product']    = $selling->getProduct();

        $data['header'] = (object)[
            'date'                  => null,
            'customer_id'           => null,
            'vehicle_id'            => null,
            'driver_id'             => null,
            'driver_pocket_money'   => null,
            'net_profit'            => null,
            'purchasing_method'     => null,
            'notes'                 => null,
            'status'                => null,
            'payment_type'          => null
        ];

        $title  = 'Data Penjualan';
        $route  = route('selling.store');
        $type   = 'create';

        return view('pages.backoffice.selling._form', compact('data', 'title', 'route', 'type'));
    }

    public function store(SellingStoreRequest $request)
    {
        $user = auth()->user();

        try {
            // insert Table Selling
            $selling                        = new Selling();
            $selling->date                  = $request->tgl_jual;
            $selling->customer_id           = $request->customer;
            $selling->vehicle_id            = $request->supplier;
            $selling->driver_id             = $request->driver;
            $selling->vehicle_id            = $request->kendaraan;
            $selling->drivers_pocket_money  = $request->uang_saku;
            $selling->purchasing_method     = $request->tipe_pembelian;
            $selling->payment_type          = $request->tipe_pembayaran;
            $selling->notes                 = $request->catatan;
            $selling->status                = $request->catatan;
            $selling->grand_total           = $request->grand_total;
            $selling->total_payment         = $request->total_bayar;
            $selling->net_profit            = $request->laba_bersih;
            $selling->status                = 'In Progress';
            $selling->created_by            = $user['name'];
            $selling->updated_by            = $user['name'];
            $selling->save();

            //insert Table Selling Detail
            $totalDataProduk = COUNT($request->produk_id);

            for ($i = 0; $i < $totalDataProduk; $i++) {
                $produk_id = $request->produk_id[$i];
                $qty = $request->jumlah_qty[$i];
                $harga_jual = $request->harga_jual[$i];
                $subtotal = $request->subtotal_produk[$i];

                // cek stok by product id
                $stocks = Stock::select('id', 'last_stock', 'price_kg', 'product_id')
                    ->where('product_id', $produk_id)
                    ->where('is_active', 1)
                    ->where('last_stock', '>', 0)
                    ->orderBy('purchase_date', 'asc')
                    ->get();

                $arr = [];
                $cekTotalStock = $qty;
                foreach ($stocks as $key => $value) {
                    if (intval($value->last_stock) >= intval($cekTotalStock)) {
                        array_push($arr, ['id' => $value->id, 'stock' => $cekTotalStock, 'price_kg' => $value->price_kg, 'price_sell' => $harga_jual, 'subtotal' => $subtotal]);
                        break;
                    } else {
                        array_push($arr, ['id' => $value->id, 'stock' => $value->last_stock, 'price_kg' => $value->price_kg, 'price_sell' => $harga_jual, 'subtotal' => $subtotal]);
                        $cekTotalStock = intval($cekTotalStock) - intval($value->last_stock);
                    }
                };

                // insert Table Selling Detail
                for ($j = 0; $j < COUNT($arr); $j++) {
                    $selling_detail = new SellingDetail();
                    $selling_detail->selling_id = $selling->id;
                    $selling_detail->stock_id = $arr[$j]['id'];
                    $selling_detail->price_kg = $arr[$j]['price_kg'];
                    $selling_detail->price_sell = $arr[$j]['price_sell'];
                    $selling_detail->qty        = $arr[$j]['stock'];
                    $selling_detail->subtotal   = $arr[$j]['subtotal'];
                    $selling_detail->save();
                }
            }

            return redirect(route('selling.index'))->with('success', 'Berhasil menambah data!');
        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage() . " at line " . $th->getLine();
            // var_dump($errorMessage);
            // die;
            return back()->with('failed', $errorMessage);
        }
    }

    public function edit(Selling $Selling)
    {
        $selling2 = new Selling;

        // $Selling->load('selling_detail.stock');
        // $Selling->load('selling_detail.stock.product');

        $sellingDetails = $Selling->getProductSummary();

        $data['customer']   = $selling2->getCustomer();
        $data['driver']     = $selling2->getDriver();
        $data['vehicle']    = $selling2->getVehicle();
        $data['product']    = $selling2->getProduct();
        $data['header']     = $Selling;
        $data['detail']     = $sellingDetails;

        // echo json_encode($data['detail']); die;

        $title = 'Data Penjualan';
        $route = route('selling.update', $Selling);
        $type = 'edit';

        return view('pages.backoffice.selling._form', compact('data', 'title', 'route', 'type'));
    }

    public function update(SellingStoreRequest $request, Selling $selling)
    {
        $user = auth()->user();

        try {
            if ($request->mode == 'Konfirmasi Lunas') {
                $selling->status = 'Completed';
                $selling->updated_by = $user['name'];
                $selling->save();

                return redirect(route('selling.index'))->with('success', 'Berhasil update data!');
                return false;
            } else if ($request->mode == 'angsuran') {
                $selling->total_payment = intval($selling->total_payment) + intval($request->angsuran);
                $selling->updated_by = $user['name'];
                $selling->notes = $request->catatan;
                $selling->save();

                return redirect(route('selling.index'))->with('success', 'Berhasil update data!');
                return false;
            }

            // insert Table Selling
            $selling->date                  = $request->tgl_jual;
            $selling->customer_id           = $request->customer;
            $selling->vehicle_id            = $request->supplier;
            $selling->driver_id             = $request->driver;
            $selling->vehicle_id            = $request->kendaraan;
            $selling->drivers_pocket_money  = $request->uang_saku;
            $selling->purchasing_method     = $request->tipe_pembelian;
            $selling->payment_type          = $request->tipe_pembayaran;
            $selling->notes                 = $request->catatan;
            $selling->grand_total           = $request->grand_total;
            $selling->total_payment         = $request->total_bayar;
            $selling->net_profit            = $request->laba_bersih;

            if ($request->mode != null) {
                $selling->status = 'On Progress';
            } else {
                $selling->status = 'In Progress';
            }

            $selling->created_by            = $user['name'];
            $selling->updated_by            = $user['name'];
            $selling->save();

            $selling->selling_detail()->delete();

            //insert Table Selling Detail
            $totalDataProduk = COUNT($request->produk_id);

            for ($i = 0; $i < $totalDataProduk; $i++) {
                $produk_id = $request->produk_id[$i];
                $qty = $request->jumlah_qty[$i];
                $harga_jual = $request->harga_jual[$i];
                $subtotal = $request->subtotal_produk[$i];

                // cek stok by product id
                $stocks = Stock::select('id', 'last_stock', 'price_kg', 'product_id')
                    ->where('product_id', $produk_id)
                    ->where('is_active', 1)
                    ->where('last_stock', '>', 0)
                    ->orderBy('purchase_date', 'asc')
                    ->get();

                $arr = [];
                $cekTotalStock = $qty;
                foreach ($stocks as $key => $value) {
                    if (intval($value->last_stock) >= intval($cekTotalStock)) {
                        array_push($arr, ['id' => $value->id, 'stock' => $cekTotalStock, 'last_stock' => intval($value->last_stock) - intval($cekTotalStock), 'price_kg' => $value->price_kg, 'price_sell' => $harga_jual, 'subtotal' => $subtotal]);
                        break;
                    } else {
                        array_push($arr, ['id' => $value->id, 'stock' => $value->last_stock, 'last_stock' => 0, 'price_kg' => $value->price_kg, 'price_sell' => $harga_jual, 'subtotal' => $subtotal]);
                        $cekTotalStock = intval($cekTotalStock) - intval($value->last_stock);
                    }
                };

                // insert Table Selling Detail
                for ($j = 0; $j < COUNT($arr); $j++) {
                    $selling_detail = new SellingDetail();
                    $selling_detail->selling_id = $selling->id;
                    $selling_detail->stock_id = $arr[$j]['id'];
                    $selling_detail->price_kg = $arr[$j]['price_kg'];
                    $selling_detail->price_sell = $arr[$j]['price_sell'];
                    $selling_detail->qty        = $arr[$j]['stock'];
                    $selling_detail->subtotal   = $arr[$j]['subtotal'];
                    $selling_detail->save();

                    if ($request->mode != null) {
                        Stock::where('id', $arr[$j]['id'])
                            ->update([
                                'stock_in_use' => $arr[$j]['stock'],
                                'last_stock' => $arr[$j]['last_stock']
                            ]);
                    }
                }
            }

            if ($request->mode != null) {
                return redirect(route('selling.index'))->with('success', 'Berhasil menyimpan data!');
            } else {
                return redirect(route('selling.edit', $selling->id))->with('success', 'Berhasil update data!');
            }
            return redirect(route('selling.index'))->with('success', 'Berhasil menambah data!');
        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage() . " at line " . $th->getLine();
            // var_dump($errorMessage);
            // die;
            return back()->with('failed', 'Gagal menyimpan data, karena : ' . $errorMessage);
        }
    }

    public function destroy(Selling $selling)
    {
        try {
            $selling->selling_detail()->delete();
            $selling->delete();
            return  redirect('selling')->with('success', 'Berhasil menghapus data!');
        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage() . " at line " . $th->getLine();
            return back()->with('failed', 'Gagal menghapus data, karena : ' . $errorMessage);
        }
    }

    public function show(Selling $Selling)
    {
        $selling = new Selling;

        // $selling->load('selling_detail.product');

        $sellingDetails = $Selling->getProductSummary();

        $data['customer']   = $selling->getCustomer();
        $data['driver']     = $selling->getDriver();
        $data['vehicle']    = $selling->getVehicle();
        $data['product']    = $selling->getProduct();
        $data['header']     = $Selling;
        $data['detail']     = $sellingDetails;

        // echo json_encode($data['product']); die;

        $title = 'Data Penjualan';
        $route = route('selling.update', $Selling);
        $type = 'show';

        return view('pages.backoffice.selling._view', compact('data', 'title', 'route', 'type'));
    }

    public function getHargaStock(Request $request)
    {
        $produk = $request->input('produk');
        $qty = $request->input('qty');

        $stocks = Stock::select('id', 'last_stock', 'price_kg', 'product_id')
            ->where('product_id', $produk)
            ->where('is_active', 1)
            ->where('last_stock', '>', 0)
            ->orderBy('purchase_date', 'asc')
            ->get();

        $arr = [];
        $cekTotalStock = $qty;
        foreach ($stocks as $key => $value) {
            if (intval($value->last_stock) >= intval($cekTotalStock)) {
                array_push($arr, ['id' => $value->id, 'stock' => $cekTotalStock, 'price_kg' => $value->price_kg, 'product_id' => $value->product_id]);
                break;
            } else {
                array_push($arr, ['id' => $value->id, 'stock' => $value->last_stock, 'price_kg' => $value->price_kg, 'product_id' => $value->product_id]);
                $cekTotalStock = intval($cekTotalStock) - intval($value->last_stock);
            }
        };

        return response()->json($arr);
    }
}
