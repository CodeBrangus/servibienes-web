// ========== PANEL DE ADMINISTRACIÓN ==========
class AdminPanel {
    constructor() {
        this.initSidebar();
        this.initDataTables();
        this.initFileUploads();
        this.initNotifications();
    }
    
    initSidebar() {
        const menuItems = document.querySelectorAll('.menu-item');
        const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
        
        menuItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && href.includes(currentPage)) {
                item.classList.add('active');
            }
            
            item.addEventListener('click', (e) => {
                if (href === 'logout.php') {
                    if (!confirm('¿Está seguro de cerrar sesión?')) {
                        e.preventDefault();
                    }
                }
            });
        });
        
        // Toggle sidebar en móviles
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('collapsed');
            });
        }
    }
    
    initDataTables() {
        // Inicializar tablas con ordenación y búsqueda
        const tables = document.querySelectorAll('.data-table');
        
        tables.forEach(table => {
            const headers = table.querySelectorAll('th[data-sort]');
            
            headers.forEach(header => {
                header.style.cursor = 'pointer';
                header.addEventListener('click', () => {
                    const column = header.getAttribute('data-sort');
                    const direction = header.getAttribute('data-direction') || 'asc';
                    this.sortTable(table, column, direction);
                    
                    // Cambiar dirección
                    header.setAttribute('data-direction', 
                        direction === 'asc' ? 'desc' : 'asc');
                });
            });
            
            // Búsqueda
            const searchInput = document.getElementById('tableSearch');
            if (searchInput) {
                searchInput.addEventListener('keyup', () => {
                    this.filterTable(table, searchInput.value);
                });
            }
        });
    }
    
    sortTable(table, column, direction) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aValue = a.querySelector(`td[data-column="${column}"]`)?.textContent || '';
            const bValue = b.querySelector(`td[data-column="${column}"]`)?.textContent || '';
            
            if (direction === 'asc') {
                return aValue.localeCompare(bValue);
            } else {
                return bValue.localeCompare(aValue);
            }
        });
        
        // Reordenar filas
        rows.forEach(row => tbody.appendChild(row));
    }
    
    filterTable(table, searchTerm) {
        const rows = table.querySelectorAll('tbody tr');
        searchTerm = searchTerm.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }
    
    initFileUploads() {
        const dropzones = document.querySelectorAll('.dropzone');
        
        dropzones.forEach(dropzone => {
            const input = dropzone.querySelector('input[type="file"]');
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => {
                    dropzone.classList.add('dragover');
                });
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => {
                    dropzone.classList.remove('dragover');
                });
            });
            
            dropzone.addEventListener('drop', (e) => {
                if (input) {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change'));
                }
            });
            
            dropzone.addEventListener('click', () => {
                if (input) input.click();
            });
            
            if (input) {
                input.addEventListener('change', (e) => {
                    const files = Array.from(e.target.files);
                    this.updateFileList(dropzone, files);
                });
            }
        });
    }
    
    updateFileList(dropzone, files) {
        const fileList = dropzone.querySelector('.file-list') || 
                        this.createFileList(dropzone);
        
        fileList.innerHTML = '';
        
        files.forEach(file => {
            const item = document.createElement('div');
            item.className = 'file-item';
            item.innerHTML = `
                <span>${file.name}</span>
                <span>(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                <button type="button" class="remove-file">×</button>
            `;
            fileList.appendChild(item);
        });
    }
    
    createFileList(dropzone) {
        const fileList = document.createElement('div');
        fileList.className = 'file-list';
        dropzone.appendChild(fileList);
        return fileList;
    }
    
    initNotifications() {
        // Verificar notificaciones cada 30 segundos
        setInterval(() => {
            this.checkNotifications();
        }, 30000);
        
        // Verificar al cargar
        this.checkNotifications();
    }
    
    async checkNotifications() {
        try {
            const response = await fetch('api/notifications.php');
            const data = await response.json();
            
            if (data.pending > 0) {
                this.showNotification(data.pending);
            }
        } catch (error) {
            console.error('Error al verificar notificaciones:', error);
        }
    }
    
    showNotification(count) {
        // Mostrar badge en el ícono de clientes
        const clientLink = document.querySelector('a[href="clients.php"]');
        if (clientLink) {
            let badge = clientLink.querySelector('.notification-badge');
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'notification-badge';
                clientLink.appendChild(badge);
            }
            badge.textContent = count;
            badge.style.display = 'inline';
        }
        
        // Mostrar notificación toast
        if (count > 0 && !document.querySelector('.notification-toast')) {
            this.showToast(`Tienes ${count} formulario(s) pendiente(s)`);
        }
    }
    
    showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-bell"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
    
    // Métodos de utilidad
    confirmAction(message) {
        return confirm(message);
    }
    
    showLoading() {
        const loader = document.createElement('div');
        loader.className = 'loading-overlay';
        loader.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(loader);
        return loader;
    }
    
    hideLoading(loader) {
        if (loader) loader.remove();
    }
    
    showMessage(type, message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `alert alert-${type}`;
        messageDiv.textContent = message;
        
        document.querySelector('.main-content').prepend(messageDiv);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }
}

// ========== EDITOR EN LÍNEA ==========
class InlineEditor {
    constructor() {
        this.editableElements = document.querySelectorAll('[data-editable]');
        this.init();
    }
    
    init() {
        this.editableElements.forEach(element => {
            element.addEventListener('dblclick', () => this.editElement(element));
        });
    }
    
    editElement(element) {
        const originalContent = element.innerHTML;
        const inputType = element.getAttribute('data-type') || 'text';
        
        let input;
        if (inputType === 'textarea') {
            input = document.createElement('textarea');
            input.value = element.textContent;
            input.rows = 5;
        } else {
            input = document.createElement('input');
            input.type = inputType;
            input.value = element.textContent;
        }
        
        input.className = 'inline-edit';
        
        element.innerHTML = '';
        element.appendChild(input);
        input.focus();
        
        const save = () => {
            const newValue = input.value.trim();
            if (newValue && newValue !== originalContent) {
                this.saveChanges(element, newValue);
            } else {
                element.innerHTML = originalContent;
            }
        };
        
        input.addEventListener('blur', save);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && inputType !== 'textarea') {
                e.preventDefault();
                save();
            }
            if (e.key === 'Escape') {
                element.innerHTML = originalContent;
            }
        });
    }
    
    async saveChanges(element, newValue) {
        const field = element.getAttribute('data-field');
        const id = element.getAttribute('data-id');
        const table = element.getAttribute('data-table');
        
        try {
            const response = await fetch('api/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    table: table,
                    id: id,
                    field: field,
                    value: newValue
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                element.textContent = newValue;
                this.showMessage('success', 'Cambios guardados');
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            console.error('Error al guardar:', error);
            this.showMessage('error', 'Error al guardar cambios');
        }
    }
    
    showMessage(type, message) {
        // Implementar notificación
    }
}

// ========== SUBIDA DE ARCHIVOS MULTIPLES ==========
class MultiUpload {
    constructor(uploadForm) {
        this.form = uploadForm;
        this.progressBar = null;
        this.init();
    }
    
    init() {
        if (!this.form) return;
        
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        const fileInput = this.form.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.updateFileCount(e));
        }
    }
    
    updateFileCount(e) {
        const count = e.target.files.length;
        const countDisplay = this.form.querySelector('.file-count') || 
                           this.createCountDisplay();
        
        countDisplay.textContent = `${count} archivo(s) seleccionado(s)`;
    }
    
    createCountDisplay() {
        const display = document.createElement('div');
        display.className = 'file-count';
        this.form.appendChild(display);
        return display;
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        const files = this.form.querySelector('input[type="file"]').files;
        if (files.length === 0) {
            alert('Por favor seleccione al menos un archivo');
            return;
        }
        
        const formData = new FormData(this.form);
        
        // Mostrar barra de progreso
        this.showProgressBar();
        
        try {
            const response = await fetch(this.form.action, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showMessage('success', `${result.count} archivo(s) subido(s) correctamente`);
                
                // Recargar la página después de 1 segundo
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error(result.error || 'Error al subir archivos');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showMessage('error', error.message);
        } finally {
            this.hideProgressBar();
        }
    }
    
    showProgressBar() {
        this.progressBar = document.createElement('div');
        this.progressBar.className = 'upload-progress';
        this.progressBar.innerHTML = `
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <span>Subiendo archivos...</span>
        `;
        document.body.appendChild(this.progressBar);
    }
    
    hideProgressBar() {
        if (this.progressBar) {
            this.progressBar.remove();
            this.progressBar = null;
        }
    }
    
    showMessage(type, message) {
        // Implementar notificación
    }
}

// ========== INICIALIZACIÓN DEL PANEL ==========
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar panel admin
    window.adminPanel = new AdminPanel();
    
    // Inicializar editor en línea si existe
    if (document.querySelector('[data-editable]')) {
        window.inlineEditor = new InlineEditor();
    }
    
    // Inicializar subida múltiple
    const uploadForms = document.querySelectorAll('form[data-multi-upload]');
    uploadForms.forEach(form => {
        new MultiUpload(form);
    });
    
    // Auto-save para formularios
    const autoSaveForms = document.querySelectorAll('form[data-autosave]');
    autoSaveForms.forEach(form => {
        form.addEventListener('input', debounce(() => {
            // Implementar auto-save
        }, 1000));
    });
    
    // Confirmación para acciones importantes
    document.querySelectorAll('[data-confirm]').forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 
                          '¿Está seguro de realizar esta acción?';
            
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
    
    // Tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            
            this._tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
});

// Función debounce para auto-save
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}