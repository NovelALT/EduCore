// Toast notification system
const toast = {
    show(message, type = 'success') {
        const container = document.querySelector('.toast-container') || 
                         document.createElement('div');
        
        if (!document.querySelector('.toast-container')) {
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 
                          type === 'error' ? 'fa-times-circle' : 
                          'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
};

// Modal handling
class Modal {
    constructor() {
        this.isDragging = false;
        this.currentX;
        this.currentY;
        this.initialX;
        this.initialY;
        this.xOffset = 0;
        this.yOffset = 0;
        
        // Bind methods
        this.dragStart = this.dragStart.bind(this);
        this.drag = this.drag.bind(this);
        this.dragEnd = this.dragEnd.bind(this);
    }

    show(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';

        // Add drag functionality
        const modalContent = modal.querySelector('.modal-content');
        modalContent.addEventListener('mousedown', this.dragStart);
        document.addEventListener('mousemove', this.drag);
        document.addEventListener('mouseup', this.dragEnd);

        // Add close button functionality if not exists
        if (!modal.querySelector('.close')) {
            const closeBtn = document.createElement('span');
            closeBtn.className = 'close';
            closeBtn.innerHTML = '&times;';
            closeBtn.onclick = () => this.hide(id);
            modalContent.appendChild(closeBtn);
        }

        // Close on outside click
        modal.onclick = (e) => {
            if (e.target === modal) this.hide(id);
        };

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.hide(id);
        });
    }

    hide(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    dragStart(e) {
        if (e.target.closest('.modal-content') && !e.target.closest('input, button, select, textarea')) {
            this.isDragging = true;
            this.initialX = e.clientX - this.xOffset;
            this.initialY = e.clientY - this.yOffset;
            e.target.closest('.modal-content').classList.add('dragging');
        }
    }

    drag(e) {
        if (this.isDragging) {
            e.preventDefault();
            const modalContent = document.querySelector('.modal-content.dragging');
            if (!modalContent) return;

            this.currentX = e.clientX - this.initialX;
            this.currentY = e.clientY - this.initialY;

            this.xOffset = this.currentX;
            this.yOffset = this.currentY;

            modalContent.style.transform = 
                `translate(${this.currentX}px, ${this.currentY}px)`;
        }
    }

    dragEnd(e) {
        const modalContent = document.querySelector('.modal-content.dragging');
        if (modalContent) {
            modalContent.classList.remove('dragging');
        }
        this.isDragging = false;
    }

    // Confirmation dialog
    confirm(message, options = {}) {
        return new Promise((resolve) => {
            const confirmModal = document.createElement('div');
            confirmModal.className = 'modal';
            confirmModal.id = 'confirmModal';
            confirmModal.innerHTML = `
                <div class="modal-content" style="max-width: 400px;">
                    <div class="modal-header">
                        <h3 class="modal-title">${options.title || 'ยืนยัน'}</h3>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger" id="confirmBtn">
                            ${options.confirmText || 'ยืนยัน'}
                        </button>
                        <button class="btn btn-secondary" id="cancelBtn">
                            ${options.cancelText || 'ยกเลิก'}
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(confirmModal);
            confirmModal.style.display = 'block';

            // Add event listeners
            document.getElementById('confirmBtn').addEventListener('click', () => {
                confirmModal.remove();
                resolve(true);
            });

            document.getElementById('cancelBtn').addEventListener('click', () => {
                confirmModal.remove();
                resolve(false);
            });

            // Close on click outside
            confirmModal.addEventListener('click', (e) => {
                if (e.target === confirmModal) {
                    confirmModal.remove();
                    resolve(false);
                }
            });
        });
    }
}

// Initialize modal
const modal = new Modal();

// AJAX form submission
async function submitForm(formId, url, callback) {
    const form = document.getElementById(formId);
    if (!form) {
        console.error(`Form with id "${formId}" not found`);
        return;
    }

    form.onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(form);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Server response:', data); // Add logging

            if (data.success) {
                toast.show(data.message || 'บันทึกข้อมูลสำเร็จ');
                if (callback) callback(data);
            } else {
                toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            console.error('Submission error:', error);
            toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error.message, 'error');
        }
    };
}

// Add modal utilities
const modalUtils = {
    close: function(modalId) {
        modal.hide(modalId);
    },
    
    resetForm: function(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
            // Clear any custom validation messages
            const errorElements = form.getElementsByClassName('error-message');
            Array.from(errorElements).forEach(el => el.remove());
        }
    },

    setModalData: function(modalId, data) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        Object.keys(data).forEach(key => {
            const input = modal.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = data[key];
            }
        });
    }
};

// Export the modal instance and utilities
window.modal = modal;
window.modalUtils = modalUtils;
window.toast = toast;
window.submitForm = submitForm;
