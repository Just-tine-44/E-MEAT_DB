// Category Filter Logic
document.querySelectorAll('.category-item').forEach(item => {
  item.addEventListener('click', () => {
    const category = item.getAttribute('data-category');
    document.querySelectorAll('.product-item').forEach(product => {
      product.style.display = product.classList.contains(category) ? 'block' : 'none';
    });
  });
});

// Display all products initially
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.product-item').forEach(product => {
    product.style.display = 'block';
  });

  const searchInput = document.querySelector('.search-bar input');
  const productItems = document.querySelectorAll('.product-item');

  // Debounce function to limit search execution
  const debounce = (func, delay) => {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => func(...args), delay);
    };
  };

  // Filtering logic
  const filterProducts = (searchValue) => {
    productItems.forEach((item) => {
      const category = item.classList.contains('beef') ? 'beef' :
                       item.classList.contains('chicken') ? 'chicken' :
                       item.classList.contains('pork') ? 'pork' : '';
      const productName = item.querySelector('.product-info h3').textContent.toLowerCase();

      // Show/hide products based on search
      if (category.includes(searchValue) || productName.includes(searchValue)) {
        item.style.display = 'block';
      } else {
        item.style.display = 'none';
      }
    });
  };

  // Event listener for input with debounce
  searchInput.addEventListener('input', debounce((e) => {
    const searchValue = e.target.value.toLowerCase();
    filterProducts(searchValue);
  }, 300)); // Adjust debounce delay (300ms is a common choice)
});

// Toggle Active Weight Button
document.querySelectorAll(".weight-buttons button").forEach((btn) => {
  btn.addEventListener("click", (e) => {
    const buttons = e.target.closest(".weight-buttons").querySelectorAll("button");
    buttons.forEach((button) => button.classList.remove("active"));
    e.target.classList.add("active");
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const sections = document.querySelectorAll("section");

  const observerOptions = {
    root: null, // Use the viewport as the root
    rootMargin: "0px",
    threshold: 0.1, // Trigger when 10% of the section is visible
  };

  const sectionObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        // Add visible class when the section enters the viewport
        entry.target.classList.add("section-visible");
        entry.target.classList.remove("section-hidden");
      } else {
        // Add hidden class when the section leaves the viewport
        entry.target.classList.add("section-hidden");
        entry.target.classList.remove("section-visible");
      }
    });
  }, observerOptions);

  // Apply observer to each section
  sections.forEach((section) => {
    section.classList.add("section-hidden"); // Start with all sections hidden
    sectionObserver.observe(section);
  });
});

// Add to Cart Functionality
let eventListenersAttached = false;

document.addEventListener("DOMContentLoaded", () => {
  if (!eventListenersAttached) {
    const addToCartButtons = document.querySelectorAll(".add-to-cart");

    addToCartButtons.forEach(button => {
      button.addEventListener("click", function() {
        const productItem = this.closest(".product-item");
        const productName = productItem.querySelector(".product-info h3").textContent;
        const productPrice = parseFloat(
          productItem.querySelector(".product-info p").textContent.replace("₱", "").replace("/KG", "")
        );
        const qtyInput = productItem.querySelector(".quantity");
        const unitSelect = productItem.querySelector(".unit");

        const qty = qtyInput.value;
        const unit = unitSelect.value;

        console.log("Sending:", { meatPartId: this.getAttribute("data-id"), qty, unit });

        fetch("add_to_cart.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: `meat_part_id=${this.getAttribute("data-id")}&qty=${qty}&unit=${unit}`
        })
        .then(response => response.json())
        .then(data => {
          alert(data.message);
        })
        .catch(error => console.error("Error:", error));
      });
    });
    eventListenersAttached = true;
  }
});

let lastScrollY = window.scrollY;
const header = document.querySelector('header');

window.addEventListener('scroll', () => {
  const currentScrollY = window.scrollY;

  if (currentScrollY > lastScrollY && currentScrollY > 100) {
    // User is scrolling down
    header.classList.add('hidden');
  } else {
    // User is scrolling up
    header.classList.remove('hidden');
    if (currentScrollY > 50) {
      header.classList.add('sticky');
    } else {
      header.classList.remove('sticky');
    }
  }

  lastScrollY = currentScrollY;
});