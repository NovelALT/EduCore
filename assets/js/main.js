document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const navbar = document.querySelector('.navbar');
    const navLinks = document.querySelector('.nav-links');
    
    menuBtn.addEventListener('click', () => {
        navbar.classList.toggle('menu-open');
        navLinks.classList.toggle('active');
    });

    // Close menu when clicking on a link
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            navbar.classList.remove('menu-open');
            navLinks.classList.remove('active');
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!navLinks.contains(e.target) && !menuBtn.contains(e.target)) {
            navbar.classList.remove('menu-open');
            navLinks.classList.remove('active');
        }
    });

    // Mobile menu toggle
    document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
        this.classList.toggle('active');
        document.querySelector('.nav-links').classList.toggle('active');
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-links') && !e.target.closest('.mobile-menu-btn')) {
            document.querySelector('.nav-links').classList.remove('active');
            document.querySelector('.mobile-menu-btn').classList.remove('active');
        }
    });

    // Close mobile menu when clicking nav links
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            document.querySelector('.nav-links').classList.remove('active');
            document.querySelector('.mobile-menu-btn').classList.remove('active');
        });
    });

    // Typing animation
    const texts = document.querySelectorAll('.typing-text');
    texts.forEach((text, index) => {
        text.style.animation = `typing 3.5s steps(40, end) ${index * 3.5}s forwards`;
        text.style.opacity = '0';
        setTimeout(() => {
            text.style.opacity = '1';
        }, index * 3500);
    });

    // Add particle.js configuration
    particlesJS('particles-js', {
        particles: {
            number: { value: 80 },
            color: { value: '#ffffff' },
            shape: { type: 'circle' },
            opacity: {
                value: 0.5,
                random: true
            },
            size: {
                value: 3,
                random: true
            },
            move: {
                enable: true,
                speed: 2,
                direction: 'none',
                random: true,
                out_mode: 'out'
            }
        },
        interactivity: {
            detect_on: 'canvas',
            events: {
                onhover: {
                    enable: true,
                    mode: 'repulse'
                },
                onclick: {
                    enable: true,
                    mode: 'push'
                }
            }
        }
    });

    // Button particle effect
    document.querySelectorAll('.btn-primary').forEach(button => {
        button.addEventListener('mousemove', e => {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            button.style.setProperty('--x', `${x}px`);
            button.style.setProperty('--y', `${y}px`);
        });
    });

    // Add mouse movement effect for tech badges
    document.querySelectorAll('.tech-badge').forEach(badge => {
        badge.addEventListener('mousemove', (e) => {
            const rect = badge.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            
            badge.querySelector('.badge-inner').style.setProperty('--mouse-x', `${x}%`);
            badge.querySelector('.badge-inner').style.setProperty('--mouse-y', `${y}%`);
        });
    });

    // Reset mouse effect when mouse leaves
    document.querySelectorAll('.tech-badge').forEach(badge => {
        badge.addEventListener('mouseleave', () => {
            badge.querySelector('.badge-inner').style.setProperty('--mouse-x', '50%');
            badge.querySelector('.badge-inner').style.setProperty('--mouse-y', '50%');
        });
    });

    // Tech badges enhanced effects
    document.querySelectorAll('.tech-badge').forEach(badge => {
        badge.addEventListener('mousemove', (e) => {
            const rect = badge.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            
            const inner = badge.querySelector('.badge-inner');
            inner.style.setProperty('--mouse-x', `${x}%`);
            inner.style.setProperty('--mouse-y', `${y}%`);
            
            // Add 3D rotation effect
            const rotateY = (x - 50) * 0.1;
            const rotateX = (y - 50) * -0.05;
            inner.style.transform = `
                translateZ(30px)
                rotateX(${rotateX}deg)
                rotateY(${rotateY}deg)
            `;
        });

        badge.addEventListener('mouseleave', () => {
            const inner = badge.querySelector('.badge-inner');
            inner.style.transform = 'translateZ(0) rotateX(0) rotateY(0)';
        });
    });

    // Initialize Typed.js with null checks
    const typedElement = document.getElementById('typed');
    const typedStringsElement = document.getElementById('typed-strings');
    
    if (typedElement && typedStringsElement) {
        new Typed('#typed', {
            stringsElement: '#typed-strings',
            typeSpeed: 50,
            backSpeed: 30,
            backDelay: 2000,
            loop: true,
            showCursor: true,
        });
    }

    // Console animation with null check
    const consoleAnimation = document.getElementById('console-animation');
    if (consoleAnimation) {
        const messages = [
            '🎉 Welcome to Codly!',
            'Learning HTML... ✓',
            'Learning CSS... ✓',
            'Learning JavaScript... ✓',
            '🌟 Ready to create amazing things!'
        ];

        let messageIndex = 0;

        function animateConsole() {
            if (messageIndex < messages.length) {
                consoleAnimation.textContent = messages[messageIndex];
                messageIndex++;
                setTimeout(animateConsole, 2000);
            }
        }

        animateConsole();
    }

    // Code editor animations
    const runBtn = document.getElementById('runCode');
    const consoleOutputEditor = document.getElementById('consoleOutput');
    
    if (runBtn && consoleOutputEditor) {
        const editorMessages = [
            'Starting your journey... ⏳',
            'Learning HTML... ⚡ Done!',
            'Practicing CSS... 🎨 Done!',
            'Mastering JavaScript... 🚀 Done!',
            '✨ คุณพร้อมที่จะเป็น Developer แล้ว!'
        ];
        
        let editorMessageIndex = 0;
        
        runBtn.addEventListener('click', function() {
            this.disabled = true;
            this.style.opacity = '0.7';
            editorMessageIndex = 0;
            
            const typeMessage = () => {
                if (editorMessageIndex < editorMessages.length) {
                    consoleOutputEditor.textContent = editorMessages[editorMessageIndex];
                    editorMessageIndex++;
                    setTimeout(typeMessage, 1500);
                } else {
                    runBtn.disabled = false;
                    runBtn.style.opacity = '1';
                }
            };
            
            typeMessage();
        });
    }

    // Code typing effect
    const codeEditorElement = document.querySelector('.editor-content code');
    if (codeEditorElement) {
        const lines = codeEditorElement.querySelectorAll('.line');
        lines.forEach((line, index) => {
            line.style.animation = `slideFadeIn 0.5s forwards ${index * 0.1}s`;
        });
    }

    // FAQ Accordion
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const faqItem = question.parentElement;
            const isActive = faqItem.classList.contains('active');
            
            // Close all FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
                const answer = item.querySelector('.faq-answer');
                answer.style.maxHeight = '0';
            });

            // Open clicked item if it wasn't active
            if (!isActive) {
                faqItem.classList.add('active');
                const answer = faqItem.querySelector('.faq-answer');
                answer.style.maxHeight = answer.scrollHeight + 'px';
            }
        });
    });

    // FAQ Search Functionality
    const faqSearch = document.querySelector('.search-box input');
    if (faqSearch) {
        faqSearch.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question span').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = '';
                    item.style.animation = 'fadeIn 0.5s ease forwards';
                } else {
                    item.style.animation = 'fadeOut 0.5s ease forwards';
                    setTimeout(() => {
                        if (!question.includes(searchTerm) && !answer.includes(searchTerm)) {
                            item.style.display = 'none';
                        }
                    }, 500);
                }
            });
        });
    }

    // Dropdown Mobile Toggle
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    
    if (dropdownToggle && dropdownMenu) {
        dropdownToggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.style.display = 'none';
            }
        });
    }

    // Auth dropdown mobile toggle
    const authBtn = document.querySelector('.auth-btn');
    const authDropdown = document.querySelector('.auth-dropdown');
    
    if (authBtn && authDropdown) {
        authBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            authDropdown.classList.toggle('show');
        });

        // Close auth dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!authBtn.contains(e.target) && !authDropdown.contains(e.target)) {
                authDropdown.classList.remove('show');
            }
        });

        // Close auth dropdown when menu closes
        menuBtn.addEventListener('click', function() {
            authDropdown.classList.remove('show');
        });
    }
});