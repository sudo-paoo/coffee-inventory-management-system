document.addEventListener('DOMContentLoaded', function() {
  // Both admin and staff can access dashboard
  Auth.initPage({
    requireAuth: true,
    allowedRoles: ['admin', 'staff'],
    initSidebar: true
  });

  console.log('Dashboard loaded for:', Auth.getUserRole());
});
