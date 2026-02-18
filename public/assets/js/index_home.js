// Бургер-меню
const burger = document.getElementById('burgerMenu');
const mobileMenu = document.getElementById('mobileMenu');
const mobileOverlay = document.getElementById('mobileOverlay');
const closeMobileMenu = document.getElementById('closeMobileMenu');
const mobileServicesToggle = document.getElementById('mobileServicesToggle');
const mobileServicesMenu = document.getElementById('mobileServicesMenu');

function openMobileMenu() {
    mobileMenu.classList.remove('translate-x-full');
    mobileOverlay.classList.remove('opacity-0', 'invisible');
    document.body.classList.add('overflow-hidden');

    const spans = burger.querySelectorAll('span');
    spans[0]?.classList.add('rotate-45', 'translate-y-1.5');
    spans[1]?.classList.add('opacity-0');
    spans[2]?.classList.add('-rotate-45', '-translate-y-1.5');
}

function closeMobileMenuFunc() {
    mobileMenu.classList.add('translate-x-full');
    mobileOverlay.classList.add('opacity-0', 'invisible');
    document.body.classList.remove('overflow-hidden');

    const spans = burger.querySelectorAll('span');
    spans[0]?.classList.remove('rotate-45', 'translate-y-1.5');
    spans[1]?.classList.remove('opacity-0');
    spans[2]?.classList.remove('-rotate-45', '-translate-y-1.5');
}

burger?.addEventListener('click', openMobileMenu);
closeMobileMenu?.addEventListener('click', closeMobileMenuFunc);
mobileOverlay?.addEventListener('click', closeMobileMenuFunc);

mobileServicesToggle?.addEventListener('click', (e) => {
    e.preventDefault();
    mobileServicesMenu.classList.toggle('hidden');
    const icon = mobileServicesToggle.querySelector('i');
    icon.classList.toggle('rotate-180');
});

document.querySelectorAll('#mobileMenu a').forEach(link => {
    link.addEventListener('click', closeMobileMenuFunc);
});

// Scroll reveal
const revealElements = document.querySelectorAll('.scroll-reveal');
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

revealElements.forEach(el => observer.observe(el));

// Капча
const captchaDisplay = document.getElementById('captchaDisplay');
const refreshCaptcha = document.getElementById('refreshCaptcha');
const captchaInput = document.getElementById('captchaInput');

function generateCaptcha() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let code = '';
    for (let i = 0; i < 4; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    if (captchaDisplay) captchaDisplay.textContent = code;
    return code;
}

let currentCaptcha = generateCaptcha();

refreshCaptcha?.addEventListener('click', () => {
    currentCaptcha = generateCaptcha();
});

// Отправка формы
const contactForm = document.getElementById('contactForm');
const formSuccess = document.getElementById('formSuccess');

contactForm?.addEventListener('submit', (e) => {
    e.preventDefault();

    if (captchaInput.value.toUpperCase() !== currentCaptcha) {
        alert('Неверный код. Попробуйте снова.');
        currentCaptcha = generateCaptcha();
        captchaInput.value = '';
        return;
    }

    contactForm.classList.add('hidden');
    formSuccess.classList.remove('hidden');
});
