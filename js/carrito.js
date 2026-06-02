document.addEventListener('DOMContentLoaded', () => {
    ActualizarContadorMenu();
});

function ActualizarContadorMenu() {
    const badge = document.getElementById('contador-carrito');
    if (!badge) return; 

    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    let totalProductos = carrito.reduce((total, item) => total + item.cantidad, 0);
    
    badge.textContent = totalProductos;
}

function AgregarAlCarritoGlobal(id, nombre, precio, imagen) {
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    
    const existe = carrito.find(p => p.id === id);
    if (existe) {
        existe.cantidad += 1;
    } else {
        carrito.push({ id, nombre, precio, imagen, cantidad: 1 });
    }
    
    localStorage.setItem('carrito', JSON.stringify(carrito));
    ActualizarContadorMenu();
    alert(`¡${nombre} se agregó al carrito con éxito!`);
}