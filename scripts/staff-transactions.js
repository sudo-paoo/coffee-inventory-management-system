document.addEventListener('DOMContentLoaded', function() {
  // Only staff can access this page
  Auth.initPage({
    requireAuth: true,
    allowedRoles: ['staff'],
    initSidebar: true
  });

  console.log('Staff Transactions loaded');
});
