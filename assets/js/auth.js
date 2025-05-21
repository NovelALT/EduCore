// สร้างเม็ดอนิเมชั่น
function createParticles() {
    const particles = document.querySelector('.particles');
    const particleCount = 50;
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.width = Math.random() * 5 + 'px';
        particle.style.height = particle.style.width;
        particle.style.left = Math.random() * 100 + 'vw';
        particle.style.top = Math.random() * 100 + 'vh';
        particle.style.animationDelay = Math.random() * 5 + 's';
        particles.appendChild(particle);
    }
}

// เช็ค Password
function initializePasswordToggles() {
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            const ripple = document.createElement('div');
            ripple.className = 'ripple';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 1000);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.classList.add('show');
                icon.style.transform = 'scale(1.1) rotate(180deg)';
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.classList.remove('show');
                icon.style.transform = 'scale(1) rotate(0deg)';
            }
            
            input.focus();
        });
    });
}

// Alert toast
function showToast(type, message) {
    const toast = document.getElementById('toast');
    toast.className = `toast ${type}`;
    toast.querySelector('.toast-message').textContent = message;
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// เช็คความปลอดภัยของ Password
function checkPasswordStrength(password) {
    const strength = {
        score: 0,
        hasLower: /[a-z]/.test(password),
        hasUpper: /[A-Z]/.test(password),
        hasNumber: /\d/.test(password),
        hasSpecial: /[!@#$%^&*]/.test(password),
        isLongEnough: password.length >= 8
    };
    
    strength.score = Object.values(strength).filter(Boolean).length - 1;
    
    if (password.length === 0) return 'empty';
    
    const levels = {
        1: { class: 'weak', text: 'อ่อน', width: '33%' },
        2: { class: 'medium', text: 'ปานกลาง', width: '66%' },
        3: { class: 'strong', text: 'แข็งแรง', width: '100%' }
    };
    
    return levels[strength.score] || levels[1];
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    createParticles();
    initializePasswordToggles();

    // Add keyboard shortcut for password toggle
    document.addEventListener('keydown', function(e) {
        if (e.altKey && e.key.toLowerCase() === 'p') {
            e.preventDefault();
            document.querySelector('.toggle-password').click();
        }
    });

    // Handle login form
    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const button = this.querySelector('.login-btn');
            button.classList.add('loading');
            
            setTimeout(() => {
                button.classList.remove('loading');
                showToast('success', 'เข้าสู่ระบบสำเร็จ!');
                // window.location.href = '/dashboard';
            }, 2000);
        });
    }

    // Handle register form
    const registerForm = document.querySelector('.register-form');
    if (registerForm) {
        const validateInput = (input) => {
            const errorClass = 'input-error';
            let isValid = true;
            
            switch(input.type) {
                case 'email':
                    isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
                    break;
                case 'password':
                    if (input.id === 'confirm_password') {
                        const password = document.getElementById('password');
                        isValid = input.value === password.value;
                    } else {
                        isValid = checkPasswordStrength(input.value).class !== 'weak';
                    }
                    break;
                default:
                    isValid = input.value.length >= 2;
            }
            
            if (!isValid) {
                input.parentElement.classList.add(errorClass);
            } else {
                input.parentElement.classList.remove(errorClass);
            }
            
            return isValid;
        };

        registerForm.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => validateInput(input));
            input.addEventListener('blur', () => validateInput(input));
        });

        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                showToast('error', 'รหัสผ่านไม่ตรงกัน');
                return;
            }
            
            if (checkPasswordStrength(password).class === 'weak') {
                showToast('error', 'รหัสผ่านไม่ปลอดภัยเพียงพอ');
                return;
            }

            const button = this.querySelector('.register-btn');
            button.classList.add('loading');
            
            setTimeout(() => {
                button.classList.remove('loading');
                showToast('success', 'สมัครสมาชิกสำเร็จ!');
                // window.location.href = '/login';
            }, 2000);
        });
    }
});
