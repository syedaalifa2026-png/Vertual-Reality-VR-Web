// js/products.js - Complete products data (all 19 products)

const products = [
    { 
        id: 1, 
        name: "Meta Quest 3 (128GB)", 
        category: "headset", 
        subcategory: "standalone", 
        price: 62500, 
        oldPrice: null, 
        rating: 4.8, 
        image: "images/Meta Quest 3 (128GB).png", 
        badge: null, 
        badgeText: "", 
        isNew: false,
        description: "Next-gen standalone VR with mixed reality, 128GB storage, and improved performance. Experience immersive gaming and productivity like never before.", 
        officialUrl: "https://www.meta.com/quest/quest-3/", 
        specs: ["Display: LCD 2064x2208 per eye", "Refresh Rate: 90Hz/120Hz", "Processor: Snapdragon XR2 Gen 2", "Storage: 128GB", "RAM: 8GB", "Battery: ~2.2 hours", "Weight: 515g"],
        icon: "fa-vr-cardboard",
        storage: "128GB"
    },
    { 
        id: 2, 
        name: "Meta Quest 3S (128GB)", 
        category: "headset", 
        subcategory: "standalone", 
        price: 54900, 
        oldPrice: null, 
        rating: 4.7, 
        image: "images/Meta Quest 3S (128GB).png", 
        badge: "new", 
        badgeText: "NEW", 
        isNew: true,
        description: "More affordable version of Quest 3 with slightly reduced specs but same great experience.", 
        officialUrl: "https://www.meta.com/quest/quest-3s/", 
        specs: ["Display: LCD 1832x1920 per eye", "Refresh Rate: 90Hz", "Processor: Snapdragon XR2 Gen 1", "Storage: 128GB", "RAM: 6GB", "Battery: ~2.5 hours", "Weight: 503g"],
        icon: "fa-glasses",
        storage: "128GB"
    },
    { 
        id: 3, 
        name: "PICO 4 Ultra (256GB)", 
        category: "headset", 
        subcategory: "standalone", 
        price: 68900, 
        oldPrice: null, 
        rating: 4.6, 
        image: "images/PICO 4 Ultra (256GB).png", 
        badge: null, 
        badgeText: "", 
        isNew: true,
        description: "High-end standalone VR headset with eye tracking and enterprise features.", 
        officialUrl: "https://www.pico-interactive.com/", 
        specs: ["Display: 4K+ resolution", "Refresh Rate: 90Hz", "Processor: Snapdragon XR2", "Storage: 256GB", "Eye Tracking: Yes", "Weight: 586g"],
        icon: "fa-microchip",
        storage: "256GB"
    },
    { 
        id: 4, 
        name: "PICO Neo 3 (128GB)", 
        category: "headset", 
        subcategory: "standalone", 
        price: 42900, 
        oldPrice: null, 
        rating: 4.5, 
        image: "images/PICO Neo 3 (128GB).png", 
        badge: null, 
        badgeText: "", 
        isNew: false,
        description: "Standalone VR headset with good performance and comfortable design.", 
        officialUrl: "https://www.pico-interactive.com/", 
        specs: ["Display: 4K resolution", "Refresh Rate: 90Hz", "Processor: Snapdragon XR2", "Storage: 128GB", "Battery: ~3 hours", "Weight: 595g"],
        icon: "fa-eye",
        storage: "128GB"
    },
    { 
        id: 5, 
        name: "Valve Index", 
        category: "headset", 
        subcategory: "pcvr", 
        price: 125000, 
        oldPrice: null, 
        rating: 4.9, 
        image: "images/Valve Index.png", 
        badge: "sale", 
        badgeText: "PREMIUM", 
        isNew: false,
        description: "High-fidelity PC VR system with finger tracking controllers and wide FOV.", 
        officialUrl: "https://www.valvesoftware.com/en/index", 
        specs: ["Display: 1440x1600 per eye", "Refresh Rate: 144Hz", "FOV: 130°", "Tracking: SteamVR Base Station 2.0", "Controllers: Knuckles", "Weight: 809g"],
        icon: "fa-gamepad",
        storage: "Full Kit"
    },
    { 
        id: 6, 
        name: "HTC Vive Pro 2", 
        category: "headset", 
        subcategory: "pcvr", 
        price: 135000, 
        oldPrice: null, 
        rating: 4.9, 
        image: "images/HTC Vive Pro 2.png", 
        badge: null, 
        badgeText: "", 
        isNew: false,
        description: "Premium PC VR headset with 5K resolution and 120Hz refresh rate.", 
        officialUrl: "https://www.vive.com/us/product/vive-pro-2/", 
        specs: ["Display: 5K (2448x2448 per eye)", "Refresh Rate: 120Hz", "FOV: 120°", "Tracking: SteamVR Base Station", "Weight: 785g"],
        icon: "fa-crown",
        storage: "Full Kit"
    },
    { 
        id: 7, 
        name: "HP Reverb G2", 
        category: "headset", 
        subcategory: "pcvr", 
        price: 78900, 
        oldPrice: null, 
        rating: 4.5, 
        image: "images/HP Reverb G2.png", 
        badge: null, 
        badgeText: "", 
        isNew: false,
        description: "High-resolution PC VR headset developed with Valve and Microsoft.", 
        officialUrl: "https://www.hp.com/us-en/vr/reverb-g2-vr-headset.html", 
        specs: ["Display: 2160x2160 per eye", "Refresh Rate: 90Hz", "Audio: Spatial 3D audio", "Tracking: Inside-out 4 cameras", "Weight: 550g"],
        icon: "fa-laptop-code",
        storage: "Full Kit"
    },
    { 
        id: 8, 
        name: "PlayStation VR2", 
        category: "headset", 
        subcategory: "console", 
        price: 72800, 
        oldPrice: null, 
        rating: 4.7, 
        image: "images/PlayStation VR2.png", 
        badge: "new", 
        badgeText: "HAPTIC", 
        isNew: true,
        description: "Next-gen VR for PS5 with eye tracking, haptic feedback, and 4K HDR.", 
        officialUrl: "https://www.playstation.com/en-us/ps-vr2/", 
        specs: ["Display: OLED 2000x2040 per eye", "Refresh Rate: 90/120Hz", "FOV: 110°", "Haptics: Headset vibration", "Eye Tracking: Yes", "Weight: 560g"],
        icon: "fa-playstation",
        storage: "Standard"
    },
    { 
        id: 9, 
        name: "Samsung Gear VR", 
        category: "accessory", 
        subcategory: "mobile", 
        price: 12900, 
        oldPrice: 18900, 
        rating: 4.0, 
        image: "images/Samsung Gear VR.png", 
        badge: "sale", 
        badgeText: "SALE", 
        isNew: false,
        description: "Mobile VR headset for Samsung smartphones with controller.", 
        officialUrl: "https://www.samsung.com/uk/mobile-accessories/gear-vr/", 
        specs: ["Compatibility: Select Samsung Galaxy phones", "FOV: 101°", "Controller: Included", "Weight: 345g"],
        icon: "fa-mobile-alt",
        storage: "Mobile"
    },
    { 
        id: 10, 
        name: "Apple Vision Pro (256GB)", 
        category: "headset", 
        subcategory: "arglasses", 
        price: 385000, 
        oldPrice: 429000, 
        rating: 5.0, 
        image: "images/Apple Vision Pro (256GB).png", 
        badge: "new", 
        badgeText: "REVOLUTION", 
        isNew: true,
        description: "Revolutionary spatial computer with ultra-high-resolution displays.", 
        officialUrl: "https://www.apple.com/apple-vision-pro/", 
        specs: ["Display: Micro OLED 23M pixels", "Processor: M2 + R1 chips", "Storage: 256GB", "OS: visionOS", "Tracking: Eye, hand, voice", "Weight: 600g"],
        icon: "fa-apple",
        storage: "256GB"
    },
    { 
        id: 11, 
        name: "Meta Quest Pro", 
        category: "headset", 
        subcategory: "enterprise", 
        price: 149900, 
        oldPrice: null, 
        rating: 4.8, 
        image: "images/Meta Quest Pro.png", 
        badge: null, 
        badgeText: "", 
        isNew: false,
        description: "Premium mixed reality headset with advanced passthrough and productivity.", 
        officialUrl: "https://www.meta.com/quest/quest-pro/", 
        specs: ["Display: Quantum Dot LCD", "Storage: 256GB", "RAM: 12GB", "Tracking: Inside-out + eye/face tracking", "Mixed Reality: Full color passthrough", "Weight: 722g"],
        icon: "fa-cube",
        storage: "256GB"
    },
    { 
        id: 12, 
        name: "HTC Vive Focus Vision", 
        category: "headset", 
        subcategory: "enterprise", 
        price: 89900, 
        oldPrice: null, 
        rating: 4.6, 
        image: "images/HTC Vive Focus Vision.png", 
        badge: null, 
        badgeText: "", 
        isNew: false,
        description: "Standalone AR/VR headset with enterprise-grade features.", 
        officialUrl: "https://www.vive.com/us/product/vive-focus-vision/", 
        specs: ["Display: 5K resolution", "Battery: Hot-swappable", "Tracking: Inside-out 6DOF", "Sensors: Eye and lip tracking", "Weight: 785g"],
        icon: "fa-vr-cardboard",
        storage: "Enterprise"
    },
    { 
        id: 13, 
        name: "HTC Vive XR Elite", 
        category: "headset", 
        subcategory: "arglasses", 
        price: 135000, 
        oldPrice: 159000, 
        rating: 4.7, 
        image: "images/HTC Vive XR Elite.png", 
        badge: "sale", 
        badgeText: "-15%", 
        isNew: false,
        description: "Premium standalone headset with modular design.", 
        officialUrl: "https://www.vive.com/us/product/vive-xr-elite/", 
        specs: ["Display: 3840x1920 combined", "FOV: 110°", "Weight: 625g", "Battery: Removable", "Mixed Reality: Color passthrough"],
        icon: "fa-star",
        storage: "Mixed Reality"
    },
    { 
        id: 14, 
        name: "Varjo XR-4", 
        category: "headset", 
        subcategory: "enterprise", 
        price: 495000, 
        oldPrice: 550000, 
        rating: 5.0, 
        image: "images/Varjo XR-4.png", 
        badge: "new", 
        badgeText: "ENTERPRISE", 
        isNew: true,
        description: "Professional-grade VR/MR headset with human-eye resolution.", 
        officialUrl: "https://varjo.com/products/xr-4/", 
        specs: ["Display: 3840x3744 per eye", "FOV: 120° x 105°", "Cameras: LiDAR + RGB", "Tracking: Inside-out", "Weight: 650g"],
        icon: "fa-rocket",
        storage: "Enterprise"
    },
    { 
        id: 15, 
        name: "Meta Quest 2 (128GB)", 
        category: "headset", 
        subcategory: "standalone", 
        price: 45900, 
        oldPrice: 54900, 
        rating: 4.5, 
        image: "images/Meta Quest 2 (128GB).png", 
        badge: "sale", 
        badgeText: "BESTSELLER", 
        isNew: false,
        description: "Best-selling standalone VR headset with wireless freedom.", 
        officialUrl: "https://www.meta.com/quest/products/quest-2/", 
        specs: ["Display: 1832x1920 per eye", "Refresh Rate: 90/120Hz", "Processor: Snapdragon XR2", "Storage: 128GB", "Battery: ~2-3 hours", "Weight: 503g"],
        icon: "fa-fire",
        storage: "128GB"
    },
    { 
        id: 16, 
        name: "ThinkReality VRX", 
        category: "headset", 
        subcategory: "enterprise", 
        price: 199900, 
        oldPrice: null, 
        rating: 4.4, 
        image: "images/ThinkReality VRX.png", 
        badge: null, 
        badgeText: "", 
        isNew: false,
        description: "Enterprise-grade VR headset for training and collaboration.", 
        officialUrl: "https://www.lenovo.com/us/en/p/thinkreality/thinkreality-vrx/", 
        specs: ["Display: 4K+ resolution", "Processor: Snapdragon XR2", "Tracking: 6DOF inside-out", "Design: Lightweight ergonomic", "Weight: 550g"],
        icon: "fa-brain",
        storage: "Enterprise"
    },
    { 
        id: 17, 
        name: "Lenovo Legion Glasses", 
        category: "accessory", 
        subcategory: "arglasses", 
        price: 45900, 
        oldPrice: null, 
        rating: 4.3, 
        image: "images/Lenovo Legion Glasses.png", 
        badge: null, 
        badgeText: "", 
        isNew: false,
        description: "AR glasses designed for gaming with micro-OLED displays.", 
        officialUrl: "https://www.lenovo.com/us/en/p/accessories-and-software/legion-glasses/", 
        specs: ["Display: Micro-OLED per eye", "Resolution: 1920x1080", "Weight: 100g", "Audio: Built-in speakers", "Compatibility: USB-C devices"],
        icon: "fa-glasses",
        storage: "AR Glasses"
    },
    { 
        id: 18, 
        name: "VR Charging Dock Station", 
        category: "accessory", 
        subcategory: "accessory", 
        price: 5990, 
        oldPrice: 8990, 
        rating: 4.6, 
        image: "images/VR Charging Dock Station.png", 
        badge: "sale", 
        badgeText: "-33%", 
        isNew: false,
        description: "Convenient charging station for VR controllers and headsets.", 
        officialUrl: "https://www.amazon.com/vr-charging-dock/", 
        specs: ["Input: USB-C", "Output: Dual controller charging", "LED Indicators: Yes", "Safety: Overcharge protection"],
        icon: "fa-battery-full",
        storage: "Accessory"
    },
    { 
        id: 19, 
        name: "Premium VR Carrying Case", 
        category: "accessory", 
        subcategory: "accessory", 
        price: 3990, 
        oldPrice: 6990, 
        rating: 4.7, 
        image: "images/Premium VR Carrying Case.png", 
        badge: "new", 
        badgeText: "ESSENTIAL", 
        isNew: true,
        description: "Protective hard case for VR headset and accessories.", 
        officialUrl: "https://www.amazon.com/vr-carrying-case/", 
        specs: ["Material: EVA hard shell", "Interior: Soft foam padding", "Capacity: Headset + controllers", "Weight: 500g"],
        icon: "fa-suitcase",
        storage: "Accessory"
    }
];

// Helper functions
function getProductById(id) {
    return products.find(p => p.id === parseInt(id));
}

function getProductsByCategory(category) {
    if (category === 'all') return products;
    return products.filter(p => p.subcategory === category);
}

function getProductsBySubcategory(subcategory) {
    if (subcategory === 'all') return products;
    return products.filter(p => p.subcategory === subcategory);
}

function searchProducts(searchTerm) {
    if (!searchTerm || searchTerm.trim() === '') return products;
    const term = searchTerm.toLowerCase();
    return products.filter(p => p.name.toLowerCase().includes(term) || 
                                p.subcategory.toLowerCase().includes(term));
}

function sortProducts(products, sortType) {
    const sorted = [...products];
    switch(sortType) {
        case 'price_low':
            return sorted.sort((a, b) => a.price - b.price);
        case 'price_high':
            return sorted.sort((a, b) => b.price - a.price);
        case 'rating':
            return sorted.sort((a, b) => b.rating - a.rating);
        case 'newest':
            return sorted.sort((a, b) => (b.isNew ? 1 : 0) - (a.isNew ? 1 : 0));
        default:
            return sorted;
    }
}