document.addEventListener("DOMContentLoaded", async () => {
    const container = document.querySelector("#contenedor-productos");

    try {
        const response = await fetch("./controllers/productos.php");
        const productos = await response.json();

        productos.forEach((product, index) => {
            const card = document.createElement("div");
            card.className = "product-card bg-white";
            card.setAttribute("data-aos", "fade-up");
            card.setAttribute("data-aos-delay", index * 100);

            card.innerHTML = `
                <img src="${product.image}" alt="${product.name}" class="product-img w-full h-64 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2 accent-color">${product.name}</h3>
                    <p class="text-gray-500 mb-2 text-sm">Categoría: <span class="font-medium">${product.category}</span></p>
                    <p class="text-gray-600 mb-4">${product.short_description}</p>
                    <p class="text-lg font-bold text-green-600">$${parseFloat(product.price).toFixed(2)}</p>
                    <p class="text-sm text-gray-500">Stock: ${product.stock}</p>
                </div>
            `;

            container.appendChild(card);
        });
    } catch (error) {
        console.error("Error al cargar los productos:", error);
        container.innerHTML = "<p class='text-red-500'>Error al cargar los productos.</p>";
    }
});