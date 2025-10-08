// To open add product button when clicked
document.addEventListener("DOMContentLoaded", function() {
    document.querySelector('.add-product').onclick = function() {
        document.getElementById('addProductModal').classList.add('show');
    };

    // Image preview before upload
    document.getElementById('productImage').addEventListener('change', function() {
        const preview = document.getElementById('imagePreview');
        const file = this.files[0];
        if(file){
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" style="height:70px;"/>`;
            }
            reader.readAsDataURL(file);
        }
    });

    // Submit Add Product Form via AJAX
    document.getElementById('addProductForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('add_product.php', {
            method: 'POST',
            body: formData
        })
        .then(r=>r.json())
        .then(data=>{
            if(data.success){
                // Optionally, append row to table immediately
                addProductRowToTable(data.product);
                closeModal();
                this.reset();
                document.getElementById('imagePreview').innerHTML = "Drag image here or <span class='browse-link'>Browse image</span>";
            } else {
                alert('Add failed');
            }
        });
        return false;
    };
});

function closeModal() {
    document.getElementById('addProductModal').classList.remove('show');
}

// Helper to add a new row in table
function addProductRowToTable(product) {
    const table = document.getElementById('productTableBody');
    if (table) {
        let row = document.createElement('tr');
        row.innerHTML = `
            <td>${product.name}</td>
            <td>${product.id}</td>
            <td>${product.quantity}</td>
            <td>${product.price}</td>
            <td>${product.expiry}</td>
            <td><span class="category">${product.category}</span></td>
            <td>
                <button class="action-btn edit" onclick="editProduct('${product.id}')">Edit</button>
                <button class="action-btn delete" onclick="deleteProduct('${product.id}')">Delete</button>
            </td>
        `;
        table.prepend(row);
    }
}