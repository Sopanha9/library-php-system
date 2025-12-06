<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Library Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#0A84FF',
            secondary: '#5E5CE6',
          }
        }
      }
    }
  </script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
    
    * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
    
    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
      position: relative;
      overflow-x: hidden;
    }
    
    /* Glassmorphism Effects */
    .glass {
      background: rgba(255, 255, 255, 0.7);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    }
    
    .glass-dark {
      background: rgba(30, 41, 59, 0.7);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    
    .glass-blue {
      background: linear-gradient(135deg, rgba(10, 132, 255, 0.15) 0%, rgba(94, 92, 230, 0.15) 100%);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(10, 132, 255, 0.3);
      box-shadow: 0 8px 32px rgba(10, 132, 255, 0.15);
    }
    
    /* Floating Animation */
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }
    
    .float { animation: float 6s ease-in-out infinite; }
    
    /* Card Hover Effects */
    .card-hover {
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card-hover:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 20px 60px rgba(10, 132, 255, 0.25);
    }
    
    /* Smooth Shadows */
    .shadow-glass {
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    
    .shadow-glass-lg {
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12), 0 8px 20px rgba(0, 0, 0, 0.08);
    }
    
    /* iOS-style Buttons */
    .btn-ios {
      background: linear-gradient(135deg, #0A84FF 0%, #5E5CE6 100%);
      box-shadow: 0 4px 15px rgba(10, 132, 255, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .btn-ios:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(10, 132, 255, 0.4);
    }
    
    .btn-ios:active {
      transform: translateY(0px);
    }
    
    /* Gradient Text */
    .text-gradient {
      background: linear-gradient(135deg, #0A84FF 0%, #5E5CE6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    /* Scrollbar */
    ::-webkit-scrollbar { width: 10px; }
    ::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.1); }
    ::-webkit-scrollbar-thumb { 
      background: linear-gradient(135deg, #0A84FF 0%, #5E5CE6 100%);
      border-radius: 10px;
    }
    
    /* Input Focus */
    input:focus, textarea:focus, select:focus {
      outline: none;
      border-color: #0A84FF;
      box-shadow: 0 0 0 4px rgba(10, 132, 255, 0.1);
    }
    
    /* SweetAlert2 Glassmorphism Customization */
    .swal2-popup {
      background: rgba(255, 255, 255, 0.85) !important;
      backdrop-filter: blur(20px) saturate(180%) !important;
      -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
      border: 1px solid rgba(255, 255, 255, 0.3) !important;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
      border-radius: 20px !important;
    }
    
    .swal2-title {
      color: #1f2937 !important;
      font-weight: 700 !important;
      font-size: 1.5rem !important;
    }
    
    .swal2-html-container {
      color: #4b5563 !important;
      font-size: 1rem !important;
    }
    
    .swal2-confirm {
      background: linear-gradient(135deg, #0A84FF 0%, #5E5CE6 100%) !important;
      box-shadow: 0 4px 15px rgba(10, 132, 255, 0.3) !important;
      border-radius: 12px !important;
      padding: 12px 32px !important;
      font-weight: 600 !important;
      border: none !important;
    }
    
    .swal2-confirm:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 8px 25px rgba(10, 132, 255, 0.4) !important;
    }
    
    .swal2-cancel {
      background: rgba(255, 255, 255, 0.7) !important;
      backdrop-filter: blur(10px) !important;
      color: #4b5563 !important;
      border: 1px solid rgba(255, 255, 255, 0.3) !important;
      border-radius: 12px !important;
      padding: 12px 32px !important;
      font-weight: 600 !important;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08) !important;
    }
    
    .swal2-cancel:hover {
      background: rgba(255, 255, 255, 0.9) !important;
      transform: translateY(-2px) !important;
    }
    
    .swal2-icon.swal2-success {
      border-color: #10b981 !important;
      color: #10b981 !important;
    }
    
    .swal2-icon.swal2-error {
      border-color: #ef4444 !important;
      color: #ef4444 !important;
    }
    
    .swal2-icon.swal2-warning {
      border-color: #f59e0b !important;
      color: #f59e0b !important;
    }
    
    .swal2-icon.swal2-info {
      border-color: #0A84FF !important;
      color: #0A84FF !important;
    }
  </style>
</head>
<body class="min-h-screen">
<script>
// Global SweetAlert2 Configuration
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  customClass: {
    popup: 'glass-toast'
  },
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
});

// Confirmation Dialog Helper
function confirmDialog(title, text, confirmText = 'Yes, proceed!', cancelText = 'Cancel') {
  return Swal.fire({
    title: title,
    text: text,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: cancelText,
    reverseButtons: true
  });
}

// Success Alert Helper
function successAlert(title, text) {
  return Swal.fire({
    icon: 'success',
    title: title,
    text: text,
    confirmButtonText: 'Great!'
  });
}

// Error Alert Helper
function errorAlert(title, text) {
  return Swal.fire({
    icon: 'error',
    title: title,
    text: text,
    confirmButtonText: 'OK'
  });
}

// Info Alert Helper
function infoAlert(title, text) {
  return Swal.fire({
    icon: 'info',
    title: title,
    text: text,
    confirmButtonText: 'Got it'
  });
}
</script>