// Sample user credentials 
// ! (In production, this should be on the server)
const SAMPLE_USERS = {
  admin: {
    email: 'admin@brew-kenhearted.com',
    password: 'admin123',
    role: 'admin',
    name: 'Admin User',
    avatar: 'A'
  },
  staff: {
    email: 'staff@brew-kenhearted.com',
    password: 'staff123',
    role: 'staff',
    name: 'Staff User',
    avatar: 'S'
  }
};

// Auth utility object
const Auth = {
  login(email, password) {
    // Find matching user
    const user = Object.values(SAMPLE_USERS).find(
      u => u.email === email && u.password === password
    );

    if (user) {
      // Store user data in sessionStorage
      const userData = {
        email: user.email,
        role: user.role,
        name: user.name,
        avatar: user.avatar,
        loginTime: new Date().toISOString()
      };
      
      sessionStorage.setItem('currentUser', JSON.stringify(userData));
      return userData;
    }

    return null;
  },

  // Logout current user
  logout() {
    sessionStorage.removeItem('currentUser');
    window.location.href = '/login.html';
  },

  // Get current logged in user
  getCurrentUser() {
    const userStr = sessionStorage.getItem('currentUser');
    return userStr ? JSON.parse(userStr) : null;
  },

  // Check if user is logged in
  isLoggedIn() {
    return this.getCurrentUser() !== null;
  },

  /**
   * Get current user's role
   * @returns {string|null} 'admin' or 'staff' or null
   */
  getUserRole() {
    const user = this.getCurrentUser();
    return user ? user.role : null;
  },
  
  // Check if current user is admin
  isAdmin() {
    return this.getUserRole() === 'admin';
  },

  // Check if current user is staff
  isStaff() {
    return this.getUserRole() === 'staff';
  },

  // Require authentication - redirect to login if not logged in
  requireAuth() {
    if (!this.isLoggedIn()) {
      window.location.href = '/login.html';
      return false;
    }
    return true;
  },

  // Require specific role - redirect to 403 if not authorized
  requireRole(allowedRoles) {
    if (!this.requireAuth()) return false;

    const roles = Array.isArray(allowedRoles) ? allowedRoles : [allowedRoles];
    const userRole = this.getUserRole();

    if (!roles.includes(userRole)) {
      window.location.href = '/403.html';
      return false;
    }

    return true;
  },
  
  // Get role-specific route
  getRoleBasedRoute(page) {
    const role = this.getUserRole();
    
    // Shared pages (both admin and staff)
    if (page === 'dashboard') {
      return '/pages/dashboard.html';
    }
    if (page === 'settings') {
      return '/pages/settings.html';
    }

    // Role-specific pages
    if (page === 'inventory') {
      return role === 'admin' 
        ? '/pages/admin/inventory.html' 
        : '/pages/staff/inventory.html';
    }
    if (page === 'transactions') {
      return role === 'admin' 
        ? '/pages/admin/transactions.html' 
        : '/pages/staff/transactions.html';
    }

    return '#';
  },

  // Initialize sidebar navigation with role-based routes
  initSidebarNavigation() {
    if (!this.isLoggedIn()) return;

    const menuItems = document.querySelectorAll('.sidebar-menu .menu-item');
    
    menuItems.forEach(item => {
      const span = item.querySelector('span');
      if (!span) return;

      const text = span.textContent.trim().toLowerCase();
      const route = this.getRoleBasedRoute(text);
      
      if (route !== '#') {
        item.href = route;
      }
    });
  },

  // Update user info in sidebar
  updateUserInfo() {
    const user = this.getCurrentUser();
    if (!user) return;

    // Update avatar
    const avatarEl = document.querySelector('.user-avatar');
    if (avatarEl) {
      avatarEl.textContent = user.avatar;
    }

    // Update name
    const nameEl = document.querySelector('.user-info .name');
    if (nameEl) {
      nameEl.textContent = user.name;
    }

    // Update email
    const emailEl = document.querySelector('.user-info .email');
    if (emailEl) {
      emailEl.textContent = user.email;
    }
  },

  // Initialize auth for a page
  initPage(options = {}) {
    const {
      requireAuth = true,
      allowedRoles = null,
      initSidebar = true
    } = options;

    // Check authentication
    if (requireAuth && !this.requireAuth()) {
      return;
    }

    // Check role authorization
    if (allowedRoles && !this.requireRole(allowedRoles)) {
      return;
    }

    // Initialize sidebar
    if (initSidebar) {
      this.initSidebarNavigation();
      this.updateUserInfo();
    }
  }
};

window.Auth = Auth;