<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>
<body>
    <h1>Tambah Produk</h1>
    <form action="{{ route('store_produk') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="nama_produk">Nama Produk:</label><br>
        <input type="text" id="nama_produk" name="nama_produk"><br>
        
        <label for="kategori_produk">Kategori Produk:</label><br>
        <select id="kategori_produk" name="kategori_produk" required>
            @foreach($categoryNames as $categoryName)
            <option value="{{ $categoryName }}">{{ $categoryName }}</option>
            @endforeach
        </select><br>
        
        <label for="harga_beli">Harga Beli:</label><br>
        <input type="number" id="harga_beli" name="harga_beli" value="{{ isset($product) ? $product->harga_beli : '' }}" oninput="calculatePrice()" placeholder="Rp."><br>

        <label for="harga_jual">Harga Jual:</label><br>
        <input type="text" id="harga_jual" name="harga_jual" value="Rp. 0" readonly><br>
        
        <label for="stok_produk">Stok Produk:</label><br>
        <input type="number" id="stok_produk" name="stok_produk"><br>
        
        <label for="image">Image:</label><br>
        <input type="file" id="image" name="image"><br>
        
        <button type="submit">Tambah</button>
    </form>

    <script>
        function formatRupiah(angka) {
            var hargaBeli = angka.value.replace(/[^0-9]/g, '');
            angka.value = formatRupiahFunction(hargaBeli);
            calculatePrice();
        }

        function formatRupiahFunction(angka) {
            var reverse = angka.toString().split('').reverse().join(''),
                ribuan = reverse.match(/\d{1,3}/g);
            ribuan = ribuan.join('.').split('').reverse().join('');
            return 'Rp. ' + ribuan;
        }

        function calculatePrice() {
            var hargaBeli = parseFloat(document.getElementById('harga_beli').value);
            if (!isNaN(hargaBeli)) {
                var hargaJual = hargaBeli * 1.3;
                document.getElementById('harga_jual').value = formatRupiahFunction(hargaJual.toFixed(2));
            } else {
                document.getElementById('harga_jual').value = "Rp. 0";
            }
        }


        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 1500
            });
        @elseif(session('error'))
            Swal.fire({
                icon: 'error',
                title: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 1500
            });
        @endif
    </script>
</body>
</html>
