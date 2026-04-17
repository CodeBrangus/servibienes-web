class FileUploader {
    constructor(options = {}) {
        this.options = {
            maxFiles: 10,
            maxSize: 5 * 1024 * 1024, // 5MB
            allowedTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            uploadUrl: 'process/upload.php',
            ...options
        };
        
        this.init();
    }
    
    init() {
        // Inicializar dropzones
        document.querySelectorAll('.dropzone').forEach(dropzone => {
            this.setupDropzone(dropzone);
        });
        
        // Inicializar inputs de archivo
        document.querySelectorAll('input[type="file"][multiple]').forEach(input => {
            this.setupFileInput(input);
        });
    }
    
    setupDropzone(dropzone) {
        const input = dropzone.querySelector('input[type="file"]');
        
        // Eventos de drag & drop
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
            const files = Array.from(e.dataTransfer.files);
            this.handleFiles(files, input);
        });
        
        dropzone.addEventListener('click', () => {
            if (input) input.click();
        });
        
        if (input) {
            input.addEventListener('change', (e) => {
                const files = Array.from(e.target.files);
                this.handleFiles(files, input);
            });
        }
    }
    
    setupFileInput(input) {
        input.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            this.handleFiles(files, input);
        });
    }
    
    handleFiles(files, input) {
        // Validar número máximo de archivos
        if (files.length > this.options.maxFiles) {
            this.showError(`Máximo ${this.options.maxFiles} archivos permitidos`);
            return;
        }
        
        // Validar cada archivo
        const validFiles = [];
        const errors = [];
        
        files.forEach(file => {
            // Validar tipo
            if (!this.options.allowedTypes.includes(file.type)) {
                errors.push(`Tipo no permitido: ${file.name}`);
                return;
            }
            
            // Validar tamaño
            if (file.size > this.options.maxSize) {
                errors.push(`Archivo muy grande: ${file.name} (Máx: ${this.formatBytes(this.options.maxSize)})`);
                return;
            }
            
            validFiles.push(file);
        });
        
        // Mostrar errores
        if (errors.length > 0) {
            this.showError(errors.join('<br>'));
        }
        
        // Mostrar vista previa
        if (validFiles.length > 0) {
            this.showPreview(validFiles, input);
            
            // Crear DataTransfer para mantener los archivos
            const dataTransfer = new DataTransfer();
            validFiles.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
        }
    }
    
    showPreview(files, input) {
        // Buscar contenedor de vista previa
        let previewContainer = input.parentNode.querySelector('.file-preview');
        
        if (!previewContainer) {
            previewContainer = document.createElement('div');
            previewContainer.className = 'file-preview';
            previewContainer.style.marginTop = '10px';
            input.parentNode.appendChild(previewContainer);
        }
        
        previewContainer.innerHTML = '';
        
        files.forEach((file, index) => {
            const preview = document.createElement('div');
            preview.className = 'file-preview-item';
            preview.style.cssText = `
                display: inline-block;
                margin: 5px;
                position: relative;
                width: 80px;
                height: 80px;
                border-radius: 5px;
                overflow: hidden;
                border: 1px solid #ddd;
            `;
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else if (file.type.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = URL.createObjectURL(file);
                video.controls = true;
                video.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
                preview.appendChild(video);
            } else {
                preview.textContent = file.name;
                preview.style.cssText += 'display: flex; align-items: center; justify-content: center; font-size: 12px;';
            }
            
            // Botón para eliminar
            const removeBtn = document.createElement('button');
            removeBtn.innerHTML = '×';
            removeBtn.style.cssText = `
                position: absolute;
                top: 2px;
                right: 2px;
                background: #e74c3c;
                color: white;
                border: none;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                font-size: 12px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            `;
            
            removeBtn.addEventListener('click', () => {
                this.removeFile(index, input);
                preview.remove();
            });
            
            preview.appendChild(removeBtn);
            previewContainer.appendChild(preview);
        });
    }
    
    removeFile(index, input) {
        const files = Array.from(input.files);
        files.splice(index, 1);
        
        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
    }
    
    async uploadFiles(formData, progressCallback = null) {
        try {
            const response = await fetch(this.options.uploadUrl, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(`${result.count} archivo(s) subido(s) correctamente`);
                return result;
            } else {
                throw new Error(result.error || 'Error al subir archivos');
            }
        } catch (error) {
            this.showError(error.message);
            throw error;
        }
    }
    
    showError(message) {
        this.showMessage('error', message);
    }
    
    showSuccess(message) {
        this.showMessage('success', message);
    }
    
    showMessage(type, message) {
        // Crear elemento de mensaje
        const messageDiv = document.createElement('div');
        messageDiv.className = `upload-message upload-${type}`;
        messageDiv.innerHTML = message;
        messageDiv.style.cssText = `
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        `;
        
        if (type === 'error') {
            messageDiv.style.background = '#f8d7da';
            messageDiv.style.color = '#721c24';
            messageDiv.style.border = '1px solid #f5c6cb';
        } else {
            messageDiv.style.background = '#d4edda';
            messageDiv.style.color = '#155724';
            messageDiv.style.border = '1px solid #c3e6cb';
        }
        
        // Insertar al principio del body
        document.body.insertBefore(messageDiv, document.body.firstChild);
        
        // Auto-eliminar después de 5 segundos
        setTimeout(() => {
            messageDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => messageDiv.remove(), 300);
        }, 5000);
    }
    
    formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.fileUploader = new FileUploader();
    
    // Añadir estilos CSS para animaciones
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateY(0);
                opacity: 1;
            }
            to {
                transform: translateY(-100%);
                opacity: 0;
            }
        }
        
        .dragover {
            border-color: #0f6fb1 !important;
            background: rgba(15, 111, 177, 0.1) !important;
        }
    `;
    document.head.appendChild(style);
});