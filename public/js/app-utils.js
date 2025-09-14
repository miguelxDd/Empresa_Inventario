// SweetAlert2 configuration and utilities
window.Swal2Config = {
    // Configuración global de SweetAlert2
    defaults: {
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-primary me-2',
            cancelButton: 'btn btn-secondary'
        }
    },

    // Función para mostrar mensajes de éxito
    success: function(title, text = '') {
        return Swal.fire({
            icon: 'success',
            title: title,
            text: text,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            ...this.defaults
        });
    },

    // Función para mostrar mensajes de error
    error: function(title, text = '') {
        return Swal.fire({
            icon: 'error',
            title: title,
            text: text,
            ...this.defaults
        });
    },

    // Función para mostrar mensajes de información
    info: function(title, text = '') {
        return Swal.fire({
            icon: 'info',
            title: title,
            text: text,
            ...this.defaults
        });
    },

    // Función para mostrar mensajes de advertencia
    warning: function(title, text = '') {
        return Swal.fire({
            icon: 'warning',
            title: title,
            text: text,
            ...this.defaults
        });
    },

    // Función para confirmaciones
    confirm: function(title, text = '', confirmText = 'Sí, continuar') {
        return Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: confirmText,
            reverseButtons: true,
            ...this.defaults
        });
    },

    // Función para mostrar loading
    loading: function(title = 'Procesando...') {
        return Swal.fire({
            title: title,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
};

// DataTables configuración en español
window.DataTablesSpanish = {
    language: {
        decimal: "",
        emptyTable: "No hay datos disponibles en la tabla",
        info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
        infoEmpty: "Mostrando 0 a 0 de 0 entradas",
        infoFiltered: "(filtrado de _MAX_ entradas totales)",
        infoPostFix: "",
        thousands: ",",
        lengthMenu: "Mostrar _MENU_ entradas",
        loadingRecords: "Cargando...",
        processing: "Procesando...",
        search: "Buscar:",
        zeroRecords: "No se encontraron registros coincidentes",
        paginate: {
            first: "Primero",
            last: "Último",
            next: "Siguiente",
            previous: "Anterior"
        },
        aria: {
            sortAscending: ": activar para ordenar la columna ascendente",
            sortDescending: ": activar para ordenar la columna descendente"
        }
    }
};

// Funciones compatibles con el código existente
window.showLoading = function(title = 'Procesando...') {
    return Swal.fire({
        title: title,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

window.hideLoading = function() {
    Swal.close();
};

window.showToast = function(message, type = 'success') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: type,
        title: message
    });
};

// Función auxiliar para manejar errores AJAX
window.handleAjaxError = function(xhr, status, error) {
    let message = 'Error en la comunicación con el servidor';
    
    if (xhr.responseJSON && xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
    } else if (xhr.responseText) {
        try {
            const response = JSON.parse(xhr.responseText);
            message = response.message || message;
        } catch (e) {
            message = xhr.statusText || message;
        }
    }

    Swal2Config.error('Error', message);
};

// Configurar CSRF token para todas las peticiones AJAX
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});
