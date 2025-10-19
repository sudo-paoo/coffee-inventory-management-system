document.addEventListener('DOMContentLoaded', function() {
  // Both admin and staff can access settings
  Auth.initPage({
    requireAuth: true,
    allowedRoles: ['admin', 'staff'],
    initSidebar: true
  });

  console.log('Settings loaded for:', Auth.getUserRole());
});
