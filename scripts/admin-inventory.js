document.addEventListener('DOMContentLoaded', function() {
  // Only admin can access this page
  Auth.initPage({
    requireAuth: true,
    allowedRoles: ['admin'],
    initSidebar: true
  });

  console.log('Admin Inventory loaded');
});
