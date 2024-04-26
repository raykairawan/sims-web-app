<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>
<body>
    <h1>Welcome to Dashboard</h1>
    <p>You are logged in, {{ Auth::user()->name }}!</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>

    <ul>
        <li><a href="{{ route('categories.index') }}">Categories</a></li>
    </ul>

    <input type="text" id="searchInput" placeholder="Search by Name" onkeyup="searchProducts()">
    <select id="categoryFilter" onchange="filterByCategory()">
        <option value="">All Categories</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>

    <!-- Display Products Table -->
    <h2>Products</h2>
    <a href="{{ route('tambah_produk') }}" class="btn btn-primary">Tambah Produk</a>
    <a href="{{ route('export_products') }}" class="btn btn-success">Export to Excel</a>
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Kategori Produk</th>
                <th>Harga Beli</th>
                <th>Harga Jual</th>
                <th>Stok Produk</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="productsTableBody">
            @foreach($products as $key => $product)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $product->nama_produk }}</td>
                <td>{{ $product->category->name ?? '' }}</td>
                <td class="harga-beli">{{ $product->harga_beli }}</td>
                <td class="harga-jual">{{ $product->harga_jual }}</td>
                <td>{{ $product->stok_produk }}</td>
                <td>
                    <img src="{{ $product->image }}" alt="Product Image" style="max-width: 100px;">
                </td>

                <td>
                    <!-- Update Action -->
                    <a href="{{ route('edit_produk', ['id' => $product->id]) }}">Edit</a>
                    
                    <!-- Delete Action -->
                    <form id="deleteForm{{ $product->id }}" 
                            method="POST" 
                            action="{{ route('delete_produk', ['id' => $product->id]) }}" 
                            style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button onclick="confirmDelete({{ $product->id }})">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination Links -->
    {{ $products->links() }}

    <script>
        function confirmDelete(productId) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm' + productId).submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var hargaBeliElements = document.querySelectorAll('.harga-beli');
            var hargaJualElements = document.querySelectorAll('.harga-jual');

            hargaBeliElements.forEach(function(element) {
                element.textContent = formatRupiah(element.textContent);
            });

            hargaJualElements.forEach(function(element) {
                element.textContent = formatRupiah(element.textContent);
            });
        });

        function formatRupiah(angka) {
            var reverse = angka.toString().split('').reverse().join('');
            var ribuan = reverse.match(/\d{1,3}/g);
            ribuan = ribuan.join('.').split('').reverse().join('');
            return 'Rp. ' + ribuan;
        }

        function initializeEventListeners() {
            document.getElementById('productsTableBody').addEventListener('click', function(event) {
                if (event.target.matches('.edit-product')) {
                    var productId = event.target.dataset.productId;
                    window.location.href = '/edit_produk/' + productId;
                } else if (event.target.matches('.delete-product')) {
                    var productId = event.target.dataset.productId;
                    confirmDelete(productId);
                }
            });
        }

        initializeEventListeners();

        function filterByCategory() {
            var categoryId = document.getElementById('categoryFilter').value;
            if (categoryId === "") {
                fetch('/search')
                    .then(response => response.json())
                    .then(data => {
                        renderProducts(data);
                        initializeEventListeners();
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                fetch('/search?category_id=' + categoryId)
                    .then(response => response.json())
                    .then(data => {
                        renderProducts(data);
                        initializeEventListeners();
                    })
                    .catch(error => console.error('Error:', error));
            }
        }


        function searchProducts() {
            var searchValue = document.getElementById('searchInput').value.trim();
            if (searchValue !== '') {
                fetch('/search?nama_produk=' + searchValue)
                    .then(response => response.json())
                    .then(data => {
                        renderProducts(data);
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                fetch('/search')
                    .then(response => response.json())
                    .then(data => {
                        renderProducts(data);
                    })
                    .catch(error => console.error('Error:', error));
            }
        }

        function renderProducts(products) {
            var tableBody = document.getElementById('productsTableBody');
            tableBody.innerHTML = '';

            products.forEach(function(product, index) {
                var row = `<tr>
                    <td>${index + 1}</td>
                    <td>${product.nama_produk}</td>
                    <td>${product.kategori_produk}</td>
                    <td class="harga-beli">${product.harga_beli}</td>
                    <td class="harga-jual">${product.harga_jual}</td>
                    <td>${product.stok_produk}</td>
                    <td><img src="${product.image}" alt="Product Image" style="max-width: 100px;"></td>
                    <td>
                        <a href="{{ route('edit_produk', ['id' => $product->id]) }}">Edit</a>
                        
                        <!-- Delete Action -->
                        <form id="deleteForm{{ $product->id }}" 
                                method="POST" 
                                action="{{ route('delete_produk', ['id' => $product->id]) }}" 
                                style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button onclick="confirmDelete({{ $product->id }})">Delete</button>
                        </form>
                    </td>
                </tr>`;
                tableBody.innerHTML += row;
            });
        }

    </script>

    <script>
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
