<div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg pb-[140px]">
  <!-- Header -->
  <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white z-50">
    <div class="flex items-center h-16 px-4 border-b border-gray-100">
      <button onclick="history.back()" class="p-2 hover:bg-gray-50 rounded-full">
        <i class="bi bi-arrow-left text-xl"></i>
      </button>
      <h1 class="ml-2 text-lg font-medium">Checkout</h1>
    </div>
  </div>

  <!-- Main Content -->
  <div class="pt-20 pb-12 px-4 space-y-8">
    <!-- Section 1: Order Summary -->
    <div>
      <div class="flex items-center gap-2 mb-4">
        <i class="bi bi-cart-check text-lg text-primary"></i>
        <h2 class="text-lg font-medium">Ringkasan Pesanan</h2>
      </div>
      <div class="bg-white rounded-xl border border-gray-100 p-4">
        <div class="space-y-4">
          @foreach($carts as $cart)
            <div class="flex gap-3">
              <img src="{{$cart->product->first_image_url ?? asset('image/no-pictures.png')}}" 
                   alt="{{$cart->product->name}}" 
                   class="w-20 h-20 object-cover rounded-lg">
              <div class="flex-1">
                <h3 class="text-sm font-medium line-clamp-2">{{$cart->product->name}}</h3>
                <div class="text-sm text-gray-500 mt-1">{{$cart->quantity}} x Rp {{number_format($cart->product->price)}}</div>
                <div class="text-primary font-medium">Rp {{number_format($cart->product->price * $cart->quantity)}}</div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- Section 2: Recipient Information -->
    <div>
      <div class="flex items-center gap-2 mb-4">
        <i class="bi bi-person text-lg text-primary"></i>
        <h2 class="text-lg font-medium">Data Penerima</h2>
      </div>
      <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-4">
        <!-- Name -->
        <div>
          <label class="text-sm text-gray-600 mb-1.5 block">
            Nama Lengkap <span class="text-red-500">*</span>
          </label>
          <input type="text" 
                 wire:model.live="shippingData.recipient_name"
                 class="w-full px-4 py-2 rounded-lg border @error('shippingData.recipient_name') border-red-300 focus:ring-red-500 focus:border-red-500 @else focus:ring-2 focus:ring-primary focus:border-primary @enderror"
                 placeholder="Masukkan nama lengkap penerima"
                 required>
          @error('shippingData.recipient_name')
             <span class="text-red-500 text-xs mt-1">{{$message}}</span>

          
          @enderror
        </div>

        <!-- Phone -->
        <div>
          <label class="text-sm text-gray-600 mb-1.5 block">
            Nomor Telepon <span class="text-red-500">*</span>
          </label>
          <input wire:model.live="shippingData.phone"   
                 type="tel" 
                class="w-full px-4 py-2 rounded-lg @error('shippingData.phone') border-red-300 focus:ring-red-500 focus:border-red-500 @else focus:ring-2 focus:ring-primary focus:border-primary @enderror"
                 placeholder="Contoh: 08123456789"

                 required>
          @error('shippingData.phone')
              <span class="text-red-500 text-xs mt-1">{{$message}}</span>
          
          @enderror
        </div>
      </div>
    </div>

    <!-- Section 3: Location Selection -->
    <div>
      <div class="flex items-center gap-2 mb-4">
      <i class="bi bi-geo-alt text-lg text-primary"></i>
      <h2 class="text-lg font-medium">Pilih Lokasi</h2>
      </div>
      <div class="mb-2 px-2 py-2 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded text-sm">
      <i class="bi bi-info-circle mr-1"></i>
      Wilayah pemesanan hanya tersedia untuk Tegal, Brebes, Pemalang, dan Pekalongan.
      </div>

      @if(session('api_error'))
      <div class="mb-2 px-2 py-2 bg-red-50 border-l-4 border-red-400 text-red-800 rounded text-sm">
        <i class="bi bi-wifi-off mr-1"></i>
        {{ session('api_error') }}
      </div>
      @endif

      <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-4">
      <!-- Provinsi -->
      <div>
        <label for="provinsi" class="block text-sm text-gray-600 mb-1.5">
        Provinsi <span class="text-red-500">*</span>
        </label>
        <select id="provinsi" wire:model.live="selected_provinsi"
            class="w-full px-4 py-2 rounded-lg border @error('selected_provinsi') border-red-300 focus:ring-red-500 focus:border-red-500 @else focus:ring-2 focus:ring-primary focus:border-primary @enderror bg-white"
            required>
        <option value="">Pilih Provinsi</option>
        @foreach($availableProvinsis as $id => $name)
          <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
        </select>
        @error('selected_provinsi')
        <span class="text-red-500 text-sm mt-1 block">
          <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
        </span>
        @enderror
      </div>

      <!-- Kabupaten/Kota -->
      <div>
        <label for="kabupaten" class="block text-sm text-gray-600 mb-1.5">
        Kabupaten/Kota <span class="text-red-500">*</span>
        </label>
        <select id="kabupaten" wire:model.live="selected_kabupaten"
            class="w-full px-4 py-2 rounded-lg border @error('selected_kabupaten') border-red-300 focus:ring-red-500 focus:border-red-500 @else focus:ring-2 focus:ring-primary focus:border-primary @enderror bg-white"
            required>
        <option value="">Pilih Kabupaten/Kota</option>
        @foreach($availableKabupatens as $id => $name)
          <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
        </select>
        @error('selected_kabupaten')
        <span class="text-red-500 text-sm mt-1 block">
          <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
        </span>
        @enderror
      </div>

      <!-- Kecamatan -->
      @if(!empty($allKecamatans))
      <div>
        <label for="kecamatan" class="block text-sm text-gray-600 mb-1.5">
        Kecamatan <span class="text-red-500">*</span>
        </label>
        <select id="kecamatan" wire:model.live="selected_kecamatan"
            class="w-full px-4 py-2 rounded-lg border @error('selected_kecamatan') border-red-300 focus:ring-red-500 focus:border-red-500 @else focus:ring-2 focus:ring-primary focus:border-primary @enderror bg-white"
            required>
        <option value="">Pilih Kecamatan</option>
        @foreach($allKecamatans as $id => $name)
          <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
        </select>
        @error('selected_kecamatan')
        <span class="text-red-500 text-sm mt-1 block">
          <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
        </span>
        @enderror
        @if(session('api_error'))
        <span class="text-red-500 text-sm mt-1 block">
          <i class="bi bi-wifi-off mr-1"></i>{{ session('api_error') }}
        </span>
        @endif
      </div>
      @endif
      </div>
    </div>

    <!-- Section 4: Shipping Address -->
    <div>
      <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-4">
        <!-- Detailed Address -->
        <div>
          <label class="text-sm text-gray-600 mb-1.5 block">
            Detail Alamat <span class="text-red-500">*</span>
          </label>
          <textarea wire:model.live="shippingData.shipping_address"
                    class="w-full px-4 py-2 rounded-lg border @error('shippingData.shipping_address') border-red-300 focus:ring-red-500 focus:border-red-500 @else focus:ring-2 focus:ring-primary focus:border-primary @enderror"
                    rows="3"
                    placeholder="Nama jalan, nomor rumah (patokan), RT/RW, Desa/Kelurahan"
                    required></textarea>
          @error('shippingData.shipping_address')
          <span class="text-red-500 text-xs mt-1">{{$message}}</span>
          @enderror
        </div>
        
        <div>
          @if($shippingData['shipping_address'] && $shippingData['recipient_name'] && $shippingData['phone'])
            <div class="mt-3 p-3 bg-gray-50 rounded-lg text-sm">
              <div class="font-medium mb-2">
                <i class="bi bi-check-circle text-green-500 mr-1"></i>Detail Pengiriman:
              </div>
              <div class="text-gray-600">Nama : {{$shippingData['recipient_name']}}</div>
              <div class="text-gray-600">Nomor : {{$shippingData['phone']}}</div>
              <div class="text-gray-600">Alamat : {{$shippingData['shipping_address']}}</div>
              <div class="text-gray-600">Biaya Ongkir : Ongkir ditentukan setelah anda melakukan pemesanan</div>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Section 5: Additional Notes -->
    <div>
      <div class="flex items-center gap-2 mb-4">
        <i class="bi bi-pencil text-lg text-primary"></i>
        <h2 class="text-lg font-medium">Catatan Tambahan</h2>
      </div>
      <div class="bg-white rounded-xl border border-gray-100 p-4">
        <textarea wire:model.live="shippingData.noted"
                  class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary"
                  rows="2"
                  placeholder="Catatan untuk kurir (opsional)"></textarea>
        <div class="text-xs text-gray-500 mt-1">
          <i class="bi bi-info-circle mr-1"></i>Field ini bersifat opsional
        </div>
      </div>
    </div>

    <!-- Section 6: Jadwal Pemakaian -->
    <div>
      <div class="flex items-center gap-2 mb-4">
        <i class="bi bi-clock text-lg text-primary flex-shrink-0"></i>
        <h2 class="text-lg font-medium">Jadwal Pemakaian</h2>
      </div>
      <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Tanggal Pengiriman -->
          <div>
            <label class="text-sm text-gray-600 mb-1.5 block">
              Tanggal <span class="text-red-500">*</span>
            </label>
            <input type="date" wire:model.live="shippingData.delivery_date"
                   class="w-full border @error('shippingData.delivery_date') border-red-300 focus:ring-red-500 focus:border-red-500 @else focus:ring-2 focus:ring-primary focus:border-primary @enderror rounded-lg px-3 py-2 text-sm focus:outline-none"
                   min="{{ date('Y-m-d') }}"
                   required />
            @error('shippingData.delivery_date')
            <span class="text-red-500 text-xs mt-1">{{$message}}</span>
            @enderror
          </div>

          <!-- Waktu Pengiriman -->
          <div>
            <label class="text-sm text-gray-600 mb-1.5 block">
              Waktu <span class="text-red-500">*</span>
            </label>
            <input type="time" wire:model.live="shippingData.delivery_time"
                   class="w-full border @error('shippingData.delivery_time') border-red-300 focus:ring-red-500 focus:border-red-500 @else focus:ring-2 focus:ring-primary focus:border-primary @enderror rounded-lg px-3 py-2 text-sm focus:outline-none cursor-pointer"
                   required />
            @error('shippingData.delivery_time')
             <span class="text-red-500 text-xs mt-1">{{$message}}</span>
            @enderror
          </div>
        </div>

        <!-- Custom Catering Option -->
        <div class="mb-4">
          <div class="flex items-center">
            <input wire:model.live="isCustomCatering" type="checkbox" id="customCatering" 
                   class="mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded" />
            <label for="customCatering" class="font-medium">Custom Pesanan</label>
          </div>
          <div class="text-xs text-gray-500 mt-1 ml-6">
            <i class="bi bi-info-circle mr-1"></i>Centang jika ingin memesan menu/tambah khusus
          </div>
        </div>

        @if ($isCustomCatering)
        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
          <label for="menu_description" class="text-sm text-gray-600 mb-1.5 block">
            Deskripsikan menu yang anda inginkan <span class="text-red-500">*</span>
          </label>
          <textarea id="menu_description" wire:model.live="customCatering.menu_description"
                    class="w-full px-4 py-2 rounded-lg border @error('customCatering.menu_description') border-red-300 focus:ring-red-500 focus:border-red-500 @else focus:ring-2 focus:ring-primary focus:border-primary @enderror"
                    rows="3"
                    placeholder="Jelaskan menu custom yang Anda inginkan secara detail..."
                    required></textarea>
          @error('customCatering.menu_description')
          <span class="text-red-500 text-xs mt-1">{{$message}}</span>
          @enderror
        </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Fixed Bottom Section -->
  <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 p-4 z-50">
    <div class="flex justify-between items-start mb-4">
      <div>
        <p class="text-sm text-gray-600">Total Pembayaran:</p>
        <p class="text-lg font-semibold text-primary">Rp {{number_format($total + $shippingCost + $price_adjustment)}}</p>
      </div>
      <div class="text-right">
        <p class="text-xs text-gray-500">{{count($carts)}} Menu</p>
      </div>
    </div>

    <!-- Validation Summary -->
    @if ($errors->any())
    <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg">
      <div class="text-sm text-red-700">
        <i class="bi bi-exclamation-triangle mr-1"></i>
        Mohon lengkapi semua field yang wajib diisi (bertanda *)
      </div>
    </div>
    @endif

    <button wire:click="createOrder"
        class="w-full h-12 flex items-center justify-center gap-2 rounded-full bg-primary text-white font-medium hover:bg-primary/90 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed"
        wire:target="createOrder"
        wire:loading.attr="disabled">
      <span wire:loading.remove wire:target="createOrder">
      <i class="bi bi-bag-check"></i>
      Buat Pesanan
      </span>
      <span wire:loading wire:target="createOrder" style="display: none;">Memproses...</span>
      </span>
    </button>
  </div>
</div>
{{-- 
@push('scripts')
    <script type="text/javascript"
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}">
    </script>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('payment-success', (data) => {
                const snapToken = data[0].payment_gateway_transaction_id;
                const orderId = data[0].order_id;

                if (snapToken) {
                    try {
                        window.snap.pay(snapToken, {
                            onSuccess: function(result) {
                                window.location.href = `/order-detail/${orderId}`;
                            },
                            onPending: function(result) {
                                window.location.href = `/order-detail/${orderId}`;
                            },
                            onError: function(result) {
                                alert('Pembayaran gagal! Silakan coba lagi.');
                            },
                            onClose: function() {
                                alert('Anda menutup halaman pembayaran sebelum menyelesaikan transaksi');
                                window.location.href = `/`;
                            }
                        });
                    } catch (error) {
                        alert('Terjadi kesalahan saat membuka popup pembayaran');
                    }
                }
            });
        });
    </script>
@endpush --}}