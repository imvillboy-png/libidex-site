document.addEventListener('DOMContentLoaded', function() {
    const burgerBtn = document.querySelector('.header__burger-btn');
    const popupMenu = document.querySelector('.popup-menu');
    const closeBtn = document.querySelector('.popup-menu__close-btn');
    const menuLinks = document.querySelectorAll('.popup-menu__link, .menu__link');
    const phoneInputs = document.querySelectorAll('.phone-input');
    const forms = document.querySelectorAll('form');

    if (burgerBtn && popupMenu) {
        burgerBtn.addEventListener('click', function() {
            popupMenu.classList.add('popup-menu_show');
        });
    }

    if (closeBtn && popupMenu) {
        closeBtn.addEventListener('click', function() {
            popupMenu.classList.remove('popup-menu_show');
        });
    }

    menuLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            const href = link.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                popupMenu.classList.remove('popup-menu_show');
            }
        });
    });

    phoneInputs.forEach(function(input) {
        input.value = '+91';
        
        input.addEventListener('input', function(e) {
            let value = e.target.value;
            
            if (!value.startsWith('+91')) {
                if (value.startsWith('+')) {
                    value = '+91' + value.replace(/\D/g, '').replace(/^91/, '');
                } else {
                    value = '+91' + value.replace(/\D/g, '');
                }
            }
            
            let digits = value.replace(/\D/g, '');
            if (digits.length > 12) {
                digits = digits.substring(0, 12);
            }
            
            e.target.value = '+' + digits;
        });
        
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' || e.key === 'Delete') {
                if (input.value === '+91' || input.value === '+9' || input.value === '+') {
                    e.preventDefault();
                    input.value = '+91';
                }
            }
        });
        
        input.addEventListener('focus', function(e) {
            if (!e.target.value) {
                e.target.value = '+91';
            }
        });
    });

    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const phoneInput = form.querySelector('input[name="phone"]');
            const phone = phoneInput ? phoneInput.value : '';
            const phoneRegex = /^\+91[0-9]{10}$/;
            
            if (!phoneRegex.test(phone)) {
                alert('कृपया एक वैध 10 अंकों का फ़ोन नंबर दर्ज करें।');
                phoneInput.focus();
                return;
            }
            
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'जमा हो रहा है...';
            submitBtn.disabled = true;
            
            fetch('order.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    alert(data.message);
                    form.reset();
                    phoneInputs.forEach(function(input) {
                        input.value = '+91';
                    });
                } else {
                    alert(data.message);
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                alert('सिस्टम में त्रुटि। कृपया बाद में पुनः प्रयास करें।');
            })
            .finally(function() {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    });
});
