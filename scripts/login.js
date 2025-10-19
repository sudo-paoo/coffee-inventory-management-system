document.addEventListener('DOMContentLoaded', function() {
  // Check if already logged in
  if (Auth.isLoggedIn()) {
    // Redirect to dashboard if already logged in
    window.location.href = '/pages/dashboard.html';
    return;
  }

  const loginForm = document.querySelector('form');
  
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
      
      // Attempt login
      const user = Auth.login(email, password);
      
      if (user) {
        // Login successful - redirect to dashboard
        window.location.href = '/pages/dashboard.html';
      } else {
        // Login failed - show error message
        showError('Invalid email or password. Please try again.');
      }
    });
  }
});

/**
 * Display error message to user
 */
function showError(message) {
  // Remove existing error if any
  const existingError = document.querySelector('.login-error');
  if (existingError) {
    existingError.remove();
  }

  // Create error message element
  const errorDiv = document.createElement('div');
  errorDiv.className = 'login-error';
  errorDiv.style.cssText = `
    background-color: #fee;
    color: #c33;
    padding: 12px 16px;
    border-radius: 4px;
    margin-bottom: 16px;
    border: 1px solid #fcc;
    font-size: 14px;
  `;
  errorDiv.textContent = message;

  // Insert error before the form
  const form = document.querySelector('form');
  form.parentNode.insertBefore(errorDiv, form);

  // Auto-remove after 5 seconds
  setTimeout(() => {
    errorDiv.remove();
  }, 5000);
}
