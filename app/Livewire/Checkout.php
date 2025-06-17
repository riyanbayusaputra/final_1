<?php

namespace App\Livewire;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Store;
use App\Models\DeliveryArea; // Import model baru
use GuzzleHttp\Client;
use Livewire\Component;
use App\Models\ProductOptionItem;
use App\Services\MidtransService;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\Notification;

class Checkout extends Component
{
    public $selectedOptions = [];
    public $selectedOptionItems = [];
    public $selectedOptionItemsName = [];
    public $selectedOptionItemsPrice = [];
    public $selectedOptionItemsId = [];
    public $selectedOptionItemsJson = [];
    public $selectedOptionItemsJsonName = [];
    public $carts = [];
    public $total = 0;
    public $shippingCost = 0;
    public $store;
    public $price_adjustment = 0;
    public $isCustomCatering = false;
    public $customCatering = [
        'menu_description' => '',
    ];
    protected $midtrans;
    public $shippingData = [
        'recipient_name' => '',
        'phone' => '',
        'shipping_address' => '',
        'noted' => '',
        'delivery_date' => '',
        'delivery_time' => '',
    ];

    // Data wilayah - sekarang hanya provinsi dan kabupaten yg sudah diatur oleh admin
    public $availableProvinsis = [];
    public $availableKabupatens = [];
    public $allKecamatans = []; // Semua kecamatan dari API
    
    public $selected_provinsi = '';
    public $selected_kabupaten = '';
    public $selected_kecamatan = '';

    
    protected function rules()
    {
        $rules = [
            'shippingData.recipient_name' => 'required|min:3',
            'shippingData.phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'shippingData.shipping_address' => 'required|min:5',
            'shippingData.delivery_date' => 'required',
            'shippingData.delivery_time' => 'required',
            'selected_provinsi' => 'required',
            'selected_kabupaten' => 'required',
        ];

        if ($this->isCustomCatering) {
            $rules['customCatering.menu_description'] = 'required|min:5';
        }

        return $rules;
    }

    protected $messages = [
        'shippingData.phone.required' => 'Nomor telepon wajib diisi.',
        'shippingData.phone.min' => 'Nomor telepon minimal 10 karakter.',
        'shippingData.phone.regex' => 'Format nomor telepon tidak valid.',
        'shippingData.recipient_name.required' => 'Nama penerima wajib diisi.',
        'shippingData.recipient_name.min' => 'Nama penerima minimal 3 karakter.',
        'shippingData.shipping_address.required' => 'Alamat pengiriman wajib diisi.',
        'shippingData.shipping_address.min' => 'Alamat pengiriman minimal 5 karakter.',
        'shippingData.delivery_date.required' => 'Tanggal pengiriman wajib dipilih.',
        'shippingData.delivery_time.required' => 'Waktu pengiriman wajib dipilih.',
        'selected_provinsi.required' => 'Provinsi wajib dipilih.',
        'selected_kabupaten.required' => 'Kabupaten/Kota wajib dipilih.',
        'customCatering.menu_description.required' => 'Deskripsi menu wajib diisi.',
    ];

    public function boot(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    public function mount()
    {
        $this->loadCarts();
        if ($this->carts->isEmpty()) {
            return redirect()->route('home');
        }
        $this->store = Store::first();

        if (auth()->check()) {
            $user = auth()->user();
            $this->shippingData['recipient_name'] = $user->name;
            $this->shippingData['phone'] = $user->no_telepon ?? '';
        }

        // Load available areas from database
        $this->loadAvailableAreas();
    }

    public function loadAvailableAreas()
    {
        // Ambil provinsi yang tersedia dari database
        $this->availableProvinsis = DeliveryArea::getAvailableProvinsi()->toArray();
    }

    public function updatedSelectedProvinsi($provinsiId)
    {
        $this->selected_provinsi = $provinsiId;
        $this->selected_kabupaten = '';
        $this->selected_kecamatan = '';

        $this->availableKabupatens = [];
        $this->allKecamatans = [];
        
        if (!empty($provinsiId)) {
            // Ambil kabupaten yang tersedia berdasarkan provinsi
            $this->availableKabupatens = DeliveryArea::getAvailableKabupaten($provinsiId)->toArray();
        }
    }

    public function updatedSelectedKabupaten($kabupatenId)
    {
        $this->selected_kabupaten = $kabupatenId;
        $this->selected_kecamatan = '';

        $this->allKecamatans = [];
        
        if (!empty($kabupatenId)) {
            // Ambil semua kecamatan dari API (bukan dari database)
            $this->allKecamatans = DeliveryArea::getKecamatanFromApi($kabupatenId)->toArray();
        }
    }

    public function updatedSelectedKecamatan($kecamatanId)
    {
        $this->selected_kecamatan = $kecamatanId;
    }

    protected function mapCustomOptionsToNames(array $customOptions): array
    {
        $names = [];

        foreach ($customOptions as $optionTypeId => $optionItemId) {
            if (is_array($optionItemId)) {
                foreach ($optionItemId as $id) {
                    $item = ProductOptionItem::find($id);
                    if ($item) {
                        $names[] = $item->name;
                    }
                }
            } else {
                $item = ProductOptionItem::find($optionItemId);
                if ($item) {
                    $names[] = $item->name;
                }
            }
        }

        return $names;
    }

    protected function getAllCartCustomOptionsJson(): string
    {
        $allNames = [];

        foreach ($this->carts as $cart) {
            $customOptions = json_decode($cart->custom_options_json, true);
            if (is_array($customOptions) && !empty($customOptions)) {
                $names = $this->mapCustomOptionsToNames($customOptions);
                $allNames = array_merge($allNames, $names);
            }
        }

        $allNames = array_values(array_unique($allNames));

        return json_encode($allNames);
    }

    public function loadCarts()
    {
        $this->carts = Cart::where('user_id', auth()->id())
            ->with('product')
            ->get();

        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total = 0;

        foreach ($this->carts as $cart) {
            $this->total += $cart->product->price * $cart->quantity;
        }
    }

    public function render()
    {
        if ($this->carts->isEmpty()) {
            return redirect()->route('home');
        }
        return view('livewire.checkout')
            ->layout('components.layouts.app', ['hideBottomNav' => true]);
    }

    public function createOrder()
    {
        if (!$this->carts->isEmpty()) {
            try {
                $this->validate();
                
                // Validasi apakah wilayah yang dipilih tersedia untuk pengiriman (hanya cek provinsi dan kabupaten)
                if (!DeliveryArea::isAreaAvailable($this->selected_provinsi, $this->selected_kabupaten)) {
                    $this->dispatch('showAlert', [
                        'message' => 'Maaf, wilayah yang Anda pilih belum tersedia untuk layanan pengiriman kami.',
                        'type' => 'error'
                    ]);
                    return;
                }

                // Dapatkan nama wilayah dari database
                $deliveryArea = DeliveryArea::active()
                    ->where('provinsi_id', $this->selected_provinsi)
                    ->where('kabupaten_id', $this->selected_kabupaten)
                    ->first();

                if (!$deliveryArea) {
                    $this->dispatch('showAlert', [
                        'message' => 'Data wilayah tidak valid.',
                        'type' => 'error'
                    ]);
                    return;
                }

                // Dapatkan nama kecamatan dari API jika dipilih
                $kecamatanName = '';
                if ($this->selected_kecamatan && !empty($this->allKecamatans)) {
                    $kecamatanName = $this->allKecamatans[$this->selected_kecamatan] ?? '';
                }

                $customOptionsJson = $this->getAllCartCustomOptionsJson();

                $order = Order::create([
                    'user_id' => auth()->id(),
                    'order_number' => 'INV-' . strtoupper(uniqid()),
                    'subtotal' => $this->total,
                    'total_amount' => $this->total,
                    'status' => 'checking',
                    'payment_status' => 'unpaid',
                    'recipient_name' => $this->shippingData['recipient_name'],
                    'phone' => $this->shippingData['phone'],
                    'shipping_address' => $this->shippingData['shipping_address'],
                    'noted' => $this->shippingData['noted'],
                    'delivery_date' => $this->shippingData['delivery_date'],
                    'delivery_time' => $this->shippingData['delivery_time'],
                    'is_custom_catering' => $this->isCustomCatering,
                    // Simpan data wilayah
                    'provinsi_id' => $this->selected_provinsi,
                    'kabupaten_id' => $this->selected_kabupaten,
                    'kecamatan_id' => $this->selected_kecamatan,
                    'provinsi_name' => $deliveryArea->provinsi_name,
                    'kabupaten_name' => $deliveryArea->kabupaten_name,
                    'kecamatan_name' => $kecamatanName,
                    'custom_options_json' => $customOptionsJson,
                ]);

                // Simpan tiap item order
                foreach ($this->carts as $cart) {
                    $customOptions = json_decode($cart->custom_options_json, true);
                    $names = [];
                    if (is_array($customOptions) && !empty($customOptions)) {
                        $names = $this->mapCustomOptionsToNames($customOptions);
                    }

                    $order->items()->create([
                        'product_id' => $cart->product_id,
                        'product_name' => $cart->product->name,
                        'quantity' => $cart->quantity,
                        'price' => $cart->product->price,
                        'custom_options_json' => json_encode($names),
                    ]);
                }

                if ($this->isCustomCatering) {
                    $order->customCatering()->create([
                        'menu_description' => $this->customCatering['menu_description'],
                    ]);
                }

                Cart::where('user_id', auth()->id())->delete();

                try {
                    Notification::route('mail', $this->store->email_notification)
                        ->notify(new NewOrderNotification($order));
                } catch (\Exception $e) {
                    // Handle notification exception
                }

                return redirect()->route('order-detail', ['orderNumber' => $order->order_number]);

            } catch (\Exception $e) {
                $this->dispatch('showAlert', [
                    'message' => $e->getMessage(),
                    'type' => 'error'
                ]);
            }
        } else {
            $this->dispatch('showAlert', [
                'message' => 'Keranjang belanja kosong',
                'type' => 'error'
            ]);
        }
    }
}