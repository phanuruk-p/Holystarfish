const toast = document.querySelector(".cart-toast");
const modal = document.querySelector(".product-modal");
const modalImage = modal?.querySelector(".modal-image");
const modalBadge = modal?.querySelector(".modal-badge");
const modalType = modal?.querySelector(".modal-type");
const modalName = modal?.querySelector("#modal-product-name");
const modalDetail = modal?.querySelector(".modal-detail");
const modalPrice = modal?.querySelector(".modal-price");
const modalInterest = modal?.querySelector(".modal-interest");
const cartModal = document.querySelector(".cart-modal");
const cartOpenButton = document.querySelector(".cart-open-button");
const cartCloseButton = document.querySelector(".cart-close");
const cartItems = document.querySelector(".cart-items");
const cartEmpty = document.querySelector(".cart-empty");
const cartCount = document.querySelector(".cart-count");
const cartSubtotal = document.querySelector(".cart-subtotal");
const cartVat = document.querySelector(".cart-vat");
const cartTotal = document.querySelector(".cart-total");
const cartCheckout = document.querySelector(".cart-checkout");
const customerLoggedIn = document.body.dataset.customerLoggedIn === "1";
let toastTimer;
let cart = JSON.parse(localStorage.getItem("holystarfishCart") || "[]");
const currentCatalog = JSON.parse(document.querySelector('#current-catalog')?.textContent || '[]');
cart = cart.map(item => {
    const current = currentCatalog.find(product => product.name === item.name || product.legacy_name === item.name);
    return current ? { ...item, name: current.name, type: current.type, price: current.price, priceValue: current.price_value, image: current.image } : item;
});
if (currentCatalog.length && cart.length) saveCart();

function showToast(message) {
    if (!toast) return;

    toast.textContent = message;
    toast.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove("show"), 2600);
}

function formatBaht(value) {
    return new Intl.NumberFormat("th-TH", {
        style: "currency",
        currency: "THB",
        maximumFractionDigits: 0,
    }).format(value);
}

function saveCart() {
    localStorage.setItem("holystarfishCart", JSON.stringify(cart));
}

function requireCustomerForCart() {
    if (customerLoggedIn) {
        return true;
    }

    window.location.href = "register.php";
    return false;
}

function productFromCard(card) {
    return {
        name: card.dataset.name,
        type: card.dataset.type,
        price: card.dataset.price,
        priceValue: Number(card.dataset.priceValue || 0),
        image: card.dataset.image,
    };
}

function addProductToCart(product) {
    if (!requireCustomerForCart()) {
        return;
    }

    const existing = cart.find((item) => item.name === product.name);

    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({ ...product, quantity: 1 });
    }

    saveCart();
    renderCart();
    showToast(`เพิ่ม ${product.name} ลงตะกร้าแล้ว`);
}

function updateCartQuantity(name, change) {
    const item = cart.find((cartItem) => cartItem.name === name);
    if (!item) return;

    item.quantity += change;
    if (item.quantity <= 0) {
        cart = cart.filter((cartItem) => cartItem.name !== name);
    }

    saveCart();
    renderCart();
}

function removeCartItem(name) {
    cart = cart.filter((item) => item.name !== name);
    saveCart();
    renderCart();
}

function renderCart() {
    if (!cartItems || !cartEmpty || !cartCount || !cartSubtotal || !cartVat || !cartTotal) {
        return;
    }

    const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    const subtotal = cart.reduce((sum, item) => sum + item.priceValue * item.quantity, 0);
    const vat = Math.round(subtotal * 0.07);
    const total = subtotal + vat;

    cartCount.textContent = String(itemCount);
    cartEmpty.hidden = cart.length > 0;
    cartItems.innerHTML = cart.map((item) => `
        <article class="cart-item">
            <img src="${item.image}" alt="${item.name}">
            <div>
                <h3>${item.name}</h3>
                <p>${item.type}</p>
                <span class="cart-item-price">${formatBaht(item.priceValue)} x ${item.quantity}</span>
                <button class="cart-remove" type="button" data-cart-remove="${item.name}">ลบสินค้า</button>
            </div>
            <div class="cart-quantity" aria-label="จำนวน ${item.name}">
                <button type="button" data-cart-minus="${item.name}" aria-label="ลดจำนวน ${item.name}">-</button>
                <span>${item.quantity}</span>
                <button type="button" data-cart-plus="${item.name}" aria-label="เพิ่มจำนวน ${item.name}">+</button>
            </div>
        </article>
    `).join("");

    cartSubtotal.textContent = formatBaht(subtotal);
    cartVat.textContent = formatBaht(vat);
    cartTotal.textContent = formatBaht(total);
}

document.querySelectorAll(".product-open").forEach((button) => {
    button.addEventListener("click", () => {
        const product = button.closest(".product-card");
        modalImage.src = product.dataset.image;
        modalImage.alt = product.dataset.name;
        modalBadge.textContent = product.dataset.badge;
        modalType.textContent = product.dataset.type;
        modalName.textContent = product.dataset.name;
        modalDetail.textContent = product.dataset.detail;
        modalPrice.textContent = product.dataset.price;
        modalInterest.dataset.product = product.dataset.name;
        modal.showModal();
    });
});

modal?.querySelector(".modal-close").addEventListener("click", () => modal.close());

modal?.addEventListener("click", (event) => {
    if (event.target === modal) {
        modal.close();
    }
});

modalInterest?.addEventListener("click", () => {
    const product = modalInterest.dataset.product;
    modal.close();
    const productCard = Array.from(document.querySelectorAll(".product-card")).find((card) => card.dataset.name === product);
    if (productCard) {
        addProductToCart(productFromCard(productCard));
    }
});

document.querySelectorAll(".add-to-cart").forEach((button) => {
    button.addEventListener("click", () => {
        const productCard = button.closest(".product-card");
        addProductToCart(productFromCard(productCard));
    });
});

cartOpenButton?.addEventListener("click", () => {
    if (!requireCustomerForCart()) {
        return;
    }

    renderCart();
    cartModal?.showModal();
});

cartCloseButton?.addEventListener("click", () => cartModal?.close());

cartModal?.addEventListener("click", (event) => {
    if (event.target === cartModal) {
        cartModal.close();
    }
});

cartItems?.addEventListener("click", (event) => {
    const plus = event.target.closest("[data-cart-plus]");
    const minus = event.target.closest("[data-cart-minus]");
    const remove = event.target.closest("[data-cart-remove]");

    if (plus) updateCartQuantity(plus.dataset.cartPlus, 1);
    if (minus) updateCartQuantity(minus.dataset.cartMinus, -1);
    if (remove) removeCartItem(remove.dataset.cartRemove);
});

cartCheckout?.addEventListener("click", () => {
    if (cart.length === 0) {
        showToast("กรุณาเพิ่มสินค้าลงตะกร้าก่อน");
        return;
    }

    if (!requireCustomerForCart()) return;
    const form = document.createElement('form');
    form.method = 'post';
    form.action = 'checkout.php';
    const fields = {action: 'prepare', csrf: document.querySelector('meta[name="csrf-token"]')?.content || '', cart: JSON.stringify(cart.map(item => ({name: item.name, quantity: item.quantity})))};
    Object.entries(fields).forEach(([name, value]) => { const input = document.createElement('input'); input.type = 'hidden'; input.name = name; input.value = value; form.append(input); });
    document.body.append(form);
    form.submit();
});

document.querySelector('.cart-reset')?.addEventListener('click', () => {
    cart = [];
    saveCart();
    renderCart();
    showToast('รีเซ็ตตะกร้าแล้ว ประวัติคำสั่งซื้อเดิมยังอยู่');
});

document.querySelector(".contact-form")?.addEventListener("submit", (event) => {
    event.preventDefault();
    showToast("รับข้อความแล้ว สามารถเชื่อมต่อระบบส่งจริงเพิ่มเติมได้");
});

const filterButtons = document.querySelectorAll(".filter-button");
const catalogProducts = document.querySelectorAll(".catalog-grid .product-card");
const catalogCount = document.querySelector(".catalog-count");
const profileMenu = document.querySelector(".profile-menu");
const profileTrigger = document.querySelector(".profile-trigger");

profileTrigger?.addEventListener("click", () => {
    const isOpen = profileMenu.classList.toggle("open");
    profileTrigger.setAttribute("aria-expanded", String(isOpen));
});

document.addEventListener("click", (event) => {
    if (!profileMenu || !profileTrigger || profileMenu.contains(event.target)) {
        return;
    }

    profileMenu.classList.remove("open");
    profileTrigger.setAttribute("aria-expanded", "false");
});

document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape" || !profileMenu || !profileTrigger) {
        return;
    }

    profileMenu.classList.remove("open");
    profileTrigger.setAttribute("aria-expanded", "false");
});

filterButtons.forEach((button) => {
    if (document.querySelector('#catalog-search')) return;
    button.addEventListener("click", () => {
        const filter = button.dataset.filter;
        let visibleCount = 0;

        filterButtons.forEach((item) => item.classList.toggle("active", item === button));
        catalogProducts.forEach((product) => {
            const visible = filter === "all" || product.dataset.category === filter;
            product.hidden = !visible;
            if (visible) visibleCount += 1;
        });

        catalogCount.textContent = `แสดงสินค้า ${visibleCount} รายการ`;
    });
});

renderCart();
